{{--
    Shared by show.blade.php and transactions.blade.php. Expects
    $transaction and $company in scope. Rendered for pending or overdue rows.
--}}
@if (in_array($transaction->status, [\App\Models\Transaction::STATUS_PENDING, \App\Models\Transaction::STATUS_OVERDUE]))
    <button
        type="button"
        class="btn btn-sm btn-success"
        data-bs-toggle="modal"
        data-bs-target="#mark-paid-{{ $transaction->id }}"
    >
        Pay Now
    </button>

    <x-confirm-modal
        :id="'mark-paid-'.$transaction->id"
        title="Pay Now — Invoice {{ $transaction->invoice_number }}"
        :action="route('admin.transactions.mark-paid', $transaction)"
        confirm-label="Confirm Payment"
        confirm-class="btn-success"
        enctype="multipart/form-data"
    >
        <dl class="row mb-3">
            <dt class="col-4">Company</dt>
            <dd class="col-8">{{ $company->name }}</dd>
            <dt class="col-4">Current Status</dt>
            <dd class="col-8"><x-status-badge :status="$transaction->status" /></dd>
        </dl>
        
        <div class="mb-3">
            <label class="form-label small mb-1">Payment Amount (PKR)</label>
            <input
                type="number"
                step="0.01"
                min="0"
                name="amount"
                value="{{ old('amount', $transaction->amount) }}"
                class="form-control"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label small mb-1">Subscription Type</label>
            <select name="subscription_type" class="form-select">
                <option value="monthly" @selected(old('subscription_type', $transaction->subscription_type) === 'monthly')>Monthly</option>
                <option value="yearly" @selected(old('subscription_type', $transaction->subscription_type) === 'yearly')>Yearly</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label small mb-1">Billing Period Start Date</label>
            <input
                type="date"
                name="starts_at"
                value="{{ old('starts_at', $transaction->billing_period_start?->format('Y-m-d')) }}"
                class="form-control"
            >
        </div>

        <div class="mb-3">
            <label class="form-label small mb-1">Notes (optional)</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Paid via bank transfer"></textarea>
        </div>

        <div class="mb-3">
            <label for="payment-screenshot-{{ $transaction->id }}" class="form-label small mb-1">Payment Screenshot (optional)</label>
            <input
                id="payment-screenshot-{{ $transaction->id }}"
                type="file"
                name="payment_screenshot"
                class="form-control"
                accept="image/jpeg,image/png,image/webp"
            >
            <div class="form-text">JPG, PNG, or WebP up to 5 MB.</div>
        </div>
    </x-confirm-modal>
@endif
