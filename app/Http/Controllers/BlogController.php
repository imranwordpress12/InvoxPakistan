<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SeoService;

class BlogController extends Controller
{
    protected SeoService $seoService;

    public function __construct(SeoService $seoService)
    {
        $this->seoService = $seoService;
    }

    protected function getArticles(): array
    {
        return [
            'fbr-digital-invoicing-compliance-guide-2026' => [
                'slug' => 'fbr-digital-invoicing-compliance-guide-2026',
                'title' => 'FBR Digital Invoicing Compliance Guide',
                'category' => 'compliance',
                'category_name' => 'Tax Compliance',
                'description' => 'A comprehensive 2026 guide explaining FBR digital tax integration, sales tax reference numbers, QR code generation, and automated compliance.',
                'content' => 'The Federal Board of Revenue (FBR) in Pakistan has accelerated the transition towards digital tax integration for tier-1 retailers, corporate enterprises, and service providers. Automating invoice submission directly to FBR database ensures real-time reporting, reduces penalty risks, and streamlines monthly sales tax return filings. In this guide, we explore how Invox Pakistan automates QR code generation, invoice verification keys, and real-time API sync.',
                'author_name' => 'Imran Amin',
                'author_role' => 'Lead Tax Compliance Engineer',
                'published_time' => '2026-01-15T09:00:00+05:00',
                'modified_time' => '2026-02-10T14:30:00+05:00',
                'read_time' => '6 min read',
                'image' => asset('images/og-image.jpg')
            ],
            'saas-subscription-billing-automation-best-practices' => [
                'slug' => 'saas-subscription-billing-automation-best-practices',
                'title' => 'SaaS Subscription Billing Automation Best Practices for Growth',
                'category' => 'software',
                'category_name' => 'Software & Billing',
                'description' => 'Learn how automated recurring billing, smart retry logic, and dunning workflows reduce customer churn and boost MRR.',
                'content' => 'Managing recurring subscriptions manually leads to billing errors, delayed payments, and uncaptured revenue. Automated dunning management sends timely email reminders, applies grace periods, and allows clients to seamlessly update payment credentials. Discover how top SaaS platforms optimize billing intervals and pricing tiers for maximum retention.',
                'author_name' => 'Ayesha Khan',
                'author_role' => 'Head of Product Growth',
                'published_time' => '2026-02-01T10:00:00+05:00',
                'modified_time' => '2026-02-20T11:15:00+05:00',
                'read_time' => '8 min read',
                'image' => asset('images/og-image.jpg')
            ],
            'jazzcash-easypaisa-bank-reconciliation-guide' => [
                'slug' => 'jazzcash-easypaisa-bank-reconciliation-guide',
                'title' => 'Streamlining JazzCash, EasyPaisa & Bank Transfer Reconciliation',
                'category' => 'fintech',
                'category_name' => 'Fintech & Payments',
                'description' => 'How Pakistani companies automate payment matching across mobile wallets, 1LINK, and direct bank transfers without manual ledger entries.',
                'content' => 'In Pakistan’s digital economy, businesses collect payments through a mix of mobile wallets like JazzCash and EasyPaisa, 1LINK 1Bill payment codes, and online bank transfers. Manual reconciliation consumes hundreds of finance hours every month. Invox Pakistan provides automated payment matching with transaction reference IDs, ensuring instant invoice mark-as-paid status.',
                'author_name' => 'Bilal Ahmed',
                'author_role' => 'Senior Financial Systems Architect',
                'published_time' => '2026-02-18T12:00:00+05:00',
                'modified_time' => '2026-03-01T16:00:00+05:00',
                'read_time' => '5 min read',
                'image' => asset('images/og-image.jpg')
            ]
        ];
    }

    public function index()
    {
        $articles = $this->getArticles();

        return view('blog.index', [
            'title' => 'Invox Pakistan Blog | SaaS, Invoicing & FBR Insights',
            'description' => 'Insights, guides, and practical advice on subscription billing, FBR compliance, financial automation, and fintech operations in Pakistan.',
            'articles' => $articles,
            'currentCategory' => null
        ]);
    }

    public function category(string $category)
    {
        $allArticles = $this->getArticles();
        $filtered = array_filter($allArticles, fn($art) => strtolower($art['category']) === strtolower($category));

        if (empty($filtered)) {
            abort(404);
        }

        $categoryName = current($filtered)['category_name'];

        return view('blog.index', [
            'title' => "{$categoryName} Articles & Guides | Invox Pakistan Blog",
            'description' => "Explore articles and guides focused on {$categoryName} for Pakistani businesses.",
            'articles' => $filtered,
            'currentCategory' => $category
        ]);
    }

    public function show(string $slug)
    {
        $articles = $this->getArticles();

        if (!isset($articles[$slug])) {
            abort(404);
        }

        $article = $articles[$slug];
        $article['url'] = url("/blog/{$slug}");

        $schemaExtra = [
            $this->seoService->getArticleSchema($article)
        ];

        return view('blog.show', [
            'title' => $article['title'],
            'description' => $article['description'],
            'article' => $article,
            'og_type' => 'article',
            'og_title' => $article['title'],
            'og_description' => $article['description'],
            'og_image' => $article['image'],
            'published_time' => $article['published_time'],
            'modified_time' => $article['modified_time'],
            'article_author' => $article['author_name'],
            'schemaExtra' => $schemaExtra
        ]);
    }
}
