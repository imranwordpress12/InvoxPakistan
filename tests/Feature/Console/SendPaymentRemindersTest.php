<?php

namespace Tests\Feature\Console;

use App\Mail\PaymentReminder;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendPaymentRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_one_reminder_to_every_customer_with_an_email_exactly_seven_days_before_due(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-14 09:00:00'));
        Mail::fake();

        $company = Company::factory()->create();
        $firstCustomer = Customer::factory()->for($company)->create(['email' => 'first@example.test']);
        $secondCustomer = Customer::factory()->for($company)->create(['email' => 'second@example.test']);
        $subscription = Subscription::factory()->for($company)->pending()->create();
        $transaction = Transaction::factory()->pending()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'due_at' => Carbon::parse('2026-09-21 15:30:00'),
            'reminder_sent_at' => null,
        ]);

        $this->artisan('transactions:send-payment-reminders')->assertExitCode(0);

        Mail::assertSent(PaymentReminder::class, 2);
        Mail::assertSent(PaymentReminder::class, fn (PaymentReminder $mail) => $mail->hasTo($firstCustomer->email));
        Mail::assertSent(PaymentReminder::class, fn (PaymentReminder $mail) => $mail->hasTo($secondCustomer->email));
        $this->assertNotNull($transaction->refresh()->reminder_sent_at);

        $this->artisan('transactions:send-payment-reminders')->assertExitCode(0);
        Mail::assertSent(PaymentReminder::class, 2);
    }

    public function test_it_does_not_remind_paid_or_not_yet_due_transactions(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-14 09:00:00'));
        Mail::fake();

        $company = Company::factory()->create();
        Customer::factory()->for($company)->create();
        $subscription = Subscription::factory()->for($company)->create();

        Transaction::factory()->pending()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'due_at' => now()->addDays(8),
        ]);
        Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => Transaction::STATUS_PAID,
            'due_at' => now()->addDays(7),
            'paid_at' => now(),
        ]);

        $this->artisan('transactions:send-payment-reminders')->assertExitCode(0);

        Mail::assertNothingSent();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }
}
