<?php

namespace App\Http\Requests\Admin;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Deliberately excludes subscription fields (type/dates) — editing those
 * intersects with renewal lifecycle logic that doesn't exist until Phase 5,
 * so Phase 4's Update covers company information, login information, and
 * FBR credentials only (see CHANGELOG_PROJECT.md for the full rationale).
 */
class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('company'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $company = $this->route('company');
        $loginUserId = $company->users()->first()?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('companies', 'email')->ignore($company->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'ntn_cnic' => ['nullable', 'string', 'max:255', Rule::unique('companies', 'ntn_cnic')->ignore($company->id)],
            'business_registration_number' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in([Company::STATUS_ACTIVE, Company::STATUS_INACTIVE])],

            'user_email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($loginUserId)],
            // Optional: leaving it blank keeps the current password.
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],

            // Optional: leaving either blank keeps that credential unchanged.
            'fbr_token_production' => ['nullable', 'string'],
            'fbr_token_sandbox' => ['nullable', 'string'],
        ];
    }
}
