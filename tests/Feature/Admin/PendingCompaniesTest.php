<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PRD #16: Pending Companies is a filtered view, not a separate entity —
 * these tests exercise the same "not currently active" predicate the admin
 * dashboard's summary card (Phase 8) already uses.
 */
class PendingCompaniesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_guest_is_redirected_away(): void
    {
        $this->get(route('admin.companies.pending'))->assertRedirect(route('admin.login'));
    }

    public function test_company_user_cannot_view_it(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();

        $this->actingAs($user)
            ->get(route('admin.companies.pending'))
            ->assertForbidden();
    }

    public function test_an_active_company_does_not_appear(): void
    {
        Subscription::factory()->for(Company::factory()->create(['name' => 'Active Co']))
            ->create(['status' => Subscription::STATUS_ACTIVE, 'ends_at' => now()->addDays(10)]);

        $this->actingAs($this->admin())
            ->get(route('admin.companies.pending'))
            ->assertDontSee('Active Co');
    }

    public function test_a_genuinely_pending_subscription_appears(): void
    {
        $company = Company::factory()->create(['name' => 'Pending Co']);
        Subscription::factory()->for($company)->pending()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.companies.pending'))
            ->assertSee('Pending Co');
    }

    public function test_a_stale_active_status_past_its_end_date_appears(): void
    {
        $company = Company::factory()->create(['name' => 'Stale Co']);
        Subscription::factory()->for($company)->create([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->subDay(),
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.companies.pending'))
            ->assertSee('Stale Co');
    }

    public function test_a_company_with_no_subscription_at_all_appears(): void
    {
        Company::factory()->create(['name' => 'No Sub Co']);

        $this->actingAs($this->admin())
            ->get(route('admin.companies.pending'))
            ->assertSee('No Sub Co');
    }

    public function test_it_shows_the_pending_transactions_invoice_amount_and_due_date_when_one_exists(): void
    {
        $company = Company::factory()->create(['name' => 'Invoiced Co']);
        $subscription = Subscription::factory()->for($company)->pending()->create(['amount' => 9999]);
        Transaction::factory()->pending()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => 'INV-VISIBLE-1',
            'amount' => 4321,
            'due_at' => now()->addDays(2),
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.companies.pending'));

        $response->assertSee('INV-VISIBLE-1');
        $response->assertSee('4,321.00');
        $response->assertDontSee('9,999.00');
    }

    public function test_it_falls_back_to_the_subscriptions_amount_and_end_date_when_no_pending_transaction_exists(): void
    {
        $company = Company::factory()->create(['name' => 'No Invoice Co']);
        Subscription::factory()->for($company)->create([
            'status' => Subscription::STATUS_PENDING,
            'amount' => 7777,
            'ends_at' => now()->addDays(3),
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.companies.pending'));

        $response->assertSee('7,777.00');
    }

    public function test_mark_as_paid_is_only_offered_when_a_pending_transaction_exists(): void
    {
        $withInvoice = Company::factory()->create();
        $subWithInvoice = Subscription::factory()->for($withInvoice)->pending()->create();
        $transaction = Transaction::factory()->pending()->create([
            'company_id' => $withInvoice->id,
            'subscription_id' => $subWithInvoice->id,
        ]);

        $withoutInvoice = Company::factory()->create();
        Subscription::factory()->for($withoutInvoice)->pending()->create();

        $response = $this->actingAs($this->admin())->get(route('admin.companies.pending'));

        $response->assertSee(route('admin.transactions.mark-paid', $transaction), false);
    }

    public function test_it_can_be_searched_by_name(): void
    {
        Subscription::factory()->for(Company::factory()->create(['name' => 'Findable Pending']))->pending()->create();
        Subscription::factory()->for(Company::factory()->create(['name' => 'Other Pending']))->pending()->create();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.companies.pending', ['search' => 'Findable']));

        $response->assertSee('Findable Pending');
        $response->assertDontSee('Other Pending');
    }

    public function test_the_due_by_filter_only_matches_companies_with_a_matching_pending_invoice(): void
    {
        $dueSoon = Company::factory()->create(['name' => 'Due Soon Co']);
        $subDueSoon = Subscription::factory()->for($dueSoon)->pending()->create();
        Transaction::factory()->pending()->create([
            'company_id' => $dueSoon->id,
            'subscription_id' => $subDueSoon->id,
            'due_at' => now()->addDay(),
        ]);

        $dueLater = Company::factory()->create(['name' => 'Due Later Co']);
        $subDueLater = Subscription::factory()->for($dueLater)->pending()->create();
        Transaction::factory()->pending()->create([
            'company_id' => $dueLater->id,
            'subscription_id' => $subDueLater->id,
            'due_at' => now()->addDays(30),
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.companies.pending', [
            'due_by' => now()->addDays(5)->toDateString(),
        ]));

        $response->assertSee('Due Soon Co');
        $response->assertDontSee('Due Later Co');
    }

    public function test_the_companies_index_page_links_to_the_pending_companies_page(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.companies.index'));

        $response->assertSee(route('admin.companies.pending'), false);
    }

    /**
     * The listing's row count and the dashboard's "Pending Companies" card
     * (Phase 8) must always agree — both are the same predicate.
     */
    public function test_the_pending_listing_count_matches_the_dashboard_summary_card(): void
    {
        Subscription::factory()->for(Company::factory())->create([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->addDays(10),
        ]);
        Subscription::factory()->for(Company::factory())->pending()->create();
        Subscription::factory()->for(Company::factory())->expired()->create();
        Company::factory()->create();

        $admin = $this->admin();

        $pendingListing = $this->actingAs($admin)->get(route('admin.companies.pending'));
        $dashboard = $this->actingAs($admin)->get(route('admin.dashboard'));

        $this->assertSame(3, $pendingListing->viewData('companies')->total());
        $dashboard->assertViewHas('pendingCompanies', 3);
    }
}
