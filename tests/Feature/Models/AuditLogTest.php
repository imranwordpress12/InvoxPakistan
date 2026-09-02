<?php

namespace Tests\Feature\Models;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_belongs_to_user_and_company_and_casts_values_to_arrays(): void
    {
        $user = User::factory()->admin()->create();
        $company = Company::factory()->create();

        $log = AuditLog::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'old_values' => ['status' => 'pending'],
            'new_values' => ['status' => 'active'],
        ]);

        $this->assertTrue($log->user->is($user));
        $this->assertTrue($log->company->is($company));
        $this->assertSame(['status' => 'pending'], $log->old_values);
        $this->assertSame(['status' => 'active'], $log->new_values);
    }

    public function test_audit_log_survives_hard_deletion_of_its_user(): void
    {
        $user = User::factory()->admin()->create();
        $log = AuditLog::factory()->create(['user_id' => $user->id]);

        $user->delete();

        $this->assertDatabaseHas('audit_logs', ['id' => $log->id]);
        $this->assertNull($log->refresh()->user_id);
    }
}
