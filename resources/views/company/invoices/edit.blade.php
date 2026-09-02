@extends('layouts.company')

@section('title', 'Edit Invoice')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Edit Invoice</h1>
        <a href="{{ route('company.invoices.drafts') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Drafts
        </a>
    </div>

    @include('company.invoices._form', ['invoice' => $invoice])
@endsection
