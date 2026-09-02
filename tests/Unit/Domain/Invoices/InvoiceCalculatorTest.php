<?php

namespace Tests\Unit\Domain\Invoices;

use App\Domain\Invoices\InvoiceCalculator;
use PHPUnit\Framework\TestCase;

class InvoiceCalculatorTest extends TestCase
{
    public function test_it_computes_value_and_tax_from_price_and_quantity(): void
    {
        $result = InvoiceCalculator::calculateItem([
            'price_per_unit' => 100,
            'quantity' => 3,
            'rate' => 18,
        ]);

        $this->assertSame(300.0, $result['value_sales_excl_st']);
        $this->assertSame(54.0, $result['sales_tax']);
        $this->assertSame(354.0, $result['total_sales_value']);
    }

    public function test_it_falls_back_to_a_directly_entered_value_when_no_price_per_unit_is_given(): void
    {
        $result = InvoiceCalculator::calculateItem([
            'price_per_unit' => 0,
            'quantity' => 5,
            'rate' => 17,
            'value_sales_excl_st' => 1000,
        ]);

        $this->assertSame(1000.0, $result['value_sales_excl_st']);
        $this->assertSame(170.0, $result['sales_tax']);
    }

    public function test_it_folds_further_tax_extra_tax_and_fed_payable_into_the_total_and_subtracts_discount(): void
    {
        $result = InvoiceCalculator::calculateItem([
            'price_per_unit' => 100,
            'quantity' => 1,
            'rate' => 18,
            'further_tax' => 10,
            'extra_tax' => 5,
            'fed_payable' => 2,
            'discount' => 3,
        ]);

        // 100 (excl st) + 18 (sales tax) + 10 + 5 + 2 - 3 = 132
        $this->assertSame(132.0, $result['total_sales_value']);
    }

    public function test_st_withheld_at_source_and_fixed_retail_price_do_not_affect_the_total(): void
    {
        $withoutExtras = InvoiceCalculator::calculateItem([
            'price_per_unit' => 100, 'quantity' => 1, 'rate' => 18,
        ]);

        $withExtras = InvoiceCalculator::calculateItem([
            'price_per_unit' => 100, 'quantity' => 1, 'rate' => 18,
            'st_withheld_at_source' => 999, 'fixed_retail_price' => 999,
        ]);

        $this->assertSame($withoutExtras['total_sales_value'], $withExtras['total_sales_value']);
    }

    public function test_it_sums_across_multiple_items(): void
    {
        // Mirrors how the controller builds this array: the raw input
        // merged with calculateItem()'s computed fields — summarizeInvoice()
        // reads further_tax/discount straight from the raw input, not from
        // calculateItem()'s return value (which only carries the three
        // computed fields).
        $raw = [
            ['price_per_unit' => 100, 'quantity' => 1, 'rate' => 18, 'further_tax' => 10, 'discount' => 5],
            ['price_per_unit' => 200, 'quantity' => 2, 'rate' => 17],
        ];
        $items = array_map(fn ($item) => [...$item, ...InvoiceCalculator::calculateItem($item)], $raw);

        $totals = InvoiceCalculator::summarizeInvoice($items);

        $this->assertSame(500.0, $totals['total_excl_st']); // 100 + 400
        $this->assertSame(86.0, $totals['total_sales_tax']); // 18 + 68
        $this->assertSame(10.0, $totals['total_further_tax']);
        $this->assertSame(5.0, $totals['total_discount']);
        $this->assertSame(123.0 + 468.0, $totals['total_amount']);
    }
}
