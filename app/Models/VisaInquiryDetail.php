<?php

namespace App\Models;

use App\Enums\InquiryType;
use App\Models\Concerns\ValidatesInquiryDetail;
use Database\Factories\VisaInquiryDetailFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['nationality_code', 'arrival_date', 'current_visa_expiry_date', 'requested_extension_days'])]
class VisaInquiryDetail extends Model
{
    /** @use HasFactory<VisaInquiryDetailFactory> */
    use HasFactory, ValidatesInquiryDetail;

    protected function casts(): array
    {
        return [
            'arrival_date' => 'immutable_date',
            'current_visa_expiry_date' => 'immutable_date',
            'requested_extension_days' => 'integer',
        ];
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    protected function inquiryType(): InquiryType
    {
        return InquiryType::Visa;
    }
}
