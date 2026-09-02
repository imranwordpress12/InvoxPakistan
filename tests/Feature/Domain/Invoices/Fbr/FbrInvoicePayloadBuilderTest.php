<?php

namespace Tests\Feature\Domain\Invoices\Fbr;

use App\Domain\Invoices\Fbr\FbrInvoicePayloadBuilder;
use App\Domain\Invoices\Fbr\FbrScenarioResolver;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FbrInvoicePayloadBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_maps_invoice_and_seller_fields_to_the_exact_fbr_json_field_names(): void
    {
        $company = Company::factory()->create([
            'ntn_cnic' => '0786909',
            'business_name' => 'Company 8',
            'province' => 'Sindh',
            'address' => 'Karachi',
        ]);
        $invoice = Invoice::factory()
            ->has(InvoiceItem::factory()->count(1)->state([
                'hs_code' => '0101.2100',
                'product_description' => 'product Description',
                'rate' => 18,
                'uom' => 'Numbers, pieces, units',
                'quantity' => 1,
                'value_sales_excl_st' => 1000,
                'sales_tax' => 180,
                'further_tax' => 120,
                'total_sales_value' => 1300,
            ]), 'items')
            ->create([
                'company_id' => $company->id,
                'invoice_type' => Invoice::TYPE_SALE,
                'invoice_date' => '2025-04-21',
                'buyer_registration_type' => Customer::REGISTRATION_TYPE_REGISTERED,
            ]);

        $result = FbrInvoicePayloadBuilder::build($invoice->load('items', 'company'), new FbrScenarioResolver);

        $payload = $result['payload'];

        $this->assertSame('Sale Invoice', $payload['invoiceType']);
        $this->assertSame('2025-04-21', $payload['invoiceDate']);
        $this->assertSame('0786909', $payload['sellerNTNCNIC']);
        $this->assertSame('Company 8', $payload['sellerBusinessName']);
        $this->assertSame('Sindh', $payload['sellerProvince']);
        $this->assertSame('Karachi', $payload['sellerAddress']);
        $this->assertSame('SN001', $payload['scenarioId']);
        $this->assertSame('', $payload['invoiceRefNo']);

        $item = $payload['items'][0];
        $this->assertSame('0101.2100', $item['hsCode']);
        $this->assertSame('18%', $item['rate']);
        $this->assertSame(1300.0, $item['totalValues']);
        $this->assertSame(1000.0, $item['valueSalesExcludingST']);
        $this->assertSame(180.0, $item['salesTaxApplicable']);
        $this->assertSame(120.0, $item['furtherTax']);
    }

    public function test_rate_is_formatted_without_trailing_zeros(): void
    {
        $company = Company::factory()->create();
        $invoice = Invoice::factory()
            ->has(InvoiceItem::factory()->state(['rate' => 0]), 'items')
            ->create(['company_id' => $company->id]);

        $result = FbrInvoicePayloadBuilder::build($invoice->load('items', 'company'), new FbrScenarioResolver);

        $this->assertSame('0%', $result['payload']['items'][0]['rate']);
    }

    public function test_a_sale_invoice_always_sends_an_empty_invoice_ref_no_regardless_of_the_local_reference(): void
    {
        $company = Company::factory()->create();
        $invoice = Invoice::factory()
            ->has(InvoiceItem::factory(), 'items')
            ->create([
                'company_id' => $company->id,
                'invoice_type' => Invoice::TYPE_SALE,
                'invoice_reference_no' => 'MY-OWN-REF-123',
            ]);

        $result = FbrInvoicePayloadBuilder::build($invoice->load('items', 'company'), new FbrScenarioResolver);

        $this->assertSame('', $result['payload']['invoiceRefNo']);
    }

    public function test_items_with_conflicting_scenarios_are_flagged(): void
    {
        $company = Company::factory()->create();
        $invoice = Invoice::factory()->create(['company_id' => $company->id]);
        InvoiceItem::factory()->for($invoice)->create(['sale_type' => 'Goods at Reduced Rate']);
        InvoiceItem::factory()->for($invoice)->create(['sale_type' => 'Petroleum Products']);

        $result = FbrInvoicePayloadBuilder::build($invoice->load('items', 'company'), new FbrScenarioResolver);

        $this->assertTrue($result['scenarioConflict']);
        $this->assertNull($result['scenarioId']);
    }
}
