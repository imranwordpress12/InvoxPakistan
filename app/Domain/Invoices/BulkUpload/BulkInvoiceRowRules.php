<?php

namespace App\Domain\Invoices\BulkUpload;

use App\Http\Requests\Company\InvoiceRequest;
use App\Models\Customer;
use App\Models\Invoice;

/**
 * The validation rules for one CSV row (Company Dashboard PRD #10),
 * mirroring {@see InvoiceRequest} field-for-
 * field wherever the same field exists on both — a bulk-uploaded invoice
 * should never be held to looser rules than one entered by hand.
 *
 * Two deliberate differences from the manual form, both because there's
 * no live JS calculator in a CSV: `invoice_reference_no` is required here
 * (it's how rows are grouped into one invoice — optional on the manual
 * form, where an invoice is never ambiguous about which one it is) and
 * `value_sales_excl_st` is always required directly (the manual form can
 * derive it from Price/Unit x Quantity; a spreadsheet can't).
 */
class BulkInvoiceRowRules
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'invoice_reference_no' => ['required', 'string', 'max:100'],

            'buyer_ntn_cnic' => ['required', 'string', 'max:20'],
            'buyer_business_name' => ['required', 'string', 'max:255'],
            'buyer_address' => ['required', 'string'],
            'buyer_registration_type' => ['required', 'in:'.Customer::REGISTRATION_TYPE_REGISTERED.','.Customer::REGISTRATION_TYPE_UNREGISTERED],
            'buyer_province' => ['required', 'string', 'max:100'],
            'buyer_strn' => ['nullable', 'string', 'max:50'],

            'invoice_date' => ['required', 'date'],
            'invoice_type' => ['required', 'in:'.Invoice::TYPE_SALE.','.Invoice::TYPE_DEBIT.','.Invoice::TYPE_CREDIT],

            'sale_type' => ['required', 'string', 'max:255'],
            'hs_code' => ['required', 'string', 'max:50'],
            'product_description' => ['required', 'string'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'uom' => ['required', 'string', 'max:100'],
            'price_per_unit' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'quantity' => ['required', 'numeric', 'min:0.0001', 'max:999999999999.9999'],
            'value_sales_excl_st' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'further_tax' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'fixed_retail_price' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'st_withheld_at_source' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'extra_tax' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'fed_payable' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'sro_schedule_no' => ['nullable', 'string', 'max:100'],
            'sro_item_sr_no' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Invoice-level fields that must be identical across every row
     * sharing the same `invoice_reference_no` — they describe the
     * invoice, not the item, and a CSV has no other way to say "this
     * line belongs to that invoice" than repeating them per row.
     *
     * @return array<int, string>
     */
    public static function invoiceLevelFields(): array
    {
        return [
            'buyer_ntn_cnic', 'buyer_business_name', 'buyer_address',
            'buyer_registration_type', 'buyer_province', 'buyer_strn',
            'invoice_date', 'invoice_type',
        ];
    }
}
