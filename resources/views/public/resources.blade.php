@extends('layouts.public')

@section('content')
<div class="container py-4">
    @include('partials.breadcrumbs', ['crumbs' => ['Resources' => route('public.resources')]])

    <header class="text-center py-4 mb-4" style="max-width: 800px; margin: 0 auto;">
        <h1 class="display-5 fw-bold text-dark mb-3">Resources, Guides &amp; ROI Calculators</h1>
        <p class="lead text-muted">Free educational whitepapers, FBR tax compliance roadmaps, and recurring billing ROI tools for Pakistani companies.</p>
    </header>

    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm p-4">
                <div class="card-body">
                    <span class="badge bg-primary-subtle text-primary mb-2">Downloadable PDF Guide</span>
                    <h2 class="h4 fw-bold">2026 FBR Digital Tax Integration Handbook</h2>
                    <p class="text-muted small">Step-by-step roadmap for software engineers and finance managers implementing FBR sales tax API integration in Pakistan.</p>
                    <a href="{{ route('public.contact') }}" class="btn btn-outline-primary btn-sm fw-semibold">Download Free Guide</a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm p-4">
                <div class="card-body">
                    <span class="badge bg-success-subtle text-success mb-2">Interactive Tool</span>
                    <h2 class="h4 fw-bold">Subscription Billing ROI &amp; Churn Calculator</h2>
                    <p class="text-muted small">Estimate how much revenue your company saves by eliminating manual payment follow-ups and automated dunning management.</p>
                    <a href="{{ route('public.contact') }}" class="btn btn-outline-success btn-sm fw-semibold">Launch Calculator</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
