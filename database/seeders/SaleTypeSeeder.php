<?php

namespace Database\Seeders;

use App\Models\SaleType;
use Illuminate\Database\Seeder;

/**
 * Starter set of Sale Types — only the values actually visible in the
 * Company Dashboard PRD's reference screenshots. This is NOT the full
 * official FBR transaction-type list; expand this seeder (or replace it
 * with a real FBR reference-data feed) before relying on it for live
 * filing. Idempotent (PRD #15), keyed on the unique `name` column.
 */
class SaleTypeSeeder extends Seeder
{
    public function run(): void
    {
        $saleTypes = [
            'Goods at standard rate (default)',
            'Goods at Reduced Rate',
            'Goods at zero-rate',
            'Petroleum Products',
            'Electricity Supply to Retailers',
            'SIM',
            'Gas to CNG stations',
        ];

        foreach ($saleTypes as $name) {
            SaleType::firstOrCreate(['name' => $name]);
        }
    }
}
