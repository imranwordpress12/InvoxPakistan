<?php

namespace Tests\Feature\Models;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_belongs_to_company_and_customer_and_has_many_items(): void
    {
        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $invoice = Invoice::factory()->create(['company_id' => $company->id, 'customer_id' => $customer->id]);
        $item = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $this->assertTrue($invoice->company->is($company));
        $this->assertTrue($invoice->customer->is($customer));
        $this->assertTrue($invoice->items->contains($item));
    }

    public function test_status_helpers_match_the_stored_status(): void
    {
        $draft = Invoice::factory()->create(['status' => Invoice::STATUS_DRAFT]);
        $this->assertTrue($draft->isDraft());
        $this->assertFalse($draft->isSuccessful());

        $successful = Invoice::factory()->submitted()->create();
        $this->assertTrue($successful->isSuccessful());
        $this->assertFalse($successful->isDraft());

        $failed = Invoice::factory()->failed()->create();
        $this->assertTrue($failed->isFailed());
    }

    /**
     * A normal (soft) delete leaves the invoice's own line items and the
     * customer/item master data completely untouched — it only sets
     * `deleted_at`, so the DB-level cascade below never fires for it.
     */
    public function test_a_normal_delete_soft_deletes_the_invoice_and_touches_nothing_else(): void
    {
        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $invoice = Invoice::factory()->create(['company_id' => $company->id, 'customer_id' => $customer->id]);
        $item = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $invoice->delete();

        $this->assertSoftDeleted($invoice);
        $this->assertDatabaseHas('invoice_items', ['id' => $item->id]);
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    /**
     * Line items are pure children of one invoice (unlike company-level
     * financial history elsewhere in this app) — a genuine hard delete of
     * the invoice does cascade-remove them at the DB level.
     */
    public function test_a_force_delete_cascades_to_remove_its_items(): void
    {
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $invoice->forceDelete();

        $this->assertDatabaseMissing('invoice_items', ['id' => $item->id]);
    }
}
