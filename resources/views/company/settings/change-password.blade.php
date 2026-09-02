@extends('layouts.company')

@section('title', 'Change Password')

@section('content')
    <h1 class="h4 mb-3">Password Management</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('company.settings.password.update') }}" novalidate>
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Current Password <span class="text-danger">*</span></label>
                        <input
                            type="password"
                            name="current_password"
                            class="form-control @error('current_password') is-invalid @enderror"
                            autocomplete="current-password"
                            required
                        >
                        @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">New Password <span class="text-danger">*</span></label>
                        <input
                            type="password"
                            name="new_password"
                            class="form-control @error('new_password') is-invalid @enderror"
                            autocomplete="new-password"
                            required
                        >
                        @error('new_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <input
                            type="password"
                            name="new_password_confirmation"
                            class="form-control"
                            autocomplete="new-password"
                            required
                        >
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
@endsection
