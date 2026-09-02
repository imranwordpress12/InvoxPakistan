<?php

namespace Tests\Feature\Domain\Invoices\BulkUpload;

use App\Domain\Invoices\BulkUpload\ImportBulkInvoices;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A Feature test (not Unit) because {@see ImportBulkInvoices} writes to
 * the database — same reasoning as every other domain-service test in
 * this codebase (e.g. SubmitInvoiceToFbrTest's sibling tests).
 */
class ImportBulkInvoicesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validRow(array $overrides = []): array
    {
        return array_replace([
            'invoice_reference_no' => 'INV-BULK-1',
            'buyer_ntn_cnic' => '1234567-8',
            'buyer_business_name' => 'Acme Traders',
            'buyer_address' => '123 Main St',
            'buyer_registration_type' => Customer::REGISTRATION_TYPE_REGISTERED,
            'buyer_province' => 'SINDH',
            'buyer_strn' => null,
            'invoice_date' => '2026-01-15',
            'invoice_type' => Invoice::TYPE_SALE,
            'sale_type' => 'Goods at standard rate (default)',
            'hs_code' => '8471.3000',
            'product_description' => 'Laptop',
            'rate' => '18',
            'uom' => 'Number',
            'price_per_unit' => '100',
            'quantity' => '2',
            'value_sales_excl_st' => '200',
            'further_tax' => null,
            'fixed_retail_price' => null,
            'st_withheld_at_source' => null,
            'extra_tax' => null,
            'fed_payable' => null,
            'discount' => null,
            'sro_schedule_no' => null,
            'sro_item_sr_no' => null,
        ], $overrides);
    }

    public function test_it_creates_a_single_item_invoice_as_a_draft(): void
    {
        $company = Company::factory()->create();

        $result = (new ImportBulkInvoices)->handle([1 => $this->validRow()], $company->id);

        $this->assertSame(['INV-BULK-1'], $result->createdReferences);
        $this->assertFalse($result->hasErrors());

        $invoice = Invoice::where('company_id', $company->id)->first();
        $this->assertTrue($invoice->isDraft());
        $this->assertSame('200.00', $invoice->total_excl_st);
        $this->assertSame('36.00', $invoice->total_sales_tax);
        $this->assertCount(1, $invoice->items);
    }

    public function test_multiple_rows_sharing_a_reference_become_one_multi_item_invoice(): void
    {
        $company = Company::factory()->create();

        $rows = [
            1 => $this->validRow(['product_description' => 'Laptop']),
            2 => $this->validRow(['product_description' => 'Mouse', 'price_per_unit' => '20', 'quantity' => '5', 'value_sales_excl_st' => '100']),
        ];

        $result = (new ImportBulkInvoices)->handle($rows, $company->id);

        $this->assertSame(['INV-BULK-1'], $result->createdReferences);
        $invoice = Invoice::where('company_id', $company->id)->first();
        $this->assertCount(2, $invoice->items);
        // 200 (excl st) + 100 (excl st) = 300; sales tax = 36 + 18 = 54.
        $this->assertSame('300.00', $invoice->total_excl_st);
        $this->assertSame('54.00', $invoice->total_sales_tax);
    }

    public function test_an_invalid_row_is_rejected_with_an_error_but_does_not_block_other_rows(): void
    {
        $company = Company::factory()->create();

        $rows = [
            1 => $this->validRow(['invoice_reference_no' => 'INV-GOOD']),
            2 => $this->validRow(['invoice_reference_no' => 'INV-BAD', 'quantity' => null]),
        ];

        $result = (new ImportBulkInvoices)->handle($rows, $company->id);

        $this->assertSame(['INV-GOOD'], $result->createdReferences);
        $this->assertTrue($result->hasErrors());
        $this->assertStringContainsString('Row 2', $result->errors[0]);
        $this->assertDatabaseCount('invoices', 1);
    }

    public function test_rows_for_the_same_reference_with_inconsistent_buyer_details_are_all_rejected(): void
    {
        $company = Company::factory()->create();

        $rows = [
            1 => $this->validRow(['buyer_business_name' => 'Acme Traders']),
            2 => $this->validRow(['buyer_business_name' => 'A Totally Different Name']),
        ];

        $result = (new ImportBulkInvoices)->handle($rows, $company->id);

        $this->assertSame([], $result->createdReferences);
        $this->assertTrue($result->hasErrors());
        $this->assertStringContainsString('disagree', $result->errors[0]);
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_a_reference_that_already_exists_for_the_company_is_skipped(): void
    {
        $company = Company::factory()->create();
        Invoice::factory()->create(['company_id' => $company->id, 'invoice_reference_no' => 'INV-BULK-1']);

        $result = (new ImportBulkInvoices)->handle([1 => $this->validRow()], $company->id);

        $this->assertSame([], $result->createdReferences);
        $this->assertTrue($result->hasErrors());
        $this->assertStringContainsString('already exists', $result->errors[0]);
        $this->assertDatabaseCount('invoices', 1);
    }

    public function test_the_same_reference_is_allowed_across_different_companies(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        Invoice::factory()->create(['company_id' => $companyB->id, 'invoice_reference_no' => 'INV-BULK-1']);

        $result = (new ImportBulkInvoices)->handle([1 => $this->validRow()], $companyA->id);

        $this->assertSame(['INV-BULK-1'], $result->createdReferences);
        $this->assertDatabaseCount('invoices', 2);
    }

    public function test_item_id_and_customer_id_are_never_set_on_a_bulk_imported_invoice(): void
    {
        $company = Company::factory()->create();

        (new ImportBulkInvoices)->handle([1 => $this->validRow()], $company->id);

        $invoice = Invoice::where('company_id', $company->id)->first();
        $this->assertNull($invoice->customer_id);
        $this->assertNull($invoice->items->first()->item_id);
    }
}
