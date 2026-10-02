@extends('layouts.public')

@section('content')
<div class="container py-4">
    @include('partials.breadcrumbs', ['crumbs' => ['Terms of Service' => route('public.terms')]])

    <article class="py-4" style="max-width: 840px; margin: 0 auto;">
        <h1 class="display-6 fw-bold text-dark mb-3">Terms of Service</h1>
        <p class="text-muted small mb-4">Last Updated: October 2, 2026</p>

        <section class="mb-4">
            <h2 class="h4 fw-bold">1. Service Agreement</h2>
            <p class="text-muted">By creating an account or subscribing to Invox Pakistan, you agree to comply with our acceptable use policies, subscription terms, and applicable tax regulations under Pakistani law.</p>
        </section>

        <section class="mb-4">
            <h2 class="h4 fw-bold">2. Subscription Billing &amp; Cancellations</h2>
            <p class="text-muted">Subscriptions are billed in advance on a recurring monthly or annual basis. You may cancel your subscription at any time prior to the next billing cycle without penalty.</p>
        </section>
    </article>
</div>
@endsection
