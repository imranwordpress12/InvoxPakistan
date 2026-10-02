<?php

namespace App\Services;

class SeoService
{
    protected string $siteName;
    protected string $baseUrl;
    protected string $defaultOgImage;
    protected string $twitterHandle;

    public function __construct()
    {
        $this->siteName = config('app.name', 'Invox Pakistan');
        $this->baseUrl = config('app.url', 'https://invox.pk');
        $this->defaultOgImage = asset('images/og-image.jpg');
        $this->twitterHandle = config('seo.twitter_handle', '@invoxpakistan');
    }

    /**
     * Format page title according to SEO standard: Primary Keyword / Benefit | Brand Name
     */
    public function formatTitle(string $title, bool $includeBrand = true): string
    {
        if (!$includeBrand || str_contains($title, $this->siteName)) {
            return $title;
        }

        return "{$title} | {$this->siteName}";
    }

    /**
     * Get clean canonical URL (strips tracking parameters)
     */
    public function getCanonicalUrl(?string $path = null): string
    {
        $url = $path ? url($path) : request()->url();
        $parsed = parse_url($url);
        
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'] ?? parse_url($this->baseUrl, PHP_URL_HOST);
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        $path = $parsed['path'] ?? '/';

        // Keep pagination query param if present
        $queryParams = [];
        if (request()->has('page')) {
            $queryParams['page'] = request()->get('page');
        }

        $queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';

        return "{$scheme}://{$host}{$port}{$path}{$queryString}";
    }

    /**
     * Generate Schema.org Organization JSON-LD array
     */
    public function getOrganizationSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $this->siteName,
            'url' => $this->baseUrl,
            'logo' => asset('images/logo.jpeg'),
            'description' => 'Invox Pakistan is an enterprise-grade company subscription, compliance & automated invoicing management platform for businesses in Pakistan.',
            'foundingDate' => '2024',
            'sameAs' => [
                'https://www.linkedin.com/company/invoxpakistan',
                'https://www.facebook.com/invoxpakistan',
                'https://twitter.com/invoxpakistan',
                'https://www.instagram.com/invoxpakistan'
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => '+92-42-35900000',
                'contactType' => 'customer support',
                'areaServed' => 'PK',
                'availableLanguage' => ['en', 'ur']
            ]
        ];
    }

    /**
     * Generate Schema.org SoftwareApplication / WebApplication JSON-LD array
     */
    public function getSoftwareApplicationSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => 'Invox Pakistan Billing & Subscription Management Platform',
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web',
            'url' => $this->baseUrl,
            'description' => 'Automated invoicing, FBR digital tax integration, client subscription management, and payment reconciliation software tailored for Pakistani enterprises.',
            'offers' => [
                '@type' => 'AggregateOffer',
                'priceCurrency' => 'PKR',
                'lowPrice' => '4999',
                'highPrice' => '24999',
                'offerCount' => '3'
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '4.9',
                'ratingCount' => '128'
            ],
            'featureList' => [
                'Automated Recurring Invoicing',
                'FBR Digital Tax Integration',
                'Multi-Currency Support',
                'JazzCash, EasyPaisa & Bank Transfer Reconciliation',
                'Automated Dunning & Payment Reminders',
                'Client Self-Service Portal'
            ]
        ];
    }

    /**
     * Generate Schema.org WebSite JSON-LD with SearchAction
     */
    public function getWebSiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $this->siteName,
            'url' => $this->baseUrl,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $this->baseUrl . '/search?q={search_term_string}'
                ],
                'query-input' => 'required name=search_term_string'
            ]
        ];
    }

    /**
     * Generate Schema.org BreadcrumbList JSON-LD array
     */
    public function getBreadcrumbsSchema(array $crumbs): array
    {
        $itemList = [];
        $position = 1;

        // Always start with Home
        $itemList[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => 'Home',
            'item' => $this->baseUrl
        ];

        foreach ($crumbs as $name => $url) {
            $itemList[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $name,
                'item' => str_starts_with($url, 'http') ? $url : url($url)
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemList
        ];
    }

    /**
     * Generate Schema.org Article JSON-LD array
     */
    public function getArticleSchema(array $article): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $article['url'] ?? request()->url()
            ],
            'headline' => $article['title'],
            'description' => $article['description'],
            'image' => $article['image'] ?? $this->defaultOgImage,
            'datePublished' => $article['published_time'],
            'dateModified' => $article['modified_time'] ?? $article['published_time'],
            'author' => [
                '@type' => 'Person',
                'name' => $article['author_name'] ?? 'Invox Editorial Team',
                'jobTitle' => $article['author_role'] ?? 'Fintech & Compliance Specialist'
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->siteName,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('images/logo.jpeg')
                ]
            ]
        ];
    }

    /**
     * Generate Schema.org FAQPage JSON-LD array
     */
    public function getFaqSchema(array $faqs): array
    {
        $mainEntity = [];

        foreach ($faqs as $faq) {
            $mainEntity[] = [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer']
                ]
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $mainEntity
        ];
    }
}
