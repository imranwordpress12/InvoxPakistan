<?php

namespace Tests\Unit\Policies;

use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use App\Policies\CustomerPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Company Dashboard PRD #18: a company user must never be able to access
 * another company's customer, even by guessing/changing an ID.
 */
class CustomerPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_company_user_may_only_touch_its_own_companys_customers(): void
    {
        $policy = new CustomerPolicy;

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->company($companyA)->create();

        $ownCustomer = Customer::factory()->create(['company_id' => $companyA->id]);
        $otherCompanysCustomer = Customer::factory()->create(['company_id' => $companyB->id]);

        $this->assertTrue($policy->view($userA, $ownCustomer));
        $this->assertTrue($policy->update($userA, $ownCustomer));
        $this->assertTrue($policy->delete($userA, $ownCustomer));

        $this->assertFalse($policy->view($userA, $otherCompanysCustomer));
        $this->assertFalse($policy->update($userA, $otherCompanysCustomer));
        $this->assertFalse($policy->delete($userA, $otherCompanysCustomer));
    }

    public function test_an_admin_has_no_special_access_to_customers(): void
    {
        $policy = new CustomerPolicy;
        $admin = User::factory()->admin()->create();
        $customer = Customer::factory()->create();

        $this->assertFalse($policy->viewAny($admin));
        $this->assertFalse($policy->view($admin, $customer));
    }
}
