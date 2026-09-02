<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'item_code' => fake()->unique()->bothify('ITM-####'),
            'item_name' => fake()->words(2, true),
            'item_type' => Item::TYPE_GOODS,
            'sale_type' => 'Goods at standard rate (default)',
            'hs_code' => fake()->numerify('####.####'),
            'rate' => 18,
            'uom' => 'Number',
            'purchase_price' => fake()->randomFloat(2, 100, 1000),
            'sale_price' => fake()->randomFloat(2, 150, 1500),
            'stock_quantity' => fake()->numberBetween(0, 500),
            'reorder_level' => fake()->numberBetween(0, 50),
            'description' => fake()->sentence(),
            'status' => Item::STATUS_ACTIVE,
        ];
    }

    public function service(): static
    {
        return $this->state(fn () => ['item_type' => Item::TYPE_SERVICE]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => Item::STATUS_INACTIVE]);
    }
}
