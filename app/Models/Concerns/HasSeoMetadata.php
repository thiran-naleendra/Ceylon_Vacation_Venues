<?php

namespace App\Models\Concerns;

use App\Models\SeoMetadata;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

trait HasSeoMetadata
{
    public function seoMetadata(): HasOne
    {
        return $this->hasOne(SeoMetadata::class, Str::snake(class_basename($this)).'_id');
    }
}
