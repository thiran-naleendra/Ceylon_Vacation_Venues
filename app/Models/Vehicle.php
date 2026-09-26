<?php

namespace App\Models;

use App\Enums\VehicleAvailability;
use App\Models\Concerns\HasPublicationStatus;
use App\Models\Concerns\HasSeoMetadata;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasSortOrder;
use Database\Factories\VehicleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['vehicle_category_id', 'title', 'slug', 'make', 'model', 'model_year', 'summary', 'description', 'seats', 'luggage_capacity', 'has_air_conditioning', 'transmission', 'fuel_type', 'rental_rate', 'currency', 'rate_unit', 'rental_terms', 'availability_status', 'sort_order'])]
class Vehicle extends Model
{
    /** @use HasFactory<VehicleFactory> */
    use HasFactory, HasPublicationStatus, HasSeoMetadata, HasSlug, HasSortOrder;

    protected function casts(): array
    {
        return [
            'model_year' => 'integer',
            'seats' => 'integer',
            'rental_rate' => 'decimal:2',
            'is_featured' => 'boolean',
            'luggage_capacity' => 'integer',
            'has_air_conditioning' => 'boolean',
            'availability_status' => VehicleAvailability::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(VehicleImage::class);
    }

    public function featuredImage(): HasOne
    {
        return $this->hasOne(VehicleImage::class)->ready()->ofMany('sort_order', 'min');
    }

    public function rentalInquiryDetails(): HasMany
    {
        return $this->hasMany(RentalInquiryDetail::class);
    }

    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }
}
