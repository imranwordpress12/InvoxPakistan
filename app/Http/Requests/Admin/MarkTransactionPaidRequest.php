<?php

namespace App\Http\Requests\Admin;

use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'subscription_type' => ['nullable', Rule::in([Subscription::TYPE_MONTHLY, Subscription::TYPE_YEARLY])],
            'starts_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_screenshot' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    /**
     * Fails safely on a double submission or ineligible transaction —
     * Pay Now is available for Pending or Overdue transactions only.
     */
    public function withValidator(ValidatorContract $validator): void
    {
        $validator->after(function ($validator) {
            $status = $this->route('transaction')->status;
            if (! in_array($status, [Transaction::STATUS_PENDING, Transaction::STATUS_OVERDUE])) {
                $validator->errors()->add('status', 'Pay Now is available for Pending or Overdue transactions only.');
            }
        });
    }
}
