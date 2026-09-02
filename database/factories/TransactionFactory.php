<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            // Created from the same company as above so the transaction's
            // company/subscription pairing stays consistent by default.
            'subscription_id' => fn (array $attributes) => Subscription::factory()
                ->create(['company_id' => $attributes['company_id']])
                ->id,
            'invoice_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'transaction_type' => Transaction::TYPE_INITIAL,
            'subscription_type' => Subscription::TYPE_MONTHLY,
            'amount' => 5000,
            'status' => Transaction::STATUS_PAID,
            'billing_period_start' => now()->toDateString(),
            'billing_period_end' => now()->addDays(30)->toDateString(),
            'due_at' => null,
            'paid_at' => now(),
            'notes' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => Transaction::STATUS_PENDING,
            'paid_at' => null,
            'due_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => Transaction::STATUS_CANCELLED,
            'paid_at' => null,
        ]);
    }

    public function renewal(): static
    {
        return $this->state(fn () => [
            'transaction_type' => Transaction::TYPE_RENEWAL,
        ]);
    }

    public function yearly(): static
    {
        return $this->state(fn () => [
            'subscription_type' => Subscription::TYPE_YEARLY,
            'amount' => 50000,
            'billing_period_end' => now()->addYear()->toDateString(),
        ]);
    }
}
