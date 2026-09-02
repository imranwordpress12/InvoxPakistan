<?php

namespace Tests\Feature\Domain;

use App\Domain\Subscriptions\SubscriptionRenewal;
use App\Models\Company;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Covers PRD #27 and the matching edge cases in PRD #64 (cases 4 & 5) using
 * the PRD's own worked example: existing end date 20-Aug, payment on 10-Aug
 * -> new start 21-Aug / new end 20-Sep.
 */
class SubscriptionRenewalTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_before_expiry_does_not_shorten_the_existing_period(): void
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create([
            'type' => Subscription::TYPE_MONTHLY,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => Carbon::parse('2026-07-21'),
            'ends_at' => Carbon::parse('2026-08-20'),
        ]);

        (new SubscriptionRenewal)->renew($subscription, Carbon::parse('2026-08-10'));

        $subscription->refresh();
        $this->assertSame('2026-08-21', $subscription->starts_at->toDateString());
        $this->assertSame('2026-09-20', $subscription->ends_at->toDateString());
        $this->assertSame(Subscription::STATUS_ACTIVE, $subscription->status);
    }

    public function test_payment_exactly_on_the_expiry_date_still_extends_rather_than_restarts(): void
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create([
            'type' => Subscription::TYPE_MONTHLY,
            'starts_at' => Carbon::parse('2026-07-21'),
            'ends_at' => Carbon::parse('2026-08-20'),
        ]);

        // ends_at >= payment date (PRD #10's ">=") still counts as "not yet
        // expired" — paying on the last valid day must not lose that day.
        (new SubscriptionRenewal)->renew($subscription, Carbon::parse('2026-08-20'));

        $this->assertSame('2026-08-21', $subscription->refresh()->starts_at->toDateString());
    }

    public function test_payment_after_expiry_starts_the_new_period_on_the_payment_date(): void
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create([
            'type' => Subscription::TYPE_MONTHLY,
            'status' => Subscription::STATUS_PENDING,
            'starts_at' => Carbon::parse('2026-06-01'),
            'ends_at' => Carbon::parse('2026-07-01'),
        ]);

        (new SubscriptionRenewal)->renew($subscription, Carbon::parse('2026-08-09'));

        $subscription->refresh();
        $this->assertSame('2026-08-09', $subscription->starts_at->toDateString());
        $this->assertSame('2026-09-08', $subscription->ends_at->toDateString());
        $this->assertSame(Subscription::STATUS_ACTIVE, $subscription->status);
    }

    public function test_yearly_renewal_after_expiry(): void
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create([
            'type' => Subscription::TYPE_YEARLY,
            'status' => Subscription::STATUS_EXPIRED,
            'starts_at' => Carbon::parse('2025-01-01'),
            'ends_at' => Carbon::parse('2026-01-01'),
        ]);

        (new SubscriptionRenewal)->renew($subscription, Carbon::parse('2026-08-09'));

        $subscription->refresh();
        $this->assertSame('2026-08-09', $subscription->starts_at->toDateString());
        $this->assertSame('2027-08-09', $subscription->ends_at->toDateString());
    }

    public function test_renewal_defaults_the_payment_date_to_now(): void
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create([
            'type' => Subscription::TYPE_MONTHLY,
            'starts_at' => now()->subDays(40),
            'ends_at' => now()->subDays(10),
        ]);

        (new SubscriptionRenewal)->renew($subscription);

        $this->assertSame(now()->toDateString(), $subscription->refresh()->starts_at->toDateString());
    }
}
