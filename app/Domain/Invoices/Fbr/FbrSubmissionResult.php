<?php

namespace App\Domain\Invoices\Fbr;

/**
 * The outcome of one FBR/IRIS submission attempt. Never store passwords/
 * FBR credentials here — `rawResponse` is FBR's own response body, which
 * should not echo the credentials that were used to authenticate the call.
 */
final class FbrSubmissionResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $fbrInvoiceNumber = null,
        public readonly ?string $rawResponse = null,
        // SVG markup (FbrQrCodeGenerator) — only ever set on success,
        // since there is nothing FBR-issued to encode on a rejected
        // submission.
        public readonly ?string $qrCode = null,
    ) {}
}
