<?php

namespace Modules\KcItem\Models;

use App\Foundation\Models\TenantAwareModel;
use App\Traits\HasTenantMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Modules\Assessment\Models\RoadmapMilestone;
use Modules\KcCategory\Models\KcCategory;
use Modules\KcItem\Enums\KcItemStatus;
use Modules\KcItem\Enums\KcItemType;
use Modules\KcItem\Enums\KcItemVisibility;
use Spatie\MediaLibrary\HasMedia;

class KcItem extends TenantAwareModel implements HasMedia
{
    use HasTenantMedia;
    use Searchable;

    protected $table = 'kc_items';

    protected $fillable = [
        'uuid',
        'category_id',
        'business_project_id',
        'domain_code',
        'industry',
        'difficulty',
        'organization_id',
        'title',
        'slug',
        'summary',
        'content',
        'type',
        'status',
        'visibility',
        'language',
        'view_count',
        'download_count',
        'is_featured',
        'is_pinned',
        'owner_id',
        'approved_by',
        'approved_at',
        'version',
        'effective_date',
        'expired_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'type'           => KcItemType::class,
        'status'         => KcItemStatus::class,
        'visibility'     => KcItemVisibility::class,
        'is_featured'    => 'boolean',
        'is_pinned'      => 'boolean',
        'view_count'     => 'integer',
        'download_count' => 'integer',
        'difficulty'     => 'integer',
        'version'        => 'integer',
        'approved_at'    => 'datetime',
        'effective_date' => 'datetime',
        'expired_date'   => 'datetime',
    ];

    // ── Scout (full-text search — MeilisearchKcItemSearchDriver) ───────────────

    public function searchableAs(): string
    {
        return 'kc_items';
    }

    /**
     * `php artisan scout:import` chạy trong context console — không có `TenantContext` (chỉ
     * `IdentifyOrganization` middleware set, vòng đời HTTP request) — `OrganizationScope` fail-
     * closed về rỗng khi context chưa set (xem `OrganizationScope::apply()`), khiến import bulk
     * âm thầm đẩy 0 record lên Meilisearch nếu không bypass tường minh ở đây. Index cần TOÀN BỘ
     * KcItem của MỌI org (lọc theo `organization_id` ở tầng driver/query lúc search), không phải
     * riêng 1 tenant.
     */
    protected function makeAllSearchableUsing($query)
    {
        return $query->withoutTenant();
    }

    /**
     * Chỉ đưa field cần cho tìm kiếm + lọc lên Meilisearch — KHÔNG đồng nghĩa "nguồn dữ liệu",
     * MySQL vẫn là single source of truth (đọc lại record thật qua whereIn ở
     * MeilisearchKcItemSearchDriver, không trả thẳng nội dung từ index).
     * `organization_id` bắt buộc có mặt để driver filter đúng tenant (Meilisearch không tự áp
     * OrganizationScope như Eloquent).
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'title' => $this->title,
            'summary' => $this->summary,
            'content' => strip_tags((string) $this->content),
            'type' => $this->type?->value,
            'status' => $this->status?->value,
            'visibility' => $this->visibility?->value,
            'industry' => $this->industry,
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(KcCategory::class, 'category_id');
    }

    /**
     * BCOS (Business Consulting OS) — Knowledge Asset gắn với Business Project (Rule R7, nullable
     * — KcItem ngoài BCOS vẫn hợp lệ).
     */
    public function businessProject(): BelongsTo
    {
        return $this->belongsTo(\Modules\BusinessProject\Models\BusinessProject::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'owner_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(KcItemAttachment::class, 'item_id')->orderBy('sort_order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(KcTag::class, 'kc_item_tags', 'item_id', 'tag_id');
    }

    public function versionHistories(): HasMany
    {
        return $this->hasMany(KcVersionHistory::class, 'item_id')->orderByDesc('version_number');
    }

    public function accessControls(): HasMany
    {
        return $this->hasMany(KcAccessControl::class, 'item_id');
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(KcFeedback::class, 'item_id');
    }

    public function viewLogs(): HasMany
    {
        return $this->hasMany(KcViewLog::class, 'item_id');
    }

    public function roadmapMilestones(): BelongsToMany
    {
        return $this->belongsToMany(RoadmapMilestone::class, 'roadmap_milestone_kc_items', 'kc_item_id', 'roadmap_milestone_id')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeApproved($query)
    {
        return $query->where('status', KcItemStatus::Approved->value);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', KcItemStatus::Draft->value);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeForDomain($query, string $domainCode)
    {
        return $query->where('domain_code', $domainCode);
    }

    public function scopeForDifficulty($query, int $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isEditable(): bool
    {
        return in_array($this->status, [KcItemStatus::Draft, KcItemStatus::Rejected]);
    }

    public function canSubmit(): bool
    {
        return $this->status === KcItemStatus::Draft || $this->status === KcItemStatus::Rejected;
    }

    public function canApprove(): bool
    {
        return $this->status === KcItemStatus::PendingReview;
    }
}
