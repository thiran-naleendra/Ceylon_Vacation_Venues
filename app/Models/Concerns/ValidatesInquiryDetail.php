<?php

namespace App\Models\Concerns;

use App\Enums\InquiryType;
use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

trait ValidatesInquiryDetail
{
    abstract protected function inquiryType(): InquiryType;

    protected static function bootValidatesInquiryDetail(): void
    {
        static::saving(function (Model $model): void {
            $inquiry = Inquiry::find($model->inquiry_id);

            if (! $inquiry || $inquiry->type !== $model->inquiryType()) {
                throw new InvalidArgumentException('The detail record must belong to the matching inquiry type.');
            }

            if ($model->exists && $model->isDirty('inquiry_id')) {
                throw new InvalidArgumentException('Inquiry details cannot be moved between inquiries.');
            }
        });
    }
}
