<?php

namespace App\Models\Concerns;

use App\Enums\PublicationStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

trait HasPublicationStatus
{
    public function isPublished(): bool
    {
        return $this->status === PublicationStatus::Published
            && $this->published_at !== null
            && $this->published_at->isPast();
    }

    public function initializeHasPublicationStatus(): void
    {
        $this->mergeCasts(['status' => PublicationStatus::class, 'published_at' => 'immutable_datetime']);
    }

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where($this->qualifyColumn('status'), PublicationStatus::Published->value)
            ->whereNotNull($this->qualifyColumn('published_at'))
            ->where($this->qualifyColumn('published_at'), '<=', now());
    }

    #[Scope]
    protected function draft(Builder $query): void
    {
        $query->where($this->qualifyColumn('status'), PublicationStatus::Draft->value);
    }
}
