<?php

namespace App\Domain\Invoices\Fbr;

/**
 * Turns whatever ended up in Invoice::$fbr_response into one human-
 * readable line — used both by SandboxFbrInvoiceSubmitter (deciding what
 * to store) and by InvoiceController (deciding what to flash to the
 * user), so there is exactly one place that knows how to read FBR's
 * validationResponse shape (API Doc.pdf, Sections 4.1.4/4.1.5).
 *
 * $fbr_response holds two different kinds of content depending on how
 * far a submission got: real FBR JSON (once a Sandbox call actually
 * happened) or a plain local message (a config/validation problem this
 * app caught before ever calling FBR — e.g. no Sandbox token configured).
 * This method handles both: valid JSON gets parsed for its nested error;
 * anything else is assumed to already be a human-readable string and is
 * returned as-is.
 */
class FbrResponseMessage
{
    public static function extract(?string $rawResponse): string
    {
        if (blank($rawResponse)) {
            return 'FBR rejected this invoice but no further detail is available.';
        }

        $decoded = json_decode($rawResponse, true);

        if (! is_array($decoded)) {
            // Not JSON — this is already a plain local message.
            return $rawResponse;
        }

        $validation = $decoded['validationResponse'] ?? [];
        $topError = trim((string) ($validation['error'] ?? ''));

        if ($topError !== '') {
            return $topError;
        }

        $itemStatuses = $validation['invoiceStatuses'] ?? [];

        if (is_array($itemStatuses)) {
            foreach ($itemStatuses as $item) {
                $itemError = trim((string) ($item['error'] ?? ''));

                if ($itemError !== '') {
                    $itemNo = $item['itemSNo'] ?? '?';

                    return "Item {$itemNo}: {$itemError}";
                }
            }
        }

        return 'FBR rejected this invoice but did not provide a specific error message.';
    }
}
