<?php

namespace App\Domain\Invoices\BulkUpload;

use SplFileObject;

/**
 * Company Dashboard PRD #10 (Bulk Invoicing). One CSV row = one invoice
 * item line — several rows sharing the same `invoice_reference_no` are
 * grouped into a single multi-item invoice by
 * {@see ImportBulkInvoices}. Every column here mirrors a field already
 * validated on the manual Create Invoice form (Phase 4) — no new field
 * names were invented for this format.
 *
 * `customer_id`/`item_id` (master-data linkage) are deliberately NOT
 * columns here: accepting a raw ID pasted into a spreadsheet would be a
 * company-isolation risk (a wrong or foreign ID silently attached to an
 * invoice) with no UI to catch a typo the way the manual form's dropdown
 * does. A bulk-uploaded invoice always stores its own snapshot values
 * directly, exactly like the manual form does when "from master" isn't
 * used.
 */
class BulkInvoiceCsvParser
{
    /**
     * @var array<int, string>
     */
    public const REQUIRED_COLUMNS = [
        'invoice_reference_no',
        'buyer_ntn_cnic',
        'buyer_business_name',
        'buyer_address',
        'buyer_registration_type',
        'buyer_province',
        'buyer_strn',
        'invoice_date',
        'invoice_type',
        'sale_type',
        'hs_code',
        'product_description',
        'rate',
        'uom',
        'price_per_unit',
        'quantity',
        'value_sales_excl_st',
        'further_tax',
        'fixed_retail_price',
        'st_withheld_at_source',
        'extra_tax',
        'fed_payable',
        'discount',
        'sro_schedule_no',
        'sro_item_sr_no',
    ];

    /**
     * Every row is validated individually and every surviving group is
     * created inside its own transaction — all synchronous, in one
     * request, with no queue/background-job mechanism anywhere in this
     * app to fall back on. An unbounded file could tie up a PHP worker
     * for an unpredictable duration (or hit `max_execution_time`) well
     * before the 5 MB upload-size limit was ever reached. This is a
     * generous ceiling for genuine bulk invoicing, not a real-world
     * constraint on legitimate use.
     */
    public const MAX_ROWS = 5000;

    /**
     * @return array<int, array<string, string>> Keyed by 1-based data row
     *                                           number (row 1 = the first
     *                                           row after the header),
     *                                           matching what a user
     *                                           would see if they opened
     *                                           the file in a spreadsheet
     *                                           (header = row 1 there).
     *
     * @throws InvalidBulkUploadFileException
     */
    public function parse(string $path): array
    {
        $file = new SplFileObject($path, 'r');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        $header = $file->fgetcsv();

        if ($header === false || $header === [null] || $header === []) {
            throw new InvalidBulkUploadFileException('The file is empty.');
        }

        // Strip a UTF-8 BOM some spreadsheet tools (e.g. Excel) prepend to
        // the very first header cell, and trim stray whitespace.
        $header = array_map(fn ($column) => trim((string) preg_replace('/^\x{FEFF}/u', '', (string) $column)), $header);

        $missing = array_diff(self::REQUIRED_COLUMNS, $header);

        if ($missing !== []) {
            throw new InvalidBulkUploadFileException(
                'The file is missing required column(s): '.implode(', ', $missing).
                '. Download the sample template and match its header row exactly.'
            );
        }

        $rows = [];
        $rowNumber = 0;

        // Deliberately not a `foreach ($file as $line)` here: SplFileObject's
        // Iterator implementation calls rewind() at the start of a foreach,
        // which seeks back to byte 0 and re-reads the header line we already
        // consumed above as if it were a data row. Reading sequentially with
        // fgetcsv() continues from the file pointer's current position.
        while (! $file->eof()) {
            $line = $file->fgetcsv();

            if ($line === null || $line === [null] || $line === false) {
                continue;
            }

            $rowNumber++;

            if ($rowNumber > self::MAX_ROWS) {
                throw new InvalidBulkUploadFileException(
                    'The file has more than '.self::MAX_ROWS.' data rows. Split it into smaller files and upload them separately.'
                );
            }

            // A short row (fewer cells than the header) pads to empty
            // strings rather than throwing — validation catches the
            // resulting missing-required-field error with a clear message
            // pointing at this exact row.
            $line = array_pad($line, count($header), '');

            // Blank cells normalize to null, not '' — mirrors Laravel's own
            // ConvertEmptyStringsToNull middleware (which only runs for
            // real HTTP requests, not this array-based CSV path) so
            // "nullable" + a type-check rule (numeric/date/in) behaves the
            // same way here as it does on the manual form.
            $rows[$rowNumber] = array_map(
                function ($value) {
                    $value = trim((string) $value);

                    return $value === '' ? null : $value;
                },
                array_combine($header, array_slice($line, 0, count($header)))
            );
        }

        if ($rows === []) {
            throw new InvalidBulkUploadFileException('The file has a header row but no data rows.');
        }

        return $rows;
    }
}
