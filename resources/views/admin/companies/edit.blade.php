@extends('layouts.admin')

@section('title', 'Edit Company')

@section('content')
    <h1 class="h4 mb-3">Edit Company &mdash; {{ $company->name }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.companies.update', $company) }}" novalidate>
        @csrf
        @method('PUT')

        <div class="card shadow-sm mb-3">
            <div class="card-header">Company Information</div>
            <div class="card-body row g-3">
                @include('admin.companies._company-information-fields', ['company' => $company])
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header">Login Information</div>
            <div class="card-body row g-3">
                <div class="col-md-12">
                    <label class="form-label">Login Email</label>
                    <input
                        type="email"
                        name="user_email"
                        value="{{ old('user_email', $company->users->first()?->email) }}"
                        class="form-control @error('user_email') is-invalid @enderror"
                        required
                    >
                    @error('user_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                    <div class="form-text">Leave blank to keep the current password.</div>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header">FBR Credentials <span class="text-muted small">(leave blank to keep unchanged)</span></div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">FBR Token Production</label>
                    <input
                        type="text"
                        name="fbr_token_production"
                        value=""
                        placeholder="{{ $company->fbr_token_production ? '•••••••• (set)' : 'not set' }}"
                        class="form-control @error('fbr_token_production') is-invalid @enderror"
                        autocomplete="off"
                    >
                    @error('fbr_token_production') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">FBR Token Sandbox</label>
                    <input
                        type="text"
                        name="fbr_token_sandbox"
                        value=""
                        placeholder="{{ $company->fbr_token_sandbox ? '•••••••• (set)' : 'not set' }}"
                        class="form-control @error('fbr_token_sandbox') is-invalid @enderror"
                        autocomplete="off"
                    >
                    @error('fbr_token_sandbox') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection
