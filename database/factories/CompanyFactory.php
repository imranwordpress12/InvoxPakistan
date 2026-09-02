<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'business_name' => fake()->company().' '.fake()->companySuffix(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('03#########'),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'province' => fake()->randomElement(['Punjab', 'Sindh', 'Khyber Pakhtunkhwa', 'Balochistan', 'Islamabad Capital Territory']),
            'country' => 'Pakistan',
            'ntn_cnic' => fake()->unique()->numerify('#######-#'),
            'business_registration_number' => fake()->numerify('REG-######'),
            'status' => Company::STATUS_ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Company::STATUS_INACTIVE,
        ]);
    }
}
