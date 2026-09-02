<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Company Dashboard PRD #7. Every tax-related field listed in the PRD
     * is its own column so nothing needs to be recomputed or guessed at
     * display time. `sale_type`/`hs_code`/`uom` are snapshotted from the
     * item (or entered directly) — same reasoning as `items`.
     *
     * cascadeOnDelete on `invoice_id`: line items are pure children of one
     * invoice, unlike the restrictOnDelete used for company-level
     * financial history elsewhere in this app.
     */
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained()->nullOnDelete();

            $table->string('sale_type')->nullable();
            $table->string('hs_code')->nullable();
            $table->text('product_description');
            $table->decimal('rate', 5, 2)->nullable();
            $table->string('uom')->nullable();

            $table->decimal('price_per_unit', 14, 2)->default(0);
            $table->decimal('quantity', 14, 4)->default(1);
            $table->decimal('value_sales_excl_st', 14, 2)->default(0);
            $table->decimal('sales_tax', 14, 2)->default(0);
            $table->decimal('further_tax', 14, 2)->default(0);
            $table->decimal('fixed_retail_price', 14, 2)->default(0);
            $table->decimal('st_withheld_at_source', 14, 2)->default(0);
            $table->decimal('extra_tax', 14, 2)->default(0);
            $table->decimal('fed_payable', 14, 2)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('total_sales_value', 14, 2)->default(0);
            $table->string('sro_schedule_no')->nullable();
            $table->string('sro_item_sr_no')->nullable();

            $table->timestamps();

            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
