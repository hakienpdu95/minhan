<?php

namespace Modules\BusinessProject\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\BusinessProject\Enums\DeliverableType;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Template Library (Phase 2, mảng 5/5) — KHÔNG dùng TenantAwareModel: `organization_id` nullable
 * = template dùng chung mọi tổ chức (mirror đúng pattern `LeadSource`/`LeadPipelineStage`), có
 * giá trị = template riêng 1 org. Query luôn phải tự where global-or-org (xem
 * TemplateLibraryController), không có global scope tự động lọc.
 */
class DeliverableTemplate extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'deliverable_templates';

    protected $fillable = [
        'uuid',
        'organization_id',
        'type',
        'name',
        'description',
        'content',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontLogEmptyChanges();
    }

    public function deliverableType(): ?DeliverableType
    {
        return DeliverableType::tryFrom($this->type);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeAvailableTo($query, ?int $organizationId)
    {
        return $query->where(function ($q) use ($organizationId) {
            $q->whereNull('organization_id')->orWhere('organization_id', $organizationId);
        });
    }
}
