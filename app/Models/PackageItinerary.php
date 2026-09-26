<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Database\Factories\PackageItineraryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['day_number', 'title', 'description', 'overnight_location', 'sort_order'])]
class PackageItinerary extends Model
{
    /** @use HasFactory<PackageItineraryFactory> */
    use HasFactory, HasSortOrder;

    protected function casts(): array
    {
        return [
            'day_number' => 'integer',
        ];
    }

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }
}
