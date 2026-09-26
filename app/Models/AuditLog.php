<?php

namespace App\Models;

use Database\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([])]
class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'metadata' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    protected static function booted(): void
    {
        static::updating(function (self $audit): void {
            throw new \LogicException('Audit records are append-only.');
        });

        static::deleting(function (self $audit): void {
            throw new \LogicException('Audit retention must use a dedicated authorized process.');
        });
    }

    #[Scope]
    protected function forSubject(Builder $query, string $type, int $id): void
    {
        $query->where('subject_type', $type)->where('subject_id', $id);
    }
}
