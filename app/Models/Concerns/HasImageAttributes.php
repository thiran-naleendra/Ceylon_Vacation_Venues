<?php

namespace App\Models\Concerns;

use App\Enums\ImageProcessingStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

trait HasImageAttributes
{
    public function initializeHasImageAttributes(): void
    {
        $this->mergeCasts([
            'file_size' => 'integer', 'width' => 'integer', 'height' => 'integer',
            'variants' => 'array', 'processing_status' => ImageProcessingStatus::class,
        ]);
    }

    #[Scope]
    protected function ready(Builder $query): void
    {
        $query->where('processing_status', ImageProcessingStatus::Ready->value);
    }
}
