<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class DevSeoValidatorController extends Controller
{
    public function index(Request $request)
    {
        // Enforce local/testing environment or authorization key
        if (!app()->environment('local', 'testing') && $request->input('key') !== env('DEV_SEO_KEY', 'invox-seo-secret')) {
            abort(403, 'SEO Validator is restricted to development environments.');
        }

        $routesToTest = [
            '/' => 'Home Landing',
            '/features' => 'Features Overview',
            '/pricing' => 'Pricing Plans',
            '/solutions' => 'Solutions Overview',
            '/industries' => 'Industries',
            '/integrations' => 'Integrations',
            '/about' => 'About Us',
            '/contact' => 'Contact Sales',
            '/blog' => 'Blog Listing',
            '/blog/fbr-digital-invoicing-compliance-guide-2026' => 'Blog Article Post',
            '/resources' => 'Resources Hub',
            '/documentation' => 'Documentation API',
            '/faq' => 'FAQ Hub',
            '/privacy-policy' => 'Privacy Policy',
            '/terms' => 'Terms of Service',
            '/sitemap' => 'HTML Sitemap',
            '/search?q=invoicing' => 'Internal Search (Noindex)',
        ];

        $results = [];

        foreach ($routesToTest as $path => $label) {
            $url = url($path);
            
            try {
                $subRequest = Request::create($path, 'GET');
                $response = app()->handle($subRequest);
                $status = $response->getStatusCode();
                $html = $response->getContent();

                if ($status !== 200) {
                    $results[] = [
                        'url' => $url,
                        'path' => $path,
                        'label' => $label,
                        'status' => $status,
                        'issues' => ["HTTP status returned {$status}"],
                        'passed' => false
                    ];
                    continue;
                }

                $analysis = $this->analyzeHtml($html, $url);
                $analysis['url'] = $url;
                $analysis['path'] = $path;
                $analysis['label'] = $label;
                $analysis['status'] = $status;
                $results[] = $analysis;

            } catch (\Throwable $e) {
                $results[] = [
                    'url' => $url,
                    'path' => $path,
                    'label' => $label,
                    'status' => 500,
                    'issues' => ["Exception: " . $e->getMessage()],
                    'passed' => false
                ];
            }
        }

        return view('dev.seo-validator', [
            'title' => 'Development SEO Validation Dashboard',
            'results' => $results
        ]);
    }

    public function analyzeHtml(string $html, string $currentUrl): array
    {
        $issues = [];
        $warnings = [];

        // Title
        preg_match('/<title>(.*?)<\/title>/is', $html, $titleMatches);
        $title = trim($titleMatches[1] ?? '');
        $titleLength = mb_strlen($title);

        if (empty($title)) {
            $issues[] = 'Missing <title> tag.';
        } elseif ($titleLength < 25 || $titleLength > 70) {
            $warnings[] = "Title length ({$titleLength} chars) outside optimal range (30-65 chars).";
        }

        // Meta Description
        preg_match('/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/is', $html, $descMatches);
        $description = trim($descMatches[1] ?? '');
        $descLength = mb_strlen($description);

        if (empty($description)) {
            $issues[] = 'Missing meta description tag.';
        } elseif ($descLength < 70 || $descLength > 170) {
            $warnings[] = "Meta description length ({$descLength} chars) outside optimal range (120-160 chars).";
        }

        // Canonical
        preg_match('/<link\s+rel=["\']canonical["\']\s+href=["\'](.*?)["\']/is', $html, $canonicalMatches);
        $canonical = trim($canonicalMatches[1] ?? '');

        if (empty($canonical)) {
            $issues[] = 'Missing canonical URL link tag.';
        }

        // Robots
        preg_match('/<meta\s+name=["\']robots["\']\s+content=["\'](.*?)["\']/is', $html, $robotsMatches);
        $robots = trim($robotsMatches[1] ?? '');

        if (empty($robots)) {
            $warnings[] = 'Missing meta robots tag.';
        }

        // H1 Count
        preg_match_all('/<h1[^>]*>(.*?)<\/h1>/is', $html, $h1Matches);
        $h1Count = count($h1Matches[0] ?? []);
        $h1Text = strip_tags(trim($h1Matches[1][0] ?? ''));

        if ($h1Count === 0) {
            $issues[] = 'Missing H1 heading.';
        } elseif ($h1Count > 1) {
            $warnings[] = "Multiple H1 headings found ({$h1Count}).";
        }

        // OpenGraph
        preg_match('/<meta\s+property=["\']og:title["\']\s+content=["\'](.*?)["\']/is', $html, $ogTitleMatches);
        $ogTitle = trim($ogTitleMatches[1] ?? '');

        preg_match('/<meta\s+property=["\']og:description["\']\s+content=["\'](.*?)["\']/is', $html, $ogDescMatches);
        $ogDesc = trim($ogDescMatches[1] ?? '');

        preg_match('/<meta\s+property=["\']og:image["\']\s+content=["\'](.*?)["\']/is', $html, $ogImgMatches);
        $ogImage = trim($ogImgMatches[1] ?? '');

        if (empty($ogTitle) || empty($ogDesc) || empty($ogImage)) {
            $warnings[] = 'Incomplete Open Graph tags (missing title, description, or image).';
        }

        // Twitter Card
        preg_match('/<meta\s+name=["\']twitter:card["\']\s+content=["\'](.*?)["\']/is', $html, $twMatches);
        $twitterCard = trim($twMatches[1] ?? '');

        if (empty($twitterCard)) {
            $warnings[] = 'Missing twitter:card tag.';
        }

        // JSON-LD Schemas
        preg_match_all('/<script\s+type=["\']application\/ld\+json["\']>(.*?)<\/script>/is', $html, $schemaMatches);
        $schemaCount = count($schemaMatches[1] ?? []);
        $schemaTypes = [];

        foreach ($schemaMatches[1] ?? [] as $schemaJson) {
            $decoded = json_decode($schemaJson, true);
            if (isset($decoded['@type'])) {
                $schemaTypes[] = $decoded['@type'];
            }
        }

        if ($schemaCount === 0) {
            $warnings[] = 'No JSON-LD structured data detected.';
        }

        // Internal Links
        preg_match_all('/<a\s+[^>]*href=["\']([^"\']+)["\']/is', $html, $linkMatches);
        $internalLinksCount = count($linkMatches[1] ?? []);

        // Images without alt
        preg_match_all('/<img\s+[^>]*>/is', $html, $imgTagMatches);
        $missingAltCount = 0;
        foreach ($imgTagMatches[0] ?? [] as $imgTag) {
            if (!str_contains($imgTag, 'alt=')) {
                $missingAltCount++;
            }
        }

        if ($missingAltCount > 0) {
            $warnings[] = "Found {$missingAltCount} image(s) missing alt attribute.";
        }

        $passed = empty($issues);

        return [
            'title' => $title,
            'title_length' => $titleLength,
            'description' => $description,
            'desc_length' => $descLength,
            'canonical' => $canonical,
            'robots' => $robots,
            'h1_text' => $h1Text,
            'h1_count' => $h1Count,
            'og_title' => $ogTitle,
            'og_image' => $ogImage,
            'twitter_card' => $twitterCard,
            'schema_count' => $schemaCount,
            'schema_types' => array_unique($schemaTypes),
            'internal_links' => $internalLinksCount,
            'missing_alt_count' => $missingAltCount,
            'issues' => $issues,
            'warnings' => $warnings,
            'passed' => $passed
        ];
    }
}
