<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SEO Configuration & Third-Party Integration Identifiers
    |--------------------------------------------------------------------------
    |
    | All IDs are configurable via environment variables and will only be loaded
    | in front-end Blade templates if present and non-empty.
    |
    */

    'site_name' => env('SITE_NAME', env('APP_NAME', 'Invox Pakistan')),
    'site_url'  => env('SITE_URL', env('APP_URL', 'https://invox.pk')),
    
    'twitter_handle' => env('TWITTER_HANDLE', '@invoxpakistan'),
    'facebook_app_id' => env('FACEBOOK_APP_ID', null),

    'google_site_verification' => env('GOOGLE_SITE_VERIFICATION', null),
    'bing_site_verification'   => env('BING_SITE_VERIFICATION', null),

    'ga_id' => env('NEXT_PUBLIC_GA_ID', env('GA_ID', null)),
    'gtm_id' => env('NEXT_PUBLIC_GTM_ID', env('GTM_ID', null)),
    'meta_pixel_id' => env('NEXT_PUBLIC_META_PIXEL_ID', env('META_PIXEL_ID', null)),
    'linkedin_partner_id' => env('LINKEDIN_PARTNER_ID', null),
    'clarity_id' => env('CLARITY_ID', null),

    'google_ads_conversion_id' => env('GOOGLE_ADS_CONVERSION_ID', null),
    'google_ads_conversion_label' => env('GOOGLE_ADS_CONVERSION_LABEL', null),

    'default_meta' => [
        'robots' => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
        'og_type' => 'website',
        'og_image' => '/images/og-image.jpg',
        'og_image_width' => '1200',
        'og_image_height' => '630',
        'og_image_alt' => 'Invox Pakistan - Company Subscription & Invoicing Platform',
        'twitter_card' => 'summary_large_image',
        'twitter_image' => '/images/twitter-image.jpg',
        'twitter_image_alt' => 'Invox Pakistan - Company Subscription & Invoicing Platform',
    ],
];
