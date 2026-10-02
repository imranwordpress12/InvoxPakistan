<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Meta Tags & Favicons -->
    @include('partials.seo-meta', [
        'title' => $title ?? null,
        'description' => $description ?? null,
        'canonical' => $canonical ?? null,
        'robots' => $robots ?? null,
        'og_type' => $og_type ?? 'website',
        'og_title' => $og_title ?? null,
        'og_description' => $og_description ?? null,
        'og_image' => $og_image ?? null,
        'twitter_title' => $twitter_title ?? null,
        'twitter_description' => $twitter_description ?? null,
        'published_time' => $published_time ?? null,
        'modified_time' => $modified_time ?? null,
        'article_author' => $article_author ?? null
    ])

    <!-- Preconnect for Third-Party CDNs (Performance TTFB Optimization) -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">

    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Custom CSS for SEO & Core Web Vitals optimization -->
    <style>
        :root {
            --invox-primary: #0284c7;
            --invox-primary-dark: #0369a1;
            --invox-dark: #0f172a;
            --invox-slate: #334155;
            --invox-bg: #f8fafc;
        }

        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: var(--invox-slate);
            background-color: #ffffff;
            line-height: 1.6;
        }

        /* Accessibility Skip Link */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 10px;
            background: #000;
            color: #fff;
            padding: 8px 16px;
            z-index: 9999;
            transition: top 0.2s ease;
        }
        .skip-link:focus {
            top: 10px;
        }

        /* Focus Ring for WCAG 2.2 AA Accessibility */
        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible {
            outline: 3px solid var(--invox-primary);
            outline-offset: 2px;
        }

        /* Navbar Enhancements */
        .navbar-invox {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
        }
        .navbar-invox .nav-link {
            color: #334155;
            font-weight: 500;
            padding: 0.5rem 0.85rem;
        }
        .navbar-invox .nav-link:hover, .navbar-invox .nav-link.active {
            color: var(--invox-primary);
        }

        /* Footer Link Hierarchy */
        .footer-invox {
            background-color: var(--invox-dark);
            color: #94a3b8;
        }
        .footer-invox a {
            color: #cbd5e1;
            text-decoration: none;
            transition: color 0.15s ease;
        }
        .footer-invox a:hover {
            color: #ffffff;
            text-decoration: underline;
        }
    </style>

    @stack('styles')

    <!-- Analytics & Tracking Scripts -->
    @include('partials.analytics')

    <!-- Default Organization & WebSite JSON-LD Schemas -->
    @php
        $seoService = app(\App\Services\SeoService::class);
    @endphp
    <script type="application/ld+json">
    {!! json_encode($seoService->getOrganizationSchema(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
    <script type="application/ld+json">
    {!! json_encode($seoService->getWebSiteSchema(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>

    @if(isset($schemaExtra) && is_array($schemaExtra))
        <script type="application/ld+json">
        {!! json_encode($schemaExtra, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>
    @endif
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Accessibility Skip Link -->
    <a href="#main-content" class="skip-link rounded">Skip to main content</a>

    <!-- Header & Semantic Crawlable Navigation -->
    <header class="sticky-top">
        <nav class="navbar navbar-expand-lg navbar-invox" aria-label="Main Navigation">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center me-4" href="{{ route('public.home') }}" title="Invox Pakistan Homepage">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="Invox Pakistan Logo">
                </a>

                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPublicContent" aria-controls="navbarPublicContent" aria-expanded="false" aria-label="Toggle navigation menu">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarPublicContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('public.features') ? 'active' : '' }}" href="{{ route('public.features') }}">Features</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('public.pricing') ? 'active' : '' }}" href="{{ route('public.pricing') }}">Pricing</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('public.solutions') ? 'active' : '' }}" href="{{ route('public.solutions') }}">Solutions</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('public.industries') ? 'active' : '' }}" href="{{ route('public.industries') }}">Industries</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('public.integrations') ? 'active' : '' }}" href="{{ route('public.integrations') }}">Integrations</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('blog.*') ? 'active' : '' }}" href="{{ route('blog.index') }}">Blog</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('public.resources') ? 'active' : '' }}" href="{{ route('public.resources') }}">Resources</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('public.documentation') ? 'active' : '' }}" href="{{ route('public.documentation') }}">Docs</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('public.faq') ? 'active' : '' }}" href="{{ route('public.faq') }}">FAQ</a>
                        </li>
                    </ul>

                    <!-- Search Form & CTA Buttons -->
                    <div class="d-flex align-items-center gap-2">
                        <form action="{{ route('public.search') }}" method="GET" class="d-none d-xl-flex me-2" role="search">
                            <div class="input-group input-group-sm">
                                <input type="search" name="q" class="form-control" placeholder="Search..." aria-label="Search site content" value="{{ request('q') }}">
                                <button class="btn btn-outline-secondary" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
                            </div>
                        </form>

                        <a href="{{ route('company.login') }}" class="btn btn-outline-dark btn-sm fw-medium px-3" onclick="if(window.trackGaEvent) trackGaEvent('click_login', { type: 'company' });">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login
                        </a>
                        <a href="{{ route('public.contact') }}" class="btn btn-primary btn-sm fw-medium px-3" onclick="if(window.trackGaEvent) trackGaEvent('click_request_demo', { location: 'navbar' });">
                            Request Demo
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content Area -->
    <main id="main-content" class="flex-grow-1">
        @if (session('status'))
            <div class="container mt-3">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i> {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer Component with Structured Links & Social Proof -->
    <footer class="footer-invox pt-5 pb-4 mt-auto border-top border-slate-700">
        <div class="container">
            <div class="row gy-4 mb-5">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ asset('images/logo.jpeg') }}" alt="Invox Pakistan Logo">
                    </div>
                    <p class="small text-slate-400 mb-3" style="max-width: 320px;">
                        Pakistan's premier company subscription management, recurring billing automation, and FBR tax-compliant digital invoicing engine for high-growth enterprises.
                    </p>
                    <div class="d-flex gap-3 text-slate-300 fs-5 mb-3" aria-label="Social media channels">
                        <a href="https://www.linkedin.com/company/invoxpakistan" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn Profile"><i class="bi bi-linkedin"></i></a>
                        <a href="https://twitter.com/invoxpakistan" target="_blank" rel="noopener noreferrer" aria-label="Twitter Profile"><i class="bi bi-twitter-x"></i></a>
                        <a href="https://www.facebook.com/invoxpakistan" target="_blank" rel="noopener noreferrer" aria-label="Facebook Profile"><i class="bi bi-facebook"></i></a>
                        <a href="https://www.instagram.com/invoxpakistan" target="_blank" rel="noopener noreferrer" aria-label="Instagram Profile"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <h2 class="h6 text-white text-uppercase tracking-wider fw-bold mb-3">Product</h2>
                    <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                        <li><a href="{{ route('public.features') }}">Features &amp; Modules</a></li>
                        <li><a href="{{ route('public.pricing') }}">Pricing Plans</a></li>
                        <li><a href="{{ route('public.integrations') }}">Integrations</a></li>
                        <li><a href="{{ route('public.documentation') }}">API Documentation</a></li>
                        <li><a href="{{ route('public.faq') }}">FAQ Center</a></li>
                    </ul>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <h2 class="h6 text-white text-uppercase tracking-wider fw-bold mb-3">Solutions</h2>
                    <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                        <li><a href="{{ route('public.solutions') }}#smes">For SMEs</a></li>
                        <li><a href="{{ route('public.solutions') }}#enterprise">For Enterprises</a></li>
                        <li><a href="{{ route('public.solutions') }}#accountants">For Accountants</a></li>
                        <li><a href="{{ route('public.industries') }}">Industry Specifics</a></li>
                        <li><a href="{{ route('public.resources') }}">Resource Library</a></li>
                    </ul>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <h2 class="h6 text-white text-uppercase tracking-wider fw-bold mb-3">Company</h2>
                    <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                        <li><a href="{{ route('public.about') }}">About Us</a></li>
                        <li><a href="{{ route('blog.index') }}">Official Blog</a></li>
                        <li><a href="{{ route('public.contact') }}">Contact Sales</a></li>
                        <li><a href="{{ route('public.sitemap') }}">HTML Sitemap</a></li>
                        <li><a href="{{ url('llms.txt') }}" target="_blank">LLMs.txt Manifest</a></li>
                    </ul>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <h2 class="h6 text-white text-uppercase tracking-wider fw-bold mb-3">Legal &amp; Trust</h2>
                    <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                        <li><a href="{{ route('public.privacy') }}">Privacy Policy</a></li>
                        <li><a href="{{ route('public.terms') }}">Terms of Service</a></li>
                        <li><a href="{{ url('.well-known/security.txt') }}" target="_blank">Security Policy</a></li>
                        <li><a href="{{ url('robots.txt') }}" target="_blank">Robots Directive</a></li>
                        <li><a href="{{ url('sitemap.xml') }}" target="_blank">XML Sitemap</a></li>
                    </ul>
                </div>
            </div>

            <div class="pt-4 border-top border-slate-800 d-flex flex-column flex-md-row align-items-center justify-content-between text-slate-400 small gap-2">
                <div>
                    &copy; {{ date('Y') }} <strong>Invox Pakistan</strong>. All rights reserved. Built with technical SEO &amp; WCAG 2.2 AA standards.
                </div>
                <div>
                    <span class="me-3"><i class="bi bi-shield-lock me-1 text-success"></i> 256-bit SSL Encrypted</span>
                    <span><i class="bi bi-check-circle me-1 text-info"></i> FBR Digital Integration Ready</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Cookie Consent Banner -->
    @include('partials.cookie-banner')

    <!-- JS Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
