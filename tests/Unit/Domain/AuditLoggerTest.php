<?php

namespace Tests\Unit\Domain;

use App\Domain\Audit\AuditLogger;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_who_what_when_module_and_company(): void
    {
        $admin = User::factory()->admin()->create();
        $company = Company::factory()->create();
        $this->actingAs($admin);

        $log = AuditLogger::log(
            action: 'company.updated',
            module: 'companies',
            description: 'Something happened.',
            company: $company,
            old: ['name' => 'Old'],
            new: ['name' => 'New'],
        );

        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame($company->id, $log->company_id);
        $this->assertSame('company.updated', $log->action);
        $this->assertSame('companies', $log->module);
        $this->assertSame('Something happened.', $log->description);
        $this->assertSame(['name' => 'Old'], $log->old_values);
        $this->assertSame(['name' => 'New'], $log->new_values);
        $this->assertNotNull($log->created_at);
    }

    public function test_it_works_for_system_actions_with_no_authenticated_user_or_company(): void
    {
        $log = AuditLogger::log(action: 'subscription.expired', module: 'subscriptions');

        $this->assertNull($log->user_id);
        $this->assertNull($log->company_id);
        $this->assertNull($log->old_values);
        $this->assertNull($log->new_values);
    }

    /**
     * Defense in depth (PRD #33/#54): even if a caller accidentally passes
     * a password or FBR credential, it must never reach the database.
     */
    public function test_it_strips_sensitive_keys_even_if_a_caller_passes_them_by_mistake(): void
    {
        $log = AuditLogger::log(
            action: 'company.updated',
            module: 'companies',
            old: ['name' => 'Old', 'password' => 'should-not-be-stored'],
            new: ['name' => 'New', 'fbr_token_production' => 'should-not-be-stored', 'fbr_token_sandbox' => 'nope'],
        );

        $this->assertArrayNotHasKey('password', $log->old_values);
        $this->assertArrayNotHasKey('fbr_token_production', $log->new_values);
        $this->assertArrayNotHasKey('fbr_token_sandbox', $log->new_values);
        $this->assertSame('New', $log->new_values['name']);
    }
}
