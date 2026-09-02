<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;

/**
 * The 7 Pakistani administrative regions shown in the Company Dashboard
 * PRD's reference screenshots. Idempotent — safe to run repeatedly
 * (PRD #15), keyed on the unique `name` column.
 */
class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            'BALOCHISTAN',
            'AZAD JAMMU AND KASHMIR',
            'CAPITAL TERRITORY',
            'KHYBER PAKHTUNKHWA',
            'PUNJAB',
            'SINDH',
            'GILGIT BALTISTAN',
        ];

        foreach ($provinces as $name) {
            Province::firstOrCreate(['name' => $name]);
        }
    }
}
