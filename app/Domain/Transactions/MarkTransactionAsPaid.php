<?php

namespace App\Domain\Transactions;

use App\Domain\Audit\AuditLogger;
use App\Domain\Subscriptions\SubscriptionPeriod;
use App\Domain\Transactions\Exceptions\TransactionAlreadyProcessedException;
use App\Mail\PaymentThankYou;
use App\Models\Subscription;
use App\Models\Transaction;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * The atomic "Pay Now / Mark Unpaid Transaction as Paid" flow:
 * validate transaction is Pending or Overdue -> mark paid -> update subscription to Active ->
 * send Thank You email -> generate exactly ONE next transaction (Due if future, Overdue if past) -> write audit log.
 */
class MarkTransactionAsPaid
{
    public function handle(
        Transaction $transaction,
        ?string $notes = null,
        ?string $paymentScreenshot = null,
        ?float $amount = null,
        ?string $subscriptionType = null,
        ?CarbonInterface $startsAt = null
    ): Transaction {
        return DB::transaction(function () use ($transaction, $notes, $paymentScreenshot, $amount, $subscriptionType, $startsAt) {
            $locked = Transaction::whereKey($transaction->id)->lockForUpdate()->first();

            if (! $locked || ! in_array($locked->status, [Transaction::STATUS_PENDING, Transaction::STATUS_OVERDUE])) {
                throw new TransactionAlreadyProcessedException(
                    "Transaction #{$transaction->id} is not eligible for payment."
                );
            }

            $subscription = Subscription::whereKey($locked->subscription_id)->lockForUpdate()->firstOrFail();

            // Check if there is a newer unpaid transaction superseding this one
            $hasNewerUnpaid = Transaction::where('subscription_id', $subscription->id)
                ->where('id', '>', $locked->id)
                ->whereIn('status', [Transaction::STATUS_PENDING, Transaction::STATUS_OVERDUE])
                ->exists();

            if ($hasNewerUnpaid) {
                throw new TransactionAlreadyProcessedException(
                    "Transaction #{$transaction->id} is not the latest unpaid transaction."
                );
            }

            $originalSubscriptionValues = $subscription->only(['status', 'starts_at', 'ends_at']);
            $oldTxStatus = $locked->status;

            // Determine updated transaction parameters
            $txType = $subscriptionType ?? $locked->subscription_type;
            $txAmount = $amount !== null ? $amount : $locked->amount;
            $txStart = $startsAt ?? $locked->billing_period_start;
            $txEnd = SubscriptionPeriod::endDateFor($txType, $txStart);

            $locked->forceFill([
                'subscription_type' => $txType,
                'amount' => $txAmount,
                'billing_period_start' => $txStart,
                'billing_period_end' => $txEnd,
                'due_at' => $txEnd,
                'status' => Transaction::STATUS_PAID,
                'paid_at' => now(),
                'notes' => filled($notes) ? $notes : $locked->notes,
                'payment_screenshot' => $paymentScreenshot ?: $locked->payment_screenshot,
            ])->save();

            // Update Subscription to Active
            $subscription->forceFill([
                'status' => Subscription::STATUS_ACTIVE,
                'type' => $txType,
                'amount' => $txAmount,
                'starts_at' => $txStart,
                'ends_at' => $txEnd,
            ])->save();

            // Send Payment Successful -> Thank You Email (Rule 13: email failure must NOT break subscription logic)
            try {
                Mail::to($locked->company->email)->send(new PaymentThankYou($locked));
            } catch (\Throwable $e) {
                // Ignore mail sending failure
            }

            // Create exactly ONE next transaction (Rules 6, 7, 16, 31)
            $nextStart = $txEnd->copy()->addDay()->startOfDay();
            $nextEnd = SubscriptionPeriod::endDateFor($subscription->type, $nextStart);
            $nextStatus = $nextEnd->isPast() ? Transaction::STATUS_OVERDUE : Transaction::STATUS_DUE;

            $alreadyCreated = Transaction::where('subscription_id', $subscription->id)
                ->where('id', '>', $locked->id)
                ->exists();

            if (! $alreadyCreated) {
                $subscription->transactions()->create([
                    'company_id' => $subscription->company_id,
                    'invoice_number' => InvoiceNumberGenerator::generate(),
                    'transaction_type' => Transaction::TYPE_RENEWAL,
                    'subscription_type' => $subscription->type,
                    'amount' => $subscription->amount,
                    'status' => $nextStatus,
                    'billing_period_start' => $nextStart,
                    'billing_period_end' => $nextEnd,
                    'due_at' => $nextEnd,
                    'paid_at' => null,
                    'notes' => null,
                ]);
            }

            AuditLogger::log(
                action: 'transaction.marked_paid',
                module: 'transactions',
                description: "Transaction \"{$locked->invoice_number}\" marked paid; subscription renewed.",
                company: $locked->company,
                old: [
                    'transaction_status' => $oldTxStatus,
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
