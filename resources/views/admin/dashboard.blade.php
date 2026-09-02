@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1 class="h4 mb-3">Admin Dashboard</h1>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Total Companies</div>
                    <div class="h3 mb-0">{{ $totalCompanies }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100 border-success">
                <div class="card-body">
                    <div class="text-muted small">Paid Companies</div>
                    <div class="h3 mb-0 text-success">{{ $paidCompanies }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100 border-warning">
                <div class="card-body">
                    <div class="text-muted small">Pending Companies</div>
                    <div class="h3 mb-0 text-warning">{{ $pendingCompanies }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Companies Overview</span>
            <a href="{{ route('admin.companies.index') }}" class="small">View all</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Subscription</th>
                        <th>Status</th>
                        <th>Expiry</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($companiesOverview as $company)
                        <tr>
                            <td>{{ $company->name }}</td>
                            <td>{{ $company->latestSubscription ? ucfirst($company->latestSubscription->type) : '—' }}</td>
                            <td><x-status-badge :status="$company->latestSubscription?->status" /></td>
                            <td>{{ $company->transactions->last()?->due_at?->format('d-M-Y') ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-sm btn-outline-secondary">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No companies yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">Recent Payments</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Invoice Number</th>
                        <th>Subscription Type</th>
                        <th>Amount</th>
                        <th>Payment Date</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentPayments as $payment)
                        <tr>
                            <td>{{ $payment->company->name }}</td>
                            <td>{{ $payment->invoice_number }}</td>
                            <td>{{ ucfirst($payment->subscription_type) }}</td>
                            <td>PKR {{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->paid_at->format('d-M-Y') }}</td>
                            <td><x-status-badge :status="$payment->status" /></td>
                            <td class="text-end">
                                <a href="{{ route('admin.companies.transactions', $payment->company) }}" class="btn btn-sm btn-outline-secondary">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No payments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
