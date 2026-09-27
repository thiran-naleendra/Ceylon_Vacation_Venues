<?php

use App\Http\Controllers\Admin\AmenityController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GalleryCategoryController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\PropertyTypeController;
use App\Http\Controllers\Admin\RedirectController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TourPackageController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VehicleCategoryController;
use App\Http\Controllers\Admin\VehicleController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store'])
            ->middleware('throttle:admin-login')
            ->name('login.store');
    });

    Route::middleware(['auth', 'admin'])->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::patch('packages/{package}/status', [TourPackageController::class, 'updateStatus'])->name('packages.status');
        Route::resource('packages', TourPackageController::class);
        Route::patch('vehicles/{vehicle}/status', [VehicleController::class, 'updateStatus'])->name('vehicles.status');
        Route::resource('vehicles', VehicleController::class);
        Route::resource('vehicle-categories', VehicleCategoryController::class)->except(['show']);
        Route::patch('properties/{property}/status', [PropertyController::class, 'updateStatus'])->name('properties.status');
        Route::resource('properties', PropertyController::class);
        Route::resource('property-types', PropertyTypeController::class)->except(['show']);
        Route::resource('amenities', AmenityController::class)->except(['show']);
        Route::get('inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
        Route::get('inquiries/{inquiry}', [InquiryController::class, 'show'])->name('inquiries.show');
        Route::patch('inquiries/{inquiry}/status', [InquiryController::class, 'updateStatus'])->name('inquiries.status');
        Route::post('inquiries/{inquiry}/notes', [InquiryController::class, 'storeNote'])->name('inquiries.notes.store');
        Route::redirect('visa', '/admin/inquiries?type=visa')->name('visa.index');
        Route::redirect('baggage', '/admin/inquiries?type=baggage')->name('baggage.index');
        Route::get('pages', [PageController::class, 'index'])->name('pages.index');
        Route::get('pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
        Route::put('pages/{page}', [PageController::class, 'update'])->name('pages.update');
        Route::patch('pages/{page}/status', [PageController::class, 'updateStatus'])->name('pages.status');
        Route::get('settings', [SettingsController::class, 'edit'])->name('settings.index');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::resource('gallery-categories', GalleryCategoryController::class)->except(['show']);
        Route::resource('gallery', GalleryController::class)->except(['show']);
        Route::resource('blog-categories', BlogCategoryController::class)->except(['show']);
        Route::resource('blog', BlogPostController::class)->parameters(['blog' => 'post']);
        Route::resource('redirects', RedirectController::class)->except(['show']);
        Route::resource('users', UserController::class);
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');

        foreach ([
            'seo' => 'SEO',
        ] as $path => $title) {
            Route::view($path, 'admin.placeholder', compact('title'))->name($path.'.index');
        }

        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    });
});
