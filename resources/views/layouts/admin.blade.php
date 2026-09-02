<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin') &mdash; {{ config('app.name', 'Invox Pakistan') }}</title>

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
    {{-- Admin-only theme — a separate file from both app.css and
         company-theme.css on purpose. Nothing in this file can affect
         the Company side because it is never linked from there, and
         vice versa. --}}
    <link href="{{ asset('css/admin-theme.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light admin-navbar">
        <div class="container-fluid">
            <button
                class="btn btn-sm sidebar-toggle-btn d-lg-none me-2"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#adminSidebar"
                aria-controls="adminSidebar"
                aria-label="Toggle navigation"
            >
                <i class="bi bi-list fs-5"></i>
            </button>

            <a class="navbar-brand fw-semibold" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-building-check me-1"></i> Invox Pakistan
                <span class="admin-badge ms-1">Admin</span>
            </a>

            <div class="d-flex align-items-center ms-auto">
                <span class="admin-user-name small me-3 d-none d-sm-inline">{{ auth()->user()->name }}</span>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn" title="Logout" aria-label="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="d-flex">
        {{-- Sidebar: Dashboard / Companies / Audit Logs only (PRD #12).
             offcanvas (Bootstrap 5.2+, native — no new JS): a normal
             static sidebar at lg+ (reset explicitly in admin-theme.css —
             see its comment for why, not via the offcanvas-lg suffix)
             and a toggleable off-canvas overlay below that. --}}
        <nav
            class="offcanvas offcanvas-start admin-sidebar border-end flex-shrink-0"
            style="width: 220px;"
            tabindex="-1"
            id="adminSidebar"
            aria-labelledby="adminSidebarLabel"
        >
            <div class="offcanvas-header d-lg-none">
                <h5 class="offcanvas-title" id="adminSidebarLabel">Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Close"></button>
            </div>

            <div class="offcanvas-body p-0">
                <div class="list-group list-group-flush pt-2">
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="list-group-item list-group-item-action {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                    >
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>

                    <a
                        href="{{ route('admin.companies.index') }}"
                        class="list-group-item list-group-item-action {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}"
                    >
                        <i class="bi bi-building me-2"></i> Companies
                    </a>

                    <a
                        href="{{ route('admin.audit-logs.index') }}"
                        class="list-group-item list-group-item-action {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}"
                    >
                        <i class="bi bi-journal-text me-2"></i> Audit Logs
                    </a>
                </div>
            </div>
        </nav>

        <main class="flex-grow-1 p-4">
            @if (session('status'))
                <div class="alert alert-success" role="alert">{{ session('status') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
        crossorigin="anonymous"></script>

    @stack('scripts')
</body>
</html>
