<?php

namespace App\Models;

use Database\Factories\SeoMetadataFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['meta_title', 'meta_description', 'canonical_url', 'robots_index', 'robots_follow', 'og_title', 'og_description', 'og_image_alt'])]
class SeoMetadata extends Model
{
    /** @use HasFactory<SeoMetadataFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id');
    }

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class, 'tour_package_id');
    }

    public function vehicleCategory(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function blogCategory(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function blogPost(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }

    public function galleryAlbum(): BelongsTo
    {
        return $this->belongsTo(GalleryAlbum::class, 'gallery_album_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    protected $table = 'seo_metadata';
}
