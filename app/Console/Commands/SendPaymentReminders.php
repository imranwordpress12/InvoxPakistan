<?php

namespace App\Console\Commands;

use App\Mail\PaymentReminder;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendPaymentReminders extends Command
{
    protected $signature = 'transactions:send-payment-reminders';

    protected $description = 'Send payment reminders for unpaid transactions due in seven days.';

    public function handle(): int
    {
        $dueDateLimit = now()->addDays(7)->endOfDay()->toDateTimeString();
        $transactionIds = Transaction::query()
            ->whereIn('status', [Transaction::STATUS_DUE, Transaction::STATUS_PENDING])
            ->where('due_at', '<=', $dueDateLimit)
            ->whereNull('reminder_sent_at')
            ->pluck('id');

        $sent = 0;
        $skipped = 0;

        foreach ($transactionIds as $transactionId) {
            DB::transaction(function () use ($transactionId, &$sent, &$skipped): void {
                $transaction = Transaction::query()
                    ->whereKey($transactionId)
                    ->lockForUpdate()
                    ->with(['company.customers', 'subscription'])
                    ->first();

                if (! $transaction || $transaction->reminder_sent_at !== null) {
                    return;
                }

                // If subscription is inactive, stop transaction processing
                if ($transaction->subscription && $transaction->subscription->status === \App\Models\Subscription::STATUS_INACTIVE) {
                    $skipped++;
                    return;
                }

                // Change status Due -> Pending
                $transaction->status = Transaction::STATUS_PENDING;
                $transaction->reminder_sent_at = now();
                $transaction->save();

                // Send email to company email
                try {
                    Mail::to($transaction->company->email)->send(new PaymentReminder($transaction));
                    $sent++;
                } catch (\Throwable $e) {
                    // Ignore email error
                }

                // Also send to registered customer emails if present
                $customers = $transaction->company->customers
                    ->filter(fn ($customer) => filled($customer->email));

                foreach ($customers as $customer) {
                    try {
                        Mail::to($customer->email)->send(new PaymentReminder($transaction, $customer));
                        $sent++;
                    } catch (\Throwable $e) {
                        // Ignore email error
                    }
                }
            });
        }

        $this->info("Sent {$sent} payment reminder(s); processed transition Due -> Pending.");

        return self::SUCCESS;
    }
}
