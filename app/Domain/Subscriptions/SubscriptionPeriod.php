<?php

namespace App\Domain\Subscriptions;

use App\Models\Subscription;
use Carbon\CarbonInterface;
use InvalidArgumentException;

/**
 * Pure date math for subscription periods (PRD #6-#8: monthly = +30 days,
 * yearly = +1 year). Kept out of both the Subscription model and
 * CompanyOnboardingService so Phase 5 (renewals) and Phase 11 (the
 * expiry scheduler) reuse this exact calculation instead of re-deriving it
 * slightly differently somewhere else.
 */
class SubscriptionPeriod
{
    public static function endDateFor(string $type, CarbonInterface $startsAt): CarbonInterface
    {
        return match ($type) {
            Subscription::TYPE_MONTHLY => $startsAt->copy()->addDays(30),
            Subscription::TYPE_YEARLY => $startsAt->copy()->addYear(),
            default => throw new InvalidArgumentException("Unknown subscription type [{$type}]."),
        };
    }
}
