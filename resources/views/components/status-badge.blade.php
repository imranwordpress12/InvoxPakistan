{{--
    Centralised status -> Modern Bootstrap 5.3 subtle badge mapping:
    Paid/Active -> success-subtle, Due/Draft -> warning-subtle, Expired/Failed -> danger-subtle,
    Cancelled/Inactive -> secondary-subtle, Submitted -> info-subtle.
--}}
@props(['status'])

@php
    $classes = match (strtolower((string) $status)) {
        'active', 'paid', 'successful' => 'bg-success-subtle text-success border border-success-subtle',
        'due', 'draft' => 'bg-warning-subtle text-dark border border-warning-subtle',
        'pending', 'expired', 'failed' => 'bg-danger-subtle text-danger border border-danger-subtle',
        'cancelled', 'inactive' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
        'submitted' => 'bg-info-subtle text-info border border-info-subtle',
        default => 'bg-light text-dark border',
    };

    $icon = match (strtolower((string) $status)) {
        'active', 'paid', 'successful' => 'bi-check-circle-fill',
        'due', 'draft' => 'bi-clock-history',
        'pending', 'expired', 'failed' => 'bi-exclamation-triangle-fill',
        'cancelled', 'inactive' => 'bi-dash-circle-fill',
        'submitted' => 'bi-send-fill',
        default => 'bi-dot',
    };
@endphp

<span {{ $attributes->merge(['class' => "badge rounded-pill fw-semibold px-2.5 py-1.5 align-middle $classes"]) }}>
    <i class="bi {{ $icon }} me-1"></i>{{ $status ? ucfirst($status) : '—' }}
</span>
