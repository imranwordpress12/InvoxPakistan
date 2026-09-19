<?php

namespace App\Http\Requests\Company;

use App\Domain\Invoices\Fbr\FbrReferenceApiException;
use App\Domain\Invoices\Fbr\FbrReferenceService;
use App\Models\Item;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Shared by both create (store) and edit (update) — Company Dashboard
 * PRD #12/#13. `item_code` is unique per company at the database level
 * (see the items migration) — validated here too so a collision surfaces
 * as a normal form error instead of a raw DB constraint violation.
 */
class ItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('item');

        return $item
            ? $this->user()->can('update', $item)
            : $this->user()->can('create', Item::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $item = $this->route('item');

        return [
            'item_code' => [
                'required', 'string', 'max:100',
                Rule::unique('items', 'item_code')
                    ->where('company_id', $this->user()->company_id)
                    ->ignore($item?->id),
            ],
            'item_name' => ['required', 'string', 'max:255'],
            'item_type' => ['required', Rule::in([Item::TYPE_GOODS, Item::TYPE_SERVICE])],
            'sale_type' => ['nullable', 'string', 'max:255'],
            'hs_code' => ['nullable', 'string', 'max:50'],
            'rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'uom' => ['nullable', 'string', 'max:100'],
            'purchase_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in([Item::STATUS_ACTIVE, Item::STATUS_INACTIVE])],
        ];
    }

    public function withValidator(ValidatorContract $validator): void
    {
        $validator->after(fn ($validator) => $this->validateHsCodeUomCombination($validator));
    }

    private function validateHsCodeUomCombination(ValidatorContract $validator): void
    {
        $hsCode = trim((string) $this->input('hs_code', ''));
        $uom = trim((string) $this->input('uom', ''));

        if ($hsCode === '' || $uom === '') {
            return;
        }

        try {
            $allowed = app(FbrReferenceService::class)->hsUom($this->user()->company_id, $hsCode);
        } catch (FbrReferenceApiException) {
            return;
        }

        if (! array_is_list($allowed)) {
            return;
        }

        $allowedNames = collect($allowed)
            ->pluck('description')
            ->filter(fn ($description) => filled($description))
            ->map(fn ($description) => Str::lower(trim((string) $description)))
            ->all();

        if ($allowedNames !== [] && ! in_array(Str::lower($uom), $allowedNames, true)) {
            $validator->errors()->add('uom', 'The selected UoM is not valid for the selected HS Code.');
        }
    }
}
