@extends('layouts.public')

@section('content')
<div class="container py-4">
    @include('partials.breadcrumbs', ['crumbs' => ['FAQ' => route('public.faq')]])

    <header class="text-center py-4 mb-4" style="max-width: 800px; margin: 0 auto;">
        <h1 class="display-5 fw-bold text-dark mb-3">Frequently Asked Questions &amp; Answer Center</h1>
        <p class="lead text-muted">Comprehensive answers to technical, financial, and compliance questions regarding Invox Pakistan platform.</p>
    </header>

    <div class="row justify-content-center mb-5">
        <div class="col-lg-10">
            <div class="accordion accordion-flush shadow-sm border rounded-3" id="faqCenterAccordion">
                @foreach($faqs as $index => $faq)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faqHeading{{ $index }}">
                            <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }} fw-semibold fs-5" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse{{ $index }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="faqCollapse{{ $index }}">
                                {{ $faq['question'] }}
                            </button>
                        </h2>
                        <div id="faqCollapse{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="faqHeading{{ $index }}" data-bs-parent="#faqCenterAccordion">
                            <div class="accordion-body text-slate-700 lead fs-6">
                                {{ $faq['answer'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 p-4 bg-light rounded-3 text-center border">
                <h3 class="h5 fw-bold">Still have questions?</h3>
                <p class="text-muted small mb-3">Our customer support engineers and tax specialists are ready to assist your team.</p>
                <a href="{{ route('public.contact') }}" class="btn btn-primary fw-bold">Speak to Sales Support</a>
            </div>
        </div>
    </div>
</div>
@endsection
