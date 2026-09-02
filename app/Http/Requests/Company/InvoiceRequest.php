<?php

namespace App\Http\Requests\Company;

use App\Domain\Invoices\Fbr\FbrReferenceApiException;
use App\Domain\Invoices\Fbr\FbrReferenceService;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Shared by both create (store) and edit (update) — the field-level
 * requirements are identical either way (Company Dashboard PRD #6/#7);
 * the only difference between "Save as Draft" and "Submit Invoice" is
 * what the controller does afterward, driven by the `action` field.
 */
class InvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');

        return $invoice
            ? $this->user()->can('update', $invoice)
            : $this->user()->can('create', Invoice::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'integer'],

            // API Doc.pdf, Section 4.1's field table: buyerNTNCNIC is
            // "Required (Optional in case of Unregistered)".
            'buyer_ntn_cnic' => [
                Rule::requiredIf(fn () => $this->input('buyer_registration_type') === Customer::REGISTRATION_TYPE_REGISTERED),
                'nullable', 'string', 'max:20',
            ],
            'buyer_business_name' => ['required', 'string', 'max:255'],
            'buyer_address' => ['required', 'string'],
            'buyer_registration_type' => ['required', Rule::in([
                Customer::REGISTRATION_TYPE_REGISTERED,
                Customer::REGISTRATION_TYPE_UNREGISTERED,
            ])],
            'buyer_province' => ['required', 'string', 'max:100'],
            'buyer_strn' => ['nullable', 'string', 'max:50'],

            'invoice_date' => ['required', 'date'],
            'invoice_type' => ['required', Rule::in([
                Invoice::TYPE_SALE, Invoice::TYPE_DEBIT, Invoice::TYPE_CREDIT,
            ])],
            'invoice_reference_no' => ['nullable', 'string', 'max:100'],

            'action' => ['required', Rule::in(['draft', 'submit'])],

            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['nullable', 'integer'],
            'items.*.sale_type' => ['required', 'string', 'max:255'],
            'items.*.hs_code' => ['required', 'string', 'max:50'],
            'items.*.product_description' => ['required', 'string'],
            'items.*.rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.uom' => ['required', 'string', 'max:100'],
            'items.*.price_per_unit' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001', 'max:999999999999.9999'],
            'items.*.value_sales_excl_st' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'items.*.further_tax' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'items.*.fixed_retail_price' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'items.*.st_withheld_at_source' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'items.*.extra_tax' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'items.*.fed_payable' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'items.*.sro_schedule_no' => ['nullable', 'string', 'max:100'],
            'items.*.sro_item_sr_no' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * PRD #18: never trust a customer ID supplied by the frontend — if one
     * is given, it must actually belong to the authenticated company.
     */
    public function withValidator(ValidatorContract $validator): void
    {
        $validator->after(function ($validator) {
            $customerId = $this->input('customer_id');

            if (! $customerId) {
                return;
            }

            $belongsToCompany = Customer::where('id', $customerId)
                ->where('company_id', $this->user()->company_id)
                ->exists();

            if (! $belongsToCompany) {
                $validator->errors()->add('customer_id', 'The selected customer is invalid.');
            }
        });

        $validator->after(fn ($validator) => $this->validateHsCodeUomCombinations($validator));
    }

    /**
     * FBR Invoice Form Dependency spec, Section 12.2: "The server must
     * reject invalid combinations even if the frontend is bypassed." This
     * re-checks each item's HS Code/UoM pair against the live HS_UOM
     * reference API (Section 5.9) — the same one the create/edit form's
     * JS uses to populate the dropdown to begin with.
     *
     * Fails OPEN (skips the check), never closed, when FBR is
     * unreachable, or when the response is empty or not a well-formed
     * list of {description} rows — this is a bypass safety net, not a
     * reason to make saving a draft depend on FBR's own availability.
     */
    private function validateHsCodeUomCombinations(ValidatorContract $validator): void
    {
        $items = $this->input('items', []);

        if (! is_array($items) || $items === []) {
            return;
        }

        $reference = app(FbrReferenceService::class);
        $companyId = $this->user()->company_id;

        foreach ($items as $index => $item) {
            $hsCode = trim((string) ($item['hs_code'] ?? ''));
            $uom = trim((string) ($item['uom'] ?? ''));

            if ($hsCode === '' || $uom === '') {
                continue;
            }

            try {
                $allowed = $reference->hsUom($companyId, $hsCode);
            } catch (FbrReferenceApiException) {
                continue;
            }

            // A well-formed HS_UOM response is a plain list of
            // {uoM_ID, description} rows (Section 5.9's sample). Anything
            // else — empty, or not a list at all (a differently-shaped
            // response FBR itself returned, or an over-broad Http::fake
            // in a test) — isn't a usable authoritative answer.
            if (! array_is_list($allowed)) {
                continue;
            }

            $allowedNames = collect($allowed)
                ->pluck('description')
                ->filter(fn ($description) => filled($description))
                ->map(fn ($description) => Str::lower(trim((string) $description)))
                ->all();

            if ($allowedNames === []) {
                continue;
            }

            if (! in_array(Str::lower($uom), $allowedNames, true)) {
                $validator->errors()->add(
                    "items.{$index}.uom",
                    'The selected UOM is not valid for this HS Code. Please select an allowed UOM.'
                );
            }
        }
    }
}
