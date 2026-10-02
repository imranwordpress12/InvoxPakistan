@extends('layouts.public')

@section('content')
<div class="container py-4">
    @include('partials.breadcrumbs', ['crumbs' => ['Documentation' => route('public.documentation')]])

    <header class="text-center py-4 mb-4" style="max-width: 800px; margin: 0 auto;">
        <h1 class="display-5 fw-bold text-dark mb-3">Developer &amp; API Documentation</h1>
        <p class="lead text-muted">Complete technical reference for integrating Invox Pakistan RESTful API, webhooks, and machine interaction protocols into your custom applications.</p>
    </header>

    <div class="row g-4 mb-5">
        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm p-3">
                <div class="card-body">
                    <h2 class="h5 fw-bold mb-2"><i class="bi bi-code-slash text-primary me-2"></i>REST API Basics</h2>
                    <p class="text-muted small">Learn about HTTP Bearer authentication, JSON payload formats, rate limits, and standard API response headers.</p>
                    <a href="{{ url('api/v1/product-info') }}" target="_blank" class="small fw-semibold">View JSON Endpoint Spec <i class="bi bi-box-arrow-up-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm p-3">
                <div class="card-body">
                    <h2 class="h5 fw-bold mb-2"><i class="bi bi-robot text-primary me-2"></i>WebMCP AI Protocol</h2>
                    <p class="text-muted small">Discover how AI browser agents interact safely with Invox Pakistan public endpoints via the WebMCP action manifest.</p>
                    <a href="{{ url('webmcp.json') }}" target="_blank" class="small fw-semibold">View WebMCP Manifest <i class="bi bi-box-arrow-up-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm p-3">
                <div class="card-body">
                    <h2 class="h5 fw-bold mb-2"><i class="bi bi-cpu text-primary me-2"></i>LLMs.txt Manifest</h2>
                    <p class="text-muted small">Read the structured machine-readable text document built for LLM search indexing and factual answer engines.</p>
                    <a href="{{ url('llms.txt') }}" target="_blank" class="small fw-semibold">View LLMs.txt File <i class="bi bi-box-arrow-up-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
