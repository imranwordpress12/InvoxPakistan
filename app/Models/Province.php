<?php

namespace App\Models;

use Database\Factories\ProvinceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Reference data for the Province dropdown (Company Dashboard PRD #6/#14.2).
 * Customers/invoices store the province name as a plain value, not a
 * foreign key to this table — see the customers/invoices migrations.
 */
#[Fillable(['name'])]
class Province extends Model
{
    /** @use HasFactory<ProvinceFactory> */
    use HasFactory;
}
