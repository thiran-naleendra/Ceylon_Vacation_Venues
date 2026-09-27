<?php

namespace App\Providers;

use App\Models\Amenity;
use App\Models\AuditLog;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use App\Models\Inquiry;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Redirect;
use App\Models\SeoMetadata;
use App\Models\TourPackage;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Observers\AdminContentAuditObserver;
use App\Policies\AmenityPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\BlogCategoryPolicy;
use App\Policies\BlogPostPolicy;
use App\Policies\GalleryAlbumPolicy;
use App\Policies\GalleryImagePolicy;
use App\Policies\InquiryPolicy;
use App\Policies\PackagePolicy;
use App\Policies\PagePolicy;
use App\Policies\PropertyPolicy;
use App\Policies\PropertyTypePolicy;
use App\Policies\RedirectPolicy;
use App\Policies\UserPolicy;
use App\Policies\VehicleCategoryPolicy;
use App\Policies\VehiclePolicy;
use App\Services\PublicSiteData;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PublicSiteData::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([Page::class, TourPackage::class, Vehicle::class, Property::class, BlogPost::class, SeoMetadata::class] as $model) {
            $model::saved(fn (): bool => Cache::forget('public.sitemap.entries.v2'));
            $model::deleted(fn (): bool => Cache::forget('public.sitemap.entries.v2'));
        }

        foreach ([Amenity::class, BlogCategory::class, BlogPost::class, GalleryAlbum::class, GalleryImage::class, Page::class, PageSection::class, PropertyType::class, Redirect::class, SeoMetadata::class, VehicleCategory::class] as $model) {
            $model::observe(AdminContentAuditObserver::class);
        }

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(TourPackage::class, PackagePolicy::class);
        Gate::policy(Inquiry::class, InquiryPolicy::class);
        Gate::policy(BlogCategory::class, BlogCategoryPolicy::class);
        Gate::policy(BlogPost::class, BlogPostPolicy::class);
        Gate::policy(GalleryAlbum::class, GalleryAlbumPolicy::class);
        Gate::policy(GalleryImage::class, GalleryImagePolicy::class);
        Gate::policy(Page::class, PagePolicy::class);
        Gate::policy(Redirect::class, RedirectPolicy::class);
        Gate::policy(Vehicle::class, VehiclePolicy::class);
        Gate::policy(VehicleCategory::class, VehicleCategoryPolicy::class);
        Gate::policy(Property::class, PropertyPolicy::class);
        Gate::policy(PropertyType::class, PropertyTypePolicy::class);
        Gate::policy(Amenity::class, AmenityPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);

        Gate::define('access-admin', fn (User $user): bool => $user->canAccessAdmin());
        Gate::define('manage-admin-users', fn (User $user): bool => $user->role->canManageAdminUsers());
        Gate::define('publish-content', fn (User $user): bool => $user->role->canPublishContent());
        Gate::define('manage-inquiries', fn (User $user): bool => $user->role->canManageInquiries());
        Gate::define('manage-settings', fn (User $user): bool => in_array($user->role->value, ['owner', 'administrator'], true));

        View::composer('components.public.layout', function ($view): void {
            $data = app(PublicSiteData::class)->get();
            $view->with('siteSettings', $data['settings']);
            $view->with('siteSocialLinks', $data['socialLinks']);
            $view->with('sitePages', $data['pages']);
        });

        RateLimiter::for('admin-login', fn (Request $request): Limit => Limit::perMinute(10)
            ->by($request->ip()));
        RateLimiter::for('public-inquiries', fn (Request $request): Limit => Limit::perMinute(5)
            ->by($request->ip()));
    }
}
