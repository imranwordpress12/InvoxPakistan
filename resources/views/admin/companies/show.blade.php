@extends('layouts.admin')

@section('title', $company->name)

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3">
        <h1 class="h4 mb-0">{{ $company->name }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#delete-company">
                Delete
            </button>
        </div>
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

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header">Company Details</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr><th style="width: 160px;">Status</th><td><x-status-badge :status="$company->status" /></td></tr>
                            <tr><th>Business Name</th><td>{{ $company->business_name ?? '—' }}</td></tr>
                            <tr><th>Email</th><td>{{ $company->email }}</td></tr>
                            <tr><th>Login Email</th><td>{{ $company->users->first()?->email ?? '—' }}</td></tr>
                            <tr><th>Phone</th><td>{{ $company->phone ?? '—' }}</td></tr>
                            <tr><th>Address</th><td>{{ $company->address ?? '—' }}</td></tr>
                            <tr><th>City / Province</th><td>{{ implode(', ', array_filter([$company->city, $company->province])) ?: '—' }}</td></tr>
                            <tr><th>Country</th><td>{{ $company->country ?? '—' }}</td></tr>
                            <tr><th>NTN / CNIC</th><td>{{ $company->ntn_cnic ?? '—' }}</td></tr>
                            <tr><th>FBR Production Token</th><td>{{ $company->fbr_token_production ? 'On file' : 'Not set' }}</td></tr>
                            <tr><th>FBR Sandbox Token</th><td>{{ $company->fbr_token_sandbox ? 'On file' : 'Not set' }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header">Current Subscription</div>
                <div class="card-body">
                    @if ($company->latestSubscription)
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr><th style="width: 160px;">Type</th><td>{{ ucfirst($company->latestSubscription->type) }}</td></tr>
                                <tr><th>Status</th><td><x-status-badge :status="$company->latestSubscription->status" /></td></tr>
                                <tr><th>Start Date</th><td>{{ $company->latestSubscription->starts_at->format('d-M-Y') }}</td></tr>
                                <tr><th>End Date</th><td>{{ $company->latestSubscription->ends_at->format('d-M-Y') }}</td></tr>
                                <tr><th>Amount</th><td>PKR {{ number_format($company->latestSubscription->amount, 2) }}</td></tr>
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted mb-0">No subscription yet.</p>
                    @endif
                </div>
            </div>
        </div>

        @if ($company->subscriptions->count() > 1)
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">Subscription History</div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr><th>Type</th><th>Status</th><th>Start</th><th>End</th><th>Amount</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($company->subscriptions as $subscription)
                                    <tr>
                                        <td>{{ ucfirst($subscription->type) }}</td>
                                        <td><x-status-badge :status="$subscription->status" /></td>
                                        <td>{{ $subscription->starts_at->format('d-M-Y') }}</td>
                                        <td>{{ $subscription->ends_at->format('d-M-Y') }}</td>
                                        <td>PKR {{ number_format($subscription->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Transaction History</span>
                    <a href="{{ route('admin.companies.transactions', $company) }}" class="small">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr><th>Invoice</th><th>Type</th><th>Amount</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($company->transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->invoice_number }}</td>
                                    <td>{{ ucfirst($transaction->transaction_type) }}</td>
                                    <td>PKR {{ number_format($transaction->amount, 2) }}</td>
                                    <td><x-status-badge :status="$transaction->status" /></td>
                                    <td>{{ ($transaction->paid_at ?? $transaction->created_at)->format('d-M-Y') }}</td>
                                    <td>@include('admin.companies._mark-paid-button')</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">No transactions yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-confirm-modal
        id="delete-company"
        title="Delete company?"
        :action="route('admin.companies.destroy', $company)"
        method="DELETE"
        confirm-label="Delete"
        confirm-class="btn-danger"
    >
        Are you sure you want to delete <strong>{{ $company->name }}</strong>? Its subscription and transaction
        history are kept, but it will disappear from the companies listing.
    </x-confirm-modal>
@endsection
