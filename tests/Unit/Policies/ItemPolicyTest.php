<?php

namespace Tests\Unit\Policies;

use App\Models\Company;
use App\Models\Item;
use App\Models\User;
use App\Policies\ItemPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_company_user_may_only_touch_its_own_companys_items(): void
    {
        $policy = new ItemPolicy;

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->company($companyA)->create();

        $ownItem = Item::factory()->create(['company_id' => $companyA->id]);
        $otherCompanysItem = Item::factory()->create(['company_id' => $companyB->id]);

        $this->assertTrue($policy->view($userA, $ownItem));
        $this->assertTrue($policy->update($userA, $ownItem));
        $this->assertTrue($policy->delete($userA, $ownItem));

        $this->assertFalse($policy->view($userA, $otherCompanysItem));
        $this->assertFalse($policy->update($userA, $otherCompanysItem));
        $this->assertFalse($policy->delete($userA, $otherCompanysItem));
    }
}
