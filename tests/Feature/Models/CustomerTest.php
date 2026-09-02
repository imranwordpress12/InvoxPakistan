<?php

namespace Tests\Feature\Models;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_belongs_to_company_and_has_many_invoices(): void
    {
        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $invoice = Invoice::factory()->create(['company_id' => $company->id, 'customer_id' => $customer->id]);

        $this->assertTrue($customer->company->is($company));
        $this->assertTrue($customer->invoices->contains($invoice));
    }

    public function test_deleting_a_customer_soft_deletes_it_and_preserves_invoice_history(): void
    {
        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $invoice = Invoice::factory()->create(['company_id' => $company->id, 'customer_id' => $customer->id]);

        $customer->delete();

        $this->assertSoftDeleted($customer);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }
}
