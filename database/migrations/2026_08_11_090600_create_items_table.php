<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Company Dashboard PRD #12/#13. `sale_type`/`hs_code`/`uom` are
     * stored as plain values (their dropdown options come from the
     * DB-driven reference tables) rather than foreign keys — an invoice
     * line built from this item snapshots these same plain values, so
     * nothing here needs to be joined back to reference data later.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();

            $table->string('item_code');
            $table->string('item_name');
            $table->enum('item_type', ['goods', 'service']);
            $table->string('sale_type')->nullable();
            $table->string('hs_code')->nullable();
            $table->decimal('rate', 5, 2)->nullable();
            $table->string('uom')->nullable();

            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->decimal('sale_price', 12, 2)->nullable();

            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('reorder_level')->default(0);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->unique(['company_id', 'item_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
