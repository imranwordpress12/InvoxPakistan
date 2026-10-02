<!-- Cookie Consent Banner -->
<div id="cookie-consent-banner" class="fixed-bottom p-3 bg-dark text-white border-top shadow-lg d-none" style="z-index: 1050;" role="dialog" aria-live="polite" aria-label="Cookie consent banner">
    <div class="container">
        <div class="row align-items-center gy-2">
            <div class="col-lg-8">
                <p class="mb-0 small text-slate-200">
                    <i class="bi bi-shield-check text-success me-1"></i>
                    <strong>Privacy &amp; Cookie Consent:</strong> Invox Pakistan uses cookies to optimize site experience, perform analytics, and power security. Learn more in our <a href="{{ route('public.privacy') }}" class="text-info text-decoration-underline">Privacy Policy</a>.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <button id="btn-accept-all-cookies" class="btn btn-primary btn-sm px-3 me-2 fw-medium">
                    Accept All
                </button>
                <button id="btn-accept-essential-cookies" class="btn btn-outline-light btn-sm px-3">
                    Essential Only
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var banner = document.getElementById('cookie-consent-banner');
    var consent = localStorage.getItem('invox_cookie_consent');

    if (!consent) {
        banner.classList.remove('d-none');
    }

    document.getElementById('btn-accept-all-cookies').addEventListener('click', function () {
        localStorage.setItem('invox_cookie_consent', 'all');
        banner.classList.add('d-none');
        if (window.trackGaEvent) {
            window.trackGaEvent('cookie_consent', { status: 'accepted_all' });
        }
    });

    document.getElementById('btn-accept-essential-cookies').addEventListener('click', function () {
        localStorage.setItem('invox_cookie_consent', 'essential');
        banner.classList.add('d-none');
        if (window.trackGaEvent) {
            window.trackGaEvent('cookie_consent', { status: 'accepted_essential' });
        }
    });
});
</script>
