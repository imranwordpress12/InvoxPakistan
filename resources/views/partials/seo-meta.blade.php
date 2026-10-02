@php
    $seoService = app(\App\Services\SeoService::class);
    
    // Page Title
    $rawTitle = $title ?? 'Company Subscription & FBR Invoicing Platform';
    $pageTitle = $seoService->formatTitle($rawTitle, $includeBrand ?? true);
    
    // Description
    $metaDescription = $description ?? 'Automated subscription management, recurring billing, and FBR tax-compliant invoicing software designed for Pakistani companies and enterprise teams.';
    
    // Canonical
    $canonicalUrl = $canonical ?? $seoService->getCanonicalUrl();
    
    // Robots
    $metaRobots = $robots ?? config('seo.default_meta.robots');
    
    // Open Graph
    $ogType = $og_type ?? 'website';
    $ogTitle = $og_title ?? $pageTitle;
    $ogDescription = $og_description ?? $metaDescription;
    $ogImage = $og_image ?? asset(config('seo.default_meta.og_image'));
    $ogImageAlt = $og_image_alt ?? config('seo.default_meta.og_image_alt');
    
    // Twitter
    $twitterCard = $twitter_card ?? config('seo.default_meta.twitter_card');
    $twitterTitle = $twitter_title ?? $ogTitle;
    $twitterDescription = $twitter_description ?? $ogDescription;
    $twitterImage = $twitter_image ?? asset(config('seo.default_meta.twitter_image'));
    $twitterImageAlt = $twitter_image_alt ?? $ogImageAlt;
    $twitterHandle = config('seo.twitter_handle');

    // Verification codes
    $googleVerification = config('seo.google_site_verification');
    $bingVerification = config('seo.bing_site_verification');
@endphp

<!-- Primary Meta Tags -->
<title>{{ $pageTitle }}</title>
<meta name="title" content="{{ $pageTitle }}">
<meta name="description" content="{{ $metaDescription }}">
<meta name="robots" content="{{ $metaRobots }}">
<meta name="author" content="Invox Pakistan">

<!-- Canonical URL -->
<link rel="canonical" href="{{ $canonicalUrl }}">

<!-- Hreflang Tags -->
<link rel="alternate" hreflang="en" href="{{ $canonicalUrl }}">
<link rel="alternate" hreflang="x-default" href="{{ $canonicalUrl }}">

<!-- Open Graph / Facebook / LinkedIn -->
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $ogImageAlt }}">
<meta property="og:site_name" content="{{ config('seo.site_name') }}">
<meta property="og:locale" content="en_US">

@if ($ogType === 'article')
    @if (!empty($published_time))
        <meta property="article:published_time" content="{{ $published_time }}">
    @endif
    @if (!empty($modified_time))
        <meta property="article:modified_time" content="{{ $modified_time }}">
    @endif
    @if (!empty($article_author))
        <meta property="article:author" content="{{ $article_author }}">
    @endif
@endif

<!-- Twitter / X Cards -->
<meta name="twitter:card" content="{{ $twitterCard }}">
<meta name="twitter:url" content="{{ $canonicalUrl }}">
<meta name="twitter:title" content="{{ $twitterTitle }}">
<meta name="twitter:description" content="{{ $twitterDescription }}">
<meta name="twitter:image" content="{{ $twitterImage }}">
<meta name="twitter:image:alt" content="{{ $twitterImageAlt }}">
@if(!empty($twitterHandle))
    <meta name="twitter:site" content="{{ $twitterHandle }}">
    <meta name="twitter:creator" content="{{ $twitterHandle }}">
@endif

<!-- Search Engine Verifications -->
@if(!empty($googleVerification))
    <meta name="google-site-verification" content="{{ $googleVerification }}">
@endif
@if(!empty($bingVerification))
    <meta name="msvalidate.01" content="{{ $bingVerification }}">
@endif

<!-- Favicon & PWA Webmanifest -->
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
<link rel="manifest" href="{{ url('site.webmanifest') }}">
<meta name="theme-color" content="#0f172a">
