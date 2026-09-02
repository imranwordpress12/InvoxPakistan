<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Only a local-dev admin login and the Company Dashboard's
     * reference/dropdown data are seeded here. Company/subscription/
     * transaction seeding is deferred to later phases, once the creation
     * flow (and its business rules) actually exists.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Test Admin',
            'email' => 'admin@invoxpakistan.test',
        ]);

        $this->call([
            ProvinceSeeder::class,
            SaleTypeSeeder::class,
            HsCodeSeeder::class,
            UnitOfMeasureSeeder::class,
            TaxRateSeeder::class,
            MarkoTradersCompanySeeder::class,
            InvoiceSeeder::class,
        ]);
    }
}
