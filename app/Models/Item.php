<?php

namespace App\Models;

use App\Policies\ItemPolicy;
use Database\Factories\ItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A company's own item/product master data (Company Dashboard PRD #12).
 * Selecting an item on an invoice prefills description/HS code/UoM/price
 * (PRD #7) but the invoice line still snapshots its own copy — see
 * InvoiceItem.
 */
#[UsePolicy(ItemPolicy::class)]
#[Fillable([
    'company_id',
    'item_code',
    'item_name',
    'item_type',
    'sale_type',
    'hs_code',
    'rate',
    'uom',
    'purchase_price',
    'sale_price',
    'stock_quantity',
    'reorder_level',
    'description',
    'status',
])]
class Item extends Model
{
    /** @use HasFactory<ItemFactory> */
    use HasFactory, SoftDeletes;

    public const TYPE_GOODS = 'goods';

    public const TYPE_SERVICE = 'service';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
