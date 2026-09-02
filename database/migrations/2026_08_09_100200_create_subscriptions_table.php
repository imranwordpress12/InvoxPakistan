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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->restrictOnDelete();

            $table->enum('type', ['monthly', 'yearly']);

            // Payment status belongs here, NOT on companies (PRD "Critical
            // Business Rules"). "Pending Companies" is derived by filtering
            // on this status, not a separate entity/table.
            $table->enum('status', ['active', 'pending', 'expired', 'cancelled'])->default('pending');

            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->decimal('amount', 12, 2);

            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index('ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
