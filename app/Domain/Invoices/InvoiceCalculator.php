<?php

namespace App\Domain\Invoices;

/**
 * Company Dashboard PRD #7/#21: standard sales-tax invoice math, since no
 * existing calculation logic was found to preserve in this codebase (see
 * CHANGELOG_PROJECT.md, Phase 1). Every formula here is a documented
 * assumption, not a verified FBR rule — review before relying on this for
 * live filing.
 *
 * Deliberately excluded from total_sales_value:
 *  - `st_withheld_at_source`: withholding tax changes who remits the tax
 *    to FBR, not the invoice's own stated value, so it is not added or
 *    subtracted here.
 *  - `fixed_retail_price`: some goods use retail price as an alternate
 *    tax base under Pakistani sales tax law (e.g. Third Schedule items) —
 *    that rule isn't implemented here; the field is stored/displayed only.
 */
class InvoiceCalculator
{
    /**
     * @param  array<string, mixed>  $item  Raw item input (price_per_unit,
     *                                      quantity, rate, further_tax,
     *                                      extra_tax, fed_payable, discount,
     *                                      value_sales_excl_st).
     * @return array<string, float> Computed value_sales_excl_st, sales_tax,
     *                              and total_sales_value.
     */
    public static function calculateItem(array $item): array
    {
        $pricePerUnit = (float) ($item['price_per_unit'] ?? 0);
        $quantity = (float) ($item['quantity'] ?? 0);
        $rate = (float) ($item['rate'] ?? 0);

        // If a price/unit was given, it's the authoritative source for the
        // taxable value; otherwise trust whatever was entered directly
        // (e.g. a Fixed/notified-value scenario with no per-unit price).
        $valueExclSt = $pricePerUnit > 0
            ? round($pricePerUnit * $quantity, 2)
            : round((float) ($item['value_sales_excl_st'] ?? 0), 2);

        $salesTax = round($valueExclSt * $rate / 100, 2);

        $furtherTax = (float) ($item['further_tax'] ?? 0);
        $extraTax = (float) ($item['extra_tax'] ?? 0);
        $fedPayable = (float) ($item['fed_payable'] ?? 0);
        $discount = (float) ($item['discount'] ?? 0);

        $totalSalesValue = round(
            $valueExclSt + $salesTax + $furtherTax + $extraTax + $fedPayable - $discount,
            2
        );

        return [
            'value_sales_excl_st' => $valueExclSt,
            'sales_tax' => $salesTax,
            'total_sales_value' => $totalSalesValue,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items  Each already run
     *                                                   through calculateItem().
     * @return array<string, float>
     */
    public static function summarizeInvoice(array $items): array
    {
        return [
            'total_excl_st' => round(array_sum(array_column($items, 'value_sales_excl_st')), 2),
            'total_sales_tax' => round(array_sum(array_column($items, 'sales_tax')), 2),
            'total_further_tax' => round(array_sum(array_map(fn ($item) => (float) ($item['further_tax'] ?? 0), $items)), 2),
            'total_discount' => round(array_sum(array_map(fn ($item) => (float) ($item['discount'] ?? 0), $items)), 2),
            'total_amount' => round(array_sum(array_column($items, 'total_sales_value')), 2),
        ];
    }
}
