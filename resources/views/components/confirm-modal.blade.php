{{--
    Generic Bootstrap confirmation modal (PRD #46): "Are you sure...?"
    with Cancel / Confirm buttons, submitting a real form (so the action is
    still a normal, CSRF-protected POST/PUT/DELETE — never trust a hidden UI
    button alone). Reused by company deletion here, and by "Mark as Paid"
    in later phases.
--}}
@props([
    'id',
    'title',
    'action',
    'method' => 'POST',
    'confirmLabel' => 'Confirm',
    'confirmClass' => 'btn-primary',
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ $action }}">
                @csrf
                @if (strtoupper($method) !== 'POST')
                    @method($method)
                @endif

                <div class="modal-header">
                    <h5 class="modal-title">{{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{ $slot }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn {{ $confirmClass }}">{{ $confirmLabel }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
