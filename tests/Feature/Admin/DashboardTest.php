<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_admin_can_view_the_dashboard(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_guest_is_redirected_away(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_company_user_cannot_view_the_admin_dashboard(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    /**
     * "Paid" / "Pending" on the summary cards means the same thing the
     * access middleware means: currently active right now, not just
     * whatever the stored status column says (PRD #10).
     */
    public function test_summary_cards_count_companies_by_real_time_active_state(): void
    {
        // Genuinely active.
        Subscription::factory()
            ->for(Company::factory())
            ->create(['status' => Subscription::STATUS_ACTIVE, 'ends_at' => now()->addDays(10)]);

        // Genuinely pending.
        Subscription::factory()->for(Company::factory())->pending()->create();

        // Stored as "active" but the date has already lapsed — must count
        // as pending here too, exactly like Subscription::isActive() would.
        Subscription::factory()
            ->for(Company::factory())
            ->create(['status' => Subscription::STATUS_ACTIVE, 'ends_at' => now()->subDay()]);

        // No subscription at all — also "needs attention".
        Company::factory()->create();

        $response = $this->actingAs($this->admin())->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('totalCompanies', 4);
        $response->assertViewHas('paidCompanies', 1);
        $response->assertViewHas('pendingCompanies', 3);
    }

    public function test_companies_overview_lists_companies_with_their_current_subscription(): void
    {
        $company = Company::factory()->create(['name' => 'Overview Co']);
        Subscription::factory()->for($company)->create([
            'type' => Subscription::TYPE_YEARLY,
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->addYear(),
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.dashboard'));

        $response->assertSee('Overview Co');
        $response->assertSee('Yearly');
    }

    public function test_recent_payments_only_shows_paid_transactions_newest_first(): void
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create();

        $older = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => 'INV-OLDER',
            'status' => Transaction::STATUS_PAID,
            'paid_at' => now()->subDays(5),
        ]);
        $newer = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => 'INV-NEWER',
            'status' => Transaction::STATUS_PAID,
            'paid_at' => now()->subDay(),
        ]);
        $pending = Transaction::factory()->pending()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => 'INV-PENDING',
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.dashboard'));

        $payments = $response->viewData('recentPayments');

        $this->assertTrue($payments->pluck('invoice_number')->contains('INV-OLDER'));
        $this->assertTrue($payments->pluck('invoice_number')->contains('INV-NEWER'));
        $this->assertFalse($payments->pluck('invoice_number')->contains('INV-PENDING'));
        $this->assertSame('INV-NEWER', $payments->first()->invoice_number);
        $this->assertSame($newer->id, $payments->first()->id);
        $this->assertSame($older->id, $payments->last()->id);
    }
}
