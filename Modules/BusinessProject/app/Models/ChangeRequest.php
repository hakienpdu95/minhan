<?php

namespace Modules\BusinessProject\Models;

use App\Foundation\Models\TenantAwareModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BusinessProject\Enums\ChangeRequestSourceType;
use Modules\BusinessProject\Enums\ChangeRequestStatus;
use Modules\BusinessProject\Enums\DeliverableStatus;
use Modules\BusinessProject\Enums\DeliverableType;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;
use RingleSoft\LaravelProcessApproval\Models\ProcessApproval;
use RingleSoft\LaravelProcessApproval\Traits\Approvable;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Rule R5 (spec Giai đoạn 5): Issue/Risk nghiêm trọng escalate thành Change Request, duyệt qua
 * Approval Service — flow RIÊNG "Change Request Approval" (khác "Deliverable Approval" của
 * Deliverable, Ringlesoft chỉ cho 1 Model = 1 flow), đăng ký 1 lần ở BusinessProjectPermissionSeeder.
 */
class ChangeRequest extends TenantAwareModel implements ApprovableModel
{
    use Approvable;

    protected $table = 'change_requests';

    protected $fillable = [
        'organization_id',
        'uuid',
        'business_project_id',
        'source_type',
        'issue_id',
        'risk_id',
        'title',
        'description',
        'impacts_scope',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'source_type' => ChangeRequestSourceType::class,
        'status' => ChangeRequestStatus::class,
        'impacts_scope' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontLogEmptyChanges();
    }

    public function businessProject(): BelongsTo
    {
        return $this->belongsTo(BusinessProject::class);
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function risk(): BelongsTo
    {
        return $this->belongsTo(Risk::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * "nếu ảnh hưởng scope thì sinh version mới cho SOW" (spec Giai đoạn 5) — KHÔNG tự chế nội
     * dung version thay Consultant (không biết nội dung thương lượng thật là gì), chỉ mở khóa
     * lại SOW đang `confirmed` về `draft` để Consultant sửa qua đúng luồng `SaveSowAction` hiện
     * có — lần sửa tiếp theo tự sinh version mới (`UpsertSingletonDeliverableAction` luôn bump
     * version khi save). Roadmap không có luồng confirm (chỉ Proposal/SOW đi qua Rule R4) nên
     * không nằm trong điều kiện này.
     *
     * `resetApprovalCycle()` bắt buộc — nếu chỉ set `status` app về draft mà không reset,
     * Consultant KHÔNG thể "Gửi phê duyệt nội bộ" lại được (Ringlesoft ném
     * RequestAlreadySubmittedException, xem Deliverable::resetApprovalCycle()).
     */
    public function onApprovalCompleted(ProcessApproval $approval): bool
    {
        $this->status = ChangeRequestStatus::Approved->value;
        $this->save();

        if ($this->impacts_scope) {
            $sow = $this->businessProject->deliverables()
                ->where('type', DeliverableType::Sow->value)
                ->whereNull('parent_id')
                ->where('status', DeliverableStatus::Confirmed->value)
                ->first();

            $sow?->resetApprovalCycle();
            $sow?->update(['status' => DeliverableStatus::Draft->value]);
        }

        return true;
    }
}
