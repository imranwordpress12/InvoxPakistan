<?php

namespace Database\Seeders;

use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;

/**
 * Common, generic units of measure — not FBR-specific proprietary data,
 * so seeding a reasonable standard starter set here (unlike HS
 * Codes/Sale Types) doesn't risk misrepresenting compliance-specific
 * reference data. Expand as needed. Idempotent (PRD #15).
 */
class UnitOfMeasureSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            'Number', 'Kilogram', 'Liter', 'Meter', 'Square Meter',
            'Dozen', 'Pack', 'Set', 'Carton', 'Bag', 'Ton',
        ];

        foreach ($units as $name) {
            UnitOfMeasure::firstOrCreate(['name' => $name]);
        }
    }
}
