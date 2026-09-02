<?php

namespace Tests\Feature\Company;

use App\Models\Company;
use App\Models\Item;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemControllerTest extends TestCase
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

    public function test_it_lists_only_this_companys_items(): void
    {
        [$company, $user] = $this->activeCompanyUser();
        [$otherCompany] = $this->activeCompanyUser();

        Item::factory()->create(['company_id' => $company->id, 'item_name' => 'My Own Item']);
        Item::factory()->create(['company_id' => $otherCompany->id, 'item_name' => 'Someone Elses Item']);

        $response = $this->actingAs($user)->get(route('company.items.index'));

        $response->assertOk();
        $response->assertSee('My Own Item');
        $response->assertDontSee('Someone Elses Item');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('company.items.index'))->assertRedirect(route('company.login'));
    }

    public function test_a_blocked_company_is_redirected_to_subscription_required(): void
    {
        $company = Company::factory()->create();
        Subscription::factory()->for($company)->pending()->create();
        $user = User::factory()->company($company)->create();

        $this->actingAs($user)
            ->get(route('company.items.index'))
            ->assertRedirect(route('company.subscription-required'));
    }

    public function test_admin_cannot_access_the_company_item_listing(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('company.items.index'))
            ->assertForbidden();
    }
}
