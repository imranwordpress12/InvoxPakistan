<?php

namespace Database\Seeders;

use App\Models\HsCode;
use Illuminate\Database\Seeder;

/**
 * Starter set of HS Codes — only the codes actually visible in the
 * Company Dashboard PRD's reference screenshots. This is NOT the official
 * FBR HS Code classification table (which has thousands of entries) —
 * real descriptions are not available here, so each is seeded with an
 * explicit placeholder rather than a fabricated one. Replace/expand this
 * with the real FBR reference-data list before relying on it for live
 * filing. Idempotent (PRD #15), keyed on the unique `code` column.
 */
class HsCodeSeeder extends Seeder
{
    public function run(): void
    {
        $codes = [
            '8432.1010',
            '0304.7400',
            '8479.7900',
            '8415.8190',
            '8453.1000',
            '8417.1090',
            '9505.1000',
        ];

        foreach ($codes as $code) {
            HsCode::firstOrCreate(
                ['code' => $code],
                ['description' => 'Placeholder — replace with the official FBR HS Code description.']
            );
        }
    }
}
