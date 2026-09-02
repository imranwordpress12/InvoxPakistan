<?php

namespace App\Models;

use Database\Factories\TaxRateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Reference data for the Rate (%) dropdown (Company Dashboard PRD #7/#14.2).
 */
#[Fillable(['rate', 'label'])]
class TaxRate extends Model
{
    /** @use HasFactory<TaxRateFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
        ];
    }
}
