<?php

namespace App\Models;

use Database\Factories\HsCodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Reference data for the HS Code dropdown (Company Dashboard PRD #7/#14.2).
 * The seeded set is a small starter list, not the full official FBR HS
 * Code classification table — see HsCodeSeeder.
 */
#[Fillable(['code', 'description'])]
class HsCode extends Model
{
    /** @use HasFactory<HsCodeFactory> */
    use HasFactory;
}
