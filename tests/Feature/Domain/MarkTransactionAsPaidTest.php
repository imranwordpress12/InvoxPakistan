<?php

namespace Tests\Feature\Domain;

use App\Domain\Transactions\Exceptions\TransactionAlreadyProcessedException;
use App\Domain\Transactions\MarkTransactionAsPaid;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MarkTransactionAsPaidTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_marks_a_pending_transaction_paid_and_renews_the_subscription(): void
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create([
            'status' => Subscription::STATUS_PENDING,
            'starts_at' => Carbon::parse('2026-06-01'),
            'ends_at' => Carbon::parse('2026-07-01'),
        ]);
        $transaction = Transaction::factory()->pending()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
        ]);

        $updated = (new MarkTransactionAsPaid)->handle($transaction);

        $this->assertSame(Transaction::STATUS_PAID, $updated->status);
        $this->assertNotNull($updated->paid_at);

        $subscription->refresh();
        $this->assertSame(Subscription::STATUS_ACTIVE, $subscription->status);
        $this->assertSame($updated->paid_at->toDateString(), $subscription->starts_at->toDateString());
        $this->assertTrue($subscription->isActive());
    }

    public function test_it_saves_the_given_notes(): void
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create();
        $transaction = Transaction::factory()->pending()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'notes' => null,
        ]);

        $updated = (new MarkTransactionAsPaid)->handle($transaction, 'Paid via bank transfer');

        $this->assertSame('Paid via bank transfer', $updated->notes);
    }

    public function test_it_preserves_existing_notes_when_none_are_given(): void
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create();
        $transaction = Transaction::factory()->pending()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'notes' => 'Original note',
        ]);

        $updated = (new MarkTransactionAsPaid)->handle($transaction);

        $this->assertSame('Original note', $updated->notes);
    }

    /**
     * PRD #64 case 6: a second "mark as paid" on the same transaction must
     * fail safely, not silently succeed or crash — and must not touch the
     * subscription again.
     */
    public function test_marking_an_already_paid_transaction_paid_again_fails_safely(): void
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create();
        $transaction = Transaction::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => Transaction::STATUS_PAID,
            'paid_at' => now()->subDay(),
        ]);
        $subscriptionEndsAtBefore = $subscription->ends_at->toDateTimeString();

        $this->expectException(TransactionAlreadyProcessedException::class);

        try {
            (new MarkTransactionAsPaid)->handle($transaction);
        } finally {
            $this->assertSame($subscriptionEndsAtBefore, $subscription->refresh()->ends_at->toDateTimeString());
        }
    }

    /**
     * PRD #53: one atomic flow, one audit log entry covering both the
     * transaction and subscription changes (no separate "Subscription
     * Renewed" row — see the class docblock).
     */
    public function test_it_writes_one_audit_log_entry_covering_both_the_transaction_and_the_subscription(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create([
            'status' => Subscription::STATUS_PENDING,
            'starts_at' => Carbon::parse('2026-06-01'),
            'ends_at' => Carbon::parse('2026-07-01'),
        ]);
        $transaction = Transaction::factory()->pending()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => 'INV-AUDIT-1',
        ]);

        (new MarkTransactionAsPaid)->handle($transaction);

        $this->assertSame(1, AuditLog::where('action', 'transaction.marked_paid')->count());

        $log = AuditLog::where('action', 'transaction.marked_paid')->first();
        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame($company->id, $log->company_id);
        $this->assertSame(Transaction::STATUS_PENDING, $log->old_values['transaction_status']);
        $this->assertSame(Transaction::STATUS_PAID, $log->new_values['transaction_status']);
        $this->assertSame(Subscription::STATUS_PENDING, $log->old_values['subscription_status']);
        $this->assertSame(Subscription::STATUS_ACTIVE, $log->new_values['subscription_status']);
        $this->assertStringContainsString('INV-AUDIT-1', (string) $log->description);
    }

    public function test_a_cancelled_transaction_cannot_be_marked_paid(): void
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->for($company)->create();
        $transaction = Transaction::factory()->cancelled()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
        ]);

        $this->expectException(TransactionAlreadyProcessedException::class);

        (new MarkTransactionAsPaid)->handle($transaction);
    }
}
