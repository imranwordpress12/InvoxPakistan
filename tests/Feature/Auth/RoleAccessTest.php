<?php

namespace Tests\Feature\Auth;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PRD #62 Phase 3 acceptance criteria: "Admin cannot access company area"
 * and "Company cannot access admin area" — enforced server-side by the
 * `admin`/`company` middleware, independent of the login step.
 */
class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_admin_cannot_access_the_company_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('company.dashboard'))
            ->assertForbidden();
    }

    public function test_an_authenticated_company_user_cannot_access_the_admin_dashboard(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_an_authenticated_company_user_cannot_log_out_of_the_admin_session(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();

        $this->actingAs($user)
            ->post(route('admin.logout'))
            ->assertForbidden();
    }

    public function test_an_authenticated_admin_cannot_log_out_of_the_company_session(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('company.logout'))
            ->assertForbidden();
    }
}
