<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        $priceperUnit = fake()->randomFloat(2, 100, 1000);
        $quantity = fake()->numberBetween(1, 10);
        $valueExclSt = $priceperUnit * $quantity;
        $rate = 18;
        $salesTax = round($valueExclSt * $rate / 100, 2);

        return [
            'invoice_id' => Invoice::factory(),
            'item_id' => null,
            'sale_type' => 'Goods at standard rate (default)',
            'hs_code' => fake()->numerify('####.####'),
            'product_description' => fake()->words(3, true),
            'rate' => $rate,
            'uom' => 'Number',
            'price_per_unit' => $priceperUnit,
            'quantity' => $quantity,
            'value_sales_excl_st' => $valueExclSt,
            'sales_tax' => $salesTax,
            'further_tax' => 0,
            'fixed_retail_price' => 0,
            'st_withheld_at_source' => 0,
            'extra_tax' => 0,
            'fed_payable' => 0,
            'discount' => 0,
            'total_sales_value' => $valueExclSt + $salesTax,
            'sro_schedule_no' => null,
            'sro_item_sr_no' => null,
        ];
    }
}
