<?php

namespace Tests\Feature\Admin;

use App\Mail\CompanyWelcome;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CompanyManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_create_company_without_subscription_or_fbr_info(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->admin)->post(route('admin.companies.store'), [
            'name' => 'Test Company',
            'business_name' => 'Test Company Pvt Ltd',
            'email' => 'info@testcompany.test',
            'phone' => '03001112233',
            'user_email' => 'login@testcompany.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $company = Company::where('email', 'info@testcompany.test')->first();
        $this->assertNotNull($company);

        $response->assertRedirect(route('admin.companies.show', $company));

        $this->assertDatabaseHas('users', [
            'company_id' => $company->id,
            'email' => 'login@testcompany.test',
        ]);

        // Verify no subscription or transactions created on store
        $this->assertSame(0, Subscription::where('company_id', $company->id)->count());
        $this->assertSame(0, Transaction::where('company_id', $company->id)->count());

        Mail::assertSent(CompanyWelcome::class);
    }

    public function test_admin_can_activate_subscription_on_company_edit(): void
    {
        $company = Company::factory()->create();

        $today = now()->format('Y-m-d');
        $response = $this->actingAs($this->admin)->put(route('admin.companies.update', $company), [
            'name' => $company->name,
            'email' => $company->email,
            'user_email' => $company->users()->first()?->email ?? 'login@company.test',
            'status' => Company::STATUS_ACTIVE,
            'subscription_status' => Subscription::STATUS_ACTIVE,
            'subscription_starts_at' => $today,
            'subscription_type' => Subscription::TYPE_MONTHLY,
            'amount' => 1500.00,
        ]);

        $response->assertRedirect(route('admin.companies.show', $company));

        $sub = $company->fresh()->latestSubscription;
        $this->assertNotNull($sub);
        $this->assertSame(Subscription::STATUS_ACTIVE, $sub->status);
        $this->assertSame(Subscription::TYPE_MONTHLY, $sub->type);
        $this->assertSame('1500.00', (string) $sub->amount);
    }

    public function test_past_start_date_is_rejected_on_activation(): void
    {
        $company = Company::factory()->create();

        $pastDate = now()->subDays(5)->format('Y-m-d');
        $response = $this->actingAs($this->admin)->put(route('admin.companies.update', $company), [
            'name' => $company->name,
            'email' => $company->email,
            'user_email' => $company->users()->first()?->email ?? 'login@company.test',
            'status' => Company::STATUS_ACTIVE,
            'subscription_status' => Subscription::STATUS_ACTIVE,
            'subscription_starts_at' => $pastDate,
            'subscription_type' => Subscription::TYPE_MONTHLY,
            'amount' => 1500.00,
        ]);

        $response->assertSessionHasErrors(['subscription_starts_at']);
    }

    public function test_deactivation_deletes_due_and_pending_transactions_but_keeps_paid_and_overdue(): void
    {
        $company = Company::factory()->create();
        $sub = Subscription::factory()->create([
            'company_id' => $company->id,
            'status' => Subscription::STATUS_ACTIVE,
        ]);

        $paidTx = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $sub->id,
            'status' => Transaction::STATUS_PAID,
        ]);

        $overdueTx = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $sub->id,
            'status' => Transaction::STATUS_OVERDUE,
        ]);

        $dueTx = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $sub->id,
            'status' => Transaction::STATUS_DUE,
        ]);

        $pendingTx = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $sub->id,
            'status' => Transaction::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.companies.update', $company), [
            'name' => $company->name,
            'email' => $company->email,
            'user_email' => $company->users()->first()?->email ?? 'login@company.test',
            'status' => Company::STATUS_ACTIVE,
            'subscription_status' => Subscription::STATUS_INACTIVE,
        ]);

        $response->assertRedirect(route('admin.companies.show', $company));

        $this->assertSame(Subscription::STATUS_INACTIVE, $sub->fresh()->status);
        $this->assertDatabaseHas('transactions', ['id' => $paidTx->id]);
        $this->assertDatabaseHas('transactions', ['id' => $overdueTx->id]);
        $this->assertDatabaseMissing('transactions', ['id' => $dueTx->id]);
        $this->assertDatabaseMissing('transactions', ['id' => $pendingTx->id]);
    }

    public function test_reactivation_requires_new_future_or_today_start_date(): void
    {
        $company = Company::factory()->create();
        $sub = Subscription::factory()->create([
            'company_id' => $company->id,
            'status' => Subscription::STATUS_INACTIVE,
        ]);

        $today = now()->format('Y-m-d');
        $response = $this->actingAs($this->admin)->put(route('admin.companies.update', $company), [
            'name' => $company->name,
            'email' => $company->email,
            'user_email' => $company->users()->first()?->email ?? 'login@company.test',
            'status' => Company::STATUS_ACTIVE,
            'subscription_status' => Subscription::STATUS_ACTIVE,
            'subscription_starts_at' => $today,
            'subscription_type' => Subscription::TYPE_YEARLY,
            'amount' => 12000.00,
        ]);

        $response->assertRedirect(route('admin.companies.show', $company));

        $newSub = $company->fresh()->latestSubscription;
        $this->assertNotNull($newSub);
        $this->assertSame(Subscription::STATUS_ACTIVE, $newSub->status);
        $this->assertSame(Subscription::TYPE_YEARLY, $newSub->type);
    }

    public function test_type_change_recalculates_unpaid_transaction_dates_and_resets_status_to_due(): void
    {
        $company = Company::factory()->create();
        $sub = Subscription::factory()->create([
            'company_id' => $company->id,
            'type' => Subscription::TYPE_MONTHLY,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => Carbon::parse('2026-11-01'),
            'ends_at' => Carbon::parse('2026-11-30'),
        ]);

        $paidTx = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $sub->id,
            'status' => Transaction::STATUS_PAID,
            'billing_period_start' => '2026-10-01',
            'billing_period_end' => '2026-10-31',
        ]);

        $pendingTx = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $sub->id,
            'status' => Transaction::STATUS_PENDING,
            'billing_period_start' => '2026-11-01',
            'billing_period_end' => '2026-11-30',
            'due_at' => '2026-11-30',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.companies.update', $company), [
            'name' => $company->name,
            'email' => $company->email,
            'user_email' => $company->users()->first()?->email ?? 'login@company.test',
            'status' => Company::STATUS_ACTIVE,
            'subscription_status' => Subscription::STATUS_ACTIVE,
            'subscription_starts_at' => '2026-11-01',
            'subscription_type' => Subscription::TYPE_YEARLY,
            'amount' => 10000.00,
        ]);

        $response->assertRedirect(route('admin.companies.show', $company));

        // Paid transaction is NOT touched
        $this->assertSame('2026-10-31', $paidTx->fresh()->billing_period_end->toDateString());

        // Pending transaction is updated to Due with recalculated end date
        $updatedTx = $pendingTx->fresh();
        $this->assertSame(Transaction::STATUS_DUE, $updatedTx->status);
        $this->assertSame('2026-11-01', $updatedTx->billing_period_start->toDateString());
        $this->assertSame('2027-10-31', $updatedTx->billing_period_end->toDateString());
    }

    public function test_admin_can_update_fbr_credentials_and_status(): void
    {
        $company = Company::factory()->create([
            'fbr_status' => Company::FBR_STATUS_INACTIVE,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.companies.update', $company), [
            'name' => $company->name,
            'email' => $company->email,
            'user_email' => $company->users()->first()?->email ?? 'login@company.test',
            'status' => Company::STATUS_ACTIVE,
            'fbr_status' => Company::FBR_STATUS_ACTIVE,
            'fbr_token_production' => 'PROD_TOKEN_123',
            'fbr_token_sandbox' => 'SANDBOX_TOKEN_123',
        ]);

        $response->assertRedirect(route('admin.companies.show', $company));

        $company->refresh();
        $this->assertSame(Company::FBR_STATUS_ACTIVE, $company->fbr_status);
        $this->assertSame('PROD_TOKEN_123', $company->fbr_token_production);
        $this->assertSame('SANDBOX_TOKEN_123', $company->fbr_token_sandbox);
    }
}
