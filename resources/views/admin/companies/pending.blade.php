@extends('layouts.admin')

@section('title', 'Pending Companies')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Pending Companies</h1>
        <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary btn-sm">
            Back to Companies
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                {{ $error }}
            @endforeach
        </div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.companies.pending') }}" class="row g-2">
                <div class="col-md-6">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control"
                        placeholder="Search by name or email"
                    >
                </div>
                <div class="col-md-4">
                    <input
                        type="date"
                        name="due_by"
                        value="{{ request('due_by') }}"
                        class="form-control"
                        aria-label="Due by"
                    >
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary flex-grow-1">Filter</button>
                    @if (request()->anyFilled(['search', 'due_by']))
                        <a href="{{ route('admin.companies.pending') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Invoice</th>
                        <th>Subscription</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($companies as $company)
                        @php
                            $subscription = $company->latestSubscription;
                            // The eager-load in CompanyController::pending() already
                            // constrains this to pending-only, so first() is safe.
                            $transaction = $subscription?->transactions->first();
                            $dueDate = $transaction?->due_at ?? $subscription?->ends_at;
                            $amount = $transaction?->amount ?? $subscription?->amount;
                        @endphp
                        <tr>
                            <td>{{ $company->name }}</td>
                            <td>{{ $transaction?->invoice_number ?? '—' }}</td>
                            <td>{{ $subscription ? ucfirst($subscription->type) : '—' }}</td>
                            <td>{{ $amount !== null ? 'PKR '.number_format($amount, 2) : '—' }}</td>
                            <td>{{ $dueDate?->format('d-M-Y') ?? '—' }}</td>
                            <td><x-status-badge :status="$subscription?->status" /></td>
                            <td class="text-end">
                                <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-sm btn-outline-secondary">
                                    View
                                </a>
                                {{-- Nothing to mark paid until this company actually
                                     has a pending invoice — normally created
                                     automatically by Phase 11's scheduler, but a
                                     subscription can still be pending without one
                                     (e.g. it lapsed before the scheduler ever ran
                                     against it). --}}
                                @if ($transaction)
                                    @include('admin.companies._mark-paid-button')
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No companies need attention right now.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $companies->links() }}
    </div>
@endsection
