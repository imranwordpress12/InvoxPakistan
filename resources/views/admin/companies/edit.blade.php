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

        <div class="card shadow-sm mb-3">
            <div class="card-header">Subscription Information</div>
            <div class="card-body row g-3">
                @php($latestSub = $company->latestSubscription)
                <div class="col-md-3">
                    <label class="form-label">Subscription Status</label>
                    <select name="subscription_status" class="form-select @error('subscription_status') is-invalid @enderror">
                        <option value="" @selected(! old('subscription_status', $latestSub?->status))>No Subscription</option>
                        <option value="active" @selected(old('subscription_status', $latestSub?->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('subscription_status', $latestSub?->status) === 'inactive')>Inactive</option>
                    </select>
                    <div class="form-text">Active: enabled. Inactive: stop future transactions.</div>
                    @error('subscription_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Subscription Start Date</label>
                    @if ($latestSub && $latestSub->status === 'active')
                        <input type="date" value="{{ old('subscription_starts_at', $latestSub->starts_at?->format('Y-m-d')) }}" class="form-control" readonly disabled>
                        <input type="hidden" name="subscription_starts_at" value="{{ $latestSub->starts_at?->format('Y-m-d') }}">
                        <div class="form-text">Read-only for active subscriptions.</div>
                    @else
                        <input type="date" name="subscription_starts_at" value="{{ old('subscription_starts_at', now()->format('Y-m-d')) }}" class="form-control @error('subscription_starts_at') is-invalid @enderror" min="{{ now()->format('Y-m-d') }}">
                        <div class="form-text">Start Date (today or future).</div>
                        @error('subscription_starts_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @endif
                </div>
                <div class="col-md-3">
                    <label class="form-label">Subscription Type</label>
                    <select name="subscription_type" class="form-select @error('subscription_type') is-invalid @enderror">
                        <option value="monthly" @selected(old('subscription_type', $latestSub?->type) === 'monthly')>Monthly</option>
                        <option value="yearly" @selected(old('subscription_type', $latestSub?->type) === 'yearly')>Yearly</option>
                    </select>
                    @error('subscription_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Amount (PKR)</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="amount"
                        value="{{ old('amount', $latestSub?->amount) }}"
                        class="form-control @error('amount') is-invalid @enderror"
                    >
                    @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header">FBR Credentials</div>
            <div class="card-body row g-3">
                <div class="col-md-12">
                    <label class="form-label">FBR Status / Mode</label>
                    <select name="fbr_status" class="form-select @error('fbr_status') is-invalid @enderror" required>
                        <option value="active" @selected(old('fbr_status', $company->fbr_status) === 'active')>Active (Use Production Token)</option>
                        <option value="inactive" @selected(old('fbr_status', $company->fbr_status) === 'inactive')>Inactive (Use Sandbox Token)</option>
                    </select>
                    <div class="form-text">Active uses Production token; Inactive uses Sandbox token.</div>
                    @error('fbr_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">FBR Token Production</label>
                    <input
                        type="text"
                        name="fbr_token_production"
                        value=""
                        placeholder="{{ $company->fbr_token_production ? '•••••••• (set)' : 'leave blank to keep unchanged' }}"
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
                        placeholder="{{ $company->fbr_token_sandbox ? '•••••••• (set)' : 'leave blank to keep unchanged' }}"
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
