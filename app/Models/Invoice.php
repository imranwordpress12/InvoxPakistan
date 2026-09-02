<?php

namespace App\Models;

use App\Policies\InvoicePolicy;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A company's own FBR sales invoice (Company Dashboard PRD #6-#9).
 * Named `Invoice`, not `Transaction` — that model already means something
 * different in this app (subscription billing).
 */
#[UsePolicy(InvoicePolicy::class)]
#[Fillable([
    'company_id',
    'customer_id',
    'invoice_reference_no',
    'fbr_invoice_number',
    'invoice_type',
    'invoice_date',
    'status',
    'fbr_response',
    'qr_code',
    'buyer_ntn_cnic',
    'buyer_business_name',
    'buyer_address',
    'buyer_registration_type',
    'buyer_province',
    'buyer_strn',
    'total_excl_st',
    'total_sales_tax',
    'total_further_tax',
    'total_discount',
    'total_amount',
    'submitted_at',
])]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory, SoftDeletes;

    public const TYPE_SALE = 'sale';

    public const TYPE_DEBIT = 'debit';

    public const TYPE_CREDIT = 'credit';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_SUCCESSFUL = 'successful';

    public const STATUS_FAILED = 'failed';

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'submitted_at' => 'datetime',
            'total_excl_st' => 'decimal:2',
            'total_sales_tax' => 'decimal:2',
            'total_further_tax' => 'decimal:2',
            'total_discount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESSFUL;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }
}
