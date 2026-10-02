<?php

namespace App\Domain\Subscriptions;

use App\Models\Subscription;
use Carbon\CarbonInterface;
use InvalidArgumentException;

/**
 * Pure date math for subscription periods according to business rules:
 * - Monthly: End Date = same date in next calendar month - 1 day (or last valid day if same date doesn't exist).
 * - Yearly: End Date = same date next year - 1 day (or last valid day if leap day doesn't exist next year).
 */
class SubscriptionPeriod
{
    public static function endDateFor(string $type, CarbonInterface $startsAt): CarbonInterface
    {
        return match ($type) {
            Subscription::TYPE_MONTHLY => static::monthlyEndDate($startsAt),
            Subscription::TYPE_YEARLY => static::yearlyEndDate($startsAt),
            default => throw new InvalidArgumentException("Unknown subscription type [{$type}]."),
        };
    }

    public static function monthlyEndDate(CarbonInterface $startsAt): CarbonInterface
    {
        if ($startsAt->day === 1) {
            return $startsAt->copy()->endOfMonth()->startOfDay();
        }

        $nextMonth = $startsAt->copy()->addMonthNoOverflow();
        if ($nextMonth->day < $startsAt->day) {
            return $nextMonth->startOfDay();
        }

        return $nextMonth->subDay()->startOfDay();
    }

    public static function yearlyEndDate(CarbonInterface $startsAt): CarbonInterface
    {
        $nextYear = $startsAt->copy()->addYearNoOverflow();
        if ($nextYear->day < $startsAt->day) {
            return $nextYear->startOfDay();
        }

        return $nextYear->subDay()->startOfDay();
    }
}
