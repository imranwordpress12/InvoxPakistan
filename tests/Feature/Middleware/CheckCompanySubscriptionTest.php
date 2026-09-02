<?php

namespace Tests\Feature\Middleware;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PRD #10/#11/#62 Phase 7 acceptance criteria: Active -> Access,
 * Pending -> Block, Expired -> Block — enforced by the `subscription`
 * middleware on the company route group.
 */
class CheckCompanySubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private function companyUser(Subscription $subscription): User
    {
        return User::factory()->company($subscription->company)->create();
    }

    public function test_active_subscription_is_allowed_through(): void
    {
        $subscription = Subscription::factory()->create([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->addDays(10),
        ]);

        $this->actingAs($this->companyUser($subscription))
            ->get(route('company.dashboard'))
            ->assertOk();
    }

    public function test_pending_subscription_is_blocked(): void
    {
        $subscription = Subscription::factory()->pending()->create();

        $this->actingAs($this->companyUser($subscription))
            ->get(route('company.dashboard'))
            ->assertRedirect(route('company.subscription-required'));
    }

    public function test_expired_subscription_is_blocked(): void
    {
        $subscription = Subscription::factory()->expired()->create();

        $this->actingAs($this->companyUser($subscription))
            ->get(route('company.dashboard'))
            ->assertRedirect(route('company.subscription-required'));
    }

    public function test_cancelled_subscription_is_blocked(): void
    {
        $subscription = Subscription::factory()->cancelled()->create();

        $this->actingAs($this->companyUser($subscription))
            ->get(route('company.dashboard'))
            ->assertRedirect(route('company.subscription-required'));
    }

    /**
     * Status still says "active" in the DB but ends_at has already
     * passed — the middleware must catch this via isActive() even before
     * Phase 11's scheduler ever runs to reconcile the stored status.
     */
    public function test_a_stale_active_status_past_its_end_date_is_blocked(): void
    {
        $subscription = Subscription::factory()->create([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->subDay(),
        ]);

        $this->actingAs($this->companyUser($subscription))
            ->get(route('company.dashboard'))
            ->assertRedirect(route('company.subscription-required'));
    }

    public function test_a_company_with_no_subscription_at_all_is_blocked(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();

        $this->actingAs($user)
            ->get(route('company.dashboard'))
            ->assertRedirect(route('company.subscription-required'));
    }

    /**
     * Phase 12 finding: a soft-deleted company's user must not retain real
     * access. Verified this is already correct — SoftDeletes' global scope
     * makes `$user->company` resolve to null for a deleted company (the
     * relation query is filtered the same as any other Company query), so
     * this falls straight through the "no company" branch above with no
     * extra code needed. Locked in with an explicit test since Phase 4's
     * changelog had flagged this as an open question rather than confirmed.
     */
    public function test_a_soft_deleted_companys_user_is_blocked_from_the_dashboard(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();
        Subscription::factory()->for($company)->create([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->addDays(10),
        ]);
        $company->delete();

        $this->actingAs($user)
            ->get(route('company.dashboard'))
            ->assertRedirect(route('company.subscription-required'));
    }

    public function test_the_subscription_required_page_is_reachable_while_blocked_and_shows_the_prd_message(): void
    {
        $subscription = Subscription::factory()->pending()->create();

        $this->actingAs($this->companyUser($subscription))
            ->get(route('company.subscription-required'))
            ->assertOk()
            ->assertSee('Your subscription has expired or payment is pending.')
            ->assertSee(ucfirst($subscription->type));
    }

    public function test_the_subscription_required_page_redirects_an_active_company_to_the_dashboard(): void
    {
        $subscription = Subscription::factory()->create([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->addDays(10),
        ]);

        $this->actingAs($this->companyUser($subscription))
            ->get(route('company.subscription-required'))
            ->assertRedirect(route('company.dashboard'));
    }

    public function test_a_blocked_company_user_can_still_log_out(): void
    {
        $subscription = Subscription::factory()->pending()->create();
        $user = $this->companyUser($subscription);

        $response = $this->actingAs($user)->post(route('company.logout'));

        $response->assertRedirect(route('company.login'));
        $this->assertGuest();
    }

    public function test_guest_is_still_redirected_to_login_before_the_subscription_check_ever_runs(): void
    {
        $this->get(route('company.dashboard'))->assertRedirect(route('company.login'));
        $this->get(route('company.subscription-required'))->assertRedirect(route('company.login'));
    }

    public function test_an_admin_cannot_reach_company_dashboard_regardless_of_subscription_middleware(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('company.dashboard'))
            ->assertForbidden();
    }
}
