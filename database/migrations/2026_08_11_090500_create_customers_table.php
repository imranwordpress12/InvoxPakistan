<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Company Dashboard PRD #11. `company_id` scopes every row to the
     * owning company (PRD #18 data isolation). `province` is stored as a
     * plain value (populated from the DB-driven `provinces` list in the
     * UI) rather than a foreign key, matching how buyer details are
     * snapshotted onto invoices — simpler, and avoids retroactively
     * changing a customer's historical invoices if province naming ever
     * changes.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();

            $table->string('business_name');
            $table->string('ntn_cnic');
            $table->string('province');
            $table->enum('buyer_registration_type', ['registered', 'unregistered']);
            $table->string('strn')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_number')->nullable();
            $table->text('address');
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
