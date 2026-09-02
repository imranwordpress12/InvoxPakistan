<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Company Dashboard PRD #6/#8/#9. Named `invoices` (not
     * `transactions` — that table already means something different in
     * this app: subscription billing).
     *
     * Buyer details are snapshotted onto the invoice (not just a
     * `customer_id` FK) so a submitted invoice's buyer information never
     * silently changes if the customer master record is edited later.
     *
     * `status`: draft (editable, never submitted) -> submitted (FBR call
     * in flight) -> successful | failed (FBR responded). Draft invoices
     * must never be mixed with submitted ones (PRD #9).
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();

            $table->string('invoice_reference_no')->nullable();
            $table->string('fbr_invoice_number')->nullable();
            $table->enum('invoice_type', ['sale', 'debit', 'credit'])->default('sale');
            $table->date('invoice_date');
            $table->enum('status', ['draft', 'submitted', 'successful', 'failed'])->default('draft');
            $table->text('fbr_response')->nullable();

            // Buyer snapshot (PRD #6) — deliberately duplicated from the
            // customer, not just referenced, so historical invoices stay
            // accurate.
            $table->string('buyer_ntn_cnic')->nullable();
            $table->string('buyer_business_name')->nullable();
            $table->text('buyer_address')->nullable();
            $table->string('buyer_registration_type')->nullable();
            $table->string('buyer_province')->nullable();
            $table->string('buyer_strn')->nullable();

            $table->decimal('total_excl_st', 14, 2)->default(0);
            $table->decimal('total_sales_tax', 14, 2)->default(0);
            $table->decimal('total_further_tax', 14, 2)->default(0);
            $table->decimal('total_discount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);

            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'invoice_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
