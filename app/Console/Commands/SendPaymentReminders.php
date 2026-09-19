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
        $dueDate = now()->addDays(7)->toDateString();
        $transactionIds = Transaction::query()
            ->where('status', Transaction::STATUS_PENDING)
            ->whereDate('due_at', $dueDate)
            ->whereNull('reminder_sent_at')
            ->pluck('id');

        $sent = 0;
        $skipped = 0;

        foreach ($transactionIds as $transactionId) {
            DB::transaction(function () use ($transactionId, &$sent, &$skipped): void {
                $transaction = Transaction::query()
                    ->whereKey($transactionId)
                    ->lockForUpdate()
                    ->with(['company.customers'])
                    ->first();

                if (! $transaction || $transaction->status !== Transaction::STATUS_PENDING || $transaction->reminder_sent_at !== null) {
                    return;
                }

                $customers = $transaction->company->customers
                    ->filter(fn ($customer) => filled($customer->email));

                if ($customers->isEmpty()) {
                    $skipped++;

                    return;
                }

                // Claim the transaction before sending so overlapping scheduler
                // runs cannot send a second reminder for the same transaction.
                $transaction->reminder_sent_at = now();
                $transaction->save();

                foreach ($customers as $customer) {
                    Mail::to($customer->email)->send(new PaymentReminder($transaction, $customer));
                    $sent++;
                }
            });
        }

        $this->info("Sent {$sent} payment reminder(s); skipped {$skipped} transaction(s) without a customer email.");

        return self::SUCCESS;
    }
}
