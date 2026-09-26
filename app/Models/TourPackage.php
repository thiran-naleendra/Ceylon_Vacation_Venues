<?php

namespace App\Models;

use App\Enums\PackageAvailability;
use App\Models\Concerns\HasPublicationStatus;
use App\Models\Concerns\HasSeoMetadata;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasSortOrder;
use Database\Factories\TourPackageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['title', 'slug', 'summary', 'description', 'destination', 'availability_status', 'duration_days', 'duration_nights', 'starting_price', 'currency', 'price_basis', 'inclusions', 'exclusions', 'sort_order'])]
class TourPackage extends Model
{
    /** @use HasFactory<TourPackageFactory> */
    use HasFactory, HasPublicationStatus, HasSeoMetadata, HasSlug, HasSortOrder;

    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
            'duration_nights' => 'integer',
            'starting_price' => 'decimal:2',
            'is_featured' => 'boolean',
            'availability_status' => PackageAvailability::class,
        ];
    }

    public function itineraries(): HasMany
    {
        return $this->hasMany(PackageItinerary::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PackageImage::class);
    }

    public function featuredImage(): HasOne
    {
        return $this->hasOne(PackageImage::class)->ready()->ofMany('sort_order', 'min');
    }

    public function inquiryDetails(): HasMany
    {
        return $this->hasMany(PackageInquiryDetail::class);
    }

    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    #[Scope]
    protected function available(Builder $query): void
    {
        $query->where('availability_status', PackageAvailability::Available->value);
    }
}
