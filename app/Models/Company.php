<?php

namespace App\Models;

use App\Policies\CompanyPolicy;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A company is a single entity. Its current payment/subscription state
 * (paid, pending, expired, active) is derived from its subscriptions and
 * transactions — never stored as a separate "Paid Company" / "Pending
 * Company" record, and never represented by the `status` column below.
 */
#[UsePolicy(CompanyPolicy::class)]
#[Fillable([
    'name',
    'business_name',
    'email',
    'phone',
    'address',
    'city',
    'province',
    'country',
    'ntn_cnic',
    'business_registration_number',
    'fbr_token_production',
    'fbr_token_sandbox',
    'status',
])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Company Dashboard (own invoicing module, not the admin's
     * subscription-billing side): customers, items, and invoices are all
     * strictly scoped to one company (PRD #18).
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * The company's most recently created subscription, regardless of its
     * status. Business rules for what counts as the "current"/"active"
     * subscription for access-control purposes belong to later phases
     * (subscription middleware / domain services), not to this relation.
     */
    public function latestSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }
}
