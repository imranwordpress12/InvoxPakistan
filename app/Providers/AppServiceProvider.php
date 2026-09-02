<?php

namespace App\Providers;

use App\Domain\Invoices\Fbr\FbrInvoiceSubmitter;
use App\Domain\Invoices\Fbr\SandboxFbrInvoiceSubmitter;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // FBR Sandbox integration (API Doc.pdf) — real HTTP calls to
        // FBR's Digital Invoicing Sandbox API, per company via
        // Company::fbr_token_sandbox. Swapping to Production
        // later (once a company has a fbr_token_production and this is
        // deliberately extended to support it) stays a binding change
        // here, same as when this was the stub.
        $this->app->bind(FbrInvoiceSubmitter::class, SandboxFbrInvoiceSubmitter::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The whole UI is Bootstrap (PRD #3/#43) — use the framework's
        // built-in Bootstrap 5 pagination view everywhere instead of the
        // Tailwind default, no custom package/view needed.
        Paginator::useBootstrapFive();
    }
}
