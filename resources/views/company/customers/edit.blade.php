@extends('layouts.company')

@section('title', 'Edit Customer')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Edit Customer</h1>
        <a href="{{ route('company.customers.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Customers
        </a>
    </div>

    @include('company.customers._form', ['customer' => $customer])
@endsection
