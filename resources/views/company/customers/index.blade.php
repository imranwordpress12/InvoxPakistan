@extends('layouts.company')

@section('title', 'Customers')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Customers</h1>
        <a href="{{ route('company.customers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Create Customer
        </a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('company.customers.index') }}" class="row g-2">
                <div class="col-md-8">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control"
                        placeholder="Search by business name or NTN/CNIC"
                    >
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
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
                            <td>{{ $customer->business_name }}</td>
                            <td>{{ $customer->ntn_cnic }}</td>
                            <td>{{ $customer->province }}</td>
                            <td>{{ ucfirst($customer->buyer_registration_type) }}</td>
                            <td>{{ $customer->contact_person ?? '—' }}</td>
                            <td><x-status-badge :status="$customer->status" /></td>
                            <td class="text-end">
                                <a href="{{ route('company.customers.edit', $customer) }}" class="btn btn-sm btn-outline-secondary">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No customers yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $customers->links() }}
    </div>
@endsection
