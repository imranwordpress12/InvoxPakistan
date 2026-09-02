<?php

namespace Tests\Unit\Domain;

use App\Domain\Subscriptions\SubscriptionPeriod;
use App\Models\Subscription;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Tests\TestCase;

class SubscriptionPeriodTest extends TestCase
{
    public function test_monthly_period_is_thirty_days(): void
    {
        $start = Carbon::parse('2026-08-09');

        $end = SubscriptionPeriod::endDateFor(Subscription::TYPE_MONTHLY, $start);

        $this->assertSame('2026-09-08', $end->toDateString());
    }

    public function test_yearly_period_is_one_year(): void
    {
        $start = Carbon::parse('2026-08-09');

        $end = SubscriptionPeriod::endDateFor(Subscription::TYPE_YEARLY, $start);

        $this->assertSame('2027-08-09', $end->toDateString());
    }

    public function test_it_does_not_mutate_the_given_start_date(): void
    {
        $start = Carbon::parse('2026-08-09');

        SubscriptionPeriod::endDateFor(Subscription::TYPE_MONTHLY, $start);

        $this->assertSame('2026-08-09', $start->toDateString());
    }

    public function test_unknown_type_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SubscriptionPeriod::endDateFor('weekly', Carbon::now());
    }
}
