<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SeoFilesController extends Controller
{
    /**
     * Serve /robots.txt
     */
    public function robots(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            '',
            '# Disallow private and administrative areas',
            'Disallow: /admin/',
            'Disallow: /company/',
            'Disallow: /api/private/',
            'Disallow: /account/',
            'Disallow: /search',
            'Disallow: /dev/',
            '',
            '# Sitemap location',
            'Sitemap: ' . url('sitemap.xml'),
            'Sitemap: ' . url('sitemap-index.xml'),
        ]);

        return response($content, 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    /**
     * Serve /sitemap-index.xml
     */
    public function sitemapIndex(): Response
    {
        $lastMod = date('Y-m-d');
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $xml .= '  <sitemap>' . "\n";
        $xml .= '    <loc>' . url('sitemap-pages.xml') . '</loc>' . "\n";
        $xml .= '    <lastmod>' . $lastMod . '</lastmod>' . "\n";
        $xml .= '  </sitemap>' . "\n";
        $xml .= '  <sitemap>' . "\n";
        $xml .= '    <loc>' . url('sitemap-blog.xml') . '</loc>' . "\n";
        $xml .= '    <lastmod>' . $lastMod . '</lastmod>' . "\n";
        $xml .= '  </sitemap>' . "\n";
        $xml .= '</sitemapindex>';

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Serve /sitemap.xml (or main sitemap)
     */
    public function sitemap(): Response
    {
        return $this->sitemapPages();
    }

    /**
     * Serve /sitemap-pages.xml
     */
    public function sitemapPages(): Response
    {
        $pages = [
            '/' => '1.0',
            '/features' => '0.9',
            '/pricing' => '0.9',
            '/solutions' => '0.8',
            '/industries' => '0.8',
            '/integrations' => '0.8',
            '/about' => '0.7',
            '/contact' => '0.8',
            '/resources' => '0.8',
            '/documentation' => '0.8',
            '/faq' => '0.8',
            '/privacy-policy' => '0.3',
            '/terms' => '0.3',
            '/sitemap' => '0.5',
        ];

        $lastMod = date('Y-m-d');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($pages as $path => $priority) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . url($path) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $lastMod . '</lastmod>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>' . $priority . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Serve /sitemap-blog.xml
     */
    public function sitemapBlog(): Response
    {
        $blogPosts = [
            '/blog' => '0.8',
            '/blog/category/compliance' => '0.7',
            '/blog/category/software' => '0.7',
            '/blog/category/fintech' => '0.7',
            '/blog/fbr-digital-invoicing-compliance-guide-2026' => '0.8',
            '/blog/saas-subscription-billing-automation-best-practices' => '0.8',
            '/blog/jazzcash-easypaisa-bank-reconciliation-guide' => '0.8',
        ];

        $lastMod = date('Y-m-d');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($blogPosts as $path => $priority) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . url($path) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $lastMod . '</lastmod>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>' . $priority . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Serve /llms.txt
     */
    public function llmsTxt(): Response
    {
        $content = implode("\n", [
            '# Invox Pakistan',
            '',
            '> Company Subscription, Recurring Billing Automation & FBR Digital Tax Compliance Platform for Pakistani Businesses.',
            '',
            '## Overview',
            'Invox Pakistan provides automated client subscription billing, multi-currency invoicing, FBR sales tax API integration, payment dunning, and self-service client portals.',
            '',
            '## Core Pages',
            '',
            '### Home',
            url('/'),
            'Primary product overview, value proposition, and corporate trial registration.',
            '',
            '### Features',
            url('/features'),
            'In-depth breakdown of recurring billing engines, FBR tax compliance tools, customer portals, and reporting.',
            '',
            '### Pricing',
            url('/pricing'),
            'Transparent subscription plans (Starter, Growth, Enterprise) with feature comparisons.',
            '',
            '### Solutions',
            url('/solutions'),
            'Tailored billing automation solutions for SMEs, Enterprise Corporations, Accountants, and E-Commerce.',
            '',
            '### Industries',
            url('/industries'),
            'Vertical solutions for IT services, wholesale distributors, retail chains, and logistics companies.',
            '',
            '### Integrations',
            url('/integrations'),
            'Supported gateways (JazzCash, EasyPaisa, 1LINK 1Bill, Stripe) and accounting tools (QuickBooks, Xero, SAP).',
            '',
            '### Documentation',
            url('/documentation'),
            'REST API guides, webhook schemas, authentication protocols, and integration SDKs.',
            '',
            '### FAQ',
            url('/faq'),
            'Answers to common questions regarding security, FBR digital invoicing compliance, and billing setup.',
            '',
            '### Machine Interaction API',
            url('/api/v1/product-info'),
            'JSON-formatted structured product specification.',
            '',
            '### WebMCP Actions Protocol',
            url('/webmcp.json'),
            'Machine interaction schema defining safe public AI agent actions.',
            '',
            '## Contact & Sales',
            url('/contact'),
            'Schedule a live demo or contact our sales team at support@invox.pk.'
        ]);

        return response($content, 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    /**
     * Serve /site.webmanifest
     */
    public function webmanifest()
    {
        return response()->json([
            'name' => 'Invox Pakistan',
            'short_name' => 'Invox',
            'description' => 'Company Subscription & FBR Invoicing Platform',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#0f172a',
            'theme_color' => '#0284c7',
            'icons' => [
                [
                    'src' => asset('favicon-16x16.png'),
                    'sizes' => '16x16',
                    'type' => 'image/png'
                ],
                [
                    'src' => asset('favicon-32x32.png'),
                    'sizes' => '32x32',
                    'type' => 'image/png'
                ],
                [
                    'src' => asset('apple-touch-icon.png'),
                    'sizes' => '180x180',
                    'type' => 'image/png'
                ]
            ]
        ], 200, ['Content-Type' => 'application/manifest+json']);
    }

    /**
     * Serve /.well-known/security.txt
     */
    public function securityTxt(): Response
    {
        $content = implode("\n", [
            'Contact: security@invox.pk',
            'Expires: ' . date('Y-m-d\TH:i:s\Z', strtotime('+1 year')),
            'Encryption: ' . url('pgp-key.txt'),
            'Preferred-Languages: en, ur',
            'Policy: ' . url('/privacy-policy'),
            'Hiring: ' . url('/about')
        ]);

        return response($content, 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /**
     * Serve AI-Readable Machine Product Info /api/v1/product-info
     */
    public function productInfo()
    {
        return response()->json([
            'name' => 'Invox Pakistan',
            'tagline' => 'Company Subscription & FBR Invoicing Platform',
            'description' => 'Invox Pakistan is an enterprise subscription billing, recurring invoice automation, and FBR tax-compliant digital invoicing software tailored for Pakistani companies.',
            'category' => 'SoftwareApplication',
            'subCategory' => 'Business & Financial Automation',
            'targetAudience' => ['SMEs', 'Enterprise Corporations', 'Accountants', 'E-Commerce Merchants', 'IT Agencies'],
            'supportedLocales' => ['en-PK', 'ur-PK'],
            'currency' => 'PKR',
            'features' => [
                'Automated Recurring Invoicing & Dunning',
                'FBR Digital Tax API Real-Time Sync',
                'JazzCash, EasyPaisa, 1LINK & Bank Transfer Reconciliation',
                'Branded Client Self-Service Portal',
                'Multi-Currency Support & Tax Engine',
                'Role-Based Staff Permissions & Audit Logging'
            ],
            'pricingPlans' => [
                ['name' => 'Starter', 'pricePKR' => 4999, 'billingPeriod' => 'monthly', 'invoicesPerMonth' => 100],
                ['name' => 'Growth', 'pricePKR' => 12999, 'billingPeriod' => 'monthly', 'invoicesPerMonth' => 1000],
                ['name' => 'Enterprise', 'pricePKR' => 24999, 'billingPeriod' => 'monthly', 'invoicesPerMonth' => 'unlimited']
            ],
            'documentation' => url('/documentation'),
            'contact' => 'support@invox.pk'
        ], 200, ['Content-Type' => 'application/json']);
    }

    /**
     * Serve WebMCP / Machine Interaction Action Manifest /webmcp.json
     */
    public function webmcp()
    {
        return response()->json([
            'protocol' => 'WebMCP/1.0',
            'application' => 'Invox Pakistan',
            'baseUrl' => url('/'),
            'safePublicActions' => [
                [
                    'action' => 'search_products_and_features',
                    'method' => 'GET',
                    'endpoint' => url('/search'),
                    'parameters' => ['q' => 'string']
                ],
                [
                    'action' => 'view_pricing_plans',
                    'method' => 'GET',
                    'endpoint' => url('/pricing')
                ],
                [
                    'action' => 'request_sales_demo',
                    'method' => 'POST',
                    'endpoint' => url('/contact'),
                    'parameters' => [
                        'name' => 'string',
                        'email' => 'string',
                        'company_name' => 'string',
                        'message' => 'string'
                    ]
                ],
                [
                    'action' => 'get_product_info_schema',
                    'method' => 'GET',
                    'endpoint' => url('/api/v1/product-info')
                ],
                [
                    'action' => 'read_documentation',
                    'method' => 'GET',
                    'endpoint' => url('/documentation')
                ]
            ],
            'authenticationNote' => 'Destructive administrative and company management actions require authentication via HTTP Bearer token or session cookies at /company or /admin.'
        ], 200, ['Content-Type' => 'application/json']);
    }
}
