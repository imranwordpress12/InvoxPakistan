{{--
    Centralised status -> Bootstrap badge color mapping (PRD #43):
    Paid/Active -> success, Pending -> warning, Expired -> danger,
    Cancelled/Inactive -> secondary. Shared by companies, subscriptions,
    transactions, and (Company Dashboard PRD) invoices everywhere a status
    is shown.
--}}
@props(['status'])

@php
    $classes = match ($status) {
        'active', 'paid', 'successful' => 'text-bg-success',
        'pending', 'draft' => 'text-bg-warning',
        'expired', 'failed' => 'text-bg-danger',
        'cancelled', 'inactive' => 'text-bg-secondary',
        'submitted' => 'text-bg-info',
        default => 'text-bg-light border',
    };
@endphp

<span {{ $attributes->merge(['class' => "badge $classes"]) }}>{{ $status ? ucfirst($status) : '—' }}</span>
