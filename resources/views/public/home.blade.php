@extends('layouts.public')

@section('content')
<!-- Hero Section -->
<section class="bg-dark text-white py-5 position-relative overflow-hidden">
    <div class="container py-lg-4">
        <div class="row align-items-center gy-4">
            <div class="col-lg-7">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold mb-3">
                    <i class="bi bi-shield-check me-1"></i> FBR Digital Tax Integration Ready
                </span>
                <h1 class="display-4 fw-bold mb-3 text-white">
                    Company Subscription &amp; FBR Invoicing Platform
                </h1>
                <p class="lead text-slate-300 mb-4" style="max-width: 600px;">
                    Automate client subscriptions, recurring billing cycles, sales tax compliance, and payment reconciliation for companies in Pakistan.
                </p>
                <div class="d-flex flex-wrap gap-3 align-items-center">
                    <a href="{{ route('public.contact') }}" class="btn btn-primary btn-lg px-4 fw-bold" onclick="if(window.trackGaEvent) trackGaEvent('click_hero_demo');">
                        Start 14-Day Free Trial
                    </a>
                    <a href="{{ route('public.features') }}" class="btn btn-outline-light btn-lg px-4">
                        Explore Features <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="mt-4 pt-2 d-flex align-items-center gap-4 text-slate-400 small">
                    <div><i class="bi bi-check-circle-fill text-success me-1"></i> No Credit Card Required</div>
                    <div><i class="bi bi-check-circle-fill text-success me-1"></i> Instant Setup</div>
                    <div><i class="bi bi-check-circle-fill text-success me-1"></i> 24/7 Dedicated Support</div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="bg-slate-800 border border-slate-700 rounded-3 p-4 shadow-lg">
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom border-slate-700">
                        <div class="d-flex align-items-center">
                            <img src="{{ asset('images/logo.jpeg') }}" alt="Invox Pakistan Dashboard Preview" width="40" height="40" class="rounded me-2" fetchpriority="high">
                            <div>
                                <div class="fw-bold text-white small">Invox Enterprise Hub</div>
                                <div class="text-success extra-small"><i class="bi bi-circle-fill me-1"></i> FBR Live Gateway Synced</div>
                            </div>
                        </div>
                        <span class="badge bg-success">Active</span>
                    </div>

                    <div class="mb-3">
                        <div class="text-slate-400 small mb-1">Monthly Recurring Revenue (MRR)</div>
                        <div class="fs-3 fw-bold text-white">PKR 12,450,000</div>
                    </div>

                    <div class="progress mb-3" style="height: 6px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <div class="row g-2 text-center small">
                        <div class="col-4">
                            <div class="p-2 bg-slate-900 rounded border border-slate-700">
                                <div class="text-slate-400">Invoices</div>
                                <div class="fw-bold text-white">1,420</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-slate-900 rounded border border-slate-700">
                                <div class="text-slate-400">FBR Validated</div>
                                <div class="fw-bold text-success">100%</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-slate-900 rounded border border-slate-700">
                                <div class="text-slate-400">Paid</div>
                                <div class="fw-bold text-info">98.4%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trust Signals & Client Logos Section -->
<section class="py-4 bg-light border-bottom">
    <div class="container">
        <p class="text-center text-muted small text-uppercase tracking-wider fw-bold mb-3">Trusted by leading enterprises, software houses &amp; distributors across Pakistan</p>
        <div class="row align-items-center justify-content-center g-4 opacity-75">
            <div class="col-6 col-md-2 text-center fw-bold fs-5 text-secondary">TechCorp PK</div>
            <div class="col-6 col-md-2 text-center fw-bold fs-5 text-secondary">PakLogistics</div>
            <div class="col-6 col-md-2 text-center fw-bold fs-5 text-secondary">IndusDistributors</div>
            <div class="col-6 col-md-2 text-center fw-bold fs-5 text-secondary">ApexServices</div>
            <div class="col-6 col-md-2 text-center fw-bold fs-5 text-secondary">KhyberRetail</div>
        </div>
    </div>
