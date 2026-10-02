<?php

namespace Tests\Feature\Console;

use App\Mail\PaymentDue;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendDueEmailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_due_email_for_pending_transaction_when_due_date_reached(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-20 09:00:00'));
        Mail::fake();

        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create(['status' => Subscription::STATUS_ACTIVE]);
        $transaction = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => Transaction::STATUS_PENDING,
            'due_at' => Carbon::parse('2026-09-20 00:00:00'),
            'due_email_sent_at' => null,
        ]);

        $this->artisan('transactions:send-due-emails')->assertExitCode(0);

        Mail::assertSent(PaymentDue::class, 1);
        Mail::assertSent(PaymentDue::class, fn (PaymentDue $mail) => $mail->hasTo($company->email));
        $this->assertNotNull($transaction->refresh()->due_email_sent_at);

        // Run again; should not duplicate email
        $this->artisan('transactions:send-due-emails')->assertExitCode(0);
        Mail::assertSent(PaymentDue::class, 1);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }
}
