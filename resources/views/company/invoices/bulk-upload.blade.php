@extends('layouts.company')

@section('title', 'Bulk Uploads')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Bulk Uploads</h1>
        <a href="{{ route('company.invoices.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Invoices
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <h2 class="h6">How it works</h2>
            <ul class="small text-muted">
                <li>Each row in the file is one invoice item line. Multiple rows sharing the same <strong>Invoice Reference No</strong> are combined into a single invoice with multiple items.</li>
                <li>Every row for the same invoice must repeat identical buyer and invoice-level details (NTN/CNIC, business name, address, registration type, province, STRN, invoice date, invoice type).</li>
                <li>Uploaded invoices are always created as <strong>drafts</strong> — nothing is submitted to FBR automatically. Review and submit them from the Drafts page afterward.</li>
                <li>An invoice reference that already exists for your company is skipped, not overwritten.</li>
                <li>Up to {{ number_format($maxRows) }} data rows per file — split a larger export into multiple uploads.</li>
            </ul>
            <a href="{{ route('company.invoices.bulk-upload.template') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-download me-1"></i> Download Sample Template
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('company.invoices.bulk-upload.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">CSV File <span class="text-danger">*</span></label>
                    <input
                        type="file"
                        name="file"
                        accept=".csv,text/csv,text/plain"
                        class="form-control @error('file') is-invalid @enderror"
                        required
                    >
                    <div class="form-text">CSV only, up to 5 MB.</div>
                    @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-upload me-1"></i> Upload
                </button>
            </form>
        </div>
    </div>
@endsection
