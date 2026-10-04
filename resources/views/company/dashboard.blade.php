@extends('layouts.company')

@section('title', 'Company Dashboard')

@section('content')
    @if ($subscription && ! $subscription->isActive())
        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Subscription Notice:</strong> Your subscription has expired or payment is pending. Please contact the administrator to renew.
        </div>
    @elseif ($subscription && $subscription->daysRemaining() <= 7)
        <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-4">
            <i class="bi bi-clock-history me-2"></i>
            <strong>Renewal Alert:</strong> Your subscription will expire in {{ $subscription->daysRemaining() }} day{{ $subscription->daysRemaining() === 1 ? '' : 's' }}.
        </div>
    @endif

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between pb-3 mb-4 border-bottom">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Company Dashboard</h1>
            <p class="text-muted small mb-0">Overview of FBR invoice submissions, total volumes, and daily performance metrics.</p>
            <p id="dashboard-refresh-status" class="text-muted extra-small mb-0" aria-live="polite">Live data enabled; refreshing every 30 seconds.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <form method="GET" action="{{ route('company.dashboard') }}" class="d-flex flex-wrap align-items-center gap-2">
                <div>
                    <label for="date_from" class="visually-hidden">From</label>
                    <input type="date" id="date_from" name="date_from" value="{{ $dateFrom->toDateString() }}" class="form-control form-control-sm">
                </div>
                <span class="text-muted extra-small">to</span>
                <div>
                    <label for="date_to" class="visually-hidden">To</label>
                    <input type="date" id="date_to" name="date_to" value="{{ $dateTo->toDateString() }}" class="form-control form-control-sm">
                </div>
                <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold">Filter</button>
            </form>
        </div>
    </div>

    <!-- FBR Invoice Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="text-uppercase tracking-wider extra-small fw-bold text-muted">Total Invoices</span>
                            <div class="display-6 fw-bold text-dark my-1" data-dashboard-stat="total.count">{{ $totalStats['count'] }}</div>
                        </div>
                        <div class="stat-icon bg-primary-subtle text-primary">
                            <i class="bi bi-receipt"></i>
                        </div>
                    </div>
                    <div class="pt-2 border-top extra-small text-muted d-flex flex-column gap-1">
                        <div class="d-flex justify-content-between"><span>Total Amount:</span> <strong class="text-dark" data-dashboard-stat="total.total_amount">Rs {{ number_format($totalStats['total_amount'], 2) }}</strong></div>
                        <div class="d-flex justify-content-between"><span>Excl. Sales Tax:</span> <strong class="text-dark" data-dashboard-stat="total.total_excl_st">Rs {{ number_format($totalStats['total_excl_st'], 2) }}</strong></div>
                        <div class="d-flex justify-content-between"><span>Total Sales Tax:</span> <strong class="text-primary" data-dashboard-stat="total.total_sales_tax">Rs {{ number_format($totalStats['total_sales_tax'], 2) }}</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="text-uppercase tracking-wider extra-small fw-bold text-muted">Successful Invoices</span>
                            <div class="display-6 fw-bold text-success my-1" data-dashboard-stat="successful.count">{{ $successfulStats['count'] }}</div>
                        </div>
                        <div class="stat-icon bg-success-subtle text-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                    <div class="pt-2 border-top extra-small text-muted d-flex flex-column gap-1">
                        <div class="d-flex justify-content-between"><span>Total Amount:</span> <strong class="text-dark" data-dashboard-stat="successful.total_amount">Rs {{ number_format($successfulStats['total_amount'], 2) }}</strong></div>
                        <div class="d-flex justify-content-between"><span>Excl. Sales Tax:</span> <strong class="text-dark" data-dashboard-stat="successful.total_excl_st">Rs {{ number_format($successfulStats['total_excl_st'], 2) }}</strong></div>
                        <div class="d-flex justify-content-between"><span>Total Sales Tax:</span> <strong class="text-success" data-dashboard-stat="successful.total_sales_tax">Rs {{ number_format($successfulStats['total_sales_tax'], 2) }}</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-danger">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="text-uppercase tracking-wider extra-small fw-bold text-muted">Failed Submissions</span>
                            <div class="display-6 fw-bold text-danger my-1" data-dashboard-stat="failed.count">{{ $failedStats['count'] }}</div>
                        </div>
                        <div class="stat-icon bg-danger-subtle text-danger">
                            <i class="bi bi-x-circle"></i>
                        </div>
                    </div>
                    <div class="pt-2 border-top extra-small text-muted d-flex flex-column gap-1">
                        <div class="d-flex justify-content-between"><span>Total Amount:</span> <strong class="text-dark" data-dashboard-stat="failed.total_amount">Rs {{ number_format($failedStats['total_amount'], 2) }}</strong></div>
                        <div class="d-flex justify-content-between"><span>Excl. Sales Tax:</span> <strong class="text-dark" data-dashboard-stat="failed.total_excl_st">Rs {{ number_format($failedStats['total_excl_st'], 2) }}</strong></div>
                        <div class="d-flex justify-content-between"><span>Total Sales Tax:</span> <strong class="text-danger" data-dashboard-stat="failed.total_sales_tax">Rs {{ number_format($failedStats['total_sales_tax'], 2) }}</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Containers -->
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h2 class="h6 text-dark fw-bold mb-0"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Daily Invoice Submission Status</h2>
                </div>
                <div class="card-body">
                    <canvas id="dailyStatusChart" height="180"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h2 class="h6 text-dark fw-bold mb-0"><i class="bi bi-graph-up text-success me-2"></i>Daily Invoice Amounts (Successful vs Failed)</h2>
                </div>
                <div class="card-body">
                    <canvas id="dailyAmountsChart" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<script>
    const dailyStatus = @json($dailyStatus->values());
    const dailyAmounts = @json($dailyAmounts->values());

    const dailyStatusChart = new Chart(document.getElementById('dailyStatusChart'), {
        type: 'bar',
        data: {
            labels: dailyStatus.map(d => d.date),
            datasets: [
                { label: 'Successful', data: dailyStatus.map(d => d.successful), backgroundColor: '#0284c7', borderRadius: 4 },
                { label: 'Failed', data: dailyStatus.map(d => d.failed), backgroundColor: '#f43f5e', borderRadius: 4 },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });

    const dailyAmountsChart = new Chart(document.getElementById('dailyAmountsChart'), {
        type: 'bar',
        data: {
            labels: dailyAmounts.map(d => d.date),
            datasets: [
                { label: 'Successful Amount', data: dailyAmounts.map(d => d.successful_amount), backgroundColor: '#10b981', borderRadius: 4 },
                { label: 'Failed Amount', data: dailyAmounts.map(d => d.failed_amount), backgroundColor: '#ef4444', borderRadius: 4 },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } },
        },
    });

    const refreshStatus = document.getElementById('dashboard-refresh-status');
    const statsUrl = @json(route('company.dashboard.stats'));
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    const statFields = ['count', 'total_amount', 'total_excl_st', 'total_sales_tax'];

    function formatCurrency(amount) {
        return `Rs ${Number(amount).toLocaleString('en-PK', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;
    }

    async function refreshDashboardStats() {
        const url = new URL(statsUrl, window.location.origin);
        url.searchParams.set('date_from', dateFromInput.value);
        url.searchParams.set('date_to', dateToInput.value);

        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
                cache: 'no-store',
            });

            if (!response.ok) {
                throw new Error(`Dashboard stats request failed with HTTP ${response.status}.`);
            }

            const stats = await response.json();

            for (const group of ['total', 'successful', 'failed']) {
                for (const field of statFields) {
                    const element = document.querySelector(`[data-dashboard-stat="${group}.${field}"]`);
                    const value = stats[`${group}Stats`][field];

                    element.textContent = field === 'count' ? value : formatCurrency(value);
                }
            }

            dailyStatusChart.data.labels = stats.dailyStatus.map((day) => day.date);
            dailyStatusChart.data.datasets[0].data = stats.dailyStatus.map((day) => day.successful);
            dailyStatusChart.data.datasets[1].data = stats.dailyStatus.map((day) => day.failed);
            dailyStatusChart.update();

            dailyAmountsChart.data.labels = stats.dailyAmounts.map((day) => day.date);
            dailyAmountsChart.data.datasets[0].data = stats.dailyAmounts.map((day) => day.successful_amount);
            dailyAmountsChart.data.datasets[1].data = stats.dailyAmounts.map((day) => day.failed_amount);
            dailyAmountsChart.update();

            refreshStatus.textContent = `Live data updated at ${new Date().toLocaleTimeString()}.`;
            refreshStatus.classList.remove('text-danger');
            refreshStatus.classList.add('text-muted');
        } catch (error) {
            console.error('Unable to refresh company dashboard statistics.', error);
            refreshStatus.textContent = 'Live data update failed. Retrying in 30 seconds.';
            refreshStatus.classList.remove('text-muted');
            refreshStatus.classList.add('text-danger');
        }
    }

    window.setInterval(refreshDashboardStats, 30_000);
</script>
@endpush
