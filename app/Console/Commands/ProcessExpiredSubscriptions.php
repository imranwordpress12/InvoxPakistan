<?php

namespace App\Console\Commands;

use App\Domain\Audit\AuditLogger;
use App\Domain\Subscriptions\SubscriptionPeriod;
use App\Domain\Transactions\InvoiceNumberGenerator;
use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * PRD #49/#50: for every subscription that's still stored as `active` but
 * has lapsed, mark it `pending` and create its renewal invoice — unless an
 * unpaid one already exists for it. Scheduled hourly in routes/console.php
 * with `withoutOverlapping()`, but this command is also self-protecting:
 * each subscription is processed in its own row-locked transaction with a
 * fresh re-check, so running it twice back-to-back (PRD #64 case 7) or by
 * hand alongside the schedule can't create a duplicate invoice or a
 * duplicate "lapsed" audit entry.
 */
class ProcessExpiredSubscriptions extends Command
{
    protected $signature = 'subscriptions:process-expired';

    protected $description = 'Mark lapsed active subscriptions as pending and create their renewal invoice.';

    public function handle(): int
    {
        $subscriptionIds = Subscription::query()->needingExpiryProcessing()->pluck('id');

        $this->info("Found {$subscriptionIds->count()} lapsed subscription(s) to process.");

        $markedPending = 0;
        $invoicesCreated = 0;
        $alreadyHadAnInvoice = 0;

        foreach ($subscriptionIds as $subscriptionId) {
            DB::transaction(function () use ($subscriptionId, &$markedPending, &$invoicesCreated, &$alreadyHadAnInvoice) {
                // Re-fetch under a row lock and re-check — a previous
                // iteration, a concurrent run, or a manual action may have
                // already moved this subscription along since it was
                // listed above.
                $subscription = Subscription::whereKey($subscriptionId)->lockForUpdate()->first();

                if (! $subscription || $subscription->status !== Subscription::STATUS_ACTIVE || ! $subscription->hasLapsed()) {
                    return;
                }

                $originalStatus = $subscription->status;
                $subscription->status = Subscription::STATUS_PENDING;
                $subscription->save();
                $markedPending++;

                AuditLogger::log(
                    action: 'subscription.expired',
                    module: 'subscriptions',
                    description: "Subscription for \"{$subscription->company->name}\" lapsed; marked pending.",
                    company: $subscription->company,
                    old: ['status' => $originalStatus],
                    new: ['status' => $subscription->status],
                );

                // PRD #50: never create a second unpaid renewal transaction
                // for the same subscription.
                if ($subscription->transactions()->where('status', Transaction::STATUS_PENDING)->exists()) {
                    $alreadyHadAnInvoice++;

                    return;
                }

                $billingStart = $subscription->ends_at->copy()->addDay();

                $subscription->transactions()->create([
                    'company_id' => $subscription->company_id,
                    'invoice_number' => InvoiceNumberGenerator::generate(),
                    'transaction_type' => Transaction::TYPE_RENEWAL,
                    'subscription_type' => $subscription->type,
                    'amount' => $subscription->amount,
                    'status' => Transaction::STATUS_PENDING,
                    'billing_period_start' => $billingStart,
                    'billing_period_end' => SubscriptionPeriod::endDateFor($subscription->type, $billingStart),
                    // Due the moment the subscription actually lapsed, not
                    // whenever this command happened to run.
                    'due_at' => $subscription->ends_at,
                    'paid_at' => null,
                    'notes' => null,
                ]);
                $invoicesCreated++;
            });
        }

        $this->info("Marked {$markedPending} subscription(s) pending; created {$invoicesCreated} renewal invoice(s); {$alreadyHadAnInvoice} already had one pending.");

        return self::SUCCESS;
    }
}
