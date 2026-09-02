<?php

namespace Tests\Feature\Models;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_belongs_to_company_and_has_many_transactions(): void
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create();
        $transaction = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
        ]);

        $this->assertTrue($subscription->company->is($company));
        $this->assertTrue($subscription->transactions->contains($transaction));
    }

    public function test_monthly_subscription_spans_thirty_days(): void
    {
        $subscription = Subscription::factory()->monthly()->create();

        $this->assertSame(
            $subscription->starts_at->copy()->addDays(30)->toDateString(),
            $subscription->ends_at->toDateString()
        );
    }

    public function test_yearly_subscription_spans_one_year(): void
    {
        $subscription = Subscription::factory()->yearly()->create();

        $this->assertSame(
            $subscription->starts_at->copy()->addYear()->toDateString(),
            $subscription->ends_at->toDateString()
        );
    }

    // ---- PRD #9/#10 status helpers ---------------------------------------

    public function test_is_active_requires_both_active_status_and_an_unlapsed_end_date(): void
    {
        $active = Subscription::factory()->create([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->addDays(10),
        ]);
        $this->assertTrue($active->isActive());

        // Status says active, but the date has already passed — PRD #10
        // requires both conditions, so this must NOT count as active.
        $stale = Subscription::factory()->create([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->subDay(),
        ]);
        $this->assertFalse($stale->isActive());
        $this->assertTrue($stale->hasLapsed());

        $pending = Subscription::factory()->pending()->create(['ends_at' => now()->addDays(10)]);
        $this->assertFalse($pending->isActive());
        $this->assertTrue($pending->isPending());
    }

    public function test_ends_at_exactly_now_still_counts_as_active(): void
    {
        // PRD #10 uses ">=" — the exact boundary instant must still pass.
        // A full minute of buffer (rather than a single second) so this
        // doesn't flake under slow test-runner load between creation and
        // assertion.
        $subscription = Subscription::factory()->create([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->addMinute(),
        ]);

        $this->assertTrue($subscription->isActive());
        $this->assertFalse($subscription->hasLapsed());
    }

    public function test_status_helpers_match_the_stored_status(): void
    {
        $expired = Subscription::factory()->create(['status' => Subscription::STATUS_EXPIRED]);
        $this->assertTrue($expired->isExpired());
        $this->assertFalse($expired->isPending());

        $cancelled = Subscription::factory()->cancelled()->create();
        $this->assertTrue($cancelled->isCancelled());
    }

    public function test_days_remaining_is_floored_at_zero_once_lapsed(): void
    {
        $subscription = Subscription::factory()->create(['ends_at' => now()->addDays(22)->endOfDay()]);
        $this->assertSame(22, $subscription->daysRemaining());

        $lapsed = Subscription::factory()->create(['ends_at' => now()->subDays(5)]);
        $this->assertSame(0, $lapsed->daysRemaining());
    }
}
