@extends('layouts.public')

@section('content')
<div class="container py-4">
    @include('partials.breadcrumbs', ['crumbs' => ['Blog' => route('blog.index')]])

    <header class="text-center py-4 mb-4" style="max-width: 800px; margin: 0 auto;">
        <h1 class="display-5 fw-bold text-dark mb-3">Invox Pakistan Official Blog</h1>
        <p class="lead text-muted">Insights, guides, and practical advice on subscription management, FBR digital invoicing compliance, and fintech operations in Pakistan.</p>
    </header>

    <!-- Category Filters -->
    <div class="d-flex justify-content-center flex-wrap gap-2 mb-5">
        <a href="{{ route('blog.index') }}" class="btn btn-sm {{ empty($currentCategory) ? 'btn-primary' : 'btn-outline-secondary' }}">All Articles</a>
        <a href="{{ route('blog.category', 'compliance') }}" class="btn btn-sm {{ $currentCategory === 'compliance' ? 'btn-primary' : 'btn-outline-secondary' }}">Tax Compliance</a>
        <a href="{{ route('blog.category', 'software') }}" class="btn btn-sm {{ $currentCategory === 'software' ? 'btn-primary' : 'btn-outline-secondary' }}">Software &amp; Billing</a>
        <a href="{{ route('blog.category', 'fintech') }}" class="btn btn-sm {{ $currentCategory === 'fintech' ? 'btn-primary' : 'btn-outline-secondary' }}">Fintech &amp; Payments</a>
    </div>

    <!-- Article Cards -->
    <div class="row g-4 mb-5">
        @foreach($articles as $article)
            <div class="col-md-6 col-lg-4">
                <article class="card h-100 border-0 shadow-sm rounded-3">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge bg-primary-subtle text-primary fw-semibold">{{ $article['category_name'] }}</span>
                            <span class="text-muted small ms-2"><i class="bi bi-clock me-1"></i>{{ $article['read_time'] }}</span>
                        </div>
                        <h2 class="h5 fw-bold mb-3">
                            <a href="{{ route('blog.show', $article['slug']) }}" class="text-decoration-none text-dark hover-primary">
                                {{ $article['title'] }}
                            </a>
                        </h2>
                        <p class="text-muted small flex-grow-1 mb-4">
                            {{ $article['description'] }}
                        </p>
                        <div class="pt-3 border-top d-flex align-items-center justify-content-between small text-muted">
                            <div><i class="bi bi-person me-1"></i>{{ $article['author_name'] }}</div>
                            <div>{{ date('M d, Y', strtotime($article['published_time'])) }}</div>
                        </div>
                    </div>
                </article>
            </div>
        @endforeach
    </div>
</div>
@endsection
