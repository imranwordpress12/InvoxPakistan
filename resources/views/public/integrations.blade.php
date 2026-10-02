@extends('layouts.public')

@section('content')
<div class="container py-4">
    @include('partials.breadcrumbs', ['crumbs' => ['Integrations' => route('public.integrations')]])

    <header class="text-center py-4 mb-4" style="max-width: 800px; margin: 0 auto;">
        <h1 class="display-5 fw-bold text-dark mb-3">Seamless Integrations with Payment Gateways &amp; ERPs</h1>
        <p class="lead text-muted">Connect Invox Pakistan with your existing banking channels, local mobile wallets, FBR portals, and accounting software.</p>
    </header>

    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm p-4 text-center">
                <div class="card-body">
                    <div class="badge bg-success-subtle text-success p-3 rounded-circle mb-3 fs-3">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <h2 class="h5 fw-bold">JazzCash &amp; EasyPaisa</h2>
                    <p class="text-muted small">Enable direct mobile wallet payments and automated transaction ID matching for fast payment clearing.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm p-4 text-center">
                <div class="card-body">
                    <div class="badge bg-primary-subtle text-primary p-3 rounded-circle mb-3 fs-3">
                        <i class="bi bi-bank"></i>
                    </div>
                    <h2 class="h5 fw-bold">1LINK 1Bill &amp; Bank Transfer</h2>
                    <p class="text-muted small">Generate unique 1Bill reference codes for online banking payments across all major Pakistani banks.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm p-4 text-center">
                <div class="card-body">
                    <div class="badge bg-warning-subtle text-warning p-3 rounded-circle mb-3 fs-3">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h2 class="h5 fw-bold">FBR Digital Tax Gateway</h2>
                    <p class="text-muted small">Direct real-time API interface with FBR server for invoice validation, QR code rendering, and sales tax reporting.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
