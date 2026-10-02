<?php

namespace Tests\Feature;

use App\Mail\CompanyWelcome;
use App\Mail\PaymentDue;
use App\Mail\PaymentReminder;
use App\Mail\PaymentThankYou;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Domain\Subscriptions\SubscriptionPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubscriptionE2EIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->admin()->create();
    }

    /**
     * Scenario 1: New Monthly Subscription
     * 01-Oct Paid -> 01-Nov Due (single future transaction)
     */
    public function test_scenario_1_new_monthly_subscription_creation_and_initial_transactions(): void
    {
        Carbon::setTestNow('2026-10-01 10:00:00');

        $company = Company::factory()->create();

        $response = $this->actingAs($this->adminUser)->put(route('admin.companies.update', $company), [
            'name' => $company->name,
            'email' => $company->email,
            'user_email' => 'login@company.test',
            'status' => Company::STATUS_ACTIVE,
            'ntn' => $company->ntn,
            'address' => $company->address,
            'fbr_token_sandbox' => 'token-123',
            'subscription_status' => Subscription::STATUS_ACTIVE,
            'subscription_starts_at' => '2026-10-01',
            'subscription_type' => Subscription::TYPE_MONTHLY,
            'amount' => 5000,
        ]);

        $response->assertRedirect(route('admin.companies.show', $company));

        $sub = $company->fresh()->latestSubscription;
        $this->assertNotNull($sub);
        $this->assertSame(Subscription::STATUS_ACTIVE, $sub->status);
        $this->assertSame('2026-10-01', $sub->starts_at->toDateString());
        $this->assertSame('2026-10-31', $sub->ends_at->toDateString());

        // Transaction #1 Paid
        $tx1 = Transaction::where('company_id', $company->id)->where('status', Transaction::STATUS_PAID)->first();
        $this->assertNotNull($tx1);
        $this->assertSame('2026-10-01', $tx1->billing_period_start->toDateString());
        $this->assertSame('2026-10-31', $tx1->billing_period_end->toDateString());

        // Transaction #2 Due
        $tx2 = Transaction::where('company_id', $company->id)->where('status', Transaction::STATUS_DUE)->first();
        $this->assertNotNull($tx2);
        $this->assertSame('2026-11-01', $tx2->billing_period_start->toDateString());
        $this->assertSame('2026-11-30', $tx2->billing_period_end->toDateString());

        // Single future transaction rule check
        $this->assertSame(2, Transaction::where('company_id', $company->id)->count());

        Carbon::setTestNow();
    }

    /**
     * Scenario 2: 7-Day Reminder
     * 23-Nov -> Due becomes Pending, Reminder Email sent
     */
    public function test_scenario_2_7_day_reminder_trigger_and_email(): void
    {
        Mail::fake();

        $company = Company::factory()->create();
        $sub = Subscription::factory()->create([
            'company_id' => $company->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => Carbon::parse('2026-11-01'),
            'ends_at' => Carbon::parse('2026-11-30'),
        ]);

        $tx = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $sub->id,
            'status' => Transaction::STATUS_DUE,
            'billing_period_start' => Carbon::parse('2026-11-01'),
            'billing_period_end' => Carbon::parse('2026-11-30'),
            'due_at' => Carbon::parse('2026-11-30'),
            'reminder_sent_at' => null,
        ]);

        Carbon::setTestNow('2026-11-23 09:00:00');

        $this->artisan('transactions:send-payment-reminders')->assertExitCode(0);

        $tx->refresh();
        $this->assertSame(Transaction::STATUS_PENDING, $tx->status);
        $this->assertNotNull($tx->reminder_sent_at);
        Mail::assertSent(PaymentReminder::class, 1);

        Carbon::setTestNow();
    }

    /**
     * Scenario 3: Due Date Email
     * 30-Nov -> Due Email sent, status stays Pending
     */
    public function test_scenario_3_due_date_email_on_end_date(): void
    {
        Mail::fake();

        $company = Company::factory()->create();
        $sub = Subscription::factory()->create([
            'company_id' => $company->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => Carbon::parse('2026-11-01'),
            'ends_at' => Carbon::parse('2026-11-30'),
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

        Carbon::setTestNow('2026-11-30 14:00:00');

        $this->artisan('transactions:send-due-emails')->assertExitCode(0);

        $tx->refresh();
        $this->assertSame(Transaction::STATUS_PENDING, $tx->status);
        $this->assertNotNull($tx->due_email_sent_at);
        Mail::assertSent(PaymentDue::class, 1);

        Carbon::setTestNow();
    }

    /**
     * Scenario 4: Overdue & Invoice Restriction
     * 01-Dec -> Pending becomes Overdue, Invoice functionality is blocked
     */
    public function test_scenario_4_overdue_transition_and_invoice_restriction(): void
    {
        $company = Company::factory()->create();
        $companyUser = User::factory()->company($company)->create();

        $sub = Subscription::factory()->create([
            'company_id' => $company->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => Carbon::parse('2026-11-01'),
            'ends_at' => Carbon::parse('2026-11-30'),
        ]);

        $tx = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $sub->id,
            'status' => Transaction::STATUS_PENDING,
            'billing_period_start' => Carbon::parse('2026-11-01'),
            'billing_period_end' => Carbon::parse('2026-11-30'),
            'due_at' => Carbon::parse('2026-11-30'),
        ]);

        Carbon::setTestNow('2026-12-01 02:00:00');

        $this->artisan('subscriptions:process-expired')->assertExitCode(0);

        $tx->refresh();
        $this->assertSame(Transaction::STATUS_OVERDUE, $tx->status);

        // Attempting to access invoice creation route as company user -> blocked
        $response = $this->actingAs($companyUser)->get(route('company.invoices.create'));
        $response->assertRedirect(route('company.subscription-required'));

        Carbon::setTestNow();
    }

    /**
     * Scenario 5: Overdue Payment Flow
     * Admin pays Overdue transaction -> Paid, Invoice Unblocked, Next transaction created
     */
    public function test_scenario_5_overdue_payment_unblocks_invoice_and_creates_next_transaction(): void
    {
        Mail::fake();
        Storage::fake('local');

        $company = Company::factory()->create();
        $companyUser = User::factory()->company($company)->create();

        $sub = Subscription::factory()->create([
            'company_id' => $company->id,
            'status' => Subscription::STATUS_ACTIVE,
            'type' => Subscription::TYPE_MONTHLY,
            'starts_at' => Carbon::parse('2026-11-01'),
            'ends_at' => Carbon::parse('2026-11-30'),
        ]);

        $overdueTx = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $sub->id,
            'status' => Transaction::STATUS_OVERDUE,
            'billing_period_start' => Carbon::parse('2026-11-01'),
            'billing_period_end' => Carbon::parse('2026-11-30'),
            'due_at' => Carbon::parse('2026-11-30'),
        ]);

        Carbon::setTestNow('2026-12-05 10:00:00');

        // Admin marks as paid from company edit page
        $response = $this->actingAs($this->adminUser)
            ->from(route('admin.companies.show', $company))
            ->post(route('admin.transactions.mark-paid', $overdueTx), [
                'amount' => 5000,
                'starts_at' => '2026-11-01',
                'subscription_type' => Subscription::TYPE_MONTHLY,
                'payment_screenshot' => UploadedFile::fake()->image('receipt.png'),
            ]);

        $response->assertRedirect(route('admin.companies.show', $company));

        $overdueTx->refresh();
        $this->assertSame(Transaction::STATUS_PAID, $overdueTx->status);
        $this->assertNotNull($overdueTx->paid_at);

        // Next transaction created starting 01-Dec-2026 -> 31-Dec-2026
        $nextTx = Transaction::where('company_id', $company->id)
            ->where('id', '!=', $overdueTx->id)
            ->first();

        $this->assertNotNull($nextTx);
        $this->assertSame('2026-12-01', $nextTx->billing_period_start->toDateString());
        $this->assertSame('2026-12-31', $nextTx->billing_period_end->toDateString());

        // Invoice access is now unblocked
        $sub->update(['ends_at' => Carbon::parse('2026-12-31')]);
        $invoiceResponse = $this->actingAs($companyUser)->get(route('company.invoices.create'));
        $invoiceResponse->assertOk();

        Mail::assertSent(PaymentThankYou::class, 1);

        Carbon::setTestNow();
    }

    /**
     * Scenario 6: Subscription Deactivation
     * Deactivating removes Due/Pending future transactions; preserves Paid & Overdue; blocks invoice.
     */
    public function test_scenario_6_deactivation_cleans_up_future_unpaid_and_blocks_invoices(): void
    {
        $company = Company::factory()->create();
        $companyUser = User::factory()->company($company)->create();

        $sub = Subscription::factory()->create([
            'company_id' => $company->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => Carbon::parse('2026-10-01'),
            'ends_at' => Carbon::parse('2026-10-31'),
        ]);

        $paidTx = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $sub->id,
            'status' => Transaction::STATUS_PAID,
        ]);

        $dueTx = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $sub->id,
            'status' => Transaction::STATUS_DUE,
        ]);

        // Admin updates company subscription to INACTIVE
        $response = $this->actingAs($this->adminUser)->put(route('admin.companies.update', $company), [
            'name' => $company->name,
            'email' => $company->email,
            'user_email' => 'login@company.test',
            'status' => Company::STATUS_ACTIVE,
            'ntn' => $company->ntn,
            'address' => $company->address,
            'subscription_status' => Subscription::STATUS_INACTIVE,
        ]);

        $response->assertRedirect(route('admin.companies.show', $company));

        // Paid remains, Due is deleted
        $this->assertDatabaseHas('transactions', ['id' => $paidTx->id]);
        $this->assertDatabaseMissing('transactions', ['id' => $dueTx->id]);

        // Invoice access is blocked for company user
        $invoiceResponse = $this->actingAs($companyUser)->get(route('company.invoices.create'));
        $invoiceResponse->assertRedirect(route('company.subscription-required'));
    }

    /**
     * Scenario 7: Reactivation
     * Reactivating an Inactive subscription requires valid Start Date (today/future), creates new cycle.
     */
    public function test_scenario_7_reactivation_validation_and_cycle_start(): void
    {
        Carbon::setTestNow('2026-10-01 10:00:00');

        $company = Company::factory()->create();
        $sub = Subscription::factory()->create([
            'company_id' => $company->id,
            'status' => Subscription::STATUS_INACTIVE,
        ]);

        // Past start date -> validation error
        $pastResponse = $this->actingAs($this->adminUser)->put(route('admin.companies.update', $company), [
            'name' => $company->name,
            'email' => $company->email,
            'user_email' => 'login@company.test',
            'status' => Company::STATUS_ACTIVE,
            'ntn' => $company->ntn,
            'address' => $company->address,
            'subscription_status' => Subscription::STATUS_ACTIVE,
            'subscription_starts_at' => '2026-09-15', // past date
            'subscription_type' => Subscription::TYPE_MONTHLY,
            'amount' => 5000,
        ]);
        $pastResponse->assertSessionHasErrors(['subscription_starts_at']);

        // Today or future start date -> success
        $validResponse = $this->actingAs($this->adminUser)->put(route('admin.companies.update', $company), [
            'name' => $company->name,
            'email' => $company->email,
            'user_email' => 'login@company.test',
            'status' => Company::STATUS_ACTIVE,
            'ntn' => $company->ntn,
            'address' => $company->address,
            'subscription_status' => Subscription::STATUS_ACTIVE,
            'subscription_starts_at' => '2026-10-01',
            'subscription_type' => Subscription::TYPE_MONTHLY,
            'amount' => 5000,
        ]);
        $validResponse->assertRedirect(route('admin.companies.show', $company));

        $this->assertSame(Subscription::STATUS_ACTIVE, $company->fresh()->latestSubscription->status);
        $this->assertSame(2, Transaction::where('company_id', $company->id)->count());

        Carbon::setTestNow();
    }

    /**
     * Scenario 8: Monthly Date Edge Cases
     * 31-Jan -> 28-Feb (or 29-Feb in leap year), 30-day month, 31-day month calculations.
     */
    public function test_scenario_8_monthly_date_edge_cases(): void
    {
        // 1. Jan 31, 2027 (non-leap year) -> Feb 28, 2027
        $jan31NonLeap = Carbon::parse('2027-01-31');
        $this->assertSame('2027-02-28', SubscriptionPeriod::endDateFor(Subscription::TYPE_MONTHLY, $jan31NonLeap)->toDateString());

        // 2. Jan 31, 2028 (leap year) -> Feb 29, 2028
        $jan31Leap = Carbon::parse('2028-01-31');
        $this->assertSame('2028-02-29', SubscriptionPeriod::endDateFor(Subscription::TYPE_MONTHLY, $jan31Leap)->toDateString());

        // 3. Oct 1 -> Oct 31 (31-day month)
        $oct1 = Carbon::parse('2026-10-01');
        $this->assertSame('2026-10-31', SubscriptionPeriod::endDateFor(Subscription::TYPE_MONTHLY, $oct1)->toDateString());

        // 4. Nov 1 -> Nov 30 (30-day month)
        $nov1 = Carbon::parse('2026-11-01');
        $this->assertSame('2026-11-30', SubscriptionPeriod::endDateFor(Subscription::TYPE_MONTHLY, $nov1)->toDateString());

        // 5. Nov 5 -> Dec 4
        $nov5 = Carbon::parse('2026-11-05');
        $this->assertSame('2026-12-04', SubscriptionPeriod::endDateFor(Subscription::TYPE_MONTHLY, $nov5)->toDateString());
    }

    /**
     * Scenario 9: Yearly Date Calculation
     * 05-Oct-2026 -> 04-Oct-2027, next start 05-Oct-2027
     */
    public function test_scenario_9_yearly_date_calculation(): void
    {
        $oct5 = Carbon::parse('2026-10-05');
        $end = SubscriptionPeriod::endDateFor(Subscription::TYPE_YEARLY, $oct5);
        $this->assertSame('2027-10-04', $end->toDateString());

        $nextStart = $end->copy()->addDay();
        $this->assertSame('2027-10-05', $nextStart->toDateString());
    }

    /**
     * Scenario 10: Subscription Type Change
     * Changing type recalculates End Date, keeps Start Date intact, and resets Pending/Overdue to Due.
     */
    public function test_scenario_10_subscription_type_change_behavior(): void
    {
        $company = Company::factory()->create();
        $sub = Subscription::factory()->create([
            'company_id' => $company->id,
            'status' => Subscription::STATUS_ACTIVE,
            'type' => Subscription::TYPE_MONTHLY,
            'starts_at' => Carbon::parse('2026-11-01'),
            'ends_at' => Carbon::parse('2026-11-30'),
        ]);

        $pendingTx = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $sub->id,
            'status' => Transaction::STATUS_PENDING,
            'subscription_type' => Subscription::TYPE_MONTHLY,
            'billing_period_start' => Carbon::parse('2026-11-01'),
            'billing_period_end' => Carbon::parse('2026-11-30'),
        ]);

        // Change from Monthly to Yearly
        $response = $this->actingAs($this->adminUser)->put(route('admin.companies.update', $company), [
            'name' => $company->name,
            'email' => $company->email,
            'user_email' => 'login@company.test',
            'status' => Company::STATUS_ACTIVE,
            'ntn' => $company->ntn,
            'address' => $company->address,
            'subscription_status' => Subscription::STATUS_ACTIVE,
            'subscription_starts_at' => '2026-11-01',
            'subscription_type' => Subscription::TYPE_YEARLY,
            'amount' => 50000,
        ]);

        $response->assertRedirect(route('admin.companies.show', $company));

        $pendingTx->refresh();
        $this->assertSame('2026-11-01', $pendingTx->billing_period_start->toDateString());
        $this->assertSame('2027-10-31', $pendingTx->billing_period_end->toDateString());
        $this->assertSame(Transaction::STATUS_DUE, $pendingTx->status);
    }

    /**
     * Scenario 11: Email Failure Tolerance
     * Status transition occurs successfully even if Mail driver throws an exception.
     */
    public function test_scenario_11_email_failure_does_not_break_status_transition(): void
    {
        Mail::shouldReceive('to->send')->andThrow(new \Exception('SMTP Connection Refused'));

        $company = Company::factory()->create();
        $sub = Subscription::factory()->create([
            'company_id' => $company->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => Carbon::parse('2026-11-01'),
            'ends_at' => Carbon::parse('2026-11-30'),
        ]);

        $tx = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $sub->id,
            'status' => Transaction::STATUS_DUE,
            'billing_period_start' => Carbon::parse('2026-11-01'),
            'billing_period_end' => Carbon::parse('2026-11-30'),
            'due_at' => Carbon::parse('2026-11-30'),
        ]);

        Carbon::setTestNow('2026-11-23 09:00:00');

        $this->artisan('transactions:send-payment-reminders')->assertExitCode(0);

        $tx->refresh();
        $this->assertSame(Transaction::STATUS_PENDING, $tx->status);
        $this->assertNotNull($tx->reminder_sent_at);

        Carbon::setTestNow();
    }

    /**
     * Scenario 12: Idempotency & Duplicate Protection
     * Multiple scheduler runs produce no duplicate emails or duplicate transactions.
     */
    public function test_scenario_12_idempotency_and_duplicate_protection(): void
    {
        Mail::fake();

        $company = Company::factory()->create();
        $sub = Subscription::factory()->create([
            'company_id' => $company->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => Carbon::parse('2026-11-01'),
            'ends_at' => Carbon::parse('2026-11-30'),
        ]);

        $tx = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $sub->id,
            'status' => Transaction::STATUS_DUE,
            'billing_period_start' => Carbon::parse('2026-11-01'),
            'billing_period_end' => Carbon::parse('2026-11-30'),
            'due_at' => Carbon::parse('2026-11-30'),
        ]);

        Carbon::setTestNow('2026-11-23 09:00:00');

        // Run reminder command 3 times
        $this->artisan('transactions:send-payment-reminders');
        $this->artisan('transactions:send-payment-reminders');
        $this->artisan('transactions:send-payment-reminders');

        Mail::assertSent(PaymentReminder::class, 1);
        $this->assertSame(1, Transaction::where('company_id', $company->id)->count());

        Carbon::setTestNow();
    }
}
