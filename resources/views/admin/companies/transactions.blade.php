@extends('layouts.admin')

@section('title', $company->name.' — Transactions')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Transactions &mdash; {{ $company->name }}</h1>
        <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-outline-secondary btn-sm">
            Back to company
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                {{ $error }}
            @endforeach
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Type</th>
                        <th>Subscription</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Billing Period</th>
                        <th>Paid At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->invoice_number }}</td>
                            <td>{{ ucfirst($transaction->transaction_type) }}</td>
                            <td>{{ ucfirst($transaction->subscription_type) }}</td>
                            <td>PKR {{ number_format($transaction->amount, 2) }}</td>
                            <td><x-status-badge :status="$transaction->status" /></td>
                            <td>{{ $transaction->billing_period_start->format('d-M-Y') }} &ndash; {{ $transaction->billing_period_end->format('d-M-Y') }}</td>
                            <td>{{ $transaction->paid_at?->format('d-M-Y') ?? '—' }}</td>
                            <td>@include('admin.companies._mark-paid-button')</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No transactions yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $transactions->links() }}
    </div>
@endsection
