<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdminContentAuditObserver
{
    public function created(Model $model): void
    {
        $this->record($model, 'created');
    }

    public function updated(Model $model): void
    {
        $this->record($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted');
    }

    private function record(Model $model, string $operation): void
    {
        $actor = Auth::user();
        if (! $actor?->canAccessAdmin()) {
            return;
        }

        $attributes = $operation === 'created' ? $model->getAttributes() : $model->getChanges();
        $changedFields = collect(array_keys($attributes))
            ->reject(fn (string $field): bool => in_array($field, ['created_at', 'updated_at'], true))
            ->values()
            ->all();

        (new AuditLog)->forceFill([
            'actor_id' => $actor->getKey(),
            'action' => 'admin.'.Str::snake(class_basename($model)).'.'.$operation,
            'subject_type' => $model::class,
            'subject_id' => (int) $model->getKey(),
            'subject_label' => $this->label($model),
            'request_id' => (string) Str::uuid(),
            'metadata' => $changedFields === [] ? null : ['changed_fields' => $changedFields],
        ])->save();
    }

    private function label(Model $model): string
    {
        foreach (['title', 'name', 'source_path', 'slug'] as $field) {
            $value = $model->getAttribute($field);
            if (is_string($value) && $value !== '') {
                return Str::limit($value, 255, '');
            }
        }

        return class_basename($model).' #'.$model->getKey();
    }
}
