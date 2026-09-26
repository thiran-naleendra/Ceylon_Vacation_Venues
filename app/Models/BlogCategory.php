<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationStatus;
use App\Models\Concerns\HasSeoMetadata;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasSortOrder;
use Database\Factories\BlogCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'sort_order'])]
class BlogCategory extends Model
{
    /** @use HasFactory<BlogCategoryFactory> */
    use HasFactory, HasPublicationStatus, HasSeoMetadata, HasSlug, HasSortOrder;

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }
}
