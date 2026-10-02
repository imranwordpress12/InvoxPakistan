<?php

namespace Tests\Feature\Console;

use App\Mail\PaymentDue;
use App\Mail\PaymentReminder;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

use Tests\TestCase;

class SchedulerAndInvoiceRestrictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_7_day_reminder_transitions_due_to_pending_and_sends_reminder_email_once(): void
    {
        Mail::fake();

        $company = Company::factory()->create();
        $sub = Subscription::factory()->create([
            'company_id' => $company->id,
            'status' => Subscription::STATUS_ACTIVE,
        ]);

        // Transaction ending in 5 days (within the 7-day reminder window)
        $tx = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $sub->id,
            'status' => Transaction::STATUS_DUE,
            'billing_period_start' => Carbon::parse('2026-11-01'),
            'billing_period_end' => Carbon::parse('2026-11-30'),
            'due_at' => Carbon::parse('2026-11-30'),
            'reminder_sent_at' => null,
        ]);

        // Travel to 24-Nov-2026 (6 days before end date 30-Nov)
        Carbon::setTestNow('2026-11-24');

        $this->artisan('transactions:send-payment-reminders')->assertExitCode(0);

        $tx->refresh();
        $this->assertSame(Transaction::STATUS_PENDING, $tx->status);
        $this->assertNotNull($tx->reminder_sent_at);

        Mail::assertSent(PaymentReminder::class, 1);

        // Run scheduler command again — duplicate prevention ensures no duplicate email
        $this->artisan('transactions:send-payment-reminders')->assertExitCode(0);
        Mail::assertSent(PaymentReminder::class, 1);

        Carbon::setTestNow();
    }

    public function test_due_date_email_sends_on_end_date_once_and_keeps_status_pending(): void
    {
        Mail::fake();

        $company = Company::factory()->create();
        $sub = Subscription::factory()->create([
            'company_id' => $company->id,
            'status' => Subscription::STATUS_ACTIVE,
        ]);

        $tx = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $sub->id,
            'status' => Transaction::STATUS_PENDING,
            'billing_period_start' => Carbon::parse('2026-11-01'),
            'billing_period_end' => Carbon::parse('2026-11-30'),
            'due_at' => Carbon::parse('2026-11-30'),
            'due_email_sent_at' => null,
        ]);

        // Travel to End Date 30-Nov-2026
        Carbon::setTestNow('2026-11-30 10:00:00');

        $this->artisan('transactions:send-due-emails')->assertExitCode(0);

        $tx->refresh();
        $this->assertSame(Transaction::STATUS_PENDING, $tx->status);
        $this->assertNotNull($tx->due_email_sent_at);

        Mail::assertSent(PaymentDue::class, 1);

        // Run command again — duplicate prevention ensures no duplicate email
        $this->artisan('transactions:send-due-emails')->assertExitCode(0);
        Mail::assertSent(PaymentDue::class, 1);

        Carbon::setTestNow();
    }

    public function test_day_after_end_date_transitions_pending_to_overdue(): void
    {
        $company = Company::factory()->create();
        $sub = Subscription::factory()->create([
            'company_id' => $company->id,
            'status' => Subscription::STATUS_ACTIVE,
        ]);

        $tx = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $sub->id,
            'status' => Transaction::STATUS_PENDING,
            'billing_period_start' => Carbon::parse('2026-11-01'),
            'billing_period_end' => Carbon::parse('2026-11-30'),
            'due_at' => Carbon::parse('2026-11-30'),
        ]);

        // Travel to 01-Dec-2026 (Day after End Date)
        Carbon::setTestNow('2026-12-01 01:00:00');

        $this->artisan('subscriptions:process-expired')->assertExitCode(0);

        $tx->refresh();
        $this->assertSame(Transaction::STATUS_OVERDUE, $tx->status);

        Carbon::setTestNow();
    }

    public function test_invoice_restriction_blocks_inactive_subscription_and_overdue_transaction(): void
    {
        $company = Company::factory()->create();
        $companyUser = User::factory()->company($company)->create();

        // 1. Inactive Subscription -> Blocked
        $sub = Subscription::factory()->create([
            'company_id' => $company->id,
            'status' => Subscription::STATUS_INACTIVE,
        ]);

        $response = $this->actingAs($companyUser)->get(route('company.invoices.create'));
        $response->assertRedirect(route('company.subscription-required'));

        // 2. Active Subscription + Overdue Transaction -> Blocked
        $sub->update(['status' => Subscription::STATUS_ACTIVE]);

        $overdueTx = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $sub->id,
            'status' => Transaction::STATUS_OVERDUE,
            'billing_period_start' => Carbon::parse('2026-10-01'),
            'billing_period_end' => Carbon::parse('2026-10-31'),
        ]);

        $response = $this->actingAs($companyUser)->get(route('company.invoices.create'));
        $response->assertRedirect(route('company.subscription-required'));

        // 3. Paid Overdue Transaction -> Unblocked
        $overdueTx->update(['status' => Transaction::STATUS_PAID]);

        $response = $this->actingAs($companyUser)->get(route('company.invoices.create'));
        $response->assertOk();
    }
}
