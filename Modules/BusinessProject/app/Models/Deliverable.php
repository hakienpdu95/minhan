<?php

namespace Modules\BusinessProject\Models;

use App\Foundation\Models\TenantAwareModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\BusinessProject\Enums\BusinessProjectStage;
use Modules\BusinessProject\Enums\DeliverableStatus;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;
use RingleSoft\LaravelProcessApproval\Models\ProcessApproval;
use RingleSoft\LaravelProcessApproval\Traits\Approvable;
use Spatie\Activitylog\Support\LogOptions;

class Deliverable extends TenantAwareModel implements ApprovableModel
{
    use Approvable;

    protected $table = 'deliverables';

    protected $fillable = [
        'organization_id',
        'uuid',
        'business_project_id',
        'workspace',
        'type',
        'title',
        'parent_id',
        'current_version',
        'status',
        'confirmed_at',
        'confirmed_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'workspace' => BusinessProjectStage::class,
        'status' => DeliverableStatus::class,
        'confirmed_at' => 'datetime',
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

    public function businessProject(): BelongsTo
    {
        return $this->belongsTo(BusinessProject::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DeliverableVersion::class)->orderByDesc('version_number');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Deliverable evidence liên kết (Phần 6.2 spec) — deliverable này là bên
     * ĐANG cần bằng chứng. Chưa có UI dùng ở Vertical Slice 1 (Diagnosis Workspace,
     * Phase 2), quan hệ tạo sẵn để không phải sửa lại khi Phase 2 cần.
     */
    public function evidenceFor(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'deliverable_evidence_links',
            'deliverable_id',
            'evidence_id'
        )->withPivot(['evidence_type', 'note', 'created_by']);
    }

    // ── Ringlesoft ApprovableModel hook ────────────────────────────────────
    // Được gọi tự động (method_exists check trong trait Approvable) khi flow duyệt
    // hoàn tất — đồng bộ cột `status` cục bộ để UI/Query không phải hỏi lại Ringlesoft.

    public function onApprovalCompleted(ProcessApproval $approval): bool
    {
        $this->status = DeliverableStatus::Approved->value;
        $this->save();

        return true;
    }
}
