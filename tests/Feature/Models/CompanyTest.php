<?php

namespace Tests\Feature\Models;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_has_many_subscriptions_transactions_and_users(): void
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create();
        $transaction = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
        ]);
        $user = User::factory()->company($company)->create();

        $this->assertTrue($company->subscriptions->contains($subscription));
        $this->assertTrue($company->transactions->contains($transaction));
        $this->assertTrue($company->users->contains($user));
    }

    public function test_company_status_is_administrative_only_and_defaults_to_active(): void
    {
        $company = Company::factory()->create();

        $this->assertSame(Company::STATUS_ACTIVE, $company->status);
        $this->assertContains($company->status, [Company::STATUS_ACTIVE, Company::STATUS_INACTIVE]);
    }

    public function test_deleting_a_company_soft_deletes_it_and_preserves_transaction_history(): void
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create();
        $transaction = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
        ]);

        $company->delete();

        $this->assertSoftDeleted($company);
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id]);
        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id]);
    }

    public function test_email_must_be_unique(): void
    {
        $existing = Company::factory()->create();

        $this->expectException(QueryException::class);

        Company::factory()->create(['email' => $existing->email]);
    }
}
