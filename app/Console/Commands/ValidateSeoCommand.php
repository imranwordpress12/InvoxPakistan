<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\DevSeoValidatorController;
use Illuminate\Http\Request;

class ValidateSeoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:validate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan all public routes and validate SEO titles, descriptions, canonicals, robots, H1 headings, OpenGraph, JSON-LD, and image ALT tags.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting Invox Pakistan Automated Technical SEO & Indexability Audit...');
        $this->newLine();

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

        $validator = new DevSeoValidatorController();
        $tableRows = [];
        $hasErrors = false;
        $totalPassed = 0;

        foreach ($routesToTest as $path => $label) {
            try {
                $subRequest = Request::create($path, 'GET');
                $response = app()->handle($subRequest);
                $status = $response->getStatusCode();
                $html = $response->getContent();

                if ($status !== 200) {
                    $tableRows[] = [$path, $status, 'N/A', 'N/A', 'N/A', 0, 'FAIL: Non-200 Status'];
                    $hasErrors = true;
                    continue;
                }

                $analysis = $validator->analyzeHtml($html, url($path));

                $statusStr = $analysis['passed'] ? '<fg=green>PASS</>' : '<fg=red>FAIL</>';
                if ($analysis['passed']) {
                    $totalPassed++;
                } else {
                    $hasErrors = true;
                }

                $issuesCount = count($analysis['issues']);
                $warnCount = count($analysis['warnings']);

                $tableRows[] = [
                    $path,
                    $status,
                    mb_strimwidth($analysis['title'], 0, 35, '...'),
                    $analysis['title_length'] . ' ch',
                    $analysis['desc_length'] . ' ch',
                    $analysis['h1_count'],
                    implode(', ', $analysis['schema_types']),
                    "{$issuesCount} Err / {$warnCount} Warn",
                    $statusStr
                ];

            } catch (\Throwable $e) {
                $tableRows[] = [$path, 500, 'N/A', 'N/A', 'N/A', 0, '', 'Exception', '<fg=red>FAIL</>'];
                $hasErrors = true;
            }
        }

        $this->table(
            ['Route Path', 'HTTP', 'Page Title', 'Title L.', 'Desc L.', 'H1s', 'Schemas', 'Issues', 'Status'],
            $tableRows
        );

        $this->newLine();
        $this->info("Audit Completed: {$totalPassed} / " . count($routesToTest) . " routes passed SEO health checks cleanly.");

        return $hasErrors ? 1 : 0;
    }
}
