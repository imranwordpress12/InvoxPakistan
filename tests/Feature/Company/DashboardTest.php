<?php

namespace Tests\Feature\Company;

use App\Models\Company;
use App\Models\Invoice;
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
        $response->assertSee('Company Dashboard');
        $response->assertSee('refreshing every 30 seconds');
        $response->assertSee('refreshDashboardStats', false);
        $response->assertDontSee('expired or payment is pending');
        $response->assertDontSee('will expire in');
    }

    public function test_live_dashboard_stats_use_submission_date_and_only_include_the_authenticated_company(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 5)->setTime(12, 0));

        $company = Company::factory()->create();
        Subscription::factory()->for($company)->create([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->addDays(30),
        ]);
        $user = User::factory()->company($company)->create();

        Invoice::factory()->create([
            'company_id' => $company->id,
            'status' => Invoice::STATUS_SUCCESSFUL,
            'invoice_date' => '2026-09-20',
            'submitted_at' => now(),
            'total_amount' => 120,
            'total_excl_st' => 100,
            'total_sales_tax' => 20,
        ]);
        Invoice::factory()->create([
            'company_id' => $company->id,
            'status' => Invoice::STATUS_FAILED,
            'invoice_date' => '2026-09-21',
            'submitted_at' => now(),
            'total_amount' => 60,
            'total_excl_st' => 50,
            'total_sales_tax' => 10,
        ]);
        Invoice::factory()->create([
            'company_id' => $company->id,
            'status' => Invoice::STATUS_SUCCESSFUL,
            'submitted_at' => now()->subDay(),
            'total_amount' => 900,
        ]);
        Invoice::factory()->create([
            'status' => Invoice::STATUS_SUCCESSFUL,
            'submitted_at' => now(),
            'total_amount' => 5000,
        ]);

        $response = $this->actingAs($user)->getJson(route('company.dashboard.stats', [
            'date_from' => '2026-10-05',
            'date_to' => '2026-10-05',
        ]));

        $response
            ->assertOk()
            ->assertJsonPath('totalStats.count', 2)
            ->assertJsonPath('totalStats.total_amount', 180)
            ->assertJsonPath('totalStats.total_excl_st', 150)
            ->assertJsonPath('totalStats.total_sales_tax', 30)
            ->assertJsonPath('successfulStats.count', 1)
            ->assertJsonPath('failedStats.count', 1)
            ->assertJsonPath('dailyStatus.0.successful', 1)
            ->assertJsonPath('dailyStatus.0.failed', 1)
            ->assertJsonPath('dailyAmounts.0.successful_amount', 120)
            ->assertJsonPath('dailyAmounts.0.failed_amount', 60);

        $cacheControl = $response->headers->get('Cache-Control');

        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
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
