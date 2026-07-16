<?php

namespace Modules\BusinessProject\Models;

use App\Foundation\Models\TenantAwareModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BusinessProject\Enums\RenewalStatus;
use Modules\Lead\Models\Lead;
use Modules\Survey\Models\SurveyResponse;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Giai đoạn 8 (Customer Success Workspace) — mỗi hàng là 1 touchpoint (CSAT/NPS gắn 1
 * survey_response_id có sẵn, follow-up, renewal, hoặc New Opportunity đã tạo Lead). Không ép
 * 1 project chỉ có 1 hàng — CS có thể ghi nhận nhiều lần theo thời gian ("vòng đời không kết
 * thúc ở Closed").
 */
class SuccessReview extends TenantAwareModel
{
    protected $table = 'success_reviews';

    protected $fillable = [
        'organization_id',
        'uuid',
        'business_project_id',
        'survey_response_id',
        'csat_score',
        'nps_score',
        'follow_up_at',
        'follow_up_note',
        'followed_up_at',
        'renewal_status',
        'renewal_note',
        'new_lead_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'follow_up_at' => 'datetime',
        'followed_up_at' => 'datetime',
        'renewal_status' => RenewalStatus::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontLogEmptyChanges();
    }

    public function businessProject(): BelongsTo
    {
        return $this->belongsTo(BusinessProject::class);
    }

    public function surveyResponse(): BelongsTo
    {
        return $this->belongsTo(SurveyResponse::class);
    }

    public function newLead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'new_lead_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
