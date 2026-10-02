<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Company Portal') &mdash; {{ config('app.name', 'Invox Pakistan') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/company-theme.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Top Company Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light company-navbar sticky-top">
        <div class="container-fluid px-3 px-lg-4">
            <button class="btn btn-sm sidebar-toggle-btn d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#companySidebar" aria-controls="companySidebar" aria-label="Toggle navigation">
                <i class="bi bi-list fs-5"></i>
            </button>

            <a class="navbar-brand d-flex align-items-center" href="{{ route('company.dashboard') }}">
                <img src="{{ asset('images/logo.jpeg') }}" alt="Invox Pakistan Logo">
            </a>

            <div class="d-flex align-items-center ms-auto gap-3">
                <div class="d-none d-sm-flex align-items-center text-slate-700 small bg-slate-100 px-3 py-1.5 rounded-pill border">
                    <i class="bi bi-building text-primary me-2"></i>
                    <span class="fw-bold">{{ auth()->user()->company?->business_name ?? auth()->user()->name }}</span>
                </div>

                <form method="POST" action="{{ route('company.logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn" title="Logout from Portal" aria-label="Logout">
                        <i class="bi bi-box-arrow-right fs-6"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="d-flex flex-grow-1">
        <!-- Sidebar Navigation -->
        <nav class="offcanvas offcanvas-start company-sidebar border-end flex-shrink-0" style="width: 250px;" tabindex="-1" id="companySidebar" aria-labelledby="companySidebarLabel">
            <div class="offcanvas-header border-bottom">
                <div class="d-flex align-items-center">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="Invox Pakistan Logo" width="28" height="28" class="rounded me-2">
                    <h5 class="offcanvas-title text-dark h6 mb-0" id="companySidebarLabel">Company Workspace</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#companySidebar" aria-label="Close"></button>
            </div>

            <div class="offcanvas-body p-0">
                <div class="py-3 px-3">
                    <span class="text-uppercase tracking-wider extra-small fw-bold text-muted mb-2 d-block px-2">Navigation</span>
                    <div class="list-group list-group-flush gap-1">
                        <a href="{{ route('company.dashboard') }}" class="list-group-item list-group-item-action rounded-3 {{ request()->routeIs('company.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>

                        <!-- Invoices Collapsible Menu -->
                        <a class="list-group-item list-group-item-action rounded-3 d-flex justify-content-between align-items-center {{ request()->routeIs('company.invoices.*') ? 'active-group' : '' }}" data-bs-toggle="collapse" href="#sidebar-invoices" role="button" aria-expanded="{{ request()->routeIs('company.invoices.*') ? 'true' : 'false' }}">
                            <span><i class="bi bi-receipt me-2"></i> Invoices</span>
                            <i class="bi bi-chevron-down extra-small"></i>
                        </a>
                        <div class="collapse {{ request()->routeIs('company.invoices.*') ? 'show' : '' }}" id="sidebar-invoices">
                            <div class="list-group list-group-flush ms-3 ps-2 border-start gap-1 py-1">
                                <a href="{{ route('company.invoices.index') }}" class="list-group-item list-group-item-action rounded-2 py-2 small {{ request()->routeIs('company.invoices.index') ? 'active' : '' }}">
                                    Invoice Listing
                                </a>
                                <a href="{{ route('company.invoices.create') }}" class="list-group-item list-group-item-action rounded-2 py-2 small {{ request()->routeIs('company.invoices.create') ? 'active' : '' }}">
                                    Create Invoice
                                </a>
                                <a href="{{ route('company.invoices.bulk-upload.create') }}" class="list-group-item list-group-item-action rounded-2 py-2 small {{ request()->routeIs('company.invoices.bulk-upload.*') ? 'active' : '' }}">
                                    Bulk Uploads
                                </a>
                                <a href="{{ route('company.invoices.drafts') }}" class="list-group-item list-group-item-action rounded-2 py-2 small {{ request()->routeIs('company.invoices.drafts') || request()->routeIs('company.invoices.edit') ? 'active' : '' }}">
                                    Drafts &amp; Saved
                                </a>
                            </div>
                        </div>

                        <a href="{{ route('company.customers.index') }}" class="list-group-item list-group-item-action rounded-3 {{ request()->routeIs('company.customers.*') ? 'active' : '' }}">
                            <i class="bi bi-people me-2"></i> Customers
                        </a>

                        <a href="{{ route('company.items.index') }}" class="list-group-item list-group-item-action rounded-3 {{ request()->routeIs('company.items.*') ? 'active' : '' }}">
                            <i class="bi bi-box-seam me-2"></i> Items &amp; Products
                        </a>

                        <!-- Settings Collapsible Menu -->
                        <a class="list-group-item list-group-item-action rounded-3 d-flex justify-content-between align-items-center {{ request()->routeIs('company.settings.*') ? 'active-group' : '' }}" data-bs-toggle="collapse" href="#sidebar-settings" role="button" aria-expanded="{{ request()->routeIs('company.settings.*') ? 'true' : 'false' }}">
                            <span><i class="bi bi-gear me-2"></i> System Settings</span>
                            <i class="bi bi-chevron-down extra-small"></i>
                        </a>
                        <div class="collapse {{ request()->routeIs('company.settings.*') ? 'show' : '' }}" id="sidebar-settings">
                            <div class="list-group list-group-flush ms-3 ps-2 border-start gap-1 py-1">
                                <a href="{{ route('company.settings.password.edit') }}" class="list-group-item list-group-item-action rounded-2 py-2 small {{ request()->routeIs('company.settings.password.*') ? 'active' : '' }}">
                                    Change Password
                                </a>
                            </div>
                        </div>

                        <a href="{{ route('company.compliance-instructions') }}" class="list-group-item list-group-item-action rounded-3 {{ request()->routeIs('company.compliance-instructions') ? 'active' : '' }}">
                            <i class="bi bi-shield-check me-2 text-success"></i> Compliance Guide
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content Area -->
        <div class="flex-grow-1 d-flex flex-column bg-slate-50">
            <main class="flex-grow-1 p-3 p-md-4">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('submission_failed'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('submission_failed') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('bulk_upload_created'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                        <i class="bi bi-file-earmark-arrow-up-fill me-2"></i> {{ session('bulk_upload_created') }} invoice(s) created from the uploaded file, saved as drafts.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('bulk_upload_errors'))
                    <div class="alert alert-danger shadow-sm border-0 mb-4" role="alert">
                        <p class="mb-1 fw-bold"><i class="bi bi-x-circle-fill me-2"></i> {{ count(session('bulk_upload_errors')) }} row/invoice(s) from the uploaded file could not be imported:</p>
                        <ul class="mb-0 small">
                            @foreach (session('bulk_upload_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="company-footer py-3 border-top">
                <div class="container-fluid text-center text-muted small">
                    &copy; {{ date('Y') }} Invox Pakistan &mdash; Company Billing &amp; FBR Tax Integration System.
                </div>
            </footer>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button type="button" class="back-to-top-btn" id="backToTopBtn" aria-label="Back to top">
        <i class="bi bi-chevron-up"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script>
        (function () {
            const btn = document.getElementById('backToTopBtn');
            window.addEventListener('scroll', function () {
                btn.classList.toggle('show', window.scrollY > 300);
            });
            btn.addEventListener('click', function () {
                window.scrollTo({top: 0, behavior: 'smooth'});
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
