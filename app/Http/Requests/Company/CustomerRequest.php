<?php

namespace App\Http\Requests\Company;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Shared by both create (store) and edit (update) — Company Dashboard
 * PRD #11. `status` lets a company retire a customer (e.g. no longer
 * active) without losing its history — every past invoice still
 * references it via `customer_id`, and `InvoiceController::formData()`
 * already only offers `active` customers on the invoice form.
 */
class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $customer = $this->route('customer');

        return $customer
            ? $this->user()->can('update', $customer)
            : $this->user()->can('create', Customer::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'ntn_cnic' => ['required', 'string', 'max:20'],
            'province' => ['required', 'string', 'max:100'],
            'buyer_registration_type' => ['required', Rule::in([
                Customer::REGISTRATION_TYPE_REGISTERED,
                Customer::REGISTRATION_TYPE_UNREGISTERED,
            ])],
            'strn' => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string'],
            'status' => ['required', Rule::in([
                Customer::STATUS_ACTIVE,
                Customer::STATUS_INACTIVE,
            ])],
        ];
    }
}
