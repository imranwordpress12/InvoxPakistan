<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | FBR Digital Invoicing (DI) API
    |--------------------------------------------------------------------------
    |
    | URLs from "Technical Specification for DI API" v1.12 (API Doc.pdf,
    | Section 4.1/4.2) — these stay the same regardless of environment;
    | FBR routes Sandbox vs Production based on which token is used, not a
    | different domain. Kept env-overridable (not hard-coded in the
    | submitter class) purely so a URL change from FBR never needs a code
    | deploy. Per-company tokens live in Company, never here.
    |
    */
    'fbr' => [
        'sandbox_post_url' => env('FBR_SANDBOX_POST_URL', 'https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata_sb'),
        'sandbox_validate_url' => env('FBR_SANDBOX_VALIDATE_URL', 'https://gw.fbr.gov.pk/di_data/v1/di/validateinvoicedata_sb'),
        // Not used yet (this task is Sandbox-only per its own scope) —
        // present so switching a company to Production later is a matter
        // of using fbr_token_production and these URLs, not new plumbing.
        'production_post_url' => env('FBR_PRODUCTION_POST_URL', 'https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata'),
        'production_validate_url' => env('FBR_PRODUCTION_VALIDATE_URL', 'https://gw.fbr.gov.pk/di_data/v1/di/validateinvoicedata'),
        'timeout' => env('FBR_HTTP_TIMEOUT', 30),

        // Digital Invoicing *Reference* APIs (API Doc.pdf, Section 5) — a
        // separate host/path family (`pdi/v1`, `pdi/v2`) from the
        // submission endpoints above (`di_data/v1/di/...`). Used by
        // FbrReferenceService for the form's cascading dropdowns
        // (province, invoice type, HS_UOM, SaleTypeToRate, SRO, SRO Item, ...).
        'reference_base_url' => env('FBR_REFERENCE_BASE_URL', 'https://gw.fbr.gov.pk/pdi'),

        // HS_UOM (Section 5.9) requires an `annexure_id` query param that
        // this document never explains how to derive — every sample URL
        // it gives (including its own) simply uses 3. Kept as a single,
        // honestly-flagged, overridable assumption rather than guessed
        // per-call; see FbrReferenceService::hsUom().
        'hs_uom_annexure_id' => env('FBR_HS_UOM_ANNEXURE_ID', 3),

        // How long independent reference lists (provinces, transaction
        // types, ...) are cached vs. context-dependent ones (HS_UOM,
        // SaleTypeToRate, SRO, SRO Item — cache key includes their
        // parameters, see Section 15). Independent lists change rarely;
        // dependent ones are kept shorter since they're param-keyed and
        // any one entry going stale only affects that one combination.
        'reference_cache_ttl' => env('FBR_REFERENCE_CACHE_TTL', 43200), // 12h
        'dependent_reference_cache_ttl' => env('FBR_DEPENDENT_REFERENCE_CACHE_TTL', 3600), // 1h
    ],

];
