<?php

namespace Tests\Feature\Models;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_belongs_to_company_and_subscription(): void
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create();
        $transaction = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
        ]);

        $this->assertTrue($transaction->company->is($company));
        $this->assertTrue($transaction->subscription->is($subscription));
    }

    public function test_invoice_number_must_be_unique(): void
    {
        Transaction::factory()->create(['invoice_number' => 'INV-0001']);

        $this->expectException(QueryException::class);

        Transaction::factory()->create(['invoice_number' => 'INV-0001']);
    }

    public function test_a_subscription_cannot_have_two_pending_transactions_at_once(): void
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create();

        Transaction::factory()->pending()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
        ]);

        $this->expectException(QueryException::class);

        Transaction::factory()->pending()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
        ]);
    }

    public function test_multiple_paid_transactions_are_allowed_for_the_same_subscription(): void
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create();

        Transaction::factory()->count(3)->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => Transaction::STATUS_PAID,
        ]);

        $this->assertSame(3, $subscription->transactions()->count());
    }

    public function test_a_pending_transaction_can_follow_a_paid_one_on_the_same_subscription(): void
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create();

        Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => Transaction::STATUS_PAID,
        ]);

        Transaction::factory()->pending()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
        ]);

        $this->assertSame(2, $subscription->transactions()->count());
    }

    public function test_pending_dedupe_key_is_hidden_from_serialization(): void
    {
        $transaction = Transaction::factory()->create();

        $this->assertArrayNotHasKey('pending_dedupe_key', $transaction->toArray());
    }
}
