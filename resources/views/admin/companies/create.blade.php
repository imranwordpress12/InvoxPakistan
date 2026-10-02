@extends('layouts.admin')

@section('title', 'Create Company')

@section('content')
    <h1 class="h4 mb-3">Create Company</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.companies.store') }}" novalidate>
        @csrf

        <div class="card shadow-sm mb-3">
            <div class="card-header">Company Information</div>
            <div class="card-body row g-3">
                @include('admin.companies._company-information-fields', ['company' => null])
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header">Login Information</div>
            <div class="card-body row g-3">
                <div class="col-md-12">
                    <label class="form-label">Login Email</label>
                    <input type="email" name="user_email" value="{{ old('user_email') }}" class="form-control @error('user_email') is-invalid @enderror" required>
                    <div class="form-text">Used by the company to sign in — can differ from the company email above.</div>
                    @error('user_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" required>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">Create Company</button>
            <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection
