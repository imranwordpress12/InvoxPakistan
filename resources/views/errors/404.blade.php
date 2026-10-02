@extends('layouts.public', ['title' => '404 Page Not Found', 'robots' => 'noindex, follow'])

@section('content')
<div class="container py-5 text-center">
    <div class="py-5" style="max-width: 600px; margin: 0 auto;">
        <div class="display-1 fw-bold text-primary mb-3">404</div>
        <h1 class="h2 fw-bold text-dark mb-3">Page Not Found</h1>
        <p class="lead text-muted mb-4">The page you were looking for could not be found or has been relocated to a new URL.</p>

        <!-- Search Bar -->
        <form action="{{ route('public.search') }}" method="GET" class="mb-4">
            <div class="input-group input-group-lg shadow-sm">
                <input type="search" name="q" class="form-control" placeholder="Search Invox Pakistan site..." required aria-label="Search site query">
                <button class="btn btn-primary px-4 fw-bold" type="submit">Search</button>
            </div>
        </form>

        <div class="d-flex justify-content-center gap-3 mb-5">
            <a href="{{ route('public.home') }}" class="btn btn-primary px-4 fw-bold">
                <i class="bi bi-house-door me-1"></i> Return Home
            </a>
            <a href="{{ route('public.contact') }}" class="btn btn-outline-dark px-4 fw-bold">
                <i class="bi bi-envelope me-1"></i> Contact Support
            </a>
        </div>

        <div class="text-start bg-light p-4 rounded-3 border">
            <h2 class="h6 text-uppercase text-muted fw-bold mb-3">Popular Pages</h2>
            <ul class="list-unstyled mb-0 row g-2 small">
                <li class="col-6"><a href="{{ route('public.features') }}" class="text-decoration-none">&bull; Product Features</a></li>
                <li class="col-6"><a href="{{ route('public.pricing') }}" class="text-decoration-none">&bull; Pricing Plans</a></li>
                <li class="col-6"><a href="{{ route('public.integrations') }}" class="text-decoration-none">&bull; Integrations</a></li>
                <li class="col-6"><a href="{{ route('public.faq') }}" class="text-decoration-none">&bull; FAQ Center</a></li>
                <li class="col-6"><a href="{{ route('blog.index') }}" class="text-decoration-none">&bull; Official Blog</a></li>
                <li class="col-6"><a href="{{ route('public.sitemap') }}" class="text-decoration-none">&bull; HTML Sitemap</a></li>
            </ul>
        </div>
    </div>
</div>
@endsection
