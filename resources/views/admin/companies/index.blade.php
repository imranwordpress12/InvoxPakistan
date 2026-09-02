@extends('layouts.admin')

@section('title', 'Companies')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Companies</h1>
        <div class="text-muted small">Total Companies: {{ $totalCompanies }}</div>
    </div>

    <div class="d-flex gap-2 mb-3">
        <a href="{{ route('admin.companies.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Create Company
        </a>
        <a href="{{ route('admin.companies.pending') }}" class="btn btn-outline-secondary">
            <i class="bi bi-hourglass-split me-1"></i> Pending Companies
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.companies.index') }}" class="row g-2">
                <div class="col-md-4">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control"
                        placeholder="Search by name or email"
                    >
                </div>
                <div class="col-md-3">
                    <select name="subscription_type" class="form-select">
                        <option value="">All subscription types</option>
                        <option value="monthly" @selected(request('subscription_type') === 'monthly')>Monthly</option>
                        <option value="yearly" @selected(request('subscription_type') === 'yearly')>Yearly</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="subscription_status" class="form-select">
                        <option value="">All subscription statuses</option>
                        <option value="active" @selected(request('subscription_status') === 'active')>Active</option>
                        <option value="pending" @selected(request('subscription_status') === 'pending')>Pending</option>
                        <option value="expired" @selected(request('subscription_status') === 'expired')>Expired</option>
                        <option value="cancelled" @selected(request('subscription_status') === 'cancelled')>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary flex-grow-1">Filter</button>
                    @if (request()->anyFilled(['search', 'subscription_type', 'subscription_status']))
                        <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Email</th>
                        <th>Subscription</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Created At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($companies as $company)
                        <tr
                            role="link"
                            style="cursor: pointer;"
                            onclick="window.location='{{ route('admin.companies.show', $company) }}';"
                        >
                            <td>{{ $company->name }}</td>
                            <td>{{ $company->email }}</td>
                            <td>{{ $company->latestSubscription ? ucfirst($company->latestSubscription->type) : '—' }}</td>
                            <td><x-status-badge :status="$company->latestSubscription?->status" /></td>
                            <td>{{ $company->latestSubscription?->starts_at?->format('d-M-Y') ?? '—' }}</td>
                            <td>{{ $company->latestSubscription?->ends_at?->format('d-M-Y') ?? '—' }}</td>
                            <td>{{ $company->created_at->format('d-M-Y') }}</td>
                            <td class="text-end" onclick="event.stopPropagation();">
                                <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#delete-company-{{ $company->id }}"
                                >
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
                                    Are you sure you want to delete <strong>{{ $company->name }}</strong>? Its
                                    subscription and transaction history are kept, but it will disappear from this
                                    listing.
                                </x-confirm-modal>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No companies match these filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $companies->links() }}
    </div>
@endsection
