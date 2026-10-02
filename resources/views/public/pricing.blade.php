@extends('layouts.public')

@section('content')
<div class="container py-4">
    @include('partials.breadcrumbs', ['crumbs' => ['Pricing' => route('public.pricing')]])

    <header class="text-center py-4 mb-4" style="max-width: 800px; margin: 0 auto;">
        <h1 class="display-5 fw-bold text-dark mb-3">Simple, Transparent Pricing for Every Business</h1>
        <p class="lead text-muted">No hidden setup fees. Upgrade, downgrade, or cancel anytime. Includes 14-day full trial.</p>
    </header>

    <div class="row g-4 justify-content-center mb-5">
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold">Starter Plan</h2>
                    <p class="text-muted small mb-3">For small startups and local companies</p>
                    <div class="mb-4">
                        <span class="fs-2 fw-bold text-dark">PKR 4,999</span><span class="text-muted">/month</span>
                    </div>
                    <ul class="list-unstyled small mb-4 d-flex flex-column gap-2">
                        <li><i class="bi bi-check text-success me-2"></i>Up to 100 Active Invoices/mo</li>
                        <li><i class="bi bi-check text-success me-2"></i>Automated Payment Reminders</li>
                        <li><i class="bi bi-check text-success me-2"></i>FBR Tax Rate Calculation</li>
                        <li><i class="bi bi-check text-success me-2"></i>Standard Email Support</li>
                    </ul>
                    <a href="{{ route('public.contact') }}" class="btn btn-outline-primary w-100 fw-bold">Select Starter</a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-2 border-primary shadow rounded-3 position-relative">
                <div class="position-absolute top-0 start-50 translate-middle">
                    <span class="badge bg-primary px-3 py-1 rounded-pill">Recommended</span>
                </div>
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold">Growth Plan</h2>
                    <p class="text-muted small mb-3">For expanding SMEs &amp; agencies</p>
                    <div class="mb-4">
                        <span class="fs-2 fw-bold text-dark">PKR 12,999</span><span class="text-muted">/month</span>
                    </div>
                    <ul class="list-unstyled small mb-4 d-flex flex-column gap-2">
                        <li><i class="bi bi-check text-success me-2"></i>Up to 1,000 Active Invoices/mo</li>
                        <li><i class="bi bi-check text-success me-2"></i>FBR Digital Real-Time API Sync</li>
                        <li><i class="bi bi-check text-success me-2"></i>JazzCash &amp; EasyPaisa Matching</li>
                        <li><i class="bi bi-check text-success me-2"></i>Multi-User Admin Team Roles</li>
                    </ul>
                    <a href="{{ route('public.contact') }}" class="btn btn-primary w-100 fw-bold">Start Growth Trial</a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold">Enterprise Plan</h2>
                    <p class="text-muted small mb-3">For large corporations &amp; conglomerates</p>
                    <div class="mb-4">
                        <span class="fs-2 fw-bold text-dark">PKR 24,999</span><span class="text-muted">/month</span>
                    </div>
                    <ul class="list-unstyled small mb-4 d-flex flex-column gap-2">
                        <li><i class="bi bi-check text-success me-2"></i>Unlimited Invoices &amp; Customers</li>
                        <li><i class="bi bi-check text-success me-2"></i>Dedicated Tax Specialist Onboarding</li>
                        <li><i class="bi bi-check text-success me-2"></i>Custom ERP Webhooks &amp; Integration</li>
                        <li><i class="bi bi-check text-success me-2"></i>24/7 Dedicated Support &amp; SLA</li>
                    </ul>
                    <a href="{{ route('public.contact') }}" class="btn btn-outline-dark w-100 fw-bold">Contact Sales</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing FAQs -->
    <div class="py-4 border-top" style="max-width: 800px; margin: 0 auto;">
        <h2 class="h3 fw-bold text-center mb-4">Pricing Questions</h2>
        <div class="accordion accordion-flush" id="pricingFaq">
            @foreach($faqs as $index => $faq)
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#pricingFaq{{ $index }}">
                            {{ $faq['question'] }}
                        </button>
                    </h3>
                    <div id="pricingFaq{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#pricingFaq">
                        <div class="accordion-body text-muted">
                            {{ $faq['answer'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
