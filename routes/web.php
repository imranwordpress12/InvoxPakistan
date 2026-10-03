<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicPagesController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\SeoFilesController;
use App\Http\Controllers\DevSeoValidatorController;
use App\Mail\CompanyWelcome;
use Illuminate\Support\Facades\Mail;
/*
|--------------------------------------------------------------------------
| Test Email Route
|--------------------------------------------------------------------------
*/
Route::get('/test-email', function () {
    $company = App\Models\Company::where('id', 2)->first();
    Mail::to($company->email)->send(new CompanyWelcome($company));
    return 'Test email sent to '.$company->email;
});

/*
|--------------------------------------------------------------------------
| Technical SEO & Public Marketing Routes
|--------------------------------------------------------------------------
*/

// Public Marketing & Product Routes
Route::get('/', [PublicPagesController::class, 'home'])->name('public.home');
Route::get('/features', [PublicPagesController::class, 'features'])->name('public.features');
Route::get('/pricing', [PublicPagesController::class, 'pricing'])->name('public.pricing');
Route::get('/solutions', [PublicPagesController::class, 'solutions'])->name('public.solutions');
Route::get('/industries', [PublicPagesController::class, 'industries'])->name('public.industries');
Route::get('/integrations', [PublicPagesController::class, 'integrations'])->name('public.integrations');
Route::get('/about', [PublicPagesController::class, 'about'])->name('public.about');
Route::get('/contact', [PublicPagesController::class, 'contact'])->name('public.contact');
Route::post('/contact', [PublicPagesController::class, 'submitContact'])->name('public.contact.submit');
Route::get('/resources', [PublicPagesController::class, 'resources'])->name('public.resources');
Route::get('/documentation', [PublicPagesController::class, 'documentation'])->name('public.documentation');
Route::get('/faq', [PublicPagesController::class, 'faq'])->name('public.faq');
Route::get('/privacy-policy', [PublicPagesController::class, 'privacyPolicy'])->name('public.privacy');
Route::get('/terms', [PublicPagesController::class, 'terms'])->name('public.terms');
Route::get('/sitemap', [PublicPagesController::class, 'htmlSitemap'])->name('public.sitemap');
Route::get('/search', [PublicPagesController::class, 'search'])->name('public.search');

// Blog & Article Routes
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/category/{category}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Technical SEO Files & Machine Manifests
Route::get('/robots.txt', [SeoFilesController::class, 'robots']);
Route::get('/sitemap.xml', [SeoFilesController::class, 'sitemap']);
Route::get('/sitemap-index.xml', [SeoFilesController::class, 'sitemapIndex']);
Route::get('/sitemap-pages.xml', [SeoFilesController::class, 'sitemapPages']);
Route::get('/sitemap-blog.xml', [SeoFilesController::class, 'sitemapBlog']);
Route::get('/llms.txt', [SeoFilesController::class, 'llmsTxt']);
Route::get('/site.webmanifest', [SeoFilesController::class, 'webmanifest']);
Route::get('/.well-known/security.txt', [SeoFilesController::class, 'securityTxt']);
Route::get('/api/v1/product-info', [SeoFilesController::class, 'productInfo']);
Route::get('/webmcp.json', [SeoFilesController::class, 'webmcp']);

// Development SEO Validation Dashboard
Route::get('/dev/seo-validator', [DevSeoValidatorController::class, 'index'])->name('dev.seo-validator');

/*
|--------------------------------------------------------------------------
| Application Admin & Company Portal Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/admin.php';
require __DIR__.'/company.php';
