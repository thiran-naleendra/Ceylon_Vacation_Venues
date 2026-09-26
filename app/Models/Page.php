<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationStatus;
use App\Models\Concerns\HasSeoMetadata;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasSortOrder;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'slug', 'summary', 'body', 'sort_order'])]
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory, HasPublicationStatus, HasSeoMetadata, HasSlug, HasSortOrder;

    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class);
    }
}
