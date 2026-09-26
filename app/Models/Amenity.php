<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasSortOrder;
use Database\Factories\AmenityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'slug', 'description', 'is_active', 'sort_order'])] class Amenity extends Model
{
    /** @use HasFactory<AmenityFactory> */
    use HasFactory,HasSlug,HasSortOrder;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class);
    }
}
