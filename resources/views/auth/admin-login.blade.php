@extends('layouts.auth')

@section('title', 'Admin Login')

@section('content')
    <h1 class="h4 mb-1">Admin Login</h1>
    <p class="text-muted small mb-4">Sign in to manage companies, subscriptions and payments.</p>

    @if ($errors->any())
        <div class="alert alert-danger py-2">
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                class="form-control @error('email') is-invalid @enderror"
                autocomplete="username"
                autofocus
                required
            >
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                class="form-control @error('password') is-invalid @enderror"
                autocomplete="current-password"
                required
            >
        </div>

        <button type="submit" class="btn btn-primary w-100">Log in</button>
    </form>

    <p class="text-center text-muted small mt-4 mb-0">
        Not an admin? <a href="{{ route('company.login') }}">Company login</a>
    </p>
@endsection
