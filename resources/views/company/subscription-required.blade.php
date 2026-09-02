@extends('layouts.company')

@section('title', 'Subscription Required')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7 text-center">
                <i class="bi bi-exclamation-triangle-fill display-4 text-danger"></i>
                <h1 class="h4 mt-3">Subscription Required</h1>

                {{-- Exact wording from PRD #10. --}}
                <p class="text-muted">
                    Your subscription has expired or payment is pending. Please contact the
                    administrator to renew your subscription.
                </p>

                @if ($subscription)
                    <div class="card shadow-sm text-start mt-4">
                        <div class="card-header">Subscription</div>
                        <div class="card-body">
                            <table class="table table-sm mb-0">
                                <tbody>
                                    <tr><th style="width: 160px;">Type</th><td>{{ ucfirst($subscription->type) }}</td></tr>
                                    <tr><th>Status</th><td><x-status-badge :status="$subscription->status" /></td></tr>
                                    <tr><th>Ended</th><td>{{ $subscription->ends_at->format('d-M-Y') }}</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
