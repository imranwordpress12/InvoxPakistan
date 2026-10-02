@extends('layouts.auth')

@section('title', 'Company Login')

@section('content')
    <div class="text-center mb-4">
        <h1 class="h4 fw-bold text-dark mb-1">Company Portal Sign In</h1>
        <p class="text-muted small mb-0">Sign in to access your invoicing, customer records, and FBR tax status.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-3 py-2 px-3 mb-4">
            <ul class="mb-0 small ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('company.login') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control border-start-0 @error('email') is-invalid @enderror" placeholder="you@company.pk" autocomplete="username" autofocus required>
            </div>
        </div>

        <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-key"></i></span>
                <input id="password" type="password" name="password" class="form-control border-start-0 @error('password') is-invalid @enderror" placeholder="••••••••" autocomplete="current-password" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm mb-3">
            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Portal
        </button>
    </form>
@endsection