</section>

<!-- Product Value Proposition & Core Modules -->
<section class="py-5">
    <div class="container py-lg-4">
        <div class="text-center mb-5" style="max-width: 720px; margin: 0 auto;">
            <h2 class="h1 fw-bold text-dark mb-3">Built specifically for Pakistani subscription &amp; billing workflows</h2>
            <p class="lead text-muted">Invox Pakistan solves recurring payment collection, client tracking, and sales tax compliance in one unified platform.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <article class="card h-100 border-0 shadow-sm rounded-3">
                    <div class="card-body p-4">
                        <div class="bg-primary-subtle text-primary rounded-3 p-3 d-inline-block mb-3">
                            <i class="bi bi-arrow-repeat fs-3"></i>
                        </div>
                        <h3 class="h5 fw-bold card-title">Automated Recurring Billing</h3>
                        <p class="card-text text-muted">Set up weekly, monthly, or annual subscription cycles. Automatically issue invoices, apply discounts, and manage contract renewals.</p>
                        <a href="{{ route('public.features') }}" class="fw-semibold text-primary text-decoration-none">Learn more <i class="bi bi-chevron-right"></i></a>
                    </div>
                </article>
            </div>

            <div class="col-md-6 col-lg-4">
                <article class="card h-100 border-0 shadow-sm rounded-3">
                    <div class="card-body p-4">
                        <div class="bg-success-subtle text-success rounded-3 p-3 d-inline-block mb-3">
                            <i class="bi bi-qr-code-scan fs-3"></i>
                        </div>
                        <h3 class="h5 fw-bold card-title">FBR Digital Tax Compliance</h3>
                        <p class="card-text text-muted">Generate FBR-compliant invoices with QR codes, official sales tax registration numbers (STRN), and real-time FBR API sync.</p>
                        <a href="{{ route('public.features') }}" class="fw-semibold text-primary text-decoration-none">Learn more <i class="bi bi-chevron-right"></i></a>
                    </div>
                </article>
            </div>

            <div class="col-md-6 col-lg-4">
                <article class="card h-100 border-0 shadow-sm rounded-3">
                    <div class="card-body p-4">
                        <div class="bg-info-subtle text-info rounded-3 p-3 d-inline-block mb-3">
                            <i class="bi bi-wallet2 fs-3"></i>
                        </div>
                        <h3 class="h5 fw-bold card-title">JazzCash &amp; EasyPaisa Payments</h3>
                        <p class="card-text text-muted">Allow clients to pay via JazzCash, EasyPaisa, 1LINK 1Bill, or credit cards with instant payment reconciliation.</p>
                        <a href="{{ route('public.integrations') }}" class="fw-semibold text-primary text-decoration-none">Learn more <i class="bi bi-chevron-right"></i></a>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Overview Section -->
