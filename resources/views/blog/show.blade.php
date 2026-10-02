@extends('layouts.public')

@section('content')
<div class="container py-4">
    @include('partials.breadcrumbs', [
        'crumbs' => [
            'Blog' => route('blog.index'),
            $article['category_name'] => route('blog.category', $article['category']),
            $article['title'] => $article['url']
        ]
    ])

    <article class="py-4" style="max-width: 800px; margin: 0 auto;">
        <header class="mb-4">
            <div class="mb-2">
                <span class="badge bg-primary-subtle text-primary fw-semibold">{{ $article['category_name'] }}</span>
                <span class="text-muted small ms-2"><i class="bi bi-clock me-1"></i>{{ $article['read_time'] }}</span>
            </div>

            <h1 class="display-5 fw-bold text-dark mb-3">{{ $article['title'] }}</h1>

            <!-- Author & E-E-A-T Publication Info -->
            <div class="d-flex align-items-center p-3 bg-light rounded-3 border mb-4">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-3" style="width: 44px; height: 44px;">
                    {{ substr($article['author_name'], 0, 1) }}
                </div>
                <div>
                    <div class="fw-bold text-dark">{{ $article['author_name'] }}</div>
                    <div class="text-muted small">{{ $article['author_role'] }} &bull; Published {{ date('F d, Y', strtotime($article['published_time'])) }}</div>
                </div>
            </div>
        </header>

        <!-- Article Content -->
        <div class="lead text-dark mb-4 fs-5" style="line-height: 1.8;">
            {{ $article['content'] }}
        </div>

        <div class="p-4 bg-slate-50 border rounded-3 mb-5">
            <h2 class="h5 fw-bold mb-2">Key Takeaways for Pakistani Enterprise Teams</h2>
            <ul class="mb-0 text-muted small d-flex flex-column gap-2">
                <li>Automating FBR tax compliance eliminates manual sales tax filing bottlenecks.</li>
                <li>Recurring billing automation reduces dunning delays and improves monthly cash flow.</li>
                <li>Integrating local payment channels (JazzCash, EasyPaisa, 1LINK) increases customer conversion.</li>
            </ul>
        </div>

        <!-- Social Sharing Section -->
        <footer class="pt-4 border-top d-flex align-items-center justify-content-between">
            <div class="fw-semibold text-muted small">Share this article:</div>
            <div class="d-flex gap-2">
                <a href="https://twitter.com/intent/tweet?text={{ urlencode($article['title']) }}&url={{ urlencode($article['url']) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-sm" aria-label="Share on X Twitter">
                    <i class="bi bi-twitter-x"></i> Share
                </a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($article['url']) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm" aria-label="Share on LinkedIn">
                    <i class="bi bi-linkedin"></i> LinkedIn
                </a>
            </div>
        </footer>
    </article>
</div>
@endsection
