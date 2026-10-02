<?php

namespace Tests\Feature\Domain;

use App\Domain\Companies\CompanyOnboardingService;
use App\Mail\CompanyWelcome;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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
        ], $overrides);
    }

    public function test_it_creates_company_and_user_only_and_sends_welcome_email(): void
    {
        Mail::fake();

        $company = (new CompanyOnboardingService)->onboard($this->validData());

        $this->assertDatabaseHas('companies', ['id' => $company->id, 'email' => 'contact@acme.test']);
        $this->assertDatabaseHas('users', [
            'company_id' => $company->id,
            'email' => 'login@acme.test',
            'role' => User::ROLE_COMPANY,
        ]);

        // Subscription and transactions are NOT created on company creation
        $this->assertSame(0, Subscription::where('company_id', $company->id)->count());
        $this->assertSame(0, Transaction::where('company_id', $company->id)->count());

        Mail::assertSent(CompanyWelcome::class, function ($mail) use ($company) {
            return $mail->hasTo('contact@acme.test');
        });
    }

    public function test_fbr_credentials_default_to_null_and_inactive_status(): void
    {
        $company = (new CompanyOnboardingService)->onboard($this->validData());

        $this->assertSame(Company::FBR_STATUS_INACTIVE, $company->fbr_status);
        $this->assertNull($company->fbr_token_production);
        $this->assertNull($company->fbr_token_sandbox);
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
}
