<?php

namespace App\Models;

use App\Enums\InquiryType;
use App\Models\Concerns\ValidatesInquiryDetail;
use Database\Factories\BaggageInquiryDetailFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pickup_location', 'delivery_location', 'pickup_at', 'bag_count', 'estimated_weight_kg', 'special_instructions'])]
class BaggageInquiryDetail extends Model
{
    /** @use HasFactory<BaggageInquiryDetailFactory> */
    use HasFactory, ValidatesInquiryDetail;

    protected function casts(): array
    {
        return [
            'pickup_at' => 'immutable_datetime',
            'bag_count' => 'integer',
            'estimated_weight_kg' => 'decimal:2',
        ];
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    protected function inquiryType(): InquiryType
    {
        return InquiryType::Baggage;
    }
}
