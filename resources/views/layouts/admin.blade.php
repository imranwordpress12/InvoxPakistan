<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin') &mdash; {{ config('app.name', 'Invox Pakistan') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin-theme.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Top Admin Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light admin-navbar sticky-top">
        <div class="container-fluid px-3 px-lg-4">
            <button class="btn btn-sm sidebar-toggle-btn d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Toggle navigation">
                <i class="bi bi-list fs-5"></i>
            </button>

            <a class="navbar-brand d-flex align-items-center" href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('images/logo.jpeg') }}" alt="Invox Pakistan Logo">
                <span class="admin-badge">Admin Hub</span>
            </a>

            <div class="d-flex align-items-center ms-auto gap-3">
                <div class="d-none d-sm-flex align-items-center text-slate-700 small">
                    <i class="bi bi-person-badge text-primary me-2 fs-5"></i>
                    <span class="fw-bold">{{ auth()->user()->name }}</span>
                </div>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn" title="Logout from Admin" aria-label="Logout">
                        <i class="bi bi-box-arrow-right fs-6"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="d-flex flex-grow-1">
        <!-- Sidebar Navigation -->
        <nav class="offcanvas offcanvas-start admin-sidebar flex-shrink-0" style="width: 250px;" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
            <div class="offcanvas-header border-bottom border-slate-800">
                <div class="d-flex align-items-center">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="Invox Pakistan Logo" width="28" height="28" class="rounded me-2">
                    <h5 class="offcanvas-title text-white h6 mb-0" id="adminSidebarLabel">Admin Control Center</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Close"></button>
            </div>

            <div class="offcanvas-body p-0">
                <div class="py-3 px-3">
                    <span class="text-uppercase tracking-wider extra-small fw-bold text-slate-400 mb-2 d-block px-2">Main Navigation</span>
                    <div class="list-group list-group-flush rounded-3 overflow-hidden">
                        <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>

                        <a href="{{ route('admin.companies.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}">
                            <i class="bi bi-building me-2"></i> Companies
                        </a>

                        <a href="{{ route('admin.audit-logs.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
                            <i class="bi bi-journal-text me-2"></i> System Audit Logs
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Workspace -->
        <main class="flex-grow-1 p-3 p-md-4 bg-slate-50">
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    @stack('scripts')
</body>
</html>
