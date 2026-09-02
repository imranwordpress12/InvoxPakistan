<?php

namespace App\Domain\Transactions;

use App\Domain\Audit\AuditLogger;
use App\Domain\Subscriptions\SubscriptionRenewal;
use App\Domain\Transactions\Exceptions\TransactionAlreadyProcessedException;
use App\Domain\Subscriptions\SubscriptionPeriod;
use App\Domain\Transactions\InvoiceNumberGenerator;
use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

/**
 * The atomic "Mark Pending Transaction as Paid" flow (PRD #26/#53):
 * validate the transaction is still pending -> mark it paid -> renew the
 * subscription -> write one audit log entry covering both changes -> commit,
 * or roll everything back.
 */
class MarkTransactionAsPaid
{
    public function handle(Transaction $transaction, ?string $notes = null): Transaction
    {
        return DB::transaction(function () use ($transaction, $notes) {
            // Re-fetch under a row lock rather than trusting the caller's
            // (possibly stale) $transaction instance — guards against two
            // concurrent requests both marking the same transaction paid.
            $locked = Transaction::whereKey($transaction->id)->lockForUpdate()->first();

            if (! $locked || $locked->status !== Transaction::STATUS_PENDING) {
                throw new TransactionAlreadyProcessedException(
                    "Transaction #{$transaction->id} is no longer pending."
                );
            }

            $subscription = Subscription::whereKey($locked->subscription_id)->lockForUpdate()->firstOrFail();
            $latestTransaction = $subscription->transactions()->latest('id')->first();

            if (! $latestTransaction || $latestTransaction->isNot($locked)) {
                throw new TransactionAlreadyProcessedException(
                    "Transaction #{$transaction->id} is not the latest transaction."
                );
            }

            $originalSubscriptionValues = $subscription->only(['status', 'starts_at', 'ends_at']);

            $locked->forceFill([
                'status' => Transaction::STATUS_PAID,
                'paid_at' => now(),
                'notes' => filled($notes) ? $notes : $locked->notes,
            ])->save();

            (new SubscriptionRenewal)->renew($subscription, $locked->paid_at);

            $nextDueAt = $locked->due_at?->copy() ?? $locked->paid_at->copy();
            $nextDueAt = $subscription->type === Subscription::TYPE_MONTHLY
                ? $nextDueAt->addMonth()
                : $nextDueAt->addYear();
            $nextPeriodEnd = SubscriptionPeriod::endDateFor($subscription->type, $nextDueAt);

            $alreadyCreated = $subscription->transactions()
                ->where('status', Transaction::STATUS_PENDING)
                ->where('due_at', $nextDueAt)
                ->exists();

            if (! $alreadyCreated) {
                $subscription->transactions()->create([
                    'company_id' => $subscription->company_id,
                    'invoice_number' => InvoiceNumberGenerator::generate(),
                    'transaction_type' => Transaction::TYPE_RENEWAL,
                    'subscription_type' => $subscription->type,
                    'amount' => $subscription->amount,
                    'status' => Transaction::STATUS_PENDING,
                    'billing_period_start' => $nextDueAt,
                    'billing_period_end' => $nextPeriodEnd,
                    'due_at' => $nextDueAt,
                    'paid_at' => null,
                    'notes' => null,
                ]);
            }

            // One entry covers both changes (PRD #53 shows a single "Create
            // audit log" step for the whole flow) — description calls out
            // the subscription renewal explicitly so "Subscription Renewed"
            // (PRD #33/#54) is represented without a separate, redundant row.
            AuditLogger::log(
                action: 'transaction.marked_paid',
                module: 'transactions',
                description: "Transaction \"{$locked->invoice_number}\" marked paid; subscription renewed.",
                company: $locked->company,
                old: [
                    'transaction_status' => Transaction::STATUS_PENDING,
                    'subscription_status' => $originalSubscriptionValues['status'],
                    'subscription_starts_at' => (string) $originalSubscriptionValues['starts_at'],
                    'subscription_ends_at' => (string) $originalSubscriptionValues['ends_at'],
                ],
                new: [
                    'transaction_status' => Transaction::STATUS_PAID,
                    'subscription_status' => $subscription->status,
                    'subscription_starts_at' => (string) $subscription->starts_at,
                    'subscription_ends_at' => (string) $subscription->ends_at,
                ],
            );

            return $locked->refresh();
        });
    }
}
