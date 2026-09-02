<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function pendingTransaction(): Transaction
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->pending()->create();

        return Transaction::factory()->pending()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
        ]);
    }

    public function test_admin_can_mark_a_pending_transaction_as_paid(): void
    {
        $transaction = $this->pendingTransaction();

        $response = $this->actingAs($this->admin())
            ->from(route('admin.companies.show', $transaction->company))
            ->post(route('admin.transactions.mark-paid', $transaction));

        $response->assertRedirect(route('admin.companies.show', $transaction->company));
        $response->assertSessionHas('status');

        $this->assertSame(Transaction::STATUS_PAID, $transaction->refresh()->status);
        $this->assertSame(Subscription::STATUS_ACTIVE, $transaction->subscription->refresh()->status);
    }

    public function test_admin_can_attach_a_note_when_marking_paid(): void
    {
        $transaction = $this->pendingTransaction();

        $this->actingAs($this->admin())->post(route('admin.transactions.mark-paid', $transaction), [
            'notes' => 'Paid via bank transfer',
        ]);

        $this->assertSame('Paid via bank transfer', $transaction->refresh()->notes);
    }

    public function test_marking_an_already_paid_transaction_fails_safely_with_a_validation_error(): void
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create();
        $transaction = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => Transaction::STATUS_PAID,
        ]);

        $response = $this->actingAs($this->admin())
            ->from(route('admin.companies.show', $company))
            ->post(route('admin.transactions.mark-paid', $transaction));

        $response->assertRedirect(route('admin.companies.show', $company));
        $response->assertSessionHasErrors('status');
    }

    /**
     * PRD #64 case 6, exercised end-to-end through the actual HTTP route:
     * two sequential "mark as paid" submissions on the same transaction —
     * the first succeeds, the second fails safely instead of erroring or
     * double-renewing the subscription.
     */
    public function test_a_double_mark_as_paid_submission_fails_safely_on_the_second_attempt(): void
    {
        $transaction = $this->pendingTransaction();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.transactions.mark-paid', $transaction))
            ->assertSessionHasNoErrors();

        $endsAtAfterFirstPayment = $transaction->subscription->refresh()->ends_at->toDateTimeString();

        $this->actingAs($admin)->post(route('admin.transactions.mark-paid', $transaction))
            ->assertSessionHasErrors('status');

        $this->assertSame($endsAtAfterFirstPayment, $transaction->subscription->refresh()->ends_at->toDateTimeString());
    }

    public function test_guest_is_redirected_away(): void
    {
        $transaction = $this->pendingTransaction();

        $this->post(route('admin.transactions.mark-paid', $transaction))
            ->assertRedirect(route('admin.login'));
    }

    public function test_company_user_cannot_mark_a_transaction_as_paid(): void
    {
        $transaction = $this->pendingTransaction();
        $companyUser = User::factory()->company($transaction->company)->create();

        $this->actingAs($companyUser)
            ->post(route('admin.transactions.mark-paid', $transaction))
            ->assertForbidden();

        $this->assertSame(Transaction::STATUS_PENDING, $transaction->refresh()->status);
    }
}
