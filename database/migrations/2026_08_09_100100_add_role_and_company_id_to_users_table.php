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
        Schema::table('users', function (Blueprint $table) {
            // Admin users: company_id is NULL.
            // Company users: company_id = companies.id.
            // This lets a company have multiple users in the future without
            // any schema change (PRD #29).
            $table->enum('role', ['admin', 'company'])->default('company')->after('password');

            $table->foreignId('company_id')
                ->nullable()
                ->after('role')
                ->constrained('companies')
                // A company with existing user accounts cannot be hard-deleted
                // out from under them; use soft deletes for normal removal.
                ->restrictOnDelete();

            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropIndex(['role']);
            $table->dropColumn('role');
        });
    }
};
