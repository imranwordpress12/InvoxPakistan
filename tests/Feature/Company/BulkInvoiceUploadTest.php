<?php

namespace Tests\Feature\Company;

use App\Domain\Invoices\BulkUpload\BulkInvoiceCsvParser;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BulkInvoiceUploadTest extends TestCase
{
    use RefreshDatabase;

    private function activeCompanyUser(): array
    {
        $company = Company::factory()->create();
        Subscription::factory()->for($company)->create([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->addDays(30),
        ]);
        $user = User::factory()->company($company)->create();

        return [$company, $user];
    }

    private function csvFile(array $rows, ?array $header = null): UploadedFile
    {
        $header ??= BulkInvoiceCsvParser::REQUIRED_COLUMNS;
        $lines = [implode(',', $header)];

        foreach ($rows as $row) {
            $lines[] = implode(',', array_map(fn ($column) => $row[$column] ?? '', $header));
        }

        return UploadedFile::fake()->createWithContent('invoices.csv', implode("\n", $lines)."\n");
    }

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
            'buyer_registration_type' => 'registered',
            'buyer_province' => 'SINDH',
            'buyer_strn' => '',
            'invoice_date' => '2026-01-15',
            'invoice_type' => 'sale',
            'sale_type' => 'Goods at standard rate (default)',
            'hs_code' => '8471.3000',
            'product_description' => 'Laptop',
            'rate' => '18',
            'uom' => 'Number',
            'price_per_unit' => '100',
            'quantity' => '2',
            'value_sales_excl_st' => '200',
            'further_tax' => '',
            'fixed_retail_price' => '',
            'st_withheld_at_source' => '',
            'extra_tax' => '',
            'fed_payable' => '',
            'discount' => '',
            'sro_schedule_no' => '',
            'sro_item_sr_no' => '',
        ], $overrides);
    }

    public function test_the_upload_form_renders(): void
    {
        [, $user] = $this->activeCompanyUser();

        $this->actingAs($user)->get(route('company.invoices.bulk-upload.create'))->assertOk();
    }

    public function test_uploading_a_valid_file_creates_draft_invoices_scoped_to_the_company(): void
    {
        [$company, $user] = $this->activeCompanyUser();

        $response = $this->actingAs($user)->post(route('company.invoices.bulk-upload.store'), [
            'file' => $this->csvFile([$this->validRow()]),
        ]);

        $response->assertRedirect(route('company.invoices.drafts'));
        $response->assertSessionHas('bulk_upload_created', 1);

        $invoice = Invoice::where('company_id', $company->id)->first();
        $this->assertNotNull($invoice);
        $this->assertTrue($invoice->isDraft());
    }

    public function test_uploading_a_non_csv_file_is_rejected(): void
    {
        [, $user] = $this->activeCompanyUser();

        $response = $this->actingAs($user)->post(route('company.invoices.bulk-upload.store'), [
            'file' => UploadedFile::fake()->create('invoice.pdf', 10, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_uploading_a_file_missing_required_columns_shows_a_file_level_error(): void
    {
        [, $user] = $this->activeCompanyUser();

        $file = UploadedFile::fake()->createWithContent('invoices.csv', "invoice_reference_no,buyer_ntn_cnic\nINV-1,123\n");

        $response = $this->actingAs($user)->post(route('company.invoices.bulk-upload.store'), ['file' => $file]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_a_partially_bad_file_creates_the_good_rows_and_reports_the_bad_ones(): void
    {
        [$company, $user] = $this->activeCompanyUser();

        $response = $this->actingAs($user)->post(route('company.invoices.bulk-upload.store'), [
            'file' => $this->csvFile([
                $this->validRow(['invoice_reference_no' => 'INV-GOOD']),
                $this->validRow(['invoice_reference_no' => 'INV-BAD', 'quantity' => '']),
            ]),
        ]);

        $response->assertRedirect(route('company.invoices.drafts'));
        $response->assertSessionHas('bulk_upload_created', 1);
        $response->assertSessionHas('bulk_upload_errors', fn ($errors) => count($errors) === 1);
        $this->assertDatabaseHas('invoices', ['company_id' => $company->id, 'invoice_reference_no' => 'INV-GOOD']);
        $this->assertDatabaseMissing('invoices', ['company_id' => $company->id, 'invoice_reference_no' => 'INV-BAD']);
    }

    public function test_the_sample_template_download_has_every_required_column_and_a_matching_example_row(): void
    {
        [, $user] = $this->activeCompanyUser();

        $response = $this->actingAs($user)->get(route('company.invoices.bulk-upload.template'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $lines = array_filter(explode("\r\n", $response->getContent()));
        $header = str_getcsv(array_values($lines)[0]);
        $example = str_getcsv(array_values($lines)[1]);

        $this->assertSame(BulkInvoiceCsvParser::REQUIRED_COLUMNS, $header);
        $this->assertCount(count($header), $example);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('company.invoices.bulk-upload.create'))->assertRedirect(route('company.login'));
    }

    public function test_a_blocked_company_is_redirected_to_subscription_required(): void
    {
        $company = Company::factory()->create();
        Subscription::factory()->for($company)->pending()->create();
        $user = User::factory()->company($company)->create();

        $this->actingAs($user)
            ->get(route('company.invoices.bulk-upload.create'))
            ->assertRedirect(route('company.subscription-required'));
    }

    public function test_admin_cannot_access_the_bulk_upload_form(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('company.invoices.bulk-upload.create'))
            ->assertForbidden();
    }
}
