<?php

namespace App\Models;

use App\Enums\InquiryType;
use App\Models\Concerns\ValidatesInquiryDetail;
use Database\Factories\PropertyInquiryDetailFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['check_in_date', 'check_out_date', 'guests'])] class PropertyInquiryDetail extends Model
{
    /** @use HasFactory<PropertyInquiryDetailFactory> */
    use HasFactory,ValidatesInquiryDetail;

    protected function inquiryType(): InquiryType
    {
        return InquiryType::Property;
    }

    protected function casts(): array
    {
        return ['check_in_date' => 'date', 'check_out_date' => 'date', 'guests' => 'integer'];
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
