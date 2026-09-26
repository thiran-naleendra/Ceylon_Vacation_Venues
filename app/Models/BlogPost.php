<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationStatus;
use App\Models\Concerns\HasSeoMetadata;
use App\Models\Concerns\HasSlug;
use Database\Factories\BlogPostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['blog_category_id', 'title', 'slug', 'excerpt', 'body', 'featured_image_alt'])]
class BlogPost extends Model
{
    /** @use HasFactory<BlogPostFactory> */
    use HasFactory, HasPublicationStatus, HasSeoMetadata, HasSlug;

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'featured_image_variants' => 'array',
        ];
    }

    public function featuredImageUrl(?string $variant = null): ?string
    {
        if (! $this->featured_image_path) {
            return null;
        }

        $path = $variant !== null ? data_get($this->featured_image_variants, $variant.'.path', $this->featured_image_path) : $this->featured_image_path;

        return Storage::disk('public')->url($path);
    }

    public function featuredImageWidth(?string $variant = null): int
    {
        return (int) ($variant !== null ? data_get($this->featured_image_variants, $variant.'.width', 1800) : 1800);
    }

    public function featuredImageHeight(?string $variant = null): int
    {
        return (int) ($variant !== null ? data_get($this->featured_image_variants, $variant.'.height', 1013) : 1013);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }
}
