@extends('layouts.public')

@section('content')
<div class="container py-4">
    @include('partials.breadcrumbs', ['crumbs' => ['HTML Sitemap' => route('public.sitemap')]])

    <header class="py-3 mb-4">
        <h1 class="display-6 fw-bold text-dark mb-2">HTML Sitemap</h1>
        <p class="text-muted">Direct crawlable links to all indexable pages, product modules, documentation, and blog articles on Invox Pakistan.</p>
    </header>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm p-3">
                <div class="card-body">
                    <h2 class="h5 fw-bold text-primary mb-3">Core Product Pages</h2>
                    <ul class="list-unstyled d-flex flex-column gap-2 small">
                        <li><a href="{{ route('public.home') }}" class="text-decoration-none">Home Page</a></li>
                        <li><a href="{{ route('public.features') }}" class="text-decoration-none">Features Overview</a></li>
                        <li><a href="{{ route('public.pricing') }}" class="text-decoration-none">Pricing &amp; Plans</a></li>
                        <li><a href="{{ route('public.solutions') }}" class="text-decoration-none">Solutions Overview</a></li>
                        <li><a href="{{ route('public.industries') }}" class="text-decoration-none">Industries &amp; Verticals</a></li>
                        <li><a href="{{ route('public.integrations') }}" class="text-decoration-none">Integrations Engine</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm p-3">
                <div class="card-body">
                    <h2 class="h5 fw-bold text-primary mb-3">Resources &amp; Support</h2>
                    <ul class="list-unstyled d-flex flex-column gap-2 small">
                        <li><a href="{{ route('public.resources') }}" class="text-decoration-none">Resources Hub</a></li>
                        <li><a href="{{ route('public.documentation') }}" class="text-decoration-none">API Documentation</a></li>
                        <li><a href="{{ route('public.faq') }}" class="text-decoration-none">FAQ Center</a></li>
                        <li><a href="{{ route('public.about') }}" class="text-decoration-none">About Company</a></li>
                        <li><a href="{{ route('public.contact') }}" class="text-decoration-none">Contact Sales</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm p-3">
                <div class="card-body">
                    <h2 class="h5 fw-bold text-primary mb-3">Blog &amp; Articles</h2>
                    <ul class="list-unstyled d-flex flex-column gap-2 small">
                        <li><a href="{{ route('blog.index') }}" class="text-decoration-none">Blog Home</a></li>
                        <li><a href="{{ route('blog.show', 'fbr-digital-invoicing-compliance-guide-2026') }}" class="text-decoration-none">FBR Digital Invoicing Guide 2026</a></li>
                        <li><a href="{{ route('blog.show', 'saas-subscription-billing-automation-best-practices') }}" class="text-decoration-none">SaaS Subscription Billing Automation</a></li>
                        <li><a href="{{ route('blog.show', 'jazzcash-easypaisa-bank-reconciliation-guide') }}" class="text-decoration-none">JazzCash &amp; EasyPaisa Reconciliation</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
