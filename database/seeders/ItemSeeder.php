<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('email', 'marko.traders@example.com')->firstOrFail();

        $items = [
            [
                'item_code' => 'ITM-LAP-001',
                'item_name' => 'Business Laptop',
                'item_type' => Item::TYPE_GOODS,
                'sale_type' => 'Goods at standard rate (default)',
                'hs_code' => '8471.3000',
                'rate' => 18.00,
                'uom' => 'Number',
                'purchase_price' => 85000.00,
                'sale_price' => 95000.00,
                'stock_quantity' => 25,
                'reorder_level' => 5,
                'description' => 'Standard business laptop for office use.',
            ],
            [
                'item_code' => 'ITM-MON-002',
                'item_name' => 'LED Monitor 24 Inch',
                'item_type' => Item::TYPE_GOODS,
                'sale_type' => 'Goods at standard rate (default)',
                'hs_code' => '8528.5210',
                'rate' => 18.00,
                'uom' => 'Number',
                'purchase_price' => 28000.00,
                'sale_price' => 32500.00,
                'stock_quantity' => 40,
                'reorder_level' => 8,
                'description' => '24-inch LED monitor with HDMI connectivity.',
            ],
            [
                'item_code' => 'SRV-INSTALL-003',
                'item_name' => 'Equipment Installation Service',
                'item_type' => Item::TYPE_SERVICE,
                'sale_type' => 'Services',
                'hs_code' => '9985.0000',
                'rate' => 15.00,
                'uom' => 'Service',
                'purchase_price' => 0.00,
                'sale_price' => 15000.00,
                'stock_quantity' => 0,
                'reorder_level' => 0,
                'description' => 'On-site equipment installation and setup service.',
            ],
        ];

        foreach ($items as $item) {
            Item::updateOrCreate(
                ['company_id' => $company->id, 'item_code' => $item['item_code']],
                [...$item, 'company_id' => $company->id, 'status' => Item::STATUS_ACTIVE],
            );
        }
    }
}
