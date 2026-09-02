<?php

namespace Tests\Feature\Company;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Province;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerFlowTest extends TestCase
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
            'business_name' => 'Acme Traders',
            'ntn_cnic' => '1234567-8',
            'province' => 'SINDH',
            'buyer_registration_type' => Customer::REGISTRATION_TYPE_REGISTERED,
            'strn' => '12-34-5678-901-23',
            'contact_person' => 'John Doe',
            'email' => 'john@example.com',
            'contact_number' => '03001234567',
            'address' => '123 Main St',
            'status' => Customer::STATUS_ACTIVE,
        ], $overrides);
    }

    public function test_the_create_form_renders_province_options(): void
    {
        [, $user] = $this->activeCompanyUser();
        Province::factory()->create(['name' => 'SINDH']);

        $response = $this->actingAs($user)->get(route('company.customers.create'));

        $response->assertOk();
        $response->assertSee('SINDH');
    }

    public function test_it_creates_a_customer_scoped_to_the_authenticated_company(): void
    {
        [$company, $user] = $this->activeCompanyUser();

        $response = $this->actingAs($user)->post(route('company.customers.store'), $this->validPayload());

        $response->assertRedirect(route('company.customers.index'));
        $this->assertDatabaseHas('customers', [
            'company_id' => $company->id,
            'business_name' => 'Acme Traders',
            'ntn_cnic' => '1234567-8',
        ]);
    }

    public function test_business_name_and_address_are_required(): void
    {
        [, $user] = $this->activeCompanyUser();

        $response = $this->actingAs($user)->post(
            route('company.customers.store'),
            $this->validPayload(['business_name' => '', 'address' => ''])
        );

        $response->assertSessionHasErrors(['business_name', 'address']);
        $this->assertDatabaseCount('customers', 0);
    }

    public function test_a_customer_can_be_edited(): void
    {
        [$company, $user] = $this->activeCompanyUser();
        $customer = Customer::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->get(route('company.customers.edit', $customer));
        $response->assertOk();
        $response->assertSee($customer->business_name);
    }

    public function test_updating_a_customer_persists_changes(): void
    {
        [$company, $user] = $this->activeCompanyUser();
        $customer = Customer::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->put(
            route('company.customers.update', $customer),
            $this->validPayload(['business_name' => 'Updated Name', 'status' => Customer::STATUS_INACTIVE])
        );

        $response->assertRedirect(route('company.customers.index'));
        $customer->refresh();
        $this->assertSame('Updated Name', $customer->business_name);
        $this->assertSame(Customer::STATUS_INACTIVE, $customer->status);
    }

    public function test_the_rendered_edit_form_spoofs_the_put_method(): void
    {
        [$company, $user] = $this->activeCompanyUser();
        $customer = Customer::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->get(route('company.customers.edit', $customer));

        $response->assertOk();
        $response->assertSee('<input type="hidden" name="_method" value="PUT">', false);
    }

    public function test_a_company_cannot_view_or_edit_another_companys_customer(): void
    {
        [, $user] = $this->activeCompanyUser();
        [$otherCompany] = $this->activeCompanyUser();
        $foreignCustomer = Customer::factory()->create(['company_id' => $otherCompany->id]);

        $this->actingAs($user)->get(route('company.customers.edit', $foreignCustomer))->assertForbidden();
        $this->actingAs($user)
            ->put(route('company.customers.update', $foreignCustomer), $this->validPayload())
            ->assertForbidden();
    }

    public function test_an_invalid_registration_type_is_rejected(): void
    {
        [, $user] = $this->activeCompanyUser();

        $response = $this->actingAs($user)->post(
            route('company.customers.store'),
            $this->validPayload(['buyer_registration_type' => 'not-a-real-type'])
        );

        $response->assertSessionHasErrors('buyer_registration_type');
    }

    public function test_guests_cannot_reach_the_create_or_edit_routes(): void
    {
        $customer = Customer::factory()->create();

        $this->get(route('company.customers.create'))->assertRedirect(route('company.login'));
        $this->get(route('company.customers.edit', $customer))->assertRedirect(route('company.login'));
    }

    public function test_a_blocked_company_cannot_reach_the_create_form(): void
    {
        $company = Company::factory()->create();
        Subscription::factory()->for($company)->pending()->create();
        $user = User::factory()->company($company)->create();

        $this->actingAs($user)
            ->get(route('company.customers.create'))
            ->assertRedirect(route('company.subscription-required'));
    }
}
