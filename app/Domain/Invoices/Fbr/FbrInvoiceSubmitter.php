<?php

namespace App\Domain\Invoices\Fbr;

use App\Models\Invoice;

/**
 * The one seam between this app and FBR's real IRIS system. Bound to
 * StubFbrInvoiceSubmitter in AppServiceProvider until real IRIS endpoint
 * URLs/credentials/payload docs are available (see CHANGELOG_PROJECT.md,
 * Phase 1) — swapping in a real implementation later is a one-line binding
 * change, nothing that calls this interface needs to know which one is
 * active.
 */
interface FbrInvoiceSubmitter
{
    public function submit(Invoice $invoice): FbrSubmissionResult;
}
