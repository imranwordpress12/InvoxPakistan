<?php

namespace Database\Factories;

use App\Models\TaxRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaxRate>
 */
class TaxRateFactory extends Factory
{
    protected $model = TaxRate::class;

    public function definition(): array
    {
        return [
            'rate' => fake()->unique()->randomElement([0, 1, 2, 5, 8, 17, 18, 25]),
            'label' => null,
        ];
    }
}
