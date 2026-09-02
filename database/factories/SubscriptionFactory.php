<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([Subscription::TYPE_MONTHLY, Subscription::TYPE_YEARLY]);
        $startsAt = now();

        return [
            'company_id' => Company::factory(),
            'type' => $type,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => $startsAt,
            'ends_at' => $type === Subscription::TYPE_MONTHLY
                ? $startsAt->copy()->addDays(30)
                : $startsAt->copy()->addYear(),
            'amount' => $type === Subscription::TYPE_MONTHLY ? 5000 : 50000,
        ];
    }

    public function monthly(): static
    {
        return $this->state(function () {
            $startsAt = now();

            return [
                'type' => Subscription::TYPE_MONTHLY,
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addDays(30),
                'amount' => 5000,
            ];
        });
    }

    public function yearly(): static
    {
        return $this->state(function () {
            $startsAt = now();

            return [
                'type' => Subscription::TYPE_YEARLY,
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addYear(),
                'amount' => 50000,
            ];
        });
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => Subscription::STATUS_PENDING,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => Subscription::STATUS_EXPIRED,
            'starts_at' => now()->subDays(60),
            'ends_at' => now()->subDay(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => Subscription::STATUS_CANCELLED,
        ]);
    }
}
