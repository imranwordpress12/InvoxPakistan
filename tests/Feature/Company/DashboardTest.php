<?php

namespace Tests\Feature\Company;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The dashboard was redesigned for the Company Dashboard PRD around
     * invoice stats instead of a full subscription details table (see
     * CHANGELOG_PROJECT.md) — a healthy, active subscription now just
     * means no warning banner and the invoice-stats page itself renders.
     */
    public function test_a_healthy_active_subscription_shows_no_warning_and_the_invoice_stats_page_renders(): void
    {
        $company = Company::factory()->create();
        Subscription::factory()->for($company)->create([
            'type' => Subscription::TYPE_MONTHLY,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            // endOfDay() so this doesn't flake if the assertion runs a
            // moment after creation and crosses a whole-day boundary.
            'ends_at' => now()->addDays(22)->endOfDay(),
        ]);
        $user = User::factory()->company($company)->create();

        $response = $this->actingAs($user)->get(route('company.dashboard'));

        $response->assertOk();
        $response->assertSee('Stats of Invoices Submitted to FBR');
        $response->assertDontSee('expired or payment is pending');
        $response->assertDontSee('will expire in');
    }

    public function test_it_warns_when_the_subscription_is_close_to_expiry(): void
    {
        $company = Company::factory()->create();
        Subscription::factory()->for($company)->create([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->addDays(3)->endOfDay(),
        ]);
        $user = User::factory()->company($company)->create();

        $this->actingAs($user)
            ->get(route('company.dashboard'))
            ->assertSee('will expire in');
    }

    public function test_it_does_not_warn_when_the_subscription_is_not_close_to_expiry(): void
    {
        $company = Company::factory()->create();
        Subscription::factory()->for($company)->create([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->addDays(20),
        ]);
        $user = User::factory()->company($company)->create();

        $this->actingAs($user)
            ->get(route('company.dashboard'))
            ->assertDontSee('will expire in');
    }

    // ---- Phase 7: these states no longer render the dashboard at all —
    // the `subscription` middleware redirects them to
    // company.subscription-required before this controller ever runs.
    // The actual blocked-page content is covered in
    // Feature\Middleware\CheckCompanySubscriptionTest.

    public function test_a_pending_subscription_is_redirected_away_from_the_dashboard(): void
    {
        $company = Company::factory()->create();
        Subscription::factory()->for($company)->pending()->create();
        $user = User::factory()->company($company)->create();

        $this->actingAs($user)
            ->get(route('company.dashboard'))
            ->assertRedirect(route('company.subscription-required'));
    }

    public function test_a_company_with_no_subscription_yet_is_redirected_away_from_the_dashboard(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();

        $this->actingAs($user)
            ->get(route('company.dashboard'))
            ->assertRedirect(route('company.subscription-required'));
    }
}
