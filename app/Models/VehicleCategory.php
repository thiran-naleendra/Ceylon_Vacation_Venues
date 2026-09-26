<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationStatus;
use App\Models\Concerns\HasSeoMetadata;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasSortOrder;
use Database\Factories\VehicleCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'sort_order'])]
class VehicleCategory extends Model
{
    /** @use HasFactory<VehicleCategoryFactory> */
    use HasFactory, HasPublicationStatus, HasSeoMetadata, HasSlug, HasSortOrder;

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function rentalInquiryDetails(): HasMany
    {
        return $this->hasMany(RentalInquiryDetail::class);
    }
}
