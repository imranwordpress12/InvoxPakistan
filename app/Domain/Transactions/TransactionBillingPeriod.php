<?php

namespace App\Domain\Transactions;

use App\Domain\Subscriptions\SubscriptionPeriod;
use App\Models\Subscription;
use Carbon\CarbonInterface;
use InvalidArgumentException;

class TransactionBillingPeriod
{
    public static function startDateFor(string $type, CarbonInterface $date): CarbonInterface
    {
        return match ($type) {
            Subscription::TYPE_MONTHLY => $date->copy(),
            Subscription::TYPE_YEARLY => $date->copy(),
            default => throw new InvalidArgumentException("Unknown subscription type [{$type}]."),
        };
    }

    public static function endDateFor(string $type, CarbonInterface $date): CarbonInterface
    {
        return SubscriptionPeriod::endDateFor($type, $date);
    }
}