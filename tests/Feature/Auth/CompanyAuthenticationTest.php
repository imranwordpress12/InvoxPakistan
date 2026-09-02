<?php

namespace Tests\Feature\Auth;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_login_screen_can_be_rendered(): void
    {
        $this->get(route('company.login'))->assertOk();
    }

    public function test_company_user_can_authenticate_using_correct_credentials(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();

        $response = $this->post(route('company.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('company.dashboard'));
    }

    public function test_a_successful_company_login_writes_an_audit_log_entry(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();

        $this->post(route('company.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $log = AuditLog::where('action', 'company.login')->first();

        $this->assertNotNull($log);
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame($company->id, $log->company_id);
    }

    public function test_company_user_cannot_authenticate_with_wrong_password(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();

        $response = $this->from(route('company.login'))->post(route('company.login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('company.login'));
        $response->assertSessionHasErrors('email');
    }

    /**
     * Symmetric to the admin case: an admin account must never be able to
     * reach the company area through the company login form either.
     */
    public function test_an_admin_account_cannot_log_in_through_the_company_form(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->from(route('company.login'))->post(route('company.login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('company.login'));
        $response->assertSessionHasErrors('email');
    }

    public function test_company_user_can_log_out(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();
        $this->actingAs($user);

        $response = $this->post(route('company.logout'));

        $this->assertGuest();
        $response->assertRedirect(route('company.login'));
    }

    public function test_guest_visiting_company_dashboard_is_redirected_to_company_login(): void
    {
        $this->get(route('company.dashboard'))->assertRedirect(route('company.login'));
    }

    public function test_already_authenticated_company_user_visiting_login_is_redirected_to_dashboard(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();
        $this->actingAs($user);

        $this->get(route('company.login'))->assertRedirect(route('company.dashboard'));
    }

    public function test_repeated_failed_company_logins_are_rate_limited(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('company.login'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post(route('company.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }
}
