<?php

namespace Modules\BusinessProject\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phần 10 spec — nguồn dữ liệu cho KPI "Average Cycle Time theo giai đoạn". Không dùng
 * TenantAwareModel (bảng append-only, không sửa/xóa sau khi ghi — giống deliverable_versions).
 */
class BusinessProjectStageHistory extends Model
{
    protected $table = 'business_project_stage_history';

    public $timestamps = false;

    protected $fillable = [
        'business_project_id',
        'organization_id',
        'stage_from',
        'stage_to',
        'changed_by',
        'changed_at',
        'created_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function businessProject(): BelongsTo
    {
        return $this->belongsTo(BusinessProject::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
