@extends('layouts.company')

@section('title', 'Customers Directory')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between pb-3 mb-4 border-bottom">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Customer Directory</h1>
            <p class="text-muted small mb-0">Manage customer accounts, tax registration numbers, and contact information.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('company.customers.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-person-plus-fill me-1"></i> Add New Customer
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('company.customers.index') }}" class="row g-2">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="Search by business name, NTN, or CNIC...">
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Search</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Business Name</th>
                        <th>NTN / CNIC</th>
                        <th>Province</th>
                        <th>Registration Type</th>
                        <th>Contact Person</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td class="fw-bold text-dark">{{ $customer->business_name }}</td>
                            <td class="font-monospace text-slate-700">{{ $customer->ntn_cnic }}</td>
                            <td><span class="badge bg-slate-100 text-dark border">{{ $customer->province }}</span></td>
                            <td>{{ ucfirst($customer->buyer_registration_type) }}</td>
                            <td class="text-muted small">{{ $customer->contact_person ?? '—' }}</td>
                            <td><x-status-badge :status="$customer->status" /></td>
                            <td class="text-end">
                                <a href="{{ route('company.customers.edit', $customer) }}" class="btn btn-sm btn-light border text-dark fw-medium">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-people display-6 text-slate-300 d-block mb-2"></i>
                                No customer records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $customers->links() }}
    </div>
@endsection
