<?php

namespace App\Models;

use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'company_id',
    'type',
    'status',
    'starts_at',
    'ends_at',
    'amount',
])]
class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory;

    public const TYPE_MONTHLY = 'monthly';

    public const TYPE_YEARLY = 'yearly';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PENDING = 'pending';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_CANCELLED = 'cancelled';

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function latestTransaction(): HasOne
    {
        return $this->hasOne(Transaction::class)->latestOfMany();
    }

    /**
     * The Company Access Rule (PRD #10): access is allowed only when the
     * stored status is literally "active" AND the period hasn't lapsed yet.
     * Deliberately checks both — a stale `status` column (not yet caught up
     * by the Phase 11 scheduler) must never grant access on its own, and a
     * correct `status` with a passed `ends_at` must never either. Phase 7's
     * middleware calls this directly rather than re-deriving the rule.
     *
     * Keep this in sync with scopeCurrentlyActive() below — same rule,
     * expressed as an in-memory check here and as a query there.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && ! $this->hasLapsed();
    }

    /**
     * Query-level equivalent of isActive(), for counting/filtering without
     * loading every row into memory (the admin dashboard's summary cards,
     * Phase 9's Pending Companies filter, Phase 11's scheduler).
     */
    public function scopeCurrentlyActive(Builder $query): void
    {
        $query->where('status', self::STATUS_ACTIVE)->where('ends_at', '>=', now());
    }

    /**
     * PRD #49's scheduler starting query: "Find active subscriptions ->
     * Check ends_at". Deliberately status-based rather than the inverse of
     * currentlyActive() — only a subscription still *stored* as `active`
     * that has since lapsed is eligible, so one already reconciled to
     * pending/expired/cancelled (by a previous run, or by hand) is left
     * alone rather than reprocessed.
     */
    public function scopeNeedingExpiryProcessing(Builder $query): void
    {
        $query->where('status', self::STATUS_ACTIVE)->where('ends_at', '<', now());
    }

    /**
     * True once `ends_at` is in the past, regardless of what the stored
     * `status` column currently says — i.e. "reality", independent of
     * whether anything has reconciled the row yet.
     */
    public function hasLapsed(): bool
    {
        return $this->ends_at->isPast();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Whole days left until `ends_at`, floored at 0 once it has passed
     * (PRD #47 "Days Remaining"). Not meaningful for a subscription that
     * isn't currently active — callers should check isActive() first.
     */
    public function daysRemaining(): int
    {
        return max(0, now()->diffInDays($this->ends_at, absolute: false));
    }
}
