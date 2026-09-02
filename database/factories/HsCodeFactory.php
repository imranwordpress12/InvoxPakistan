<?php

namespace Database\Factories;

use App\Models\HsCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HsCode>
 */
class HsCodeFactory extends Factory
{
    protected $model = HsCode::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('####.####'),
            'description' => fake()->words(3, true),
        ];
    }
}
