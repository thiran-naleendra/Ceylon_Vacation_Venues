<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationStatus;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasSortOrder;
use Database\Factories\PropertyTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'sort_order'])] class PropertyType extends Model
{
    /** @use HasFactory<PropertyTypeFactory> */
    use HasFactory,HasPublicationStatus,HasSlug,HasSortOrder;

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }
}
