@extends('layouts.public')

@section('content')
<div class="container py-4">
    @include('partials.breadcrumbs', ['crumbs' => ['Search' => route('public.search')]])

    <header class="py-3 mb-4" style="max-width: 800px;">
        <h1 class="display-6 fw-bold text-dark mb-3">Search Invox Pakistan</h1>

        <form action="{{ route('public.search') }}" method="GET" class="mb-4">
            <div class="input-group input-group-lg shadow-sm">
                <input type="search" name="q" class="form-control" placeholder="Search features, FBR compliance, pricing..." value="{{ $query }}" required aria-label="Search site query">
                <button class="btn btn-primary px-4 fw-bold" type="submit">Search</button>
            </div>
        </form>
    </header>

    @if(!empty($query))
        <div class="mb-4">
            <p class="text-muted">Showing relevant results for "<strong>{{ $query }}</strong>":</p>
        </div>

        <div class="row g-3 mb-5" style="max-width: 800px;">
            <div class="col-12">
                <div class="card border-0 shadow-sm p-3">
                    <div class="card-body">
                        <h2 class="h5 fw-bold mb-1"><a href="{{ route('public.features') }}" class="text-decoration-none text-dark">Automated Subscription &amp; FBR Invoicing Features</a></h2>
                        <p class="text-muted small mb-0">Learn how Invox Pakistan manages recurring billing, FBR tax compliance, client self-service portals, and multi-currency invoices.</p>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm p-3">
                    <div class="card-body">
                        <h2 class="h5 fw-bold mb-1"><a href="{{ route('public.pricing') }}" class="text-decoration-none text-dark">Transparent Subscription Pricing Plans</a></h2>
                        <p class="text-muted small mb-0">Compare Starter, Growth, and Enterprise plans with transparent monthly and annual billing limits.</p>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm p-3">
                    <div class="card-body">
                        <h2 class="h5 fw-bold mb-1"><a href="{{ route('public.faq') }}" class="text-decoration-none text-dark">Frequently Asked Questions &amp; Answer Engine</a></h2>
                        <p class="text-muted small mb-0">Answers to common questions regarding security, payment gateway integrations, and tax compliance.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
