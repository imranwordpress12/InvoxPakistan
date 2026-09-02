<?php

namespace App\Domain\Invoices;

use App\Domain\Audit\AuditLogger;
use App\Domain\Invoices\Exceptions\InvoiceNotSubmittableException;
use App\Domain\Invoices\Fbr\FbrInvoiceSubmitter;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

/**
 * The atomic "Submit Invoice" flow (Company Dashboard PRD #8/#23): lock ->
 * verify still submittable -> mark submitted -> call FBR (via whichever
 * FbrInvoiceSubmitter is bound) -> record the result -> audit log -> commit.
 * Same shape as MarkTransactionAsPaid on the admin side of this app.
 */
class SubmitInvoiceToFbr
{
    public function __construct(private FbrInvoiceSubmitter $submitter) {}

    public function handle(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice) {
            $locked = Invoice::whereKey($invoice->id)->lockForUpdate()->first();

            if (! $locked || (! $locked->isDraft() && ! $locked->isFailed())) {
                throw new InvoiceNotSubmittableException(
                    "Invoice #{$invoice->id} is not eligible for submission."
                );
            }

            $originalStatus = $locked->status;
            $locked->status = Invoice::STATUS_SUBMITTED;
            $locked->save();

            $result = $this->submitter->submit($locked);

            $locked->forceFill([
                'status' => $result->success ? Invoice::STATUS_SUCCESSFUL : Invoice::STATUS_FAILED,
                'fbr_invoice_number' => $result->fbrInvoiceNumber,
                'fbr_response' => $result->rawResponse,
                'qr_code' => $result->qrCode,
                'submitted_at' => now(),
            ])->save();

            AuditLogger::log(
                action: 'invoice.submitted',
                module: 'invoices',
                description: "Invoice \"{$locked->invoice_reference_no}\" submitted to FBR: {$locked->status}.",
                company: $locked->company,
                old: ['status' => $originalStatus],
                new: ['status' => $locked->status, 'fbr_invoice_number' => $locked->fbr_invoice_number],
            );

            return $locked->refresh();
        });
    }
}
