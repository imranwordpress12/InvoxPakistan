<?php

namespace Database\Seeders;

use App\Models\TaxRate;
use Illuminate\Database\Seeder;

/**
 * Commonly-referenced Pakistani sales tax rate percentages. These are
 * standard, publicly known headline rates, not a substitute for the real
 * SRO-specific rate table — verify against current FBR notifications
 * before relying on this for live filing. Idempotent (PRD #15), keyed on
 * the unique `rate` column.
 */
class TaxRateSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [0, 1, 2, 3, 5, 8, 17, 18, 25];

        foreach ($rates as $rate) {
            TaxRate::firstOrCreate(['rate' => $rate]);
        }
    }
}
