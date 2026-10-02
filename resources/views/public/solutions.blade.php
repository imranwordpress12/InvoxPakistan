@extends('layouts.public')

@section('content')
<div class="container py-4">
    @include('partials.breadcrumbs', ['crumbs' => ['Solutions' => route('public.solutions')]])

    <header class="text-center py-4 mb-4" style="max-width: 800px; margin: 0 auto;">
        <h1 class="display-5 fw-bold text-dark mb-3">Billing Solutions Tailored to Your Business Model</h1>
        <p class="lead text-muted">Whether you are an SME, enterprise corporation, accounting firm, or e-commerce merchant, Invox Pakistan adapts to your operational workflow.</p>
    </header>

    <div class="row g-4 mb-5">
        <div class="col-md-6" id="smes">
            <div class="card h-100 border-0 shadow-sm p-4">
                <div class="card-body">
                    <h2 class="h4 fw-bold text-primary mb-3"><i class="bi bi-building me-2"></i>For Growing SMEs</h2>
                    <p class="text-muted">Eliminate manual Excel billing files. Schedule automated recurring invoices for retainers, service contracts, and monthly subscriptions with automated SMS/email payment notifications.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6" id="enterprise">
            <div class="card h-100 border-0 shadow-sm p-4">
                <div class="card-body">
                    <h2 class="h4 fw-bold text-primary mb-3"><i class="bi bi-shield-lock me-2"></i>For Enterprise Corporations</h2>
                    <p class="text-muted">Enforce strict multi-tier approval workflows, role-based security permissions, real-time FBR digital sales tax validation, and custom ERP data synchronizations.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6" id="accountants">
            <div class="card h-100 border-0 shadow-sm p-4">
                <div class="card-body">
                    <h2 class="h4 fw-bold text-primary mb-3"><i class="bi bi-calculator me-2"></i>For Accounting &amp; Audit Firms</h2>
                    <p class="text-muted">Manage multiple client companies from a single master dashboard. Export clean, audit-ready sales tax reports, withholding tax summary sheets, and reconciliation statements.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6" id="ecommerce">
            <div class="card h-100 border-0 shadow-sm p-4">
                <div class="card-body">
                    <h2 class="h4 fw-bold text-primary mb-3"><i class="bi bi-cart3 me-2"></i>For E-Commerce &amp; Digital Services</h2>
                    <p class="text-muted">Instantly generate FBR compliant receipts with QR codes for online orders, manage recurring subscription boxes, and accept JazzCash, EasyPaisa &amp; credit card payments.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
