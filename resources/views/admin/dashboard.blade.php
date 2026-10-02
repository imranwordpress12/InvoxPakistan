@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between pb-3 mb-4 border-bottom">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">System Overview &amp; Analytics</h1>
            <p class="text-muted small mb-0">Monitor registered companies, active subscriptions, and recent payment transactions.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.companies.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Register New Company
            </a>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <a href="{{ route('admin.companies.index') }}" class="text-decoration-none">
                <div class="card card-hover h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="text-uppercase tracking-wider extra-small fw-bold text-muted">Total Companies</span>
                            <div class="stat-icon bg-primary-subtle text-primary">
                                <i class="bi bi-building"></i>
                            </div>
                        </div>
                        <div class="display-6 fw-bold text-dark mb-1">{{ $totalCompanies }}</div>
                        <div class="small text-muted"><i class="bi bi-arrow-right-short text-primary"></i> View all registered entities</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="{{ route('admin.companies.index', ['subscription_status' => 'currently_active']) }}" class="text-decoration-none">
                <div class="card card-hover h-100 border-0 shadow-sm border-start border-4 border-success">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="text-uppercase tracking-wider extra-small fw-bold text-muted">Active Paid Subscriptions</span>
                            <div class="stat-icon bg-success-subtle text-success">
                                <i class="bi bi-patch-check"></i>
                            </div>
                        </div>
                        <div class="display-6 fw-bold text-success mb-1">{{ $paidCompanies }}</div>
                        <div class="small text-muted"><i class="bi bi-check-circle me-1"></i> Fully active accounts</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="{{ route('admin.companies.pending') }}" class="text-decoration-none">
                <div class="card card-hover h-100 border-0 shadow-sm border-start border-4 border-warning">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="text-uppercase tracking-wider extra-small fw-bold text-muted">Pending Renewal / Action</span>
                            <div class="stat-icon bg-warning-subtle text-warning">
                                <i class="bi bi-clock-history"></i>
                            </div>
                        </div>
                        <div class="display-6 fw-bold text-warning mb-1">{{ $pendingCompanies }}</div>
                        <div class="small text-muted"><i class="bi bi-exclamation-triangle me-1"></i> Requires verification or payment</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Transaction Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <a href="{{ route('admin.dashboard') }}#recent-payments" class="text-decoration-none">
                <div class="card card-hover h-100 border-0 shadow-sm">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase tracking-wider extra-small fw-bold text-muted mb-1">Paid Transactions</div>
                            <div class="fs-2 fw-bold text-success">{{ $paidTransactions }}</div>
                        </div>
                        <div class="stat-icon bg-success-subtle text-success rounded-circle">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6">
            <a href="{{ route('admin.companies.pending') }}" class="text-decoration-none">
                <div class="card card-hover h-100 border-0 shadow-sm">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase tracking-wider extra-small fw-bold text-muted mb-1">Pending / Unpaid Transactions</div>
                            <div class="fs-2 fw-bold text-warning">{{ $pendingTransactions }}</div>
                        </div>
                        <div class="stat-icon bg-warning-subtle text-warning rounded-circle">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Companies Overview Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h2 class="h6 fw-bold mb-0 text-dark"><i class="bi bi-building me-2 text-primary"></i>Companies Directory Overview</h2>
            <a href="{{ route('admin.companies.index') }}" class="btn btn-sm btn-outline-primary fw-semibold">View All Companies</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>Subscription Plan</th>
                        <th>Status</th>
                        <th>Expiry Date</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($companiesOverview as $company)
                        <tr>
                            <td class="fw-bold text-dark">{{ $company->name }}</td>
                            <td><span class="badge bg-slate-100 text-dark border">{{ $company->latestSubscription ? ucfirst($company->latestSubscription->type) : '—' }}</span></td>
                            <td><x-status-badge :status="$company->latestSubscription?->status" /></td>
                            <td class="text-muted small">{{ $company->transactions->last()?->due_at?->format('d-M-Y') ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-sm btn-light border text-dark fw-medium">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No companies registered yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Payments Table -->
    <div class="card border-0 shadow-sm" id="recent-payments">
        <div class="card-header bg-white py-3">
            <h2 class="h6 fw-bold mb-0 text-dark"><i class="bi bi-receipt-cutoff me-2 text-primary"></i>Recent Payment Transactions</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Invoice Number</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Payment Date</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentPayments as $payment)
                        <tr>
                            <td class="fw-bold text-dark">{{ $payment->company->name }}</td>
                            <td><code class="text-primary">{{ $payment->invoice_number }}</code></td>
                            <td>{{ ucfirst($payment->subscription_type) }}</td>
                            <td class="fw-bold">PKR {{ number_format($payment->amount, 2) }}</td>
                            <td class="text-muted small">{{ $payment->paid_at->format('d-M-Y') }}</td>
                            <td><x-status-badge :status="$payment->status" /></td>
                            <td class="text-end">
                                <a href="{{ route('admin.companies.transactions', $payment->company) }}" class="btn btn-sm btn-light border text-dark fw-medium">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No recent payment transactions recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
