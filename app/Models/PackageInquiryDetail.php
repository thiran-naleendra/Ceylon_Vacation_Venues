<?php

namespace App\Models;

use App\Enums\InquiryType;
use App\Models\Concerns\ValidatesInquiryDetail;
use Database\Factories\PackageInquiryDetailFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['preferred_start_date', 'preferred_end_date', 'adults', 'children'])]
class PackageInquiryDetail extends Model
{
    /** @use HasFactory<PackageInquiryDetailFactory> */
    use HasFactory, ValidatesInquiryDetail;

    protected function casts(): array
    {
        return [
            'preferred_start_date' => 'immutable_date',
            'preferred_end_date' => 'immutable_date',
            'adults' => 'integer',
            'children' => 'integer',
        ];
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    protected function inquiryType(): InquiryType
    {
        return InquiryType::Package;
    }
}
