<?php

namespace Database\Factories;

use App\Models\SaleType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleType>
 */
class SaleTypeFactory extends Factory
{
    protected $model = SaleType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
        ];
    }
}
