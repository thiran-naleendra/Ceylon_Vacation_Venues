<?php

namespace App\Models;

use App\Models\Concerns\HasImageAttributes;
use App\Models\Concerns\HasSortOrder;
use Database\Factories\PackageImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['alt_text', 'caption', 'sort_order'])]
class PackageImage extends Model
{
    /** @use HasFactory<PackageImageFactory> */
    use HasFactory, HasImageAttributes, HasSortOrder;

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function url(?string $variant = null): string
    {
        $path = $variant !== null ? data_get($this->variants, $variant.'.path', $this->path) : $this->path;

        return Storage::disk($this->disk)->url($path);
    }
}
