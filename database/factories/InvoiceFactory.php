<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            // Created against the same company as above, same pattern
            // used by TransactionFactory for company/subscription pairing.
            'customer_id' => fn (array $attributes) => Customer::factory()
                ->create(['company_id' => $attributes['company_id']])
                ->id,
            'invoice_reference_no' => fake()->bothify('REF-####'),
            'fbr_invoice_number' => null,
            'invoice_type' => Invoice::TYPE_SALE,
            'invoice_date' => now()->toDateString(),
            'status' => Invoice::STATUS_DRAFT,
            'fbr_response' => null,
            'buyer_ntn_cnic' => fake()->numerify('#######-#'),
            'buyer_business_name' => fake()->company(),
            'buyer_address' => fake()->streetAddress(),
            'buyer_registration_type' => Customer::REGISTRATION_TYPE_REGISTERED,
            'buyer_province' => 'Punjab',
            'buyer_strn' => fake()->numerify('##-##-####-###-##'),
            'total_excl_st' => 0,
            'total_sales_tax' => 0,
            'total_further_tax' => 0,
            'total_discount' => 0,
            'total_amount' => 0,
            'submitted_at' => null,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn () => [
            'status' => Invoice::STATUS_SUCCESSFUL,
            'fbr_invoice_number' => fake()->bothify('FBR-########'),
            'submitted_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => Invoice::STATUS_FAILED,
            'fbr_response' => 'Simulated failure for testing.',
            'submitted_at' => now(),
        ]);
    }
}
