<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;

trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::saving(function (Model $model): void {
            if ($model->exists && ! $model->isDirty('slug')) {
                return;
            }

            $source = filled($model->slug) ? $model->slug : ($model->title ?? $model->name);
            $slug = Str::slug((string) $source);

            if ($slug === '' || strlen($slug) > 180) {
                throw new InvalidArgumentException('A public slug must contain between 1 and 180 URL-safe characters.');
            }

            if (in_array($slug, ['admin', 'api', 'category', 'create', 'edit', 'preview', 'sitemap', 'robots', 'up'], true)) {
                throw new InvalidArgumentException('This slug is reserved for application routes.');
            }

            if (blank($model->slug)) {
                $base = $slug;
                $suffix = 2;

                while ($model->newQuery()->where('slug', $slug)->when($model->exists, fn ($query) => $query->whereKeyNot($model->getKey()))->exists()) {
                    $ending = '-'.$suffix++;
                    $slug = substr($base, 0, 180 - strlen($ending)).$ending;
                }
            }

            $model->slug = $slug;
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
