@props(['crumbs' => []])

@if(!empty($crumbs))
    @php
        $seoService = app(\App\Services\SeoService::class);
        $breadcrumbSchema = $seoService->getBreadcrumbsSchema($crumbs);
    @endphp

    <!-- Visible Breadcrumb Component -->
    <nav aria-label="Breadcrumb" class="py-2 mb-3">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item">
                <a href="{{ route('public.home') }}" class="text-decoration-none text-muted">
                    <i class="bi bi-house-door me-1"></i>Home
                </a>
            </li>
            @foreach($crumbs as $name => $url)
                @if($loop->last)
                    <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">{{ $name }}</li>
                @else
                    <li class="breadcrumb-item">
                        <a href="{{ str_starts_with($url, 'http') ? $url : url($url) }}" class="text-decoration-none text-muted">
                            {{ $name }}
                        </a>
                    </li>
                @endif
            @endforeach
        </ol>
    </nav>

    <!-- BreadcrumbList Schema -->
    <script type="application/ld+json">
    {!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endif
