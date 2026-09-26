<?php

namespace App\Models;

use Database\Factories\WebsiteSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([])]
class WebsiteSetting extends Model
{
    /** @use HasFactory<WebsiteSettingFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }

    #[Scope]
    protected function inGroup(Builder $query, string $group): void
    {
        $query->where('group', $group);
    }
}
