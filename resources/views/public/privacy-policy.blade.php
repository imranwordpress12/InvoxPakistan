@extends('layouts.public')

@section('content')
<div class="container py-4">
    @include('partials.breadcrumbs', ['crumbs' => ['Privacy Policy' => route('public.privacy')]])

    <article class="py-4" style="max-width: 840px; margin: 0 auto;">
        <h1 class="display-6 fw-bold text-dark mb-3">Privacy Policy</h1>
        <p class="text-muted small mb-4">Last Updated: October 2, 2026</p>

        <section class="mb-4">
            <h2 class="h4 fw-bold">1. Information We Collect</h2>
            <p class="text-muted">Invox Pakistan collects business registration info, contact names, corporate email addresses, billing records, and technical telemetry required to provide SaaS subscription billing and FBR digital invoicing services.</p>
        </section>

        <section class="mb-4">
            <h2 class="h4 fw-bold">2. Use of Analytics &amp; Cookies</h2>
            <p class="text-muted">We use Google Analytics 4, Meta Pixel, and Microsoft Clarity strictly with anonymized IP addresses to analyze platform performance. Non-essential tracking scripts are fired only after explicit user consent via our cookie banner.</p>
        </section>

        <section class="mb-4">
            <h2 class="h4 fw-bold">3. Data Protection &amp; Security</h2>
            <p class="text-muted">We enforce 256-bit SSL encryption for data in transit and AES-256 encryption at rest. Personal identifiable information is never sold or shared with unauthorized third parties.</p>
        </section>
    </article>
</div>
@endsection
