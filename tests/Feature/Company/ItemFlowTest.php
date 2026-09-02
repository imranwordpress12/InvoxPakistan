<?php

namespace Tests\Feature\Company;

use App\Models\Company;
use App\Models\HsCode;
use App\Models\Item;
use App\Models\SaleType;
use App\Models\Subscription;
use App\Models\TaxRate;
use App\Models\UnitOfMeasure;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemFlowTest extends TestCase
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

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_replace([
            'item_code' => 'ITM-001',
            'item_name' => 'Laptop',
            'item_type' => Item::TYPE_GOODS,
            'sale_type' => 'Goods at standard rate (default)',
            'hs_code' => '8471.3000',
            'rate' => 18,
            'uom' => 'Number',
            'purchase_price' => 500,
            'sale_price' => 650,
            'stock_quantity' => 10,
            'reorder_level' => 2,
            'description' => 'A laptop.',
            'status' => Item::STATUS_ACTIVE,
        ], $overrides);
    }

    public function test_the_create_form_renders_reference_data(): void
    {
        [, $user] = $this->activeCompanyUser();
        SaleType::factory()->create(['name' => 'Goods at standard rate (default)']);
        HsCode::factory()->create(['code' => '1234.5678']);
        UnitOfMeasure::factory()->create(['name' => 'Number']);
        TaxRate::factory()->create(['rate' => 18]);

        $response = $this->actingAs($user)->get(route('company.items.create'));

        $response->assertOk();
        $response->assertSee('Goods at standard rate (default)');
        $response->assertSee('1234.5678');
        $response->assertSee('Number');
    }

    public function test_it_creates_an_item_scoped_to_the_authenticated_company(): void
    {
        [$company, $user] = $this->activeCompanyUser();

        $response = $this->actingAs($user)->post(route('company.items.store'), $this->validPayload());

        $response->assertRedirect(route('company.items.index'));
        $this->assertDatabaseHas('items', [
            'company_id' => $company->id,
            'item_code' => 'ITM-001',
            'item_name' => 'Laptop',
        ]);
    }

    public function test_item_code_and_item_name_are_required(): void
    {
        [, $user] = $this->activeCompanyUser();

        $response = $this->actingAs($user)->post(
            route('company.items.store'),
            $this->validPayload(['item_code' => '', 'item_name' => ''])
        );

        $response->assertSessionHasErrors(['item_code', 'item_name']);
        $this->assertDatabaseCount('items', 0);
    }

    public function test_item_code_must_be_unique_within_the_company(): void
    {
        [$company, $user] = $this->activeCompanyUser();
        Item::factory()->create(['company_id' => $company->id, 'item_code' => 'ITM-001']);

        $response = $this->actingAs($user)->post(
            route('company.items.store'),
            $this->validPayload(['item_code' => 'ITM-001'])
        );

        $response->assertSessionHasErrors('item_code');
        $this->assertDatabaseCount('items', 1);
    }

    public function test_the_same_item_code_is_allowed_across_different_companies(): void
    {
        [, $userA] = $this->activeCompanyUser();
        [$companyB] = $this->activeCompanyUser();
        Item::factory()->create(['company_id' => $companyB->id, 'item_code' => 'ITM-001']);

        $response = $this->actingAs($userA)->post(
            route('company.items.store'),
            $this->validPayload(['item_code' => 'ITM-001'])
        );

        $response->assertRedirect(route('company.items.index'));
        $this->assertDatabaseCount('items', 2);
    }

    public function test_an_item_can_be_updated_and_keep_its_own_item_code(): void
    {
        [$company, $user] = $this->activeCompanyUser();
        $item = Item::factory()->create(['company_id' => $company->id, 'item_code' => 'ITM-001']);

        $response = $this->actingAs($user)->put(
            route('company.items.update', $item),
            $this->validPayload(['item_code' => 'ITM-001', 'item_name' => 'Updated Name'])
        );

        $response->assertRedirect(route('company.items.index'));
        $this->assertSame('Updated Name', $item->refresh()->item_name);
    }

    public function test_the_rendered_edit_form_spoofs_the_put_method(): void
    {
        [$company, $user] = $this->activeCompanyUser();
        $item = Item::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->get(route('company.items.edit', $item));

        $response->assertOk();
        $response->assertSee('<input type="hidden" name="_method" value="PUT">', false);
    }

    public function test_a_company_cannot_view_or_edit_another_companys_item(): void
    {
        [, $user] = $this->activeCompanyUser();
        [$otherCompany] = $this->activeCompanyUser();
        $foreignItem = Item::factory()->create(['company_id' => $otherCompany->id]);

        $this->actingAs($user)->get(route('company.items.edit', $foreignItem))->assertForbidden();
        $this->actingAs($user)
            ->put(route('company.items.update', $foreignItem), $this->validPayload())
            ->assertForbidden();
    }

    public function test_guests_cannot_reach_the_create_or_edit_routes(): void
    {
        $item = Item::factory()->create();

        $this->get(route('company.items.create'))->assertRedirect(route('company.login'));
        $this->get(route('company.items.edit', $item))->assertRedirect(route('company.login'));
    }

    public function test_a_blocked_company_cannot_reach_the_create_form(): void
    {
        $company = Company::factory()->create();
        Subscription::factory()->for($company)->pending()->create();
        $user = User::factory()->company($company)->create();

        $this->actingAs($user)
            ->get(route('company.items.create'))
            ->assertRedirect(route('company.subscription-required'));
    }
}
