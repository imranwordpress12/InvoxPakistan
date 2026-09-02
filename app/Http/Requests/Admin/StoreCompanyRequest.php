<?php

namespace App\Http\Requests\Admin;

use App\Models\Company;
use App\Models\Subscription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Company::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Company information (PRD #18) — only what the app actually needs is required.
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:companies,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'ntn_cnic' => ['nullable', 'string', 'max:255', 'unique:companies,ntn_cnic'],
            'business_registration_number' => ['nullable', 'string', 'max:255'],

            // Login information (PRD #19) — distinct from the company's own contact email above.
            'user_email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // Subscription information (PRD #20).
            'subscription_type' => ['required', Rule::in([Subscription::TYPE_MONTHLY, Subscription::TYPE_YEARLY])],
            'subscription_starts_at' => ['nullable', 'date'],
            // Upper bound matches the `decimal(12,2)` column on
            // subscriptions/transactions — without it, a value too large to
            // store would surface as an unhandled DB error instead of a
            // normal validation message.
            'amount' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],

            // FBR credentials (PRD #21) — optional at creation time; can be
            // added later via the edit form once the company has them.
            // Separate Production/Sandbox tokens, matching FBR IRIS's own
            // two environments.
            'fbr_token_production' => ['nullable', 'string'],
            'fbr_token_sandbox' => ['nullable', 'string'],
        ];
    }
}
