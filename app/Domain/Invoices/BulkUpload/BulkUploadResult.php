<?php

namespace App\Domain\Invoices\BulkUpload;

/**
 * Company Dashboard PRD #10 (Bulk Invoicing). The outcome of one bulk
 * upload: how many invoices were actually created, plus every row/group
 * that was rejected and why — a bulk import must never fail silently or
 * fail-all-or-nothing, since one bad row in a 200-row file shouldn't cost
 * the other 199.
 */
final class BulkUploadResult
{
    /**
     * @param  array<int, string>  $createdReferences
     * @param  array<int, string>  $errors
     */
    public function __construct(
        public readonly array $createdReferences,
        public readonly array $errors,
    ) {}

    public function createdCount(): int
    {
        return count($this->createdReferences);
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }
}
