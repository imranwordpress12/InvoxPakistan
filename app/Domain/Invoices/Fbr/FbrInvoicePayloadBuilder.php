<?php

namespace App\Domain\Invoices\Fbr;

use App\Models\Invoice;
use App\Models\InvoiceItem;

/**
 * Maps this app's Invoice/InvoiceItem fields onto the exact JSON structure
 * FBR's `postinvoicedata_sb` expects (API Doc.pdf, Section 4.1.1) — every
 * key name and shape here is transcribed directly from that document, not
 * guessed from the local field names (which mostly, but not always, read
 * the same — e.g. local `total_sales_value` -> FBR `totalValues`).
 */
class FbrInvoicePayloadBuilder
{
    /**
     * @return array{payload: array<string, mixed>, scenarioId: ?string, scenarioConflict: bool}
     *                                                                                           `scenarioId` is null if no item's Sale Type has a known
     *                                                                                           mapping (see FbrScenarioResolver); `scenarioConflict` is
     *                                                                                           true if the invoice's items resolve to more than one
     *                                                                                           distinct scenario — FBR's payload has exactly one
     *                                                                                           scenarioId per invoice (Section 4.1.1), so mixed-
     *                                                                                           scenario items on one invoice can't be represented and
     *                                                                                           must be rejected rather than guessed at.
     */
    public static function build(Invoice $invoice, FbrScenarioResolver $scenarios): array
    {
        $company = $invoice->company;

        $resolvedScenarios = $invoice->items
            ->map(fn ($item) => $scenarios->resolve($item->sale_type, $invoice->buyer_registration_type))
            ->unique()
            ->values();

        $scenarioConflict = $resolvedScenarios->filter()->count() > 1;
        $scenarioId = $scenarioConflict ? null : $resolvedScenarios->first();

        $payload = [
            'invoiceType' => self::mapInvoiceType($invoice->invoice_type),
            'invoiceDate' => $invoice->invoice_date->toDateString(),
            'sellerNTNCNIC' => (string) $company?->ntn_cnic,
            'sellerBusinessName' => (string) $company?->business_name,
            'sellerProvince' => (string) $company?->province,
            'sellerAddress' => (string) $company?->address,
            'buyerNTNCNIC' => (string) $invoice->buyer_ntn_cnic,
            'buyerBusinessName' => (string) $invoice->buyer_business_name,
            'buyerProvince' => (string) $invoice->buyer_province,
            'buyerAddress' => (string) $invoice->buyer_address,
            'buyerRegistrationType' => self::mapRegistrationType($invoice->buyer_registration_type),
            // Debit/Credit Note support is intentionally partial — see this
            // class's docblock note below. For a Sale Invoice, FBR's own
            // samples always send this as "".
            'invoiceRefNo' => $invoice->invoice_type === Invoice::TYPE_SALE
                ? ''
                : (string) ($invoice->invoice_reference_no ?? ''),
            'scenarioId' => $scenarioId,
            'items' => $invoice->items->map(fn ($item) => self::buildItem($item))->all(),
        ];

        return [
            'payload' => $payload,
            'scenarioId' => $scenarioId,
            'scenarioConflict' => $scenarioConflict,
        ];
    }

    /**
     * @param  InvoiceItem  $item
     * @return array<string, mixed>
     */
    private static function buildItem($item): array
    {
        return [
            'hsCode' => (string) $item->hs_code,
            'productDescription' => (string) $item->product_description,
            'rate' => self::formatRate((float) $item->rate),
            'uoM' => (string) $item->uom,
            'quantity' => (float) $item->quantity,
            'totalValues' => (float) $item->total_sales_value,
            'valueSalesExcludingST' => (float) $item->value_sales_excl_st,
            'fixedNotifiedValueOrRetailPrice' => (float) $item->fixed_retail_price,
            'salesTaxApplicable' => (float) $item->sales_tax,
            'salesTaxWithheldAtSource' => (float) $item->st_withheld_at_source,
            'extraTax' => (float) $item->extra_tax,
            'furtherTax' => (float) $item->further_tax,
            'sroScheduleNo' => (string) ($item->sro_schedule_no ?? ''),
            'fedPayable' => (float) $item->fed_payable,
            'discount' => (float) $item->discount,
            'saleType' => (string) $item->sale_type,
            'sroItemSerialNo' => (string) ($item->sro_item_sr_no ?? ''),
        ];
    }

    /**
     * FBR's `rate` field is a percentage STRING (sample data: "18%"), not
     * the plain decimal this app stores — trims a trailing ".00"/".0" so
     * whole-number rates read "18%" rather than "18.00%", matching the
     * documentation's own sample values exactly.
     */
    private static function formatRate(float $rate): string
    {
        $formatted = rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.');

        return ($formatted === '' ? '0' : $formatted).'%';
    }

    /**
     * Only "Sale Invoice"/"Debit Note" appear anywhere in API Doc.pdf
     * (Section 4.1's field table, and the doctypecode reference API's
     * sample response, Section 5.2.2) — "Credit Note" is never actually
     * documented as a valid invoiceType value for this API, even though
     * this app's own Invoice model has supported credit notes since
     * before this integration existed. Mapped here as the closest sane
     * extrapolation, not a confirmed value — flagged explicitly rather
     * than silently assumed.
     */
    private static function mapInvoiceType(string $type): string
    {
        return match ($type) {
            Invoice::TYPE_DEBIT => 'Debit Note',
            Invoice::TYPE_CREDIT => 'Credit Note',
            default => 'Sale Invoice',
        };
    }

    private static function mapRegistrationType(?string $type): string
    {
        return ucfirst((string) $type) === 'Registered' ? 'Registered' : 'Unregistered';
    }
}
