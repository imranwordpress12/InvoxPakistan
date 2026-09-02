<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'business_name' => fake()->company(),
            'ntn_cnic' => fake()->unique()->numerify('#######-#'),
            'province' => fake()->randomElement(['Punjab', 'Sindh', 'Khyber Pakhtunkhwa', 'Balochistan']),
            'buyer_registration_type' => Customer::REGISTRATION_TYPE_REGISTERED,
            'strn' => fake()->numerify('##-##-####-###-##'),
            'contact_person' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'contact_number' => fake()->numerify('03#########'),
            'address' => fake()->streetAddress(),
            'status' => Customer::STATUS_ACTIVE,
        ];
    }

    public function unregistered(): static
    {
        return $this->state(fn () => [
            'buyer_registration_type' => Customer::REGISTRATION_TYPE_UNREGISTERED,
            'strn' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => Customer::STATUS_INACTIVE]);
    }
}
