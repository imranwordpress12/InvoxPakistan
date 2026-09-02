<?php

namespace Tests\Feature\Domain;

use App\Domain\Companies\CompanyOnboardingService;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class CompanyOnboardingServiceTest extends TestCase
{
    use RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Acme Traders',
            'business_name' => 'Acme Traders Pvt Ltd',
            'email' => 'contact@acme.test',
            'phone' => '03001234567',
            'address' => '123 Main St',
            'city' => 'Lahore',
            'province' => 'Punjab',
            'country' => 'Pakistan',
            'ntn_cnic' => '1234567-8',
            'business_registration_number' => 'REG-000001',
            'user_email' => 'login@acme.test',
            'password' => 'password123',
            'subscription_type' => Subscription::TYPE_MONTHLY,
            'subscription_starts_at' => '2026-08-09',
            'amount' => 5000,
            'fbr_token_production' => null,
            'fbr_token_sandbox' => null,
        ], $overrides);
    }

    public function test_it_creates_company_user_subscription_and_paid_transaction_atomically(): void
    {
        $company = (new CompanyOnboardingService)->onboard($this->validData());

        $this->assertDatabaseHas('companies', ['id' => $company->id, 'email' => 'contact@acme.test']);
        $this->assertDatabaseHas('users', [
            'company_id' => $company->id,
            'email' => 'login@acme.test',
            'role' => User::ROLE_COMPANY,
        ]);

        $subscription = $company->subscriptions()->first();
        $this->assertNotNull($subscription);
        $this->assertSame(Subscription::TYPE_MONTHLY, $subscription->type);
        $this->assertSame(Subscription::STATUS_ACTIVE, $subscription->status);
        $this->assertSame('2026-08-09', $subscription->starts_at->toDateString());
        $this->assertSame('2026-09-08', $subscription->ends_at->toDateString());

        $transaction = $company->transactions()->first();
        $this->assertNotNull($transaction);
        $this->assertSame(Transaction::TYPE_INITIAL, $transaction->transaction_type);
        $this->assertSame(Transaction::STATUS_PAID, $transaction->status);
        $this->assertNotNull($transaction->paid_at);
        $this->assertSame($subscription->id, $transaction->subscription_id);
    }

    public function test_yearly_subscription_ends_one_year_after_it_starts(): void
    {
        $company = (new CompanyOnboardingService)->onboard($this->validData([
            'subscription_type' => Subscription::TYPE_YEARLY,
            'amount' => 50000,
        ]));

        $subscription = $company->subscriptions()->first();

        $this->assertSame('2027-08-09', $subscription->ends_at->toDateString());
    }

    public function test_it_creates_an_fbr_credential_when_provided(): void
    {
        $company = (new CompanyOnboardingService)->onboard($this->validData([
            'fbr_token_production' => 'secret-token',
            'fbr_token_sandbox' => 'secret-key',
        ]));

        $this->assertDatabaseHas('company_fbr_credentials', ['company_id' => $company->id]);
        $this->assertSame('secret-token', $company->fresh()->fbr_token_production);
    }

    public function test_it_skips_creating_an_fbr_credential_when_none_provided(): void
    {
        $company = (new CompanyOnboardingService)->onboard($this->validData());

        $this->assertDatabaseMissing('company_fbr_credentials', ['company_id' => $company->id]);
    }

    public function test_it_writes_a_company_created_audit_log_entry(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $company = (new CompanyOnboardingService)->onboard($this->validData());

        $log = AuditLog::where('action', 'company.created')->first();

        $this->assertNotNull($log);
        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame($company->id, $log->company_id);
        $this->assertSame('Acme Traders', $log->new_values['name']);
        $this->assertArrayNotHasKey('password', $log->new_values ?? []);
    }

    /**
     * Proves DB::transaction() actually rolls everything back on failure
     * (PRD #20/#52) — Company/User/Subscription must not partially persist
     * when a later step in the same transaction blows up.
     */
    public function test_nothing_persists_if_any_step_fails(): void
    {
        $this->expectException(InvalidArgumentException::class);

        try {
            (new CompanyOnboardingService)->onboard($this->validData([
                'subscription_type' => 'not-a-real-type',
            ]));
        } finally {
            $this->assertSame(0, Company::count());
            $this->assertSame(0, User::query()->where('role', User::ROLE_COMPANY)->count());
            $this->assertSame(0, Subscription::count());
            $this->assertSame(0, Transaction::count());
            $this->assertSame(0, AuditLog::count());
        }
    }
}
