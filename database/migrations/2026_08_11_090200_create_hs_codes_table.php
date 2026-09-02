<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Company Dashboard PRD #14.2: HS Code is a large reference dataset —
     * database-driven, never hardcoded. The seeded set is a small starter
     * list (see HsCodeSeeder); it is not the full official FBR HS Code
     * classification list.
     */
    public function up(): void
    {
        Schema::create('hs_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hs_codes');
    }
};
