@extends('layouts.company')

@section('title', 'Invoice Details')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Invoice {{ $invoice->invoice_reference_no ?? '#'.$invoice->id }}</h1>
            <x-status-badge :status="$invoice->status" />
        </div>
        <div class="d-flex gap-2">
            @if ($invoice->isDraft() || $invoice->isFailed())
                <a href="{{ route('company.invoices.edit', $invoice) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <form method="POST" action="{{ route('company.invoices.submit', $invoice) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i> {{ $invoice->isFailed() ? 'Retry Submission' : 'Submit Invoice' }}
                    </button>
                </form>
            @endif
            <a
                href="{{ $invoice->isDraft() || $invoice->isFailed() ? route('company.invoices.drafts') : route('company.invoices.index') }}"
                class="btn btn-outline-secondary"
            >
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header">Invoice Details</div>
                <div class="card-body row g-3">
                    <div class="col-md-4"><strong>Buyer NTN/CNIC</strong><div>{{ $invoice->buyer_ntn_cnic ?? '—' }}</div></div>
                    <div class="col-md-4"><strong>Buyer Business Name</strong><div>{{ $invoice->buyer_business_name ?? '—' }}</div></div>
                    <div class="col-md-4"><strong>Registration Type</strong><div>{{ ucfirst($invoice->buyer_registration_type ?? '—') }}</div></div>
                    <div class="col-md-6"><strong>Buyer Address</strong><div>{{ $invoice->buyer_address ?? '—' }}</div></div>
                    <div class="col-md-3"><strong>Province</strong><div>{{ $invoice->buyer_province ?? '—' }}</div></div>
                    <div class="col-md-3"><strong>STRN</strong><div>{{ $invoice->buyer_strn ?? '—' }}</div></div>
                    <div class="col-md-4"><strong>Invoice Date</strong><div>{{ $invoice->invoice_date->format('d-M-Y') }}</div></div>
                    <div class="col-md-4"><strong>Invoice Type</strong><div>{{ ucfirst($invoice->invoice_type) }}</div></div>
                    <div class="col-md-4"><strong>Customer (master)</strong><div>{{ $invoice->customer?->business_name ?? '—' }}</div></div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header">Item Details</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Sale Type</th>
                                <th>HS Code</th>
                                <th>Product Desc.</th>
                                <th>Rate (%)</th>
                                <th>UoM</th>
                                <th>Qty</th>
                                <th>Value Excl. ST</th>
                                <th>Sales Tax</th>
                                <th>Total Sales Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->items as $item)
                                <tr>
                                    <td>{{ $item->sale_type }}</td>
                                    <td>{{ $item->hs_code }}</td>
                                    <td>{{ $item->product_description }}</td>
                                    <td>{{ number_format($item->rate, 2) }}%</td>
                                    <td>{{ $item->uom }}</td>
                                    <td>{{ (float) $item->quantity }}</td>
                                    <td>{{ number_format($item->value_sales_excl_st, 2) }}</td>
                                    <td>{{ number_format($item->sales_tax, 2) }}</td>
                                    <td>{{ number_format($item->total_sales_value, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="6">Total</td>
                                <td>{{ number_format($invoice->total_excl_st, 2) }}</td>
                                <td>{{ number_format($invoice->total_sales_tax, 2) }}</td>
                                <td>{{ number_format($invoice->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header">FBR Submission</div>
                <div class="card-body">
                    <p class="mb-2"><strong>Status:</strong> <x-status-badge :status="$invoice->status" /></p>
                    <p class="mb-2"><strong>FBR Invoice Number:</strong> {{ $invoice->fbr_invoice_number ?? '—' }}</p>
                    <p class="mb-2"><strong>Submitted At:</strong> {{ $invoice->submitted_at?->format('d-M-Y H:i') ?? '—' }}</p>

                    @if ($invoice->isFailed() && $fbrErrorMessage)
                        <div class="alert alert-danger small mb-0">{{ $fbrErrorMessage }}</div>
                    @endif
                </div>
            </div>

            @if ($invoice->isSuccessful() && $invoice->qr_code)
                <div class="card shadow-sm mb-3">
                    <div class="card-header">
                        FBR Digital Invoicing QR
                        <i
                            class="bi bi-info-circle small"
                            title="Encodes the FBR-issued invoice number. Print alongside the FBR Digital Invoicing logo per API Doc.pdf, Section 6."
                        ></i>
                    </div>
                    <div class="card-body text-center">
                        <div style="max-width: 180px; margin: 0 auto;">{!! $invoice->qr_code !!}</div>
                    </div>
                </div>
            @endif

            @if ($invoice->fbr_response)
                <div class="card shadow-sm">
                    <div class="card-header">Raw FBR Response</div>
                    <div class="card-body">
                        <pre class="small mb-0 text-wrap" style="max-height: 220px; overflow-y: auto;">{{ $invoice->fbr_response }}</pre>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
