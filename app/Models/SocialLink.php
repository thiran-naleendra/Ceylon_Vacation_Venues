<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Database\Factories\SocialLinkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['platform', 'url', 'label', 'is_active', 'sort_order'])]
class SocialLink extends Model
{
    /** @use HasFactory<SocialLinkFactory> */
    use HasFactory, HasSortOrder;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
