<?php

namespace App\Models;

use App\Models\Concerns\HasImageAttributes;
use App\Models\Concerns\HasSortOrder;
use Database\Factories\PropertyImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['alt_text', 'caption', 'sort_order'])] class PropertyImage extends Model
{
    /** @use HasFactory<PropertyImageFactory> */
    use HasFactory,HasImageAttributes,HasSortOrder;

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function url(?string $variant = null): string
    {
        $path = $variant !== null ? data_get($this->variants, $variant.'.path', $this->path) : $this->path;

        return Storage::disk($this->disk)->url($path);
    }
}
