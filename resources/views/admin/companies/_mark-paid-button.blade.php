{{--
    Shared by show.blade.php and transactions.blade.php. Expects
    $transaction and $company in scope. Only rendered for pending rows.
--}}
@if ($transaction->status === \App\Models\Transaction::STATUS_PENDING)
    <button
        type="button"
        class="btn btn-sm btn-success"
        data-bs-toggle="modal"
        data-bs-target="#mark-paid-{{ $transaction->id }}"
    >
        Mark as Paid
    </button>

    <x-confirm-modal
        :id="'mark-paid-'.$transaction->id"
        title="Mark Transaction as Paid?"
        :action="route('admin.transactions.mark-paid', $transaction)"
        confirm-label="Confirm Payment"
        confirm-class="btn-success"
    >
        <dl class="row mb-3">
            <dt class="col-4">Company</dt>
            <dd class="col-8">{{ $company->name }}</dd>
            <dt class="col-4">Amount</dt>
            <dd class="col-8">PKR {{ number_format($transaction->amount, 2) }}</dd>
            <dt class="col-4">Subscription</dt>
            <dd class="col-8">{{ ucfirst($transaction->subscription_type) }}</dd>
        </dl>
        <p>Are you sure you want to mark this transaction as paid?</p>
        <label class="form-label small mb-1">Notes (optional)</label>
        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Paid via bank transfer"></textarea>
    </x-confirm-modal>
@endif
