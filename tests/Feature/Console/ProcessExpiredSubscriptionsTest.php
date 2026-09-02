<?php

namespace Tests\Feature\Console;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ProcessExpiredSubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_lapsed_active_subscription_is_marked_pending_and_gets_a_renewal_invoice(): void
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create([
            'type' => Subscription::TYPE_MONTHLY,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => Carbon::parse('2026-07-01'),
            'ends_at' => Carbon::parse('2026-08-01'),
            'amount' => 5000,
        ]);

        $this->artisan('subscriptions:process-expired')->assertExitCode(0);

        $subscription->refresh();
        $this->assertSame(Subscription::STATUS_PENDING, $subscription->status);

        $transaction = $subscription->transactions()->first();
        $this->assertNotNull($transaction);
        $this->assertSame(Transaction::TYPE_RENEWAL, $transaction->transaction_type);
        $this->assertSame(Transaction::STATUS_PENDING, $transaction->status);
        $this->assertSame('5000.00', $transaction->amount);
        $this->assertSame($company->id, $transaction->company_id);
        $this->assertNull($transaction->paid_at);
        $this->assertSame('2026-08-02', $transaction->billing_period_start->toDateString());
        $this->assertSame('2026-09-01', $transaction->billing_period_end->toDateString());
        $this->assertSame('2026-08-01', $transaction->due_at->toDateString());
    }

    public function test_a_yearly_subscriptions_renewal_invoice_covers_a_full_year(): void
    {
        $subscription = Subscription::factory()->for(Company::factory())->create([
            'type' => Subscription::TYPE_YEARLY,
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => Carbon::parse('2026-01-01'),
        ]);

        $this->artisan('subscriptions:process-expired');

        $transaction = $subscription->transactions()->first();
        $this->assertSame('2026-01-02', $transaction->billing_period_start->toDateString());
        $this->assertSame('2027-01-02', $transaction->billing_period_end->toDateString());
    }

    public function test_a_still_active_subscription_is_left_untouched(): void
    {
        $subscription = Subscription::factory()->for(Company::factory())->create([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->addDays(10),
        ]);

        $this->artisan('subscriptions:process-expired');

        $this->assertSame(Subscription::STATUS_ACTIVE, $subscription->refresh()->status);
        $this->assertSame(0, $subscription->transactions()->count());
    }

    public function test_an_already_pending_subscription_is_left_alone_and_not_reprocessed(): void
    {
        $subscription = Subscription::factory()->for(Company::factory())->pending()->create([
            'ends_at' => now()->subDays(5),
        ]);

        $this->artisan('subscriptions:process-expired');

        $this->assertSame(0, $subscription->transactions()->count());
        $this->assertDatabaseMissing('audit_logs', ['action' => 'subscription.expired']);
    }

    /**
     * PRD #64 case 7: running the scheduler twice must not create a
     * duplicate pending invoice or double-process the same subscription.
     */
    public function test_running_the_command_twice_does_not_duplicate_anything(): void
    {
        $subscription = Subscription::factory()->for(Company::factory())->create([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->subDays(3),
        ]);

        $this->artisan('subscriptions:process-expired');
        $this->artisan('subscriptions:process-expired');

        $this->assertSame(1, $subscription->transactions()->count());
        $this->assertSame(1, AuditLog::where('action', 'subscription.expired')->count());
    }

    /**
     * PRD #50: if an unpaid renewal transaction already exists for a
     * lapsed subscription (however it got there), the command must not
     * create a second one — even though the subscription itself still
     * needed to be flipped to pending.
     */
    public function test_it_does_not_create_a_second_invoice_when_a_pending_one_already_exists(): void
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->subDays(2),
        ]);
        Transaction::factory()->pending()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => 'INV-PRE-EXISTING',
        ]);

        $this->artisan('subscriptions:process-expired');

        $this->assertSame(Subscription::STATUS_PENDING, $subscription->refresh()->status);
        $this->assertSame(1, $subscription->transactions()->count());
        $this->assertDatabaseHas('transactions', ['invoice_number' => 'INV-PRE-EXISTING']);
    }

    public function test_it_writes_a_system_audit_log_entry_with_no_authenticated_user(): void
    {
        $company = Company::factory()->create(['name' => 'Lapsed Co']);
        Subscription::factory()->for($company)->create([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->subDay(),
        ]);

        $this->artisan('subscriptions:process-expired');

        $log = AuditLog::where('action', 'subscription.expired')->first();

        $this->assertNotNull($log);
        $this->assertNull($log->user_id);
        $this->assertSame($company->id, $log->company_id);
        $this->assertSame(Subscription::STATUS_ACTIVE, $log->old_values['status']);
        $this->assertSame(Subscription::STATUS_PENDING, $log->new_values['status']);
    }

    public function test_a_cancelled_subscription_past_its_end_date_is_not_touched(): void
    {
        $subscription = Subscription::factory()->for(Company::factory())->cancelled()->create([
            'ends_at' => now()->subDays(10),
        ]);

        $this->artisan('subscriptions:process-expired');

        $this->assertSame(Subscription::STATUS_CANCELLED, $subscription->refresh()->status);
        $this->assertSame(0, $subscription->transactions()->count());
    }
}
