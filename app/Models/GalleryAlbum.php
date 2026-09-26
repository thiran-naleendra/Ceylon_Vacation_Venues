<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationStatus;
use App\Models\Concerns\HasSeoMetadata;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasSortOrder;
use Database\Factories\GalleryAlbumFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'slug', 'description', 'is_featured', 'sort_order'])]
class GalleryAlbum extends Model
{
    /** @use HasFactory<GalleryAlbumFactory> */
    use HasFactory, HasPublicationStatus, HasSeoMetadata, HasSlug, HasSortOrder;

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(GalleryImage::class);
    }

    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }
}
