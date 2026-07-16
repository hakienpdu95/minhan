<?php

namespace Modules\BusinessProject\Models;

use App\Foundation\Models\TenantAwareModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\BusinessProject\Enums\BusinessProjectStage;
use Modules\Customer\Models\Customer;
use Modules\Lead\Models\Lead;
use Spatie\Activitylog\Support\LogOptions;

class BusinessProject extends TenantAwareModel
{
    protected $table = 'business_projects';

    protected $fillable = [
        'organization_id',
        'uuid',
        'customer_id',
        'lead_id',
        'code',
        'name',
        'current_stage',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'current_stage' => BusinessProjectStage::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontLogEmptyChanges();
    }

    // ── Relationships ────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function members(): HasMany
    {
        return $this->hasMany(BusinessProjectMember::class);
    }

    public function context(): HasOne
    {
        return $this->hasOne(BusinessContext::class);
    }

    public function deliverables(): HasMany
    {
        return $this->hasMany(Deliverable::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    public function isMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function memberRole(User $user): ?string
    {
        return $this->members()->where('user_id', $user->id)->value('project_role');
    }
}
