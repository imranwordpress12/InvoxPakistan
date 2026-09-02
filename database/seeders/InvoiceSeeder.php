<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Invoice;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::query()->where('name', 'M/S MARKO TRADERS')->firstOrFail();

        $invoice = $company->invoices()->create([
            'buyer_ntn_cnic' => '4515520',
            'buyer_business_name' => 'M/S MARKO TRADERS',
            'buyer_address' => 'OFFICE NO # 10, FIRST FLOOR, GALAXY CENTER, FEROZPUR ROAD, Lahore',
            'buyer_registration_type' => 'Registered',
            'buyer_province' => 'PUNJAB',
            'buyer_strn' => '00923212020640',
            'invoice_date' => '2026-07-31',
            'invoice_type' => Invoice::TYPE_SALE,
            'invoice_reference_no' => 'FBR-JUL2026-4515520',
            'total_excl_st' => 252000.00,
            'total_sales_tax' => 45360.00,
            'total_amount' => 297360.00,
        ]);

        $invoice->items()->create([
            'sale_type' => 'Goods at standard rate (default)',
            'hs_code' => '7803',
            'product_description' => 'Standard Local Supply',
            'rate' => 17.00,
            'uom' => 'NOS',
            'quantity' => 1.00,
            'price_per_unit' => 252000.00,
            'value_sales_excl_st' => 252000.00,
            'sales_tax' => 45360.00,
            'further_tax' => 0.00,
            'fixed_retail_price' => 0.00,
            'st_withheld_at_source' => 0.00,
            'extra_tax' => 0.00,
            'fed_payable' => 0.00,
            'discount' => 0.00,
            'total_sales_value' => 297360.00,
            'sro_schedule_no' => null,
            'sro_item_sr_no' => null,
        ]);
    }
}