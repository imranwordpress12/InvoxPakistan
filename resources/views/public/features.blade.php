@extends('layouts.public')

@section('content')
<div class="container py-4">
    @include('partials.breadcrumbs', ['crumbs' => ['Features' => route('public.features')]])

    <header class="text-center py-4 mb-4" style="max-width: 800px; margin: 0 auto;">
        <h1 class="display-5 fw-bold text-dark mb-3">Enterprise Features for Subscription &amp; Tax Compliance</h1>
        <p class="lead text-muted">Discover the complete feature set of Invox Pakistan designed to automate billing operations, FBR tax reporting, and client management.</p>
    </header>

    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm p-3">
                <div class="card-body">
                    <div class="bg-primary-subtle text-primary p-3 rounded d-inline-block mb-3">
                        <i class="bi bi-clock-history fs-3"></i>
                    </div>
                    <h2 class="h5 fw-bold">Automated Recurring Billing</h2>
                    <p class="text-muted small">Configure flexible billing schedules including monthly, quarterly, and annual intervals with automatic proration and invoice generation.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm p-3">
                <div class="card-body">
                    <div class="bg-success-subtle text-success p-3 rounded d-inline-block mb-3">
                        <i class="bi bi-patch-check fs-3"></i>
                    </div>
                    <h2 class="h5 fw-bold">FBR Digital Tax Integration</h2>
                    <p class="text-muted small">Automated calculation of provincial sales taxes (PRA, SRB, BRA, KPRA, FBR) with embedded QR codes and instant FBR reference key generation.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm p-3">
                <div class="card-body">
                    <div class="bg-info-subtle text-info p-3 rounded d-inline-block mb-3">
                        <i class="bi bi-person-workspace fs-3"></i>
                    </div>
                    <h2 class="h5 fw-bold">Branded Client Portal</h2>
                    <p class="text-muted small">Give your corporate clients access to a self-service portal to view invoice histories, download PDF receipts, and submit payment proof.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm p-3">
                <div class="card-body">
                    <div class="bg-warning-subtle text-warning p-3 rounded d-inline-block mb-3">
                        <i class="bi bi-bell fs-3"></i>
                    </div>
                    <h2 class="h5 fw-bold">Automated Dunning Reminders</h2>
                    <p class="text-muted small">Reduce overdue balances with automated multi-channel payment reminders before, on, and after due dates with custom email templates.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm p-3">
                <div class="card-body">
                    <div class="bg-danger-subtle text-danger p-3 rounded d-inline-block mb-3">
                        <i class="bi bi-currency-exchange fs-3"></i>
                    </div>
                    <h2 class="h5 fw-bold">Multi-Currency &amp; Global Billing</h2>
                    <p class="text-muted small">Bill domestic clients in PKR and international clients in USD, EUR, or GBP with automated real-time exchange rate conversions.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm p-3">
                <div class="card-body">
                    <div class="bg-purple-subtle text-purple p-3 rounded d-inline-block mb-3">
                        <i class="bi bi-file-earmark-bar-graph fs-3"></i>
                    </div>
                    <h2 class="h5 fw-bold">Financial Reporting &amp; Audit Logs</h2>
                    <p class="text-muted small">Export comprehensive sales tax ledgers, revenue breakdown reports, and immutable audit logs for seamless annual financial auditing.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
