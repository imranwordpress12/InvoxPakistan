<?php

namespace Tests\Feature\Company;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    private function activeCompanyUser(): array
    {
        $company = Company::factory()->create();
        Subscription::factory()->for($company)->create([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->addDays(30),
        ]);
        $user = User::factory()->company($company)->create();

        return [$company, $user];
    }

    public function test_it_lists_only_this_companys_customers(): void
    {
        [$company, $user] = $this->activeCompanyUser();
        [$otherCompany] = $this->activeCompanyUser();

        Customer::factory()->create(['company_id' => $company->id, 'business_name' => 'My Own Customer']);
        Customer::factory()->create(['company_id' => $otherCompany->id, 'business_name' => 'Someone Elses Customer']);

        $response = $this->actingAs($user)->get(route('company.customers.index'));

        $response->assertOk();
        $response->assertSee('My Own Customer');
        $response->assertDontSee('Someone Elses Customer');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('company.customers.index'))->assertRedirect(route('company.login'));
    }

    public function test_a_blocked_company_is_redirected_to_subscription_required(): void
    {
        $company = Company::factory()->create();
        Subscription::factory()->for($company)->pending()->create();
        $user = User::factory()->company($company)->create();

        $this->actingAs($user)
            ->get(route('company.customers.index'))
            ->assertRedirect(route('company.subscription-required'));
    }

    public function test_admin_cannot_access_the_company_customer_listing(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('company.customers.index'))
            ->assertForbidden();
    }
}
