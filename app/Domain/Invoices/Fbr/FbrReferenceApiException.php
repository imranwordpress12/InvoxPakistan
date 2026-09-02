<?php

namespace App\Domain\Invoices\Fbr;

use RuntimeException;

/**
 * A failure to load one of FBR's Digital Invoicing *Reference* APIs
 * (Section 5 — provinces, HS_UOM, SaleTypeToRate, SRO, SRO Item, ...).
 *
 * Deliberately its own exception (not just letting ConnectionException /
 * a bad HTTP status bubble up) so FbrReferenceController can catch exactly
 * this and turn it into the friendly "Unable to load X from FBR. Please
 * retry." messages Section 16 asks for, without swallowing unrelated
 * errors. `$userMessage` is always safe to show as-is; it never contains
 * the company's FBR token (see FbrHttpClient — the token is never placed
 * in a message string in the first place).
 */
class FbrReferenceApiException extends RuntimeException
{
    public function __construct(public readonly string $userMessage, ?\Throwable $previous = null)
    {
        parent::__construct($userMessage, 0, $previous);
    }
}
