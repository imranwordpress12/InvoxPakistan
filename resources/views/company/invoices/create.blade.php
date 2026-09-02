@extends('layouts.company')

@section('title', 'Create Invoice')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Create Invoice</h1>
        <a href="{{ route('company.invoices.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Invoices
        </a>
    </div>

    @include('company.invoices._form', ['invoice' => null])
@endsection
