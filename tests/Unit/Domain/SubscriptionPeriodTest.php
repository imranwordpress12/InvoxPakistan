<?php

namespace Tests\Unit\Domain;

use App\Domain\Subscriptions\SubscriptionPeriod;
use App\Models\Subscription;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Tests\TestCase;

class SubscriptionPeriodTest extends TestCase
{
    public function test_monthly_period_ends_on_same_day_next_month_minus_one_day(): void
    {
        $start = Carbon::parse('2026-08-09');

        $end = SubscriptionPeriod::endDateFor(Subscription::TYPE_MONTHLY, $start);

        // 09-Aug-2026 -> 08-Sep-2026 (same day next month minus 1 day)
        $this->assertSame('2026-09-08', $end->toDateString());
    }

    public function test_yearly_period_ends_on_same_day_next_year_minus_one_day(): void
    {
        $start = Carbon::parse('2026-08-09');

        $end = SubscriptionPeriod::endDateFor(Subscription::TYPE_YEARLY, $start);

        // 09-Aug-2026 -> 08-Aug-2027 (same day next year minus 1 day)
        $this->assertSame('2027-08-08', $end->toDateString());
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
