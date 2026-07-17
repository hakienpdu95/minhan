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
        'template_id',
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

    public function template(): BelongsTo
    {
        return $this->belongsTo(DeliverableTemplate::class, 'template_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DeliverableVersion::class)->orderByDesc('version_number');
    }

    /**
     * Rule R4 — chữ ký nội bộ khi Confirmed (xem ConfirmDeliverableAction +
     * DeliverableSignatureProvider). Append-only, có thể nhiều hàng nếu deliverable từng
     * confirmed lại sau chu kỳ sửa/resubmit (xem Deliverable::resetApprovalCycle()).
     */
    public function signatures(): HasMany
    {
        return $this->hasMany(DeliverableSignature::class)->orderByDesc('signed_at');
    }

    public function latestSignature(): ?DeliverableSignature
    {
        return $this->signatures->first();
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

    /**
     * Ringlesoft `approvalStatus` (ProcessApprovalStatus) là vòng đời 1 CHIỀU — package không hỗ
     * trợ resubmit lần 2 trên cùng 1 record (`isSubmitted()` luôn true sau lần submit đầu, dù
     * cột `status` của app đã set lại 'draft'). Bug thật phát hiện khi test Change Request mở
     * khóa SOW: gọi method này SAU KHI set `status` app về draft, để `submit()` hoạt động lại —
     * tái tạo state ban đầu giống hệt `bootApprovable()` lúc tạo record, không có API reset sẵn
     * từ vendor. Dùng ở mọi nơi "sửa nội dung sau khi đã duyệt, cần duyệt lại" (Context sau khi
     * approved, SOW sau khi Change Request impacts_scope được duyệt).
     */
    public function resetApprovalCycle(): void
    {
        if (! $this->isSubmitted()) {
            return;
        }

        $this->approvalStatus()->update([
            'steps' => $this->approvalFlowSteps()->map(fn ($step) => $step->toApprovalStatusArray()),
            'status' => \RingleSoft\LaravelProcessApproval\Enums\ApprovalStatusEnum::CREATED->value,
            'creator_id' => \Illuminate\Support\Facades\Auth::id(),
        ]);
    }
}
