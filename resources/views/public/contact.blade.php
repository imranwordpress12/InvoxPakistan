@extends('layouts.public')

@section('content')
<div class="container py-4">
    @include('partials.breadcrumbs', ['crumbs' => ['Contact Sales' => route('public.contact')]])

    <header class="text-center py-4 mb-4" style="max-width: 800px; margin: 0 auto;">
        <h1 class="display-5 fw-bold text-dark mb-3">Contact Our Sales &amp; Demo Team</h1>
        <p class="lead text-muted">Have questions about subscription packages, FBR API setup, or enterprise integrations? Speak with our software engineers today.</p>
    </header>

    <div class="row justify-content-center mb-5">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4 rounded-3">
                <div class="card-body">
                    <form action="{{ route('public.contact.submit') }}" method="POST" onsubmit="if(window.trackGaEvent) trackGaEvent('submit_contact_form');">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="contact-name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" id="contact-name" name="name" class="form-control" placeholder="e.g. Ali Raza" required value="{{ old('name') }}">
                            </div>

                            <div class="col-md-6">
                                <label for="contact-email" class="form-label fw-semibold">Business Email <span class="text-danger">*</span></label>
                                <input type="email" id="contact-email" name="email" class="form-control" placeholder="ali@company.pk" required value="{{ old('email') }}">
                            </div>

                            <div class="col-md-6">
                                <label for="contact-phone" class="form-label fw-semibold">Phone / WhatsApp Number</label>
                                <input type="tel" id="contact-phone" name="phone" class="form-control" placeholder="+92 300 1234567" value="{{ old('phone') }}">
                            </div>

                            <div class="col-md-6">
                                <label for="contact-company" class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                                <input type="text" id="contact-company" name="company_name" class="form-control" placeholder="Company Pvt Ltd" required value="{{ old('company_name') }}">
                            </div>

                            <div class="col-12">
                                <label for="contact-message" class="form-label fw-semibold">How can we help your business? <span class="text-danger">*</span></label>
                                <textarea id="contact-message" name="message" rows="4" class="form-control" placeholder="Tell us about your billing volume, current software, or FBR tax compliance requirements..." required>{{ old('message') }}</textarea>
                            </div>

                            <div class="col-12 pt-2">
                                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">
                                    Send Message &amp; Schedule Demo
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
