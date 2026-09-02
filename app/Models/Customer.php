<?php

namespace App\Models;

use App\Policies\CustomerPolicy;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A company's own buyer master data (Company Dashboard PRD #11). Always
 * scoped to `company_id` — never trust a customer ID supplied by the
 * frontend without checking it belongs to the authenticated company
 * (PRD #18), enforced via CustomerPolicy.
 */
#[UsePolicy(CustomerPolicy::class)]
#[Fillable([
    'company_id',
    'business_name',
    'ntn_cnic',
    'province',
    'buyer_registration_type',
    'strn',
    'contact_person',
    'email',
    'contact_number',
    'address',
    'status',
])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory, SoftDeletes;

    public const REGISTRATION_TYPE_REGISTERED = 'registered';

    public const REGISTRATION_TYPE_UNREGISTERED = 'unregistered';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
