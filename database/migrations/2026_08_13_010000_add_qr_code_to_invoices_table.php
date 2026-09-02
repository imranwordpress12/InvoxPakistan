<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FBR Digital Invoicing requires a QR code on every submitted invoice
     * (API Doc.pdf, Section 6). Stored as an SVG string (generated from
     * the FBR-issued invoice number — see FbrQrCodeGenerator) rather than
     * a file path, since it's small text data with nowhere else useful to
     * live, and rendering an inline `<svg>` in the invoice details page
     * needs no extra storage/public-disk wiring.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->longText('qr_code')->nullable()->after('fbr_response');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('qr_code');
        });
    }
};
