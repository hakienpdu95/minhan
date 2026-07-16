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

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(\Modules\Task\Models\Task::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    public function risks(): HasMany
    {
        return $this->hasMany(Risk::class);
    }

    public function changeRequests(): HasMany
    {
        return $this->hasMany(ChangeRequest::class);
    }

    /**
     * Rule R7 — Knowledge Asset gắn project (spec Phần 6.3). Liên kết 2 chiều đầy đủ (industry,
     * types case_study/lessons_learned/best_practice...) là Phase 2 — ở đây chỉ đủ để đếm điều
     * kiện gate.
     */
    public function kcItems(): HasMany
    {
        return $this->hasMany(\Modules\KcItem\Models\KcItem::class);
    }

    /**
     * Giai đoạn 8 — Customer Success Workspace (CSAT/NPS, follow-up, renewal, New Opportunity).
     */
    public function successReviews(): HasMany
    {
        return $this->hasMany(SuccessReview::class);
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
