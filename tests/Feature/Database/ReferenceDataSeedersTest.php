<?php

namespace Tests\Feature\Database;

use App\Models\HsCode;
use App\Models\Province;
use App\Models\SaleType;
use App\Models\TaxRate;
use App\Models\UnitOfMeasure;
use Database\Seeders\HsCodeSeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\SaleTypeSeeder;
use Database\Seeders\TaxRateSeeder;
use Database\Seeders\UnitOfMeasureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Company Dashboard PRD #15: seeders for newly-introduced dropdown data
 * must be repeatable/idempotent — running twice must never create
 * duplicate rows.
 */
class ReferenceDataSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_province_seeder_is_idempotent(): void
    {
        $this->seed(ProvinceSeeder::class);
        $this->seed(ProvinceSeeder::class);

        $this->assertSame(7, Province::count());
    }

    public function test_sale_type_seeder_is_idempotent(): void
    {
        $this->seed(SaleTypeSeeder::class);
        $this->seed(SaleTypeSeeder::class);

        $this->assertSame(7, SaleType::count());
    }

    public function test_hs_code_seeder_is_idempotent(): void
    {
        $this->seed(HsCodeSeeder::class);
        $this->seed(HsCodeSeeder::class);

        $this->assertSame(7, HsCode::count());
    }

    public function test_unit_of_measure_seeder_is_idempotent(): void
    {
        $this->seed(UnitOfMeasureSeeder::class);
        $this->seed(UnitOfMeasureSeeder::class);

        $this->assertSame(11, UnitOfMeasure::count());
    }

    public function test_tax_rate_seeder_is_idempotent(): void
    {
        $this->seed(TaxRateSeeder::class);
        $this->seed(TaxRateSeeder::class);

        $this->assertSame(9, TaxRate::count());
    }
}
