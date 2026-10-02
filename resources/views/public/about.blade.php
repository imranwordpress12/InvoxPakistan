@extends('layouts.public')

@section('content')
<div class="container py-4">
    @include('partials.breadcrumbs', ['crumbs' => ['About Us' => route('public.about')]])

    <header class="text-center py-4 mb-4" style="max-width: 800px; margin: 0 auto;">
        <h1 class="display-5 fw-bold text-dark mb-3">About Invox Pakistan</h1>
        <p class="lead text-muted">Empowering Pakistani enterprises with automated subscription management, financial compliance, and seamless digital tax integration.</p>
    </header>

    <div class="row gy-4 align-items-center mb-5">
        <div class="col-lg-6">
            <h2 class="h3 fw-bold mb-3">Our Mission &amp; Purpose</h2>
            <p class="text-muted">Founded in Lahore, Pakistan, Invox Pakistan was created to solve the complex financial friction faced by growing companies in recurring revenue billing and digital sales tax compliance.</p>
            <p class="text-muted">Our platform bridges traditional enterprise operations with modern fintech innovations, enabling businesses to automate invoicing, reduce payment delays, and comply with FBR digital tax mandates effortlessy.</p>
        </div>
        <div class="col-lg-6">
            <div class="bg-light p-4 rounded-3 border">
                <h3 class="h5 fw-bold mb-3"><i class="bi bi-building me-2 text-primary"></i>Corporate E-E-A-T &amp; Trust Credentials</h3>
                <ul class="list-unstyled small text-muted d-flex flex-column gap-2 mb-0">
                    <li><strong class="text-dark">Official Corporate Office:</strong> Invox Financial Tech Towers, Gulberg III, Lahore, Pakistan</li>
                    <li><strong class="text-dark">Tax Registration:</strong> NTN 892014-7 / STRN 3277876123490</li>
                    <li><strong class="text-dark">Security Infrastructure:</strong> 256-Bit SSL Encryption, SOC2 Compliant Cloud, Daily Offsite Backups</li>
                    <li><strong class="text-dark">Support Hotline:</strong> +92-42-35900000 / support@invox.pk</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
