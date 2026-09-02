<?php

namespace Tests\Feature\Models;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_belongs_to_company_and_has_many_invoice_items(): void
    {
        $company = Company::factory()->create();
        $item = Item::factory()->create(['company_id' => $company->id]);
        $invoice = Invoice::factory()->create(['company_id' => $company->id]);
        $invoiceItem = InvoiceItem::factory()->create(['invoice_id' => $invoice->id, 'item_id' => $item->id]);

        $this->assertTrue($item->company->is($company));
        $this->assertTrue($item->invoiceItems->contains($invoiceItem));
    }

    public function test_item_code_is_unique_per_company_but_not_globally(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        Item::factory()->create(['company_id' => $companyA->id, 'item_code' => 'ITM-001']);

        // Same code, different company — must be allowed.
        Item::factory()->create(['company_id' => $companyB->id, 'item_code' => 'ITM-001']);

        $this->assertSame(2, Item::where('item_code', 'ITM-001')->count());
    }

    public function test_item_code_must_be_unique_within_the_same_company(): void
    {
        $company = Company::factory()->create();
        Item::factory()->create(['company_id' => $company->id, 'item_code' => 'ITM-001']);

        $this->expectException(QueryException::class);

        Item::factory()->create(['company_id' => $company->id, 'item_code' => 'ITM-001']);
    }

    public function test_deleting_an_item_soft_deletes_it_and_preserves_invoice_history(): void
    {
        $company = Company::factory()->create();
        $item = Item::factory()->create(['company_id' => $company->id]);
        $invoice = Invoice::factory()->create(['company_id' => $company->id]);
        $invoiceItem = InvoiceItem::factory()->create(['invoice_id' => $invoice->id, 'item_id' => $item->id]);

        $item->delete();

        $this->assertSoftDeleted($item);
        $this->assertDatabaseHas('invoice_items', ['id' => $invoiceItem->id]);
    }
}
