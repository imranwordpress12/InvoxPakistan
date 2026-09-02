<?php

namespace Tests\Feature\Company;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
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

    public function test_it_lists_only_this_companys_non_draft_invoices(): void
    {
        [$company, $user] = $this->activeCompanyUser();
        [$otherCompany] = $this->activeCompanyUser();

        $ownSubmitted = Invoice::factory()->create([
            'company_id' => $company->id,
            'status' => Invoice::STATUS_SUCCESSFUL,
            'invoice_reference_no' => 'OWN-SUBMITTED',
        ]);
        Invoice::factory()->create([
            'company_id' => $company->id,
            'status' => Invoice::STATUS_DRAFT,
            'invoice_reference_no' => 'OWN-DRAFT',
        ]);
        Invoice::factory()->create([
            'company_id' => $otherCompany->id,
            'status' => Invoice::STATUS_SUCCESSFUL,
            'invoice_reference_no' => 'OTHER-COMPANY',
        ]);

        $response = $this->actingAs($user)->get(route('company.invoices.index'));

        $response->assertOk();
        $response->assertSee('OWN-SUBMITTED');
        $response->assertDontSee('OWN-DRAFT');
        $response->assertDontSee('OTHER-COMPANY');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('company.invoices.index'))->assertRedirect(route('company.login'));
    }

    public function test_a_blocked_company_is_redirected_to_subscription_required(): void
    {
        $company = Company::factory()->create();
        Subscription::factory()->for($company)->pending()->create();
        $user = User::factory()->company($company)->create();

        $this->actingAs($user)
            ->get(route('company.invoices.index'))
            ->assertRedirect(route('company.subscription-required'));
    }

    public function test_admin_cannot_access_the_company_invoice_listing(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('company.invoices.index'))
            ->assertForbidden();
    }
}
