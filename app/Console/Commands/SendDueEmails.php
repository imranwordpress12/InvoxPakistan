<?php

namespace App\Console\Commands;

use App\Mail\PaymentDue;
use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendDueEmails extends Command
{
    protected $signature = 'transactions:send-due-emails';

    protected $description = 'Send due emails for pending transactions whose due date has been reached.';

    public function handle(): int
    {
        $transactionIds = Transaction::query()
            ->where('status', Transaction::STATUS_PENDING)
            ->where('due_at', '<=', now())
            ->whereNull('due_email_sent_at')
            ->pluck('id');

        $sent = 0;
        $skipped = 0;

        foreach ($transactionIds as $transactionId) {
            DB::transaction(function () use ($transactionId, &$sent, &$skipped): void {
                $transaction = Transaction::query()
                    ->whereKey($transactionId)
                    ->lockForUpdate()
                    ->with(['company', 'subscription'])
                    ->first();

                if (! $transaction || $transaction->status !== Transaction::STATUS_PENDING || $transaction->due_email_sent_at !== null) {
                    return;
                }

                if ($transaction->subscription && $transaction->subscription->status === Subscription::STATUS_INACTIVE) {
                    $skipped++;

                    return;
                }

                $transaction->due_email_sent_at = now();
                $transaction->save();

                try {
                    Mail::to($transaction->company->email)->send(new PaymentDue($transaction));
                    $sent++;
                } catch (\Throwable $e) {
                    // Ignore email send failure
                }
            });
        }

        $this->info("Sent {$sent} due email(s); skipped {$skipped}.");

        return self::SUCCESS;
    }
}
