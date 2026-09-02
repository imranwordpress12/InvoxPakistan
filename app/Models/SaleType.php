<?php

namespace App\Models;

use Database\Factories\SaleTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Reference data for the Sale Type dropdown (Company Dashboard PRD #7/#14.2).
 */
#[Fillable(['name'])]
class SaleType extends Model
{
    /** @use HasFactory<SaleTypeFactory> */
    use HasFactory;
}
