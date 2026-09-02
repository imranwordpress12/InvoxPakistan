<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

/**
 * Company Dashboard PRD #18 — same reasoning as CustomerPolicy. Also
 * covers the draft-editing rule (PRD #8/#9) — see update()/delete() below
 * for exactly which statuses that covers and why.
 */
class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isCompany();
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->isCompany() && $user->company_id === $invoice->company_id;
    }

    public function create(User $user): bool
    {
        return $user->isCompany();
    }

    /**
     * PRD #8 says an invoice "submitted to IRIS" cannot be edited — but
     * the Compliance page's own wording (PRD #17, "Duplicate Invoice
     * Risk") is explicit that "if IRIS returns a valid failure reason,
     * the invoice is considered not submitted." So a `failed` invoice is
     * editable too (to fix whatever caused the rejection before
     * resubmitting) — only `submitted`/`successful` are locked.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        return $user->isCompany()
            && $user->company_id === $invoice->company_id
            && ($invoice->isDraft() || $invoice->isFailed());
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->isCompany()
            && $user->company_id === $invoice->company_id
            && ($invoice->isDraft() || $invoice->isFailed());
    }

    public function submit(User $user, Invoice $invoice): bool
    {
        return $user->isCompany()
            && $user->company_id === $invoice->company_id
            && ($invoice->isDraft() || $invoice->isFailed());
    }
}
