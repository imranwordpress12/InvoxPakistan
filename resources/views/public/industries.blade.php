@extends('layouts.public')

@section('content')
<div class="container py-4">
    @include('partials.breadcrumbs', ['crumbs' => ['Industries' => route('public.industries')]])

    <header class="text-center py-4 mb-4" style="max-width: 800px; margin: 0 auto;">
        <h1 class="display-5 fw-bold text-dark mb-3">Vertical Solutions Across Major Pakistani Industries</h1>
        <p class="lead text-muted">See how Invox Pakistan solves industry-specific invoicing, tax compliance, and recurring payment challenges.</p>
    </header>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm p-3">
                <div class="card-body">
                    <div class="text-primary mb-3 fs-2"><i class="bi bi-laptop"></i></div>
                    <h2 class="h5 fw-bold">IT Services &amp; Software Houses</h2>
                    <p class="text-muted small">Bill global and local clients with multi-currency support, automated milestone invoices, and retainer management.</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm p-3">
                <div class="card-body">
                    <div class="text-primary mb-3 fs-2"><i class="bi bi-truck"></i></div>
                    <h2 class="h5 fw-bold">Logistics &amp; Supply Chain</h2>
                    <p class="text-muted small">Automate consignment invoicing, provincial tax rate mapping (PRA, SRB, KPRA, BRA), and automated customer payment tracking.</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm p-3">
                <div class="card-body">
                    <div class="text-primary mb-3 fs-2"><i class="bi bi-boxes"></i></div>
                    <h2 class="h5 fw-bold">Wholesale &amp; Distribution</h2>
                    <p class="text-muted small">Process high-volume bulk invoices with credit term management, FBR digital QR codes, and sales agent tracking.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
