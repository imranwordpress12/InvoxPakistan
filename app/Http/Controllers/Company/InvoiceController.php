<?php

namespace App\Http\Controllers\Company;

use App\Domain\Invoices\Exceptions\InvoiceNotSubmittableException;
use App\Domain\Invoices\Fbr\FbrResponseMessage;
use App\Domain\Invoices\InvoiceCalculator;
use App\Domain\Invoices\SubmitInvoiceToFbr;
use App\Http\Controllers\Controller;
use App\Http\Requests\Company\InvoiceRequest;
use App\Models\Customer;
use App\Models\HsCode;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Province;
use App\Models\SaleType;
use App\Models\TaxRate;
use App\Models\UnitOfMeasure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /**
     * Company Dashboard PRD #5. Draft invoices must never be mixed with
     * submitted ones (PRD #9) — this listing only ever shows invoices
     * that have actually been through the submit flow (or attempted to).
     *
     * Always scoped to the authenticated user's own company (PRD #18) —
     * never a request-supplied company ID.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::query()
            ->where('company_id', $request->user()->company_id)
            ->whereIn('status', [Invoice::STATUS_SUBMITTED, Invoice::STATUS_SUCCESSFUL, Invoice::STATUS_FAILED])
            ->with('customer');

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_reference_no', 'like', "%{$search}%")
                    ->orWhere('buyer_business_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        $invoices = $query->latest('invoice_date')->paginate(20)->withQueryString();

        return view('company.invoices.index', ['invoices' => $invoices]);
    }

    /**
     * PRD #9: drafts must never be mixed with the main listing above.
     */
    public function drafts(Request $request): View
    {
        $this->authorize('viewAny', Invoice::class);

        $invoices = Invoice::query()
            ->where('company_id', $request->user()->company_id)
            ->where('status', Invoice::STATUS_DRAFT)
            ->latest()
            ->paginate(20);

        return view('company.invoices.drafts', ['invoices' => $invoices]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Invoice::class);

        return view('company.invoices.create', $this->formData($request));
    }

    /**
     * PRD #8: Create Invoice -> (Save as Draft | Submit Invoice). Either
     * way the invoice row itself is always created first as a draft
     * inside one DB transaction; "submit" is a second, separate step
     * against an already-persisted row (SubmitInvoiceToFbr), not folded
     * into the same transaction — a partially-failed FBR call must not
     * roll back the invoice's own existence.
     */
    public function store(InvoiceRequest $request, SubmitInvoiceToFbr $submitter): RedirectResponse
    {
        $data = $request->validated();

        $invoice = DB::transaction(function () use ($data, $request) {
            $items = array_map(fn ($item) => [...$item, ...InvoiceCalculator::calculateItem($item)], $data['items']);
            $totals = InvoiceCalculator::summarizeInvoice($items);

            $invoice = Invoice::create([
                'company_id' => $request->user()->company_id,
                'customer_id' => $data['customer_id'] ?? null,
                'invoice_reference_no' => $data['invoice_reference_no'] ?? null,
                'invoice_type' => $data['invoice_type'],
                'invoice_date' => $data['invoice_date'],
                'status' => Invoice::STATUS_DRAFT,
                'buyer_ntn_cnic' => $data['buyer_ntn_cnic'],
                'buyer_business_name' => $data['buyer_business_name'],
                'buyer_address' => $data['buyer_address'],
                'buyer_registration_type' => $data['buyer_registration_type'],
                'buyer_province' => $data['buyer_province'],
                'buyer_strn' => $data['buyer_strn'] ?? null,
                ...$totals,
            ]);

            $this->syncItems($invoice, $items);

            return $invoice;
        });

        return $this->afterSave($invoice, $data['action'], $submitter);
    }

    /**
     * Invoice Details (Read — company_id isolation via InvoicePolicy's
     * `view` ability, same as every other action here).
     */
    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        return view('company.invoices.show', [
            'invoice' => $invoice->load('items', 'customer'),
            'fbrErrorMessage' => $invoice->isFailed() ? FbrResponseMessage::extract($invoice->fbr_response) : null,
        ]);
    }

    public function edit(Invoice $invoice): View
    {
        $this->authorize('update', $invoice);

        return view('company.invoices.edit', [
            ...$this->formData(request()),
            'invoice' => $invoice->load('items'),
        ]);
    }

    public function update(InvoiceRequest $request, Invoice $invoice, SubmitInvoiceToFbr $submitter): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $invoice) {
            $items = array_map(fn ($item) => [...$item, ...InvoiceCalculator::calculateItem($item)], $data['items']);
            $totals = InvoiceCalculator::summarizeInvoice($items);

            $invoice->update([
                'customer_id' => $data['customer_id'] ?? null,
                'invoice_reference_no' => $data['invoice_reference_no'] ?? null,
                'invoice_type' => $data['invoice_type'],
                'invoice_date' => $data['invoice_date'],
                'buyer_ntn_cnic' => $data['buyer_ntn_cnic'],
                'buyer_business_name' => $data['buyer_business_name'],
                'buyer_address' => $data['buyer_address'],
                'buyer_registration_type' => $data['buyer_registration_type'],
                'buyer_province' => $data['buyer_province'],
                'buyer_strn' => $data['buyer_strn'] ?? null,
                ...$totals,
            ]);

            // Full replace rather than diffing — simplest correct approach
            // for a dynamic multi-row form with no stable per-row identity
            // guaranteed between requests.
            $invoice->items()->delete();
            $this->syncItems($invoice, $items);
        });

        return $this->afterSave($invoice->refresh(), $data['action'], $submitter);
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorize('delete', $invoice);

        $invoice->delete();

        return redirect()
            ->route('company.invoices.drafts')
            ->with('status', 'Draft invoice deleted.');
    }

    /**
     * Re-submit an existing draft or previously-failed invoice, without
     * going through the edit form again.
     */
    public function submit(Invoice $invoice, SubmitInvoiceToFbr $submitter): RedirectResponse
    {
        $this->authorize('submit', $invoice);

        return $this->attemptSubmit($invoice, $submitter);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncItems(Invoice $invoice, array $items): void
    {
        foreach ($items as $item) {
            $invoice->items()->create([
                'item_id' => $item['item_id'] ?? null,
                'sale_type' => $item['sale_type'],
                'hs_code' => $item['hs_code'],
                'product_description' => $item['product_description'],
                'rate' => $item['rate'],
                'uom' => $item['uom'],
                'price_per_unit' => $item['price_per_unit'] ?? 0,
                'quantity' => $item['quantity'],
                'value_sales_excl_st' => $item['value_sales_excl_st'],
                'sales_tax' => $item['sales_tax'],
                'further_tax' => $item['further_tax'] ?? 0,
                'fixed_retail_price' => $item['fixed_retail_price'] ?? 0,
                'st_withheld_at_source' => $item['st_withheld_at_source'] ?? 0,
                'extra_tax' => $item['extra_tax'] ?? 0,
                'fed_payable' => $item['fed_payable'] ?? 0,
                'discount' => $item['discount'] ?? 0,
                'total_sales_value' => $item['total_sales_value'],
                'sro_schedule_no' => $item['sro_schedule_no'] ?? null,
                'sro_item_sr_no' => $item['sro_item_sr_no'] ?? null,
            ]);
        }
    }

    private function afterSave(Invoice $invoice, string $action, SubmitInvoiceToFbr $submitter): RedirectResponse
    {
        if ($action === 'draft') {
            return redirect()
                ->route('company.invoices.drafts')
                ->with('status', "Invoice \"{$invoice->invoice_reference_no}\" saved as draft.");
        }

        return $this->attemptSubmit($invoice, $submitter);
    }

    /**
     * A failed invoice is redirected to its own Invoice Details page, not
     * Drafts — drafts() only ever queries status="draft", so a "failed"
     * invoice (correctly redirected there previously) would never
     * actually appear on the page the user just landed on. The details
     * page always reflects whatever the invoice's current status
     * actually is, and already surfaces the FBR error plus Edit/Retry
     * actions for a draft-or-failed invoice.
     */
    private function attemptSubmit(Invoice $invoice, SubmitInvoiceToFbr $submitter): RedirectResponse
    {
        try {
            $submitter->handle($invoice);
        } catch (InvoiceNotSubmittableException) {
            return redirect()
                ->route('company.invoices.show', $invoice)
                ->with('submission_failed', 'This invoice is no longer eligible for submission.');
        }

        $invoice->refresh();

        if ($invoice->isSuccessful()) {
            return redirect()
                ->route('company.invoices.index')
                ->with('status', "Invoice \"{$invoice->invoice_reference_no}\" submitted successfully.");
        }

        $reason = FbrResponseMessage::extract($invoice->fbr_response);

        return redirect()
            ->route('company.invoices.show', $invoice)
            ->with(
                'submission_failed',
                "Invoice \"{$invoice->invoice_reference_no}\" submission failed: {$reason} It has been kept so you can review and retry."
            );
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Request $request): array
    {
        $companyId = $request->user()->company_id;

        return [
            'provinces' => Province::orderBy('name')->pluck('name'),
            'saleTypes' => SaleType::orderBy('name')->pluck('name'),
            'hsCodes' => HsCode::orderBy('code')->get(['code', 'description']),
            'unitsOfMeasure' => UnitOfMeasure::orderBy('name')->pluck('name'),
            'taxRates' => TaxRate::orderBy('rate')->get(['rate', 'label']),
            'customers' => Customer::where('company_id', $companyId)
                ->where('status', Customer::STATUS_ACTIVE)
                ->orderBy('business_name')
                ->get(),
            'items' => Item::where('company_id', $companyId)
                ->where('status', Item::STATUS_ACTIVE)
                ->orderBy('item_name')
                ->get(),
        ];
    }
}
