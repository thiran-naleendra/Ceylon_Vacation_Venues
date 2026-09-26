<?php

namespace App\Models;

use Database\Factories\RedirectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['source_path', 'destination_path'])]
class Redirect extends Model
{
    /** @use HasFactory<RedirectFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    protected static function booted(): void
    {
        static::saving(function (self $redirect): void {
            foreach ([$redirect->source_path, $redirect->destination_path] as $path) {
                if (! is_string($path) || ! str_starts_with($path, '/') || str_starts_with($path, '//')
                    || strlen($path) > 512 || preg_match('/[\\\\\x00-\x20?#]/', $path)
                    || preg_match('/%(?:0[0-9a-f]|1[0-9a-f]|20|2f|5c|7f)/i', $path)
                    || preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $path)) {
                    throw new \InvalidArgumentException('Redirects require safe absolute internal paths without queries or fragments.');
                }
            }

            if ($redirect->source_path === $redirect->destination_path) {
                throw new \InvalidArgumentException('A redirect cannot target itself.');
            }

            if (! in_array($redirect->status_code ?? 301, [301, 302, 307, 308], true)) {
                throw new \InvalidArgumentException('Invalid redirect status code.');
            }

            $visited = [$redirect->source_path];
            $destination = $redirect->destination_path;

            while ($destination !== null) {
                if (in_array($destination, $visited, true)) {
                    throw new \InvalidArgumentException('Redirect loops are not allowed.');
                }

                $visited[] = $destination;
                $destination = static::query()->active()->where('source_path', $destination)
                    ->when($redirect->exists, fn (Builder $query) => $query->whereKeyNot($redirect->getKey()))
                    ->value('destination_path');
            }
        });
    }
}
