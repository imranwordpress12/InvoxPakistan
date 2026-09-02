@extends('layouts.company')

@section('title', 'Drafts')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Drafts</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('company.invoices.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Create Invoice
            </a>
            <a href="{{ route('company.invoices.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-clipboard-check me-1"></i> Invoice Listing
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Invoice Reference</th>
                        <th>Buyer</th>
                        <th>Invoice Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_reference_no ?? '—' }}</td>
                            <td>{{ $invoice->buyer_business_name ?? '—' }}</td>
                            <td>{{ $invoice->invoice_date->format('d-M-Y') }}</td>
                            <td>Rs {{ number_format($invoice->total_amount, 2) }}</td>
                            <td><x-status-badge :status="$invoice->status" /></td>
                            <td>{{ $invoice->created_at->format('d-M-Y H:i') }}</td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('company.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-secondary">
                                        View
                                    </a>

                                    <a href="{{ route('company.invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-secondary">
                                        Edit
                                    </a>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#submit-invoice-{{ $invoice->id }}"
                                    >
                                        Submit
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#delete-invoice-{{ $invoice->id }}"
                                    >
                                        Delete
                                    </button>
                                </div>

                                <x-confirm-modal
                                    :id="'submit-invoice-'.$invoice->id"
                                    title="Submit Invoice"
                                    :action="route('company.invoices.submit', $invoice)"
                                    method="POST"
                                    confirm-label="Submit"
                                    confirm-class="btn-primary"
                                >
                                    Submit invoice
                                    "{{ $invoice->invoice_reference_no ?? '#'.$invoice->id }}"
                                    to FBR now? {{ $invoice->isFailed() ? 'This invoice previously failed submission and will be retried.' : '' }}
                                </x-confirm-modal>

                                <x-confirm-modal
                                    :id="'delete-invoice-'.$invoice->id"
                                    title="Delete Draft Invoice"
                                    :action="route('company.invoices.destroy', $invoice)"
                                    method="DELETE"
                                    confirm-label="Delete"
                                    confirm-class="btn-danger"
                                >
                                    Delete draft invoice
                                    "{{ $invoice->invoice_reference_no ?? '#'.$invoice->id }}"?
                                    This cannot be undone.
                                </x-confirm-modal>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No draft invoices.</td>
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
