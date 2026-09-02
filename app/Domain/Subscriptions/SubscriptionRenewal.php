<?php

namespace App\Domain\Subscriptions;

use App\Models\Subscription;
use Carbon\CarbonInterface;

/**
 * Subscription renewal date math (PRD #27 / edge cases #64.4-#64.5):
 *
 * - If the subscription has already lapsed by the payment date, the new
 *   period starts on the payment date itself — no backdating, no grace
 *   period, per the PRD's recommended behavior.
 * - If payment arrives before (or exactly on) the existing `ends_at`, the
 *   new period starts the day *after* the current `ends_at` — this never
 *   shortens unused subscription time (PRD #27's 20-Aug/10-Aug example).
 *
 * Persists the update immediately (a single-row write is its own unit of
 * work) — Phase 6's "mark transaction paid" flow wraps this call together
 * with its own Transaction/AuditLog writes in one outer DB::transaction().
 */
class SubscriptionRenewal
{
    public function renew(Subscription $subscription, ?CarbonInterface $paymentDate = null): Subscription
    {
        $paymentDate = $paymentDate?->copy() ?? now();

        $newStartsAt = $paymentDate->lte($subscription->ends_at)
            ? $subscription->ends_at->copy()->addDay()
            : $paymentDate;

        $subscription->forceFill([
            'starts_at' => $newStartsAt,
            'ends_at' => SubscriptionPeriod::endDateFor($subscription->type, $newStartsAt),
            'status' => Subscription::STATUS_ACTIVE,
        ])->save();

        return $subscription;
    }
}
