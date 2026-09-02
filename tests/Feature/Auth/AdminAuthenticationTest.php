<?php

namespace Tests\Feature\Auth;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_screen_can_be_rendered(): void
    {
        $this->get(route('admin.login'))->assertOk();
    }

    public function test_admin_can_authenticate_using_correct_credentials(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_a_successful_admin_login_writes_an_audit_log_entry(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $log = AuditLog::where('action', 'admin.login')->first();

        $this->assertNotNull($log);
        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame('auth', $log->module);
    }

    public function test_a_failed_admin_login_does_not_write_an_audit_log_entry(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ]);

        $this->assertDatabaseMissing('audit_logs', ['action' => 'admin.login']);
    }

    public function test_admin_cannot_authenticate_with_wrong_password(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->from(route('admin.login'))->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');
    }

    /**
     * A Company must never be able to reach the admin area (PRD #4.2) —
     * not even with otherwise-correct credentials.
     */
    public function test_a_company_account_cannot_log_in_through_the_admin_form(): void
    {
        $company = Company::factory()->create();
        $companyUser = User::factory()->company($company)->create();

        $response = $this->from(route('admin.login'))->post(route('admin.login'), [
            'email' => $companyUser->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');
    }

    public function test_admin_can_log_out(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->post(route('admin.logout'));

        $this->assertGuest();
        $response->assertRedirect(route('admin.login'));
    }

    public function test_guest_visiting_admin_dashboard_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_already_authenticated_admin_visiting_login_is_redirected_to_dashboard(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->get(route('admin.login'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_repeated_failed_admin_logins_are_rate_limited(): void
    {
        $admin = User::factory()->admin()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('admin.login'), [
                'email' => $admin->email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }
}
