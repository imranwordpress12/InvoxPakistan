@extends('layouts.company')

@section('title', 'Create Item')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Create Item</h1>
        <a href="{{ route('company.items.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Items
        </a>
    </div>

    @include('company.items._form', ['item' => null])
@endsection
