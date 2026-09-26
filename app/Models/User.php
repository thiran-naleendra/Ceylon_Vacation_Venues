<?php

namespace App\Models;

use App\Enums\UserRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** @var array<string, mixed> */
    protected $attributes = [
        'role' => 'none',
        'is_active' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'last_login_at' => 'immutable_datetime',
        ];
    }

    public function assignedInquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'assigned_to');
    }

    public function inquiryNotes(): HasMany
    {
        return $this->hasMany(InquiryNote::class, 'author_id');
    }

    public function inquiryStatusHistories(): HasMany
    {
        return $this->hasMany(InquiryStatusHistory::class, 'changed_by');
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_id');
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    #[Scope]
    protected function withRole(Builder $query, UserRole $role): void
    {
        $query->where('role', $role->value);
    }

    public function canAccessAdmin(): bool
    {
        return $this->is_active && $this->role->canAccessAdmin();
    }
}
