<?php

namespace App\Http\Requests\Admin;

use App\Models\Transaction;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;

class MarkTransactionPaidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('markAsPaid', $this->route('transaction'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_screenshot' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    /**
     * Fails safely (PRD #64 case 6) on a double "Mark as Paid" submission —
     * a normal validation error redirect, not an exception page. The
     * MarkTransactionAsPaid service re-checks this again under a row lock
     * for the genuine-race case this request-level check can't catch.
     */
    public function withValidator(ValidatorContract $validator): void
    {
        $validator->after(function ($validator) {
            if ($this->route('transaction')->status !== Transaction::STATUS_PENDING) {
                $validator->errors()->add('status', 'This transaction has already been processed.');
            }
        });
    }
}
