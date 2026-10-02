<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SeoService;

class PublicPagesController extends Controller
{
    protected SeoService $seoService;

    public function __construct(SeoService $seoService)
    {
        $this->seoService = $seoService;
    }

    public function home()
    {
        $faqs = [
            [
                'question' => 'What is Invox Pakistan?',
                'answer' => 'Invox Pakistan is an enterprise subscription management, recurring billing automation, and FBR tax-compliant digital invoicing platform for companies in Pakistan.'
            ],
            [
                'question' => 'Does Invox Pakistan integrate with FBR Digital Invoicing?',
                'answer' => 'Yes, Invox Pakistan natively interfaces with FBR digital tax API standards, generating QR codes, invoice reference numbers, and real-time compliance reporting.'
            ],
            [
                'question' => 'What payment gateways are supported in Pakistan?',
                'answer' => 'Invox Pakistan supports JazzCash, EasyPaisa, bank transfers, 1LINK 1Bill, and international cards via Stripe.'
            ],
            [
                'question' => 'Is there a free trial available for new companies?',
                'answer' => 'Yes, we offer a 14-day full feature free trial with no credit card required.'
            ]
        ];

        $schemaExtra = [
            $this->seoService->getSoftwareApplicationSchema(),
            $this->seoService->getFaqSchema($faqs)
        ];

        return view('public.home', [
            'title' => 'Company Subscription & FBR Invoicing',
            'description' => 'Automated company subscription billing, FBR tax-compliant digital invoicing, recurring payment management, and client portal for Pakistani businesses.',
            'faqs' => $faqs,
            'schemaExtra' => $schemaExtra
        ]);
    }

    public function features()
    {
        return view('public.features', [
            'title' => 'SaaS Subscription & FBR Invoicing Features',
            'description' => 'Explore features including automated recurring billing, FBR tax compliance, client self-service portal, multi-currency invoicing, and payment dunning.'
        ]);
    }

    public function pricing()
    {
        $faqs = [
            [
                'question' => 'Can I change my subscription plan at any time?',
                'answer' => 'Yes, you can upgrade, downgrade, or cancel your Invox Pakistan subscription at any time directly from your account settings.'
            ],
            [
                'question' => 'Are there any hidden transaction fees?',
                'answer' => 'No hidden charges. Our monthly subscription plans include transparent limits and flat rates.'
            ]
        ];

        $schemaExtra = [
            $this->seoService->getFaqSchema($faqs)
        ];

        return view('public.pricing', [
            'title' => 'Transparent Pricing Plans & Subscription Packages',
            'description' => 'Compare Invox Pakistan pricing tiers for startups, growing SMEs, and enterprise corporations. 14-day free trial included.',
            'faqs' => $faqs,
            'schemaExtra' => $schemaExtra
        ]);
    }

    public function solutions()
    {
        return view('public.solutions', [
            'title' => 'Billing Solutions for SMEs & Enterprise Teams',
            'description' => 'Tailored subscription billing and compliance solutions for Pakistani SMEs, enterprise corporations, accountants, and e-commerce merchants.'
        ]);
    }

    public function industries()
    {
        return view('public.industries', [
            'title' => 'Industry Use Cases & Vertical Solutions',
            'description' => 'Discover how Invox Pakistan powers billing and tax compliance for IT services, wholesale distributors, logistics, healthcare, and retail businesses.'
        ]);
    }

    public function integrations()
    {
        return view('public.integrations', [
            'title' => 'Accounting & Payment Gateway Integrations',
            'description' => 'Connect Invox Pakistan with JazzCash, EasyPaisa, 1LINK, QuickBooks, Xero, SAP, and FBR tax services seamlessly for automated reconciliation.'
        ]);
    }

    public function about()
    {
        return view('public.about', [
            'title' => 'About Invox Pakistan | Our Mission & Team',
            'description' => 'Learn about Invox Pakistan, our founding mission, security certifications, executive team, and commitment to Pakistani enterprise fintech compliance.'
        ]);
    }

    public function contact()
    {
        return view('public.contact', [
            'title' => 'Contact Sales & Request Live Software Demo',
            'description' => 'Get in touch with the Invox Pakistan team. Schedule a personalized product demo or request pricing support for your business.'
        ]);
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company_name' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        return redirect()->back()->with('status', 'Thank you! Your inquiry has been submitted. Our team will contact you within 2 business hours.');
    }

    public function resources()
    {
        return view('public.resources', [
            'title' => 'Fintech Guides, Calculators & Case Studies',
            'description' => 'Access free resources, FBR digital invoicing guides, billing automation ROI calculators, and client case studies from Invox Pakistan.'
        ]);
    }

    public function documentation()
    {
        return view('public.documentation', [
            'title' => 'API Reference & Integration Documentation',
            'description' => 'Complete developer documentation, RESTful API endpoint guides, webhooks, and SDKs for Invox Pakistan billing platform.'
        ]);
    }

    public function faq()
    {
        $faqs = [
            [
                'question' => 'How does automated recurring billing work?',
                'answer' => 'Invox Pakistan automatically generates and emails recurring invoices based on set billing schedules (monthly, quarterly, annually) and collects payments.'
            ],
            [
                'question' => 'How does FBR integration work in Invox Pakistan?',
                'answer' => 'Invoices generated on Invox Pakistan are automatically validated against FBR sales tax rules and assigned official reference numbers.'
            ],
            [
                'question' => 'Is my corporate customer data secure?',
                'answer' => 'Yes, we enforce 256-bit SSL encryption, automated daily offsite backups, role-based access control, and strict ISO-aligned data governance.'
            ],
            [
                'question' => 'Can my clients view their payment history?',
                'answer' => 'Yes, every company gets access to a branded Client Self-Service Portal to review invoices, download receipts, and update payment details.'
            ]
        ];

        $schemaExtra = [
            $this->seoService->getFaqSchema($faqs)
        ];

        return view('public.faq', [
            'title' => 'Frequently Asked Questions & Support Hub',
            'description' => 'Find answers to common questions about subscription management, FBR compliance, billing automation, security, and integrations.',
            'faqs' => $faqs,
            'schemaExtra' => $schemaExtra
        ]);
    }

    public function privacyPolicy()
    {
        return view('public.privacy-policy', [
            'title' => 'Privacy Policy & Data Governance Standards',
            'description' => 'Read Invox Pakistan’s privacy policy detailing our data collection, cookie usage, protection protocols, and regulatory compliance.'
        ]);
    }

    public function terms()
    {
        return view('public.terms', [
            'title' => 'Terms of Service & Subscription Agreement',
            'description' => 'Invox Pakistan terms of service, acceptable use policy, service level agreements (SLAs), and client subscription terms.'
        ]);
    }

    public function htmlSitemap()
    {
        return view('public.html-sitemap', [
            'title' => 'HTML Site Map & Complete Route Navigation',
            'description' => 'Full directory of indexable pages, feature modules, documentation, solutions, and blog articles on Invox Pakistan.'
        ]);
    }

    public function search(Request $request)
    {
        $query = trim($request->input('q', ''));
        
        return view('public.search', [
            'title' => $query ? "Search results for '{$query}'" : "Search Site Content",
            'description' => "Search Invox Pakistan documentation, feature guides, pricing plans, and blog articles.",
            'query' => $query,
            'robots' => 'noindex, follow'
        ]);
    }
}
