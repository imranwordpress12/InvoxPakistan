<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Company') &mdash; {{ config('app.name', 'Invox Pakistan') }}</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr"
        crossorigin="anonymous">
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    {{-- Company Dashboard-only theme (PRD reskin) — a separate file from
         app.css on purpose, since layouts/admin.blade.php also loads
         app.css. Nothing in this file can affect the Admin/Super Admin
         side because it is never linked from there. --}}
    <link href="{{ asset('css/company-theme.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light company-navbar">
        <div class="container-fluid">
            <button
                class="btn btn-sm sidebar-toggle-btn d-lg-none me-2"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#companySidebar"
                aria-controls="companySidebar"
                aria-label="Toggle navigation"
            >
                <i class="bi bi-list fs-5"></i>
            </button>

            <a class="navbar-brand fw-semibold" href="{{ route('company.dashboard') }}">
                <i class="bi bi-building-check me-1"></i> Invox Pakistan
            </a>

            <div class="d-flex align-items-center ms-auto">
                <span class="company-name small me-3 d-none d-sm-inline">
                    {{ auth()->user()->company?->business_name ?? auth()->user()->name }}
                </span>

                <form method="POST" action="{{ route('company.logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn" title="Logout" aria-label="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="d-flex">
        {{-- Sidebar structure per the Company Dashboard PRD #3. Sub-items
             whose pages don't exist yet render as disabled "soon" entries,
             same pattern used throughout the admin side of this app.
             offcanvas-lg (Bootstrap 5.2+, native — no new JS): behaves as
             a normal static sidebar at lg and up, and becomes a
             toggleable off-canvas overlay below that, so the sidebar no
             longer disappears/overflows on small screens. --}}
        {{-- Bootstrap's "offcanvas-lg" responsive variant only resets to a
             normal static panel at lg+ when nested inside a
             .navbar-expand-lg container (it's built for navbar collapse
             menus) — this sidebar isn't, so that reset is done explicitly
             in company-theme.css instead of relying on the -lg suffix. --}}
        <nav
            class="offcanvas offcanvas-start company-sidebar border-end flex-shrink-0"
            style="width: 240px;"
            tabindex="-1"
            id="companySidebar"
            aria-labelledby="companySidebarLabel"
        >
            <div class="offcanvas-header d-lg-none">
                <h5 class="offcanvas-title" id="companySidebarLabel">Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#companySidebar" aria-label="Close"></button>
            </div>

            <div class="offcanvas-body p-0">
                <div class="list-group list-group-flush pt-2">
                    <a
                        href="{{ route('company.dashboard') }}"
                        class="list-group-item list-group-item-action {{ request()->routeIs('company.dashboard') ? 'active' : '' }}"
                    >
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>

                    <a
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ request()->routeIs('company.invoices.*') ? 'active-group' : '' }}"
                        data-bs-toggle="collapse"
                        href="#sidebar-invoices"
                        role="button"
                        aria-expanded="{{ request()->routeIs('company.invoices.*') ? 'true' : 'false' }}"
                    >
                        <span><i class="bi bi-clipboard-check me-2"></i> Invoices</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('company.invoices.*') ? 'show' : '' }}" id="sidebar-invoices">
                        <div class="list-group list-group-flush">
                            <a
                                href="{{ route('company.invoices.index') }}"
                                class="list-group-item list-group-item-action ps-5 {{ request()->routeIs('company.invoices.index') ? 'active' : '' }}"
                            >
                                Invoice Listing
                            </a>
                            <a
                                href="{{ route('company.invoices.create') }}"
                                class="list-group-item list-group-item-action ps-5 {{ request()->routeIs('company.invoices.create') ? 'active' : '' }}"
                            >
                                Create Invoice
                            </a>
                            <a
                                href="{{ route('company.invoices.bulk-upload.create') }}"
                                class="list-group-item list-group-item-action ps-5 {{ request()->routeIs('company.invoices.bulk-upload.*') ? 'active' : '' }}"
                            >
                                Bulk Uploads
                            </a>
                            <a
                                href="{{ route('company.invoices.drafts') }}"
                                class="list-group-item list-group-item-action ps-5 {{ request()->routeIs('company.invoices.drafts') || request()->routeIs('company.invoices.edit') ? 'active' : '' }}"
                            >
                                Drafts
                            </a>
                        </div>
                    </div>

                    <a
                        href="{{ route('company.customers.index') }}"
                        class="list-group-item list-group-item-action {{ request()->routeIs('company.customers.*') ? 'active' : '' }}"
                    >
                        <i class="bi bi-people me-2"></i> Customers
                    </a>

                    <a
                        href="{{ route('company.items.index') }}"
                        class="list-group-item list-group-item-action {{ request()->routeIs('company.items.*') ? 'active' : '' }}"
                    >
                        <i class="bi bi-box-seam me-2"></i> Items
                    </a>

                    <a
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ request()->routeIs('company.settings.*') ? 'active-group' : '' }}"
                        data-bs-toggle="collapse"
                        href="#sidebar-settings"
                        role="button"
                        aria-expanded="{{ request()->routeIs('company.settings.*') ? 'true' : 'false' }}"
                    >
                        <span><i class="bi bi-gear me-2"></i> System Settings</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('company.settings.*') ? 'show' : '' }}" id="sidebar-settings">
                        <div class="list-group list-group-flush">
                            <a
                                href="{{ route('company.settings.password.edit') }}"
                                class="list-group-item list-group-item-action ps-5 {{ request()->routeIs('company.settings.password.*') ? 'active' : '' }}"
                            >
                                Change Password
                            </a>
                        </div>
                    </div>

                    <a
                        href="{{ route('company.compliance-instructions') }}"
                        class="list-group-item list-group-item-action {{ request()->routeIs('company.compliance-instructions') ? 'active' : '' }}"
                    >
                        <i class="bi bi-shield-check me-2"></i> Compliance Instructions
                    </a>
                </div>
            </div>
        </nav>

        <div class="flex-grow-1 d-flex flex-column">
            <main class="flex-grow-1 p-4">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                @endif

                @if (session('submission_failed'))
                    <div class="alert alert-danger" role="alert">{{ session('submission_failed') }}</div>
                @endif

                @if (session('bulk_upload_created'))
                    <div class="alert alert-success" role="alert">
                        {{ session('bulk_upload_created') }} invoice(s) created from the uploaded file, saved as drafts.
                    </div>
                @endif

                @if (session('bulk_upload_errors'))
                    <div class="alert alert-danger" role="alert">
                        <p class="mb-1">{{ count(session('bulk_upload_errors')) }} row/invoice(s) from the uploaded file could not be imported:</p>
                        <ul class="mb-0 small">
                            @foreach (session('bulk_upload_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="company-footer py-3">
                <div class="container text-center text-muted small">
                    &copy; {{ date('Y') }} Invox Pakistan &mdash; All rights reserved.
                </div>
            </footer>
        </div>
    </div>

    <button type="button" class="back-to-top-btn" id="backToTopBtn" aria-label="Back to top">
        <i class="bi bi-chevron-up"></i>
    </button>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
        crossorigin="anonymous"></script>

    <script>
        // Purely cosmetic — show/hide the floating back-to-top button.
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
