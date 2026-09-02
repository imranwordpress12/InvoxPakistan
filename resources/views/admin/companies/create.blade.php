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

        <div class="card shadow-sm mb-3">
            <div class="card-header">Subscription Information</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Subscription Type</label>
                    <select name="subscription_type" class="form-select @error('subscription_type') is-invalid @enderror" required>
                        <option value="" disabled @selected(old('subscription_type') === null)>Choose...</option>
                        <option value="monthly" @selected(old('subscription_type') === 'monthly')>Monthly</option>
                        <option value="yearly" @selected(old('subscription_type') === 'yearly')>Yearly</option>
                    </select>
                    @error('subscription_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Start Date</label>
                    <input
                        type="date"
                        name="subscription_starts_at"
                        value="{{ old('subscription_starts_at', now()->toDateString()) }}"
                        class="form-control @error('subscription_starts_at') is-invalid @enderror"
                    >
                    @error('subscription_starts_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Amount (PKR)</label>
                    <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount') }}" class="form-control @error('amount') is-invalid @enderror" required>
                    @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <div class="alert alert-light border small mb-0">
                        The company is created with this subscription already <strong>active</strong> and its first
                        invoice marked <strong>paid</strong> (PRD #20).
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header">FBR Credentials <span class="text-muted small">(optional — can be added later)</span></div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">FBR Token Production</label>
                    <input type="text" name="fbr_token_production" value="{{ old('fbr_token_production') }}" class="form-control @error('fbr_token_production') is-invalid @enderror" autocomplete="off">
                    @error('fbr_token_production') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">FBR Token Sandbox</label>
                    <input type="text" name="fbr_token_sandbox" value="{{ old('fbr_token_sandbox') }}" class="form-control @error('fbr_token_sandbox') is-invalid @enderror" autocomplete="off">
                    @error('fbr_token_sandbox') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Create Company</button>
            <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection
