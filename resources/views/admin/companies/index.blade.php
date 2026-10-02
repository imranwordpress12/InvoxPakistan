@extends('layouts.admin')

@section('title', 'Companies Directory')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between pb-3 mb-4 border-bottom">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Company Directory</h1>
            <p class="text-muted small mb-0">Total Companies Registered: <strong>{{ $totalCompanies }}</strong></p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('admin.companies.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Register Company
            </a>
            <a href="{{ route('admin.companies.pending') }}" class="btn btn-outline-secondary">
                <i class="bi bi-hourglass-split me-1"></i> Pending Verifications
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.companies.index') }}" class="row g-2">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="Search by name or email...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="subscription_type" class="form-select">
                        <option value="">All Subscription Types</option>
                        <option value="monthly" @selected(request('subscription_type') === 'monthly')>Monthly</option>
                        <option value="yearly" @selected(request('subscription_type') === 'yearly')>Yearly</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="subscription_status" class="form-select">
                        <option value="">All Subscription Statuses</option>
                        <option value="active" @selected(request('subscription_status') === 'active')>Active</option>
                        <option value="pending" @selected(request('subscription_status') === 'pending')>Pending</option>
                        <option value="expired" @selected(request('subscription_status') === 'expired')>Expired</option>
                        <option value="cancelled" @selected(request('subscription_status') === 'cancelled')>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1 fw-bold">Filter</button>
                    @if (request()->anyFilled(['search', 'subscription_type', 'subscription_status']))
                        <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary" title="Clear Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>Email Address</th>
                        <th>Subscription</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($companies as $company)
                        <tr role="link" style="cursor: pointer;" onclick="window.location='{{ route('admin.companies.show', $company) }}';">
                            <td class="fw-bold text-dark">{{ $company->name }}</td>
                            <td class="text-muted small">{{ $company->email }}</td>
                            <td><span class="badge bg-slate-100 text-dark border">{{ $company->latestSubscription ? ucfirst($company->latestSubscription->type) : '—' }}</span></td>
                            <td><x-status-badge :status="$company->latestSubscription?->status" /></td>
                            <td class="text-muted small">{{ $company->latestSubscription?->starts_at?->format('d-M-Y') ?? '—' }}</td>
                            <td class="text-muted small">{{ $company->latestSubscription?->ends_at?->format('d-M-Y') ?? '—' }}</td>
                            <td class="text-muted small">{{ $company->created_at->format('d-M-Y') }}</td>
                            <td class="text-end" onclick="event.stopPropagation();">
                                <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-sm btn-light border text-dark fw-medium">View</a>
                                <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-sm btn-light border text-dark fw-medium">Edit</a>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#delete-company-{{ $company->id }}">
                                    Delete
                                </button>

                                <x-confirm-modal
                                    :id="'delete-company-'.$company->id"
                                    title="Delete company?"
                                    :action="route('admin.companies.destroy', $company)"
                                    method="DELETE"
                                    confirm-label="Delete"
                                    confirm-class="btn-danger"
                                >
                                    Are you sure you want to delete <strong>{{ $company->name }}</strong>? Its subscription and transaction history are preserved, but it will disappear from this directory.
                                </x-confirm-modal>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-building display-6 text-slate-300 d-block mb-2"></i>
                                No companies match the current filter selection.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $companies->links() }}
    </div>
@endsection
