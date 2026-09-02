<?php

namespace Tests\Feature\Admin;

use App\Domain\Audit\AuditLogger;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_audit_log_listing(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        AuditLogger::log(
            action: 'company.created',
            module: 'companies',
            description: 'Test company created.',
            old: [],
            new: ['name' => 'Test Co'],
        );

        $response = $this->actingAs($admin)->get(route('admin.audit-logs.index'));

        $response->assertOk();
        $response->assertSee('company.created');
        $response->assertSee('companies');
    }

    public function test_guest_is_redirected_away(): void
    {
        $this->get(route('admin.audit-logs.index'))->assertRedirect(route('admin.login'));
    }

    /**
     * PRD #4.2: a Company must never be able to access audit logs.
     */
    public function test_company_user_cannot_view_audit_logs(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();

        $this->actingAs($user)
            ->get(route('admin.audit-logs.index'))
            ->assertForbidden();
    }

    public function test_the_sidebar_links_to_it(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertSee(route('admin.audit-logs.index'), false);
    }
}