<section class="py-5 bg-light border-top border-bottom">
    <div class="container py-lg-4">
        <div class="text-center mb-5">
            <h2 class="h1 fw-bold text-dark mb-3">Transparent subscription packages</h2>
            <p class="lead text-muted">Flexible plans tailored for startups, growing companies, and large enterprises.</p>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-3">
                    <div class="card-body p-4">
                        <h3 class="h4 fw-bold">Starter Plan</h3>
                        <p class="text-muted small mb-3">Ideal for small teams &amp; early startups</p>
                        <div class="mb-4">
                            <span class="fs-2 fw-bold text-dark">PKR 4,999</span><span class="text-muted">/month</span>
                        </div>
                        <ul class="list-unstyled small mb-4 d-flex flex-column gap-2">
                            <li><i class="bi bi-check text-success me-2"></i>Up to 100 Active Invoices/mo</li>
                            <li><i class="bi bi-check text-success me-2"></i>Automated Payment Reminders</li>
                            <li><i class="bi bi-check text-success me-2"></i>FBR Tax Rate Integration</li>
                            <li><i class="bi bi-check text-success me-2"></i>Standard Client Portal</li>
                        </ul>
                        <a href="{{ route('public.contact') }}" class="btn btn-outline-primary w-100 fw-bold">Choose Starter</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-2 border-primary shadow rounded-3 position-relative">
                    <div class="position-absolute top-0 start-50 translate-middle">
                        <span class="badge bg-primary px-3 py-1 rounded-pill">Most Popular</span>
                    </div>
                    <div class="card-body p-4">
                        <h3 class="h4 fw-bold">Growth Plan</h3>
                        <p class="text-muted small mb-3">Perfect for growing SMEs &amp; agencies</p>
                        <div class="mb-4">
                            <span class="fs-2 fw-bold text-dark">PKR 12,999</span><span class="text-muted">/month</span>
                        </div>
                        <ul class="list-unstyled small mb-4 d-flex flex-column gap-2">
                            <li><i class="bi bi-check text-success me-2"></i>Up to 1,000 Active Invoices/mo</li>
                            <li><i class="bi bi-check text-success me-2"></i>FBR Real-Time API Sync &amp; QR Codes</li>
                            <li><i class="bi bi-check text-success me-2"></i>JazzCash &amp; EasyPaisa Matching</li>
                            <li><i class="bi bi-check text-success me-2"></i>Multi-User Admin Access</li>
                        </ul>
                        <a href="{{ route('public.contact') }}" class="btn btn-primary w-100 fw-bold">Start Growth Trial</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-3">
                    <div class="card-body p-4">
                        <h3 class="h4 fw-bold">Enterprise Plan</h3>
                        <p class="text-muted small mb-3">Custom automation for large companies</p>
                        <div class="mb-4">
                            <span class="fs-2 fw-bold text-dark">PKR 24,999</span><span class="text-muted">/month</span>
                        </div>
                        <ul class="list-unstyled small mb-4 d-flex flex-column gap-2">
                            <li><i class="bi bi-check text-success me-2"></i>Unlimited Monthly Invoices</li>
                            <li><i class="bi bi-check text-success me-2"></i>Dedicated FBR Tax Specialist Support</li>
                            <li><i class="bi bi-check text-success me-2"></i>Custom ERP &amp; Banking Webhooks</li>
                            <li><i class="bi bi-check text-success me-2"></i>SLA &amp; Priority 24/7 Phone Line</li>
                        </ul>
                        <a href="{{ route('public.contact') }}" class="btn btn-outline-dark w-100 fw-bold">Contact Sales</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Frequently Asked Questions (FAQ) Section -->
<section class="py-5">
    <div class="container py-lg-4" style="max-width: 800px;">
        <div class="text-center mb-5">
            <h2 class="h1 fw-bold text-dark mb-3">Frequently Asked Questions</h2>
            <p class="lead text-muted">Everything you need to know about Invox Pakistan platform.</p>
        </div>

        <div class="accordion accordion-flush shadow-sm rounded-3 border" id="homeFaqAccordion">
            @foreach($faqs as $index => $faq)
                <div class="accordion-item">
                    <h3 class="accordion-header" id="heading{{ $index }}">
                        <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }} fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $index }}">
                            {{ $faq['question'] }}
                        </button>
                    </h3>
                    <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $index }}" data-bs-parent="#homeFaqAccordion">
                        <div class="accordion-body text-muted">
                            {{ $faq['answer'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('public.faq') }}" class="fw-semibold text-primary text-decoration-none">View all FAQs <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="bg-light text-white py-5">
    <div class="container text-center py-3">
        <h2 class="h1 fw-bold mb-3">Ready to streamline your company billing &amp; tax compliance?</h2>
        <p class="lead text-white-50 mb-4" style="max-width: 600px; margin: 0 auto;">Join hundreds of Pakistani companies using Invox Pakistan to automate subscription invoices.</p>
        <a href="{{ route('public.contact') }}" class="btn btn-dark btn-lg px-5 fw-bold shadow">
            Request Demo &amp; Start Trial
        </a>
    </div>
</section>
@endsection
