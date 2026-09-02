<?php

namespace App\Domain\Invoices\Fbr;

use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * The real FBR Sandbox implementation of FbrInvoiceSubmitter (replacing
 * StubFbrInvoiceSubmitter — see AppServiceProvider's binding), built
 * directly from "Technical Specification for DI API" v1.12 (API Doc.pdf):
 * Section 3.1 (Bearer-token auth), Section 4.1 (postinvoicedata_sb — used
 * here, not validateinvoicedata_sb, since that endpoint never issues a
 * real FBR invoice number and this is the actual "Submit Invoice" action,
 * not a pre-check), Section 4.1.6 (HTTP status codes), and Section 9
 * (Sandbox scenarioId requirement).
 *
 * Every failure path below returns success:false rather than throwing —
 * a rejected/mis-configured submission is a normal, expected outcome in
 * this app's lifecycle (Invoice::STATUS_FAILED, editable and
 * resubmittable), not an exceptional one. Whatever ends up in
 * `rawResponse` — real FBR JSON or a plain local message — is decoded
 * back into one human-readable line by FbrResponseMessage, used both
 * here (implicitly, by storing it) and by InvoiceController (explicitly,
 * for the user-facing flash message).
 */
class SandboxFbrInvoiceSubmitter implements FbrInvoiceSubmitter
{
    public function __construct(
        private FbrScenarioResolver $scenarios,
        private FbrQrCodeGenerator $qrCodes,
    ) {}

    public function submit(Invoice $invoice): FbrSubmissionResult
    {
        $token = Company::whereKey($invoice->company_id)->value('fbr_token_sandbox');

        if (blank($token)) {
            return $this->failure('Configuration error: no FBR Sandbox token is set up for this company. '.
                'Ask an administrator to add one under Company Settings before submitting invoices.');
        }

        $sellerError = $this->checkSellerDataPresent($invoice);
        if ($sellerError !== null) {
            return $this->failure($sellerError);
        }

        $built = FbrInvoicePayloadBuilder::build($invoice->loadMissing('items', 'company'), $this->scenarios);

        if ($built['scenarioConflict']) {
            return $this->failure('This invoice mixes items whose Sale Types map to different FBR scenarios; '.
                'FBR only accepts one scenario per invoice. Split these items across separate invoices.');
        }

        if ($built['scenarioId'] === null) {
            return $this->failure('No FBR Sandbox scenario is configured for one or more of this invoice\'s '.
                'item Sale Types. Contact an administrator to review the Sale Type reference data.');
        }

        return $this->callFbr($token, $built['payload']);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function callFbr(string $token, array $payload): FbrSubmissionResult
    {
        // dd("Token: $token", $payload);
        try {
            $response = Http::withToken($token)
                ->timeout((int) config('services.fbr.timeout', 30))
                ->acceptJson()
                ->post(config('services.fbr.sandbox_post_url'), $payload);
        } catch (ConnectionException $e) {
            return $this->failure('Could not reach the FBR Sandbox API (network/connection error): '.$e->getMessage());
        }

        $body = $response->json();

        // A non-2xx HTTP status (401/500 — Section 4.1.6's documented
        // codes) doesn't get special-cased with its own message: FBR's
        // real error responses (confirmed live — a 401 comes back with
        // the exact same validationResponse.error shape as a 200-with-
        // rejection) are handed to interpret()/FbrResponseMessage the
        // same way regardless of HTTP status, so the actual FBR-worded
        // reason surfaces either way. Only fall back to a synthesized
        // message when there's genuinely no parseable body to read one
        // from.
        if (! $response->successful() && ! is_array($body)) {
            $raw = trim($response->body());

            return $this->failure($raw !== '' ? $raw : 'FBR returned HTTP '.$response->status().' with no response body.');
        }

        if (! is_array($body)) {
            return $this->failure('FBR returned a response that could not be parsed as JSON: '.$response->body());
        }

        return $this->interpret($body);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function interpret(array $body): FbrSubmissionResult
    {
        $validation = $body['validationResponse'] ?? [];
        $invoiceNumber = $body['invoiceNumber'] ?? null;
        $topStatus = strtolower((string) ($validation['status'] ?? ''));
        $itemStatuses = $validation['invoiceStatuses'] ?? [];

        $allItemsValid = is_array($itemStatuses)
            ? collect($itemStatuses)->every(fn ($item) => strtolower((string) ($item['status'] ?? '')) === 'valid')
            : true;

        // Treat a genuinely issued invoice number as the authoritative
        // success signal — the documentation's own sample "invalid"
        // responses are internally inconsistent about statusCode/status
        // (Section 4.1.5 shows statusCode "00" — normally "valid" — on a
        // response that is actually invalid), but no sample ever shows
        // an invoiceNumber on a rejected submission.
        $success = filled($invoiceNumber) && $topStatus === 'valid' && $allItemsValid;

        $rawResponse = json_encode($body);

        if (! $success) {
            return $this->failure($rawResponse);
        }

        return new FbrSubmissionResult(
            success: true,
            fbrInvoiceNumber: (string) $invoiceNumber,
            rawResponse: $rawResponse,
            qrCode: $this->qrCodes->generate((string) $invoiceNumber),
        );
    }

    /**
     * A missing seller field would otherwise reach FBR only to be
     * rejected with a cryptic error (e.g. 0001/0082/0108) — checked
     * locally first for a clearer message, and to avoid wasting a real
     * Sandbox call on data this app already knows is incomplete.
     */
    private function checkSellerDataPresent(Invoice $invoice): ?string
    {
        $company = $invoice->company;

        $missing = array_keys(array_filter([
            'seller NTN/CNIC' => blank($company?->ntn_cnic),
            'seller business name' => blank($company?->business_name),
            'seller province' => blank($company?->province),
            'seller address' => blank($company?->address),
        ]));

        if ($missing === []) {
            return null;
        }

        return 'This company\'s own profile is missing: '.implode(', ', $missing).
            '. Ask an administrator to complete the company profile before submitting invoices to FBR.';
    }

    private function failure(string $rawResponse): FbrSubmissionResult
    {
        return new FbrSubmissionResult(success: false, fbrInvoiceNumber: null, rawResponse: $rawResponse);
    }
}
