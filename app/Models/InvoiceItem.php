<?php

namespace App\Models;

use Database\Factories\InvoiceItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single line on an Invoice (Company Dashboard PRD #7). Always accessed
 * through its parent Invoice — never has its own route/ID exposed
 * directly, so it doesn't need its own policy (InvoicePolicy already
 * gates the parent).
 */
#[Fillable([
    'invoice_id',
    'item_id',
    'sale_type',
    'hs_code',
    'product_description',
    'rate',
    'uom',
    'price_per_unit',
    'quantity',
    'value_sales_excl_st',
    'sales_tax',
    'further_tax',
    'fixed_retail_price',
    'st_withheld_at_source',
    'extra_tax',
    'fed_payable',
    'discount',
    'total_sales_value',
    'sro_schedule_no',
    'sro_item_sr_no',
])]
class InvoiceItem extends Model
{
    /** @use HasFactory<InvoiceItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'price_per_unit' => 'decimal:2',
            'quantity' => 'decimal:4',
            'value_sales_excl_st' => 'decimal:2',
            'sales_tax' => 'decimal:2',
            'further_tax' => 'decimal:2',
            'fixed_retail_price' => 'decimal:2',
            'st_withheld_at_source' => 'decimal:2',
            'extra_tax' => 'decimal:2',
            'fed_payable' => 'decimal:2',
            'discount' => 'decimal:2',
            'total_sales_value' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
