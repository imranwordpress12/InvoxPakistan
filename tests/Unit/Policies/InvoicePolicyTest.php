<?php

namespace Tests\Unit\Policies;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use App\Policies\InvoicePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_company_user_may_only_touch_its_own_companys_invoices(): void
    {
        $policy = new InvoicePolicy;

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->company($companyA)->create();

        $ownInvoice = Invoice::factory()->create(['company_id' => $companyA->id]);
        $otherCompanysInvoice = Invoice::factory()->create(['company_id' => $companyB->id]);

        $this->assertTrue($policy->view($userA, $ownInvoice));
        $this->assertFalse($policy->view($userA, $otherCompanysInvoice));
    }

    /**
     * PRD #8: "Once an invoice is submitted to IRIS, it cannot be edited,
     * reversed, or cancelled." A `failed` invoice is explicitly excluded
     * from that lock — the Compliance page's own wording (PRD #17) says
     * "if IRIS returns a valid failure reason, the invoice is considered
     * not submitted," so it must stay editable to fix and retry.
     */
    public function test_a_draft_or_failed_invoice_may_be_updated_or_deleted_but_not_a_submitted_one(): void
    {
        $policy = new InvoicePolicy;
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();

        $draft = Invoice::factory()->create(['company_id' => $company->id, 'status' => Invoice::STATUS_DRAFT]);
        $failed = Invoice::factory()->create(['company_id' => $company->id, 'status' => Invoice::STATUS_FAILED]);
        $successful = Invoice::factory()->create(['company_id' => $company->id, 'status' => Invoice::STATUS_SUCCESSFUL]);
        $submitted = Invoice::factory()->create(['company_id' => $company->id, 'status' => Invoice::STATUS_SUBMITTED]);

        $this->assertTrue($policy->update($user, $draft));
        $this->assertTrue($policy->delete($user, $draft));
        $this->assertTrue($policy->update($user, $failed));
        $this->assertTrue($policy->delete($user, $failed));

        $this->assertFalse($policy->update($user, $successful));
        $this->assertFalse($policy->delete($user, $successful));
        $this->assertFalse($policy->update($user, $submitted));
        $this->assertFalse($policy->delete($user, $submitted));
    }

    /**
     * A draft or a previously-failed invoice may be (re)submitted; a
     * successful or already-in-flight one may not.
     */
    public function test_only_a_draft_or_failed_invoice_may_be_submitted(): void
    {
        $policy = new InvoicePolicy;
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();

        $draft = Invoice::factory()->create(['company_id' => $company->id, 'status' => Invoice::STATUS_DRAFT]);
        $failed = Invoice::factory()->create(['company_id' => $company->id, 'status' => Invoice::STATUS_FAILED]);
        $successful = Invoice::factory()->create(['company_id' => $company->id, 'status' => Invoice::STATUS_SUCCESSFUL]);
        $submitted = Invoice::factory()->create(['company_id' => $company->id, 'status' => Invoice::STATUS_SUBMITTED]);

        $this->assertTrue($policy->submit($user, $draft));
        $this->assertTrue($policy->submit($user, $failed));
        $this->assertFalse($policy->submit($user, $successful));
        $this->assertFalse($policy->submit($user, $submitted));
    }
}
