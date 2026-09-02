<?php

namespace App\Domain\Invoices\BulkUpload;

use App\Domain\Invoices\InvoiceCalculator;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Company Dashboard PRD #10 (Bulk Invoicing) — the orchestrating step:
 * validate every row, group rows into invoices by `invoice_reference_no`,
 * and create each valid group as its own draft invoice. Every bulk-
 * uploaded invoice lands as a **draft**, never auto-submitted — exactly
 * like the manual form's "Save as Draft" button, so a company always
 * gets to review before anything reaches FBR (a bulk mistake submitted
 * straight to IRIS would be much harder to walk back than a bad manual
 * entry).
 *
 * One invoice group's failure never affects another's — each valid
 * group is created inside its own `DB::transaction()`, and a rejected
 * group simply contributes an error message instead of aborting the
 * whole import.
 */
class ImportBulkInvoices
{
    /**
     * @param  array<int, array<string, mixed>>  $rows  Keyed by 1-based
     *                                                  data row number,
     *                                                  as returned by
     *                                                  BulkInvoiceCsvParser::parse().
     */
    public function handle(array $rows, int $companyId): BulkUploadResult
    {
        [$validRows, $errors] = $this->validateRows($rows);

        $groups = $this->groupByInvoiceReference($validRows);

        $created = [];

        foreach ($groups as $reference => $group) {
            $consistencyError = $this->checkGroupConsistency($reference, $group);

            if ($consistencyError !== null) {
                $errors[] = $consistencyError;

                continue;
            }

            if ($this->referenceAlreadyExists($companyId, $reference)) {
                $errors[] = "Invoice reference \"{$reference}\": already exists for this company — skipped.";

                continue;
            }

            $this->createInvoice($companyId, $reference, $group);
            $created[] = $reference;
        }

        return new BulkUploadResult($created, $errors);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, string>}
     */
    private function validateRows(array $rows): array
    {
        $valid = [];
        $errors = [];

        foreach ($rows as $rowNumber => $row) {
            $validator = Validator::make($row, BulkInvoiceRowRules::rules());

            if ($validator->fails()) {
                $errors[] = "Row {$rowNumber}: ".$validator->errors()->first();

                continue;
            }

            $valid[$rowNumber] = $validator->validated();
        }

        return [$valid, $errors];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, array<int, array<string, mixed>>> Keyed by
     *                                                         invoice_reference_no,
     *                                                         preserving
     *                                                         each group's
     *                                                         original row
     *                                                         order.
     */
    private function groupByInvoiceReference(array $rows): array
    {
        $groups = [];

        foreach ($rows as $row) {
            $groups[$row['invoice_reference_no']][] = $row;
        }

        return $groups;
    }

    /**
     * @param  array<int, array<string, mixed>>  $group
     */
    private function checkGroupConsistency(string $reference, array $group): ?string
    {
        $first = $group[0];

        foreach ($group as $row) {
            foreach (BulkInvoiceRowRules::invoiceLevelFields() as $field) {
                if ($row[$field] !== $first[$field]) {
                    return "Invoice reference \"{$reference}\": rows disagree on \"{$field}\" — every row for the same invoice must repeat identical buyer/invoice details.";
                }
            }
        }

        return null;
    }

    private function referenceAlreadyExists(int $companyId, string $reference): bool
    {
        return Invoice::where('company_id', $companyId)
            ->where('invoice_reference_no', $reference)
            ->exists();
    }

    /**
     * @param  array<int, array<string, mixed>>  $group
     */
    private function createInvoice(int $companyId, string $reference, array $group): void
    {
        DB::transaction(function () use ($companyId, $reference, $group) {
            $first = $group[0];

            $items = array_map(
                fn ($row) => [...$row, ...InvoiceCalculator::calculateItem($row)],
                $group
            );
            $totals = InvoiceCalculator::summarizeInvoice($items);

            $invoice = Invoice::create([
                'company_id' => $companyId,
                'customer_id' => null,
                'invoice_reference_no' => $reference,
                'invoice_type' => $first['invoice_type'],
                'invoice_date' => $first['invoice_date'],
                'status' => Invoice::STATUS_DRAFT,
                'buyer_ntn_cnic' => $first['buyer_ntn_cnic'],
                'buyer_business_name' => $first['buyer_business_name'],
                'buyer_address' => $first['buyer_address'],
                'buyer_registration_type' => $first['buyer_registration_type'],
                'buyer_province' => $first['buyer_province'],
                'buyer_strn' => $first['buyer_strn'] ?? null,
                ...$totals,
            ]);

            foreach ($items as $item) {
                $invoice->items()->create([
                    'item_id' => null,
                    'sale_type' => $item['sale_type'],
                    'hs_code' => $item['hs_code'],
                    'product_description' => $item['product_description'],
                    'rate' => $item['rate'],
                    'uom' => $item['uom'],
                    'price_per_unit' => $item['price_per_unit'] ?? 0,
                    'quantity' => $item['quantity'],
                    'value_sales_excl_st' => $item['value_sales_excl_st'],
                    'sales_tax' => $item['sales_tax'],
                    'further_tax' => $item['further_tax'] ?? 0,
                    'fixed_retail_price' => $item['fixed_retail_price'] ?? 0,
                    'st_withheld_at_source' => $item['st_withheld_at_source'] ?? 0,
                    'extra_tax' => $item['extra_tax'] ?? 0,
                    'fed_payable' => $item['fed_payable'] ?? 0,
                    'discount' => $item['discount'] ?? 0,
                    'total_sales_value' => $item['total_sales_value'],
                    'sro_schedule_no' => $item['sro_schedule_no'] ?? null,
                    'sro_item_sr_no' => $item['sro_item_sr_no'] ?? null,
                ]);
            }
        });
    }
}
