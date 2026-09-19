@extends('layouts.admin')

@section('title', 'Transaction '.$transaction->invoice_number)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('admin.companies.transactions', $transaction->company) }}" class="small">Back to transactions</a>
            <h1 class="h4 mb-0 mt-1">Transaction {{ $transaction->invoice_number }}</h1>
        </div>
        <x-status-badge :status="$transaction->status" />
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header">Payment Details</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr><th>Company</th><td>{{ $transaction->company->name }}</td></tr>
                            <tr><th>Email</th><td>{{ $transaction->company->email }}</td></tr>
                            <tr><th>Amount</th><td>PKR {{ number_format($transaction->amount, 2) }}</td></tr>
                            <tr><th>Type</th><td>{{ ucfirst($transaction->transaction_type) }}</td></tr>
                            <tr><th>Subscription</th><td>{{ ucfirst($transaction->subscription_type) }}</td></tr>
                            <tr><th>Billing Period</th><td>{{ $transaction->billing_period_start->format('d-M-Y') }} &ndash; {{ $transaction->billing_period_end->format('d-M-Y') }}</td></tr>
                            <tr><th>Paid At</th><td>{{ $transaction->paid_at?->format('d-M-Y H:i') ?? '—' }}</td></tr>
                            <tr><th>Notes</th><td>{{ $transaction->notes ?? '—' }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header">Online Payment Screenshot</div>
                <div class="card-body text-center">
                    @if ($transaction->payment_screenshot)
                        <img
                            src="{{ route('admin.transactions.screenshot', $transaction) }}"
                            alt="Payment screenshot for {{ $transaction->invoice_number }}"
                            class="img-fluid rounded border"
                            style="max-height: 600px;"
                        >
                    @else
                        <p class="text-muted mb-0 py-5">No payment screenshot was uploaded.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection