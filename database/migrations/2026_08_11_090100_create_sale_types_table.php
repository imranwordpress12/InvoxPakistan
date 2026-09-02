<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Company Dashboard PRD #14.2: Sale Type has more than 5 options
     * (Goods at standard rate, Reduced Rate, zero-rate, Petroleum Products,
     * Electricity Supply to Retailers, SIM, Gas to CNG stations, ...), so
     * it must be database-driven rather than hardcoded.
     */
    public function up(): void
    {
        Schema::create('sale_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_types');
    }
};
