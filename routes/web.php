<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PublicInquiryController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SitemapController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::any('register', fn () => abort(404));
Route::get('sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('robots.txt', fn () => response("User-agent: *\nDisallow: /admin\nDisallow: /admin/login\nSitemap: ".route('sitemap')."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']))->name('robots');
Route::get('tour-packages', [SiteController::class, 'packages'])->name('packages.index');
Route::get('tour-packages/{package}', [SiteController::class, 'package'])->name('packages.show')->missing(fn (Request $request) => app(RedirectController::class)($request));
Route::get('vehicle-rental', [SiteController::class, 'vehicles'])->name('vehicles.index');
Route::get('vehicle-rental/{vehicle}', [SiteController::class, 'vehicle'])->name('vehicles.show')->missing(fn (Request $request) => app(RedirectController::class)($request));
Route::get('villas-houses', [PropertyController::class, 'index'])->name('properties.index');
Route::get('villas-houses/{property}', [PropertyController::class, 'show'])->name('properties.show')->missing(fn (Request $request) => app(RedirectController::class)($request));

Route::get('gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('about', [SiteController::class, 'fixedPage'])->defaults('page_key', 'about')->defaults('canonical_route', 'about')->name('about');
Route::get('privacy-policy', [SiteController::class, 'fixedPage'])->defaults('page_key', 'privacy-policy')->defaults('canonical_route', 'privacy')->name('privacy');
Route::get('terms-and-conditions', [SiteController::class, 'fixedPage'])->defaults('page_key', 'terms-and-conditions')->defaults('canonical_route', 'terms')->name('terms');

Route::get('blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('blog/{post}', [BlogController::class, 'show'])->name('blog.show')->missing(fn (Request $request) => app(RedirectController::class)($request));

Route::controller(PublicInquiryController::class)->group(function (): void {
    Route::get('tour-packages/{package}/inquire', 'package')->name('inquiries.package.create');
    Route::post('tour-packages/{package}/inquire', 'storePackage')->middleware('throttle:public-inquiries')->defaults('inquiry_type', 'package')->name('inquiries.package.store');
    Route::get('vehicles/{vehicle}/inquire', 'rental')->name('inquiries.rental.create');
    Route::post('vehicles/{vehicle}/inquire', 'storeRental')->middleware('throttle:public-inquiries')->defaults('inquiry_type', 'rental')->name('inquiries.rental.store');
    Route::get('villas-houses/{property}/inquire', 'property')->name('inquiries.property.create');
    Route::post('villas-houses/{property}/inquire', 'storeProperty')->middleware('throttle:public-inquiries')->defaults('inquiry_type', 'property')->name('inquiries.property.store');
    Route::get('visa-extension', 'visaPage')->name('services.visa');
    Route::get('visa-extension/inquire', 'visa')->name('inquiries.visa.create');
    Route::post('visa-extension/inquire', 'storeVisa')->middleware('throttle:public-inquiries')->defaults('inquiry_type', 'visa')->name('inquiries.visa.store');
    Route::get('baggage-transport', 'baggagePage')->name('services.baggage');
    Route::get('baggage-transport/inquire', 'baggage')->name('inquiries.baggage.create');
    Route::post('baggage-transport/inquire', 'storeBaggage')->middleware('throttle:public-inquiries')->defaults('inquiry_type', 'baggage')->name('inquiries.baggage.store');
    Route::get('contact', 'contact')->name('inquiries.contact.create');
    Route::post('contact', 'storeContact')->middleware('throttle:public-inquiries')->defaults('inquiry_type', 'general')->name('inquiries.contact.store');
});

require __DIR__.'/admin.php';

Route::get('{page}', [SiteController::class, 'page'])->name('pages.show')->missing(fn (Request $request) => app(RedirectController::class)($request));
Route::fallback(RedirectController::class);
