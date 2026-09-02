@extends('layouts.company')

@section('title', 'Dashboard')

@section('content')
    {{--
        Subscription expiry warning kept from the original Subscription/
        Payment System dashboard — see CHANGELOG_PROJECT.md for why the
        fuller subscription details table was dropped from this page in
        favour of matching the Company Dashboard PRD's screenshot.
    --}}
    @if ($subscription && ! $subscription->isActive())
        <div class="alert alert-danger">
            Your subscription has expired or payment is pending. Please contact the administrator
            to renew your subscription.
        </div>
    @elseif ($subscription && $subscription->daysRemaining() <= 7)
        <div class="alert alert-warning">
            Your subscription will expire in {{ $subscription->daysRemaining() }}
            day{{ $subscription->daysRemaining() === 1 ? '' : 's' }}. Please contact the administrator
            for renewal.
        </div>
    @endif

    <form method="GET" action="{{ route('company.dashboard') }}" class="d-flex flex-wrap align-items-end gap-2 justify-content-end mb-4">
        <div>
            <label for="date_from" class="form-label small mb-1">Date From:</label>
            <input type="date" id="date_from" name="date_from" value="{{ $dateFrom->toDateString() }}" class="form-control">
        </div>
        <div>
            <label for="date_to" class="form-label small mb-1">Date To:</label>
            <input type="date" id="date_to" name="date_to" value="{{ $dateTo->toDateString() }}" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    <h2 class="h5 text-primary fw-bold mb-3">Stats of Invoices Submitted to FBR</h2>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold h5 mb-1">Total Invoices</div>
                            <div class="h3 mb-2">{{ $totalStats['count'] }}</div>
                        </div>
                        <div class="stat-icon bg-primary-subtle text-primary">
                            <i class="bi bi-envelope-fill"></i>
                        </div>
                    </div>
                    <div class="small text-muted">
                        Total Amount: <strong>Rs {{ number_format($totalStats['total_amount'], 2) }}</strong><br>
                        Total Excl. ST: <strong>Rs {{ number_format($totalStats['total_excl_st'], 2) }}</strong><br>
                        Total Sales Tax: <strong>Rs {{ number_format($totalStats['total_sales_tax'], 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold h5 mb-1">Total Successful Invoices</div>
                            <div class="h3 mb-2 text-success">{{ $successfulStats['count'] }}</div>
                        </div>
                        <div class="stat-icon bg-success-subtle text-success">
                            <i class="bi bi-check-lg"></i>
                        </div>
                    </div>
                    <div class="small text-muted">
                        Total Amount: <strong>Rs {{ number_format($successfulStats['total_amount'], 2) }}</strong><br>
                        Total Excl. ST: <strong>Rs {{ number_format($successfulStats['total_excl_st'], 2) }}</strong><br>
                        Total Sales Tax: <strong>Rs {{ number_format($successfulStats['total_sales_tax'], 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold h5 mb-1">Total Failed Invoices</div>
                            <div class="h3 mb-2 text-danger">{{ $failedStats['count'] }}</div>
                        </div>
                        <div class="stat-icon bg-danger-subtle text-danger">
                            <i class="bi bi-x-lg"></i>
                        </div>
                    </div>
                    <div class="small text-muted">
                        Total Amount: <strong>Rs {{ number_format($failedStats['total_amount'], 2) }}</strong><br>
                        Total Excl. ST: <strong>Rs {{ number_format($failedStats['total_excl_st'], 2) }}</strong><br>
                        Total Sales Tax: <strong>Rs {{ number_format($failedStats['total_sales_tax'], 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h3 class="h6 text-primary fw-bold mb-3">Daily Invoice Submission Status</h3>
            <canvas id="dailyStatusChart" height="90"></canvas>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="h6 text-primary fw-bold mb-3">Daily Invoice Amounts (Successful vs Failed)</h3>
            <canvas id="dailyAmountsChart" height="90"></canvas>
        </div>
    </div>
@endsection

@push('scripts')
{{-- Only new client-side dependency added for this page: Chart.js via
     CDN (same "no build step" approach already used for Bootstrap), so
     the two daily charts required by PRD #4 can actually render. --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<script>
    const dailyStatus = @json($dailyStatus->values());
    const dailyAmounts = @json($dailyAmounts->values());

    new Chart(document.getElementById('dailyStatusChart'), {
        type: 'bar',
        data: {
            labels: dailyStatus.map(d => d.date),
            datasets: [
                { label: 'Successful', data: dailyStatus.map(d => d.successful), backgroundColor: '#0d1b4c' },
                { label: 'Failed', data: dailyStatus.map(d => d.failed), backgroundColor: '#c7d2fe' },
            ],
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });

    new Chart(document.getElementById('dailyAmountsChart'), {
        type: 'bar',
        data: {
            labels: dailyAmounts.map(d => d.date),
            datasets: [
                { label: 'Successful Amount', data: dailyAmounts.map(d => d.successful_amount), backgroundColor: '#22c55e' },
                { label: 'Failed Amount', data: dailyAmounts.map(d => d.failed_amount), backgroundColor: '#ef4444' },
            ],
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } },
        },
    });
</script>
@endpush
