<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Database\Factories\PageSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['content', 'is_enabled', 'sort_order'])]
class PageSection extends Model
{
    /** @use HasFactory<PageSectionFactory> */
    use HasFactory, HasSortOrder;

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'is_enabled' => 'boolean',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
