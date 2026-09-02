@extends('layouts.company')

@section('title', 'Invoices')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Invoices</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('company.invoices.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Create Invoice
            </a>
            <a href="{{ route('company.invoices.bulk-upload.create') }}" class="btn btn-outline-secondary">
                <i class="bi bi-upload me-1"></i> Bulk Uploads
            </a>
            <a href="{{ route('company.invoices.drafts') }}" class="btn btn-outline-secondary">
                <i class="bi bi-file-earmark-text me-1"></i> Drafts
            </a>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('company.invoices.index') }}" class="row g-2">
                <div class="col-md-6">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control"
                        placeholder="Search by invoice reference or buyer"
                    >
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="submitted" @selected(request('status') === 'submitted')>Submitted</option>
                        <option value="successful" @selected(request('status') === 'successful')>Successful</option>
                        <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                    </select>
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
                        <th>Invoice Reference</th>
                        <th>Buyer</th>
                        <th>Type</th>
                        <th>Invoice Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_reference_no ?? $invoice->fbr_invoice_number ?? '—' }}</td>
                            <td>{{ $invoice->buyer_business_name ?? $invoice->customer?->business_name ?? '—' }}</td>
                            <td>{{ ucfirst($invoice->invoice_type) }}</td>
                            <td>{{ $invoice->invoice_date->format('d-M-Y') }}</td>
                            <td>Rs {{ number_format($invoice->total_amount, 2) }}</td>
                            <td><x-status-badge :status="$invoice->status" /></td>
                            <td class="text-end">
                                <a href="{{ route('company.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-secondary">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No submitted invoices yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $invoices->links() }}
    </div>
@endsection
