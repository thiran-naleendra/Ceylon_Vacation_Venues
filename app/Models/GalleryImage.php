<?php

namespace App\Models;

use App\Models\Concerns\HasImageAttributes;
use App\Models\Concerns\HasPublicationStatus;
use App\Models\Concerns\HasSortOrder;
use Database\Factories\GalleryImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['gallery_album_id', 'title', 'alt_text', 'caption', 'sort_order', 'is_visible'])]
class GalleryImage extends Model
{
    /** @use HasFactory<GalleryImageFactory> */
    use HasFactory, HasImageAttributes, HasPublicationStatus, HasSortOrder;

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(GalleryAlbum::class, 'gallery_album_id');
    }

    public function url(?string $variant = null): string
    {
        $path = $variant !== null ? data_get($this->variants, $variant.'.path', $this->path) : $this->path;

        return Storage::disk($this->disk)->url($path);
    }

    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where('is_visible', true)->ready();
    }
}
