<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

trait HasSortOrder
{
    public function initializeHasSortOrder(): void
    {
        $this->mergeCasts(['sort_order' => 'integer']);
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy($this->qualifyColumn('sort_order'))->orderBy($this->qualifyColumn('id'));
    }
}
