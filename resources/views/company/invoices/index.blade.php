@extends('layouts.company')

@section('title', 'Invoices')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between pb-3 mb-4 border-bottom">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Invoice Management</h1>
            <p class="text-muted small mb-0">View submitted sales tax invoices, FBR reference numbers, and status reports.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
            <a href="{{ route('company.invoices.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Create Invoice
            </a>
            <a href="{{ route('company.invoices.bulk-upload.create') }}" class="btn btn-outline-secondary">
                <i class="bi bi-upload me-1"></i> Bulk Upload
            </a>
            <a href="{{ route('company.invoices.drafts') }}" class="btn btn-outline-secondary">
                <i class="bi bi-file-earmark-text me-1"></i> Drafts &amp; Saved
            </a>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('company.invoices.index') }}" class="row g-2">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="Search by invoice reference or buyer business name...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">All FBR Statuses</option>
                        <option value="submitted" @selected(request('status') === 'submitted')>Submitted</option>
                        <option value="successful" @selected(request('status') === 'successful')>Successful</option>
                        <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Invoice Reference</th>
                        <th>Buyer Business</th>
                        <th>Type</th>
                        <th>Invoice Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td class="fw-bold"><code class="text-primary fs-6">{{ $invoice->invoice_reference_no ?? $invoice->fbr_invoice_number ?? '—' }}</code></td>
                            <td class="fw-semibold text-dark">{{ $invoice->buyer_business_name ?? $invoice->customer?->business_name ?? '—' }}</td>
                            <td><span class="badge bg-slate-100 text-dark border">{{ ucfirst($invoice->invoice_type) }}</span></td>
                            <td class="text-muted small">{{ $invoice->invoice_date->format('d-M-Y') }}</td>
                            <td class="fw-bold text-dark">Rs {{ number_format($invoice->total_amount, 2) }}</td>
                            <td><x-status-badge :status="$invoice->status" /></td>
                            <td class="text-end">
                                <a href="{{ route('company.invoices.show', $invoice) }}" class="btn btn-sm btn-light border text-dark fw-medium">
                                    <i class="bi bi-eye me-1"></i> View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-receipt display-6 text-slate-300 d-block mb-2"></i>
                                No submitted invoices found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $invoices->links() }}
    </div>
@endsection
