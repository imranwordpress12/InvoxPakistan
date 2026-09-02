<?php

namespace App\Models;

use Database\Factories\UnitOfMeasureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Reference data for the Unit of Measure dropdown (Company Dashboard
 * PRD #7/#14.2).
 */
#[Fillable(['name'])]
class UnitOfMeasure extends Model
{
    /** @use HasFactory<UnitOfMeasureFactory> */
    use HasFactory;

    // Eloquent's default pluralization would guess `unit_of_measures`
    // (pluralizing only the last word); the migration names it
    // `units_of_measure` (reads more naturally), so this is explicit.
    protected $table = 'units_of_measure';
}
