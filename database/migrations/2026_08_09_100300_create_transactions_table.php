<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->restrictOnDelete();

            $table->foreignId('subscription_id')
                ->constrained('subscriptions')
                ->restrictOnDelete();

            $table->string('invoice_number')->unique();

            // 'initial' = first invoice created with the company; 'renewal' =
            // every subsequent billing-period invoice (PRD #24-#27).
            $table->enum('transaction_type', ['initial', 'renewal']);

            // Snapshot of the subscription type at the time of this
            // transaction (kept even if the subscription's type changes later).
            $table->enum('subscription_type', ['monthly', 'yearly']);

            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');

            $table->date('billing_period_start');
            $table->date('billing_period_end');

            $table->dateTime('due_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Duplicate-renewal protection (PRD #50/#51 - "the database should
            // also use suitable uniqueness/indexing rules where possible").
            // This generated column is NULL for every non-pending row, and
            // equals subscription_id only while status = 'pending'. A unique
            // index on it then allows unlimited paid/cancelled rows per
            // subscription but at most one pending row at a time - enforced
            // by the database, not just application logic.
            $table->unsignedBigInteger('pending_dedupe_key')
                ->nullable()
                ->virtualAs("case when status = 'pending' then subscription_id else null end");

            $table->unique('pending_dedupe_key');
            $table->index('subscription_id');
            $table->index('company_id');
            $table->index('status');
            $table->index('due_at');
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
