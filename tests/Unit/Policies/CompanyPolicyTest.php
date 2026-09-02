<?php

namespace Tests\Unit\Policies;

use App\Models\Company;
use App\Models\User;
use App\Policies\CompanyPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admins_are_authorized(): void
    {
        $policy = new CompanyPolicy;
        $admin = User::factory()->admin()->create();
        $company = Company::factory()->create();
        $companyUser = User::factory()->company($company)->create();

        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->create($admin));
        $this->assertTrue($policy->view($admin, $company));
        $this->assertTrue($policy->update($admin, $company));
        $this->assertTrue($policy->delete($admin, $company));

        $this->assertFalse($policy->viewAny($companyUser));
        $this->assertFalse($policy->create($companyUser));
        $this->assertFalse($policy->view($companyUser, $company));
        $this->assertFalse($policy->update($companyUser, $company));
        $this->assertFalse($policy->delete($companyUser, $company));
    }
}
