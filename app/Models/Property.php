<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationStatus;
use App\Models\Concerns\HasSeoMetadata;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasSortOrder;
use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['property_type_id', 'name', 'slug', 'short_description', 'description', 'location', 'address_description', 'price', 'currency', 'pricing_unit', 'bedrooms', 'bathrooms', 'max_guests', 'beds_details', 'availability_information', 'check_in_time', 'check_out_time', 'sort_order'])]
class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory,HasPublicationStatus,HasSeoMetadata,HasSlug,HasSortOrder;

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'bathrooms' => 'decimal:1', 'bedrooms' => 'integer', 'max_guests' => 'integer', 'is_featured' => 'boolean', 'check_in_time' => 'datetime:H:i', 'check_out_time' => 'datetime:H:i'];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class, 'property_type_id');
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class);
    }

    public function featuredImage(): HasOne
    {
        return $this->hasOne(PropertyImage::class)->ready()->ofMany('sort_order', 'min');
    }

    public function inquiryDetails(): HasMany
    {
        return $this->hasMany(PropertyInquiryDetail::class);
    }

    #[Scope]
    protected function featured(Builder $q): void
    {
        $q->where('is_featured', true);
    }
}
