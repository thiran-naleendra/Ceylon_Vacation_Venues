<?php

namespace App\Models;

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use Database\Factories\InquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'phone', 'country_code', 'subject', 'message'])]
class Inquiry extends Model
{
    /** @use HasFactory<InquiryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => InquiryType::class,
            'status' => InquiryStatus::class,
            'privacy_acknowledged_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function packageDetails(): HasOne
    {
        return $this->hasOne(PackageInquiryDetail::class);
    }

    public function rentalDetails(): HasOne
    {
        return $this->hasOne(RentalInquiryDetail::class);
    }

    public function visaDetails(): HasOne
    {
        return $this->hasOne(VisaInquiryDetail::class);
    }

    public function baggageDetails(): HasOne
    {
        return $this->hasOne(BaggageInquiryDetail::class);
    }

    public function propertyDetails(): HasOne
    {
        return $this->hasOne(PropertyInquiryDetail::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(InquiryNote::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(InquiryStatusHistory::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $inquiry): void {
            $inquiry->reference ??= (string) Str::ulid();
        });

        static::updating(function (self $inquiry): void {
            if ($inquiry->isDirty(['type', 'reference'])) {
                throw new \LogicException('An inquiry type and reference cannot change after creation.');
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    #[Scope]
    protected function ofType(Builder $query, InquiryType $type): void
    {
        $query->where('type', $type->value);
    }

    #[Scope]
    protected function withStatus(Builder $query, InquiryStatus $status): void
    {
        $query->where('status', $status->value);
    }

    #[Scope]
    protected function assignedTo(Builder $query, User $user): void
    {
        $query->where('assigned_to', $user->getKey());
    }

    #[Scope]
    protected function open(Builder $query): void
    {
        $query->whereIn('status', [InquiryStatus::New->value, InquiryStatus::Contacted->value, InquiryStatus::InProgress->value, InquiryStatus::AwaitingCustomer->value]);
    }
}
