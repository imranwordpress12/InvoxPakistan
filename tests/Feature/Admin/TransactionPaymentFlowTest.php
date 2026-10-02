<?php

namespace Tests\Feature\Admin;

use App\Mail\PaymentThankYou;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TransactionPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_activating_subscription_creates_paid_transaction_and_one_future_transaction(): void
    {
        Carbon::setTestNow('2026-10-01');

        $company = Company::factory()->create();

        $response = $this->actingAs($this->admin)->put(route('admin.companies.update', $company), [
            'name' => $company->name,
            'email' => $company->email,
            'user_email' => $company->users()->first()?->email ?? 'login@company.test',
            'status' => Company::STATUS_ACTIVE,
            'subscription_status' => Subscription::STATUS_ACTIVE,
            'subscription_starts_at' => '2026-10-01',
            'subscription_type' => Subscription::TYPE_MONTHLY,
            'amount' => 1000.00,
        ]);

        $response->assertRedirect(route('admin.companies.show', $company));

        $transactions = Transaction::where('company_id', $company->id)->orderBy('id')->get();
        $this->assertCount(2, $transactions);

        // Transaction #1: Initial Paid transaction (01-Oct-2026 to 31-Oct-2026)
        $tx1 = $transactions[0];
        $this->assertSame(Transaction::TYPE_INITIAL, $tx1->transaction_type);
        $this->assertSame(Transaction::STATUS_PAID, $tx1->status);
        $this->assertSame('2026-10-01', $tx1->billing_period_start->toDateString());
        $this->assertSame('2026-10-31', $tx1->billing_period_end->toDateString());

        // Transaction #2: Exactly ONE future transaction starting from previous end date + 1 day (01-Nov-2026 to 30-Nov-2026)
        $tx2 = $transactions[1];
        $this->assertSame(Transaction::TYPE_RENEWAL, $tx2->transaction_type);
        $this->assertSame('2026-11-01', $tx2->billing_period_start->toDateString());
        $this->assertSame('2026-11-30', $tx2->billing_period_end->toDateString());
    }

    public function test_pay_now_processes_pending_transaction_and_creates_next_single_transaction(): void
    {
        Mail::fake();
        Storage::fake('local');

        $company = Company::factory()->create();
        $sub = Subscription::factory()->create([
            'company_id' => $company->id,
            'type' => Subscription::TYPE_MONTHLY,
            'status' => Subscription::STATUS_PENDING,
            'starts_at' => Carbon::parse('2026-10-01'),
            'ends_at' => Carbon::parse('2026-10-31'),
        ]);

        $pendingTx = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $sub->id,
            'transaction_type' => Transaction::TYPE_RENEWAL,
            'subscription_type' => Subscription::TYPE_MONTHLY,
            'amount' => 1000.00,
            'status' => Transaction::STATUS_PENDING,
            'billing_period_start' => Carbon::parse('2026-11-01'),
            'billing_period_end' => Carbon::parse('2026-11-30'),
            'due_at' => Carbon::parse('2026-11-30'),
        ]);

        $file = UploadedFile::fake()->image('receipt.png');

        $response = $this->actingAs($this->admin)->post(route('admin.transactions.mark-paid', $pendingTx), [
            'amount' => 1000.00,
            'subscription_type' => Subscription::TYPE_MONTHLY,
            'notes' => 'Paid via bank transfer',
            'payment_screenshot' => $file,
        ]);

        $response->assertSessionHasNoErrors();

        $pendingTx->refresh();
        $this->assertSame(Transaction::STATUS_PAID, $pendingTx->status);
        $this->assertNotNull($pendingTx->paid_at);
        $this->assertSame('Paid via bank transfer', $pendingTx->notes);
        $this->assertNotNull($pendingTx->payment_screenshot);

        // Subscription renewed to Active
        $this->assertSame(Subscription::STATUS_ACTIVE, $sub->fresh()->status);

        // Thank you email sent
        Mail::assertSent(PaymentThankYou::class);

        // Exactly ONE next transaction created starting from 01-Dec-2026 to 31-Dec-2026
        $allTxs = Transaction::where('company_id', $company->id)->orderBy('id')->get();
        $this->assertCount(2, $allTxs);

        $nextTx = $allTxs[1];
        $this->assertSame('2026-12-01', $nextTx->billing_period_start->toDateString());
        $this->assertSame('2026-12-31', $nextTx->billing_period_end->toDateString());
    }

    public function test_pay_now_processes_overdue_transaction_and_creates_next_transaction_as_overdue_if_past(): void
    {
        Mail::fake();

        $company = Company::factory()->create();
        $sub = Subscription::factory()->create([
            'company_id' => $company->id,
            'type' => Subscription::TYPE_MONTHLY,
            'status' => Subscription::STATUS_ACTIVE,
        ]);

        // Jan transaction that became Overdue (01-Jan-2026 to 31-Jan-2026)
        $overdueTx = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $sub->id,
            'transaction_type' => Transaction::TYPE_RENEWAL,
            'subscription_type' => Subscription::TYPE_MONTHLY,
            'amount' => 1000.00,
            'status' => Transaction::STATUS_OVERDUE,
            'billing_period_start' => Carbon::parse('2026-01-01'),
            'billing_period_end' => Carbon::parse('2026-01-31'),
            'due_at' => Carbon::parse('2026-01-31'),
        ]);

        // Admin pays it much later e.g. today
        $response = $this->actingAs($this->admin)->post(route('admin.transactions.mark-paid', $overdueTx), [
            'amount' => 1000.00,
            'subscription_type' => Subscription::TYPE_MONTHLY,
            'notes' => 'Late payment received',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertSame(Transaction::STATUS_PAID, $overdueTx->fresh()->status);

        // Next transaction (01-Feb-2026 to 28-Feb-2026) is created as Overdue because 28-Feb-2026 has passed
        $nextTx = Transaction::where('company_id', $company->id)->where('id', '>', $overdueTx->id)->first();
        $this->assertNotNull($nextTx);
        $this->assertSame('2026-02-01', $nextTx->billing_period_start->toDateString());
        $this->assertSame('2026-02-28', $nextTx->billing_period_end->toDateString());
        $this->assertSame(Transaction::STATUS_OVERDUE, $nextTx->status);
    }

    public function test_duplicate_payment_submission_fails_gracefully(): void
    {
        $company = Company::factory()->create();
        $sub = Subscription::factory()->create([
            'company_id' => $company->id,
        ]);

        $paidTx = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $sub->id,
            'status' => Transaction::STATUS_PAID,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.transactions.mark-paid', $paidTx), [
            'amount' => 1000.00,
        ]);

        $response->assertSessionHasErrors(['status']);
    }
}
