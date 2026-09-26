<?php

namespace App\Models;

use App\Enums\InquiryType;
use App\Models\Concerns\ValidatesInquiryDetail;
use Database\Factories\RentalInquiryDetailFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pickup_at', 'return_at', 'pickup_location', 'return_location', 'driver_required'])]
class RentalInquiryDetail extends Model
{
    /** @use HasFactory<RentalInquiryDetailFactory> */
    use HasFactory, ValidatesInquiryDetail;

    protected function casts(): array
    {
        return [
            'pickup_at' => 'immutable_datetime',
            'return_at' => 'immutable_datetime',
            'driver_required' => 'boolean',
        ];
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
    }

    protected function inquiryType(): InquiryType
    {
        return InquiryType::Rental;
    }

    protected static function booted(): void
    {
        static::saving(function (self $detail): void {
            if (! $detail->vehicle_id && ! $detail->vehicle_category_id) {
                throw new \InvalidArgumentException('A rental inquiry requires a vehicle or category.');
            }

            if ($detail->vehicle_id && $detail->vehicle_category_id
                && ! Vehicle::whereKey($detail->vehicle_id)->where('vehicle_category_id', $detail->vehicle_category_id)->exists()) {
                throw new \InvalidArgumentException('The rental vehicle must belong to the selected category.');
            }
        });
    }
}
