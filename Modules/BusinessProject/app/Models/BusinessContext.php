<?php

namespace Modules\BusinessProject\Models;

use App\Foundation\Models\TenantAwareModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Support\LogOptions;

class BusinessContext extends TenantAwareModel
{
    protected $table = 'business_contexts';

    protected $fillable = [
        'organization_id',
        'business_project_id',
        'company_profile',
        'stakeholders',
        'strategic_goals',
        'deliverable_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'company_profile' => 'array',
        'stakeholders' => 'array',
        'strategic_goals' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontLogEmptyChanges();
    }

    public function businessProject(): BelongsTo
    {
        return $this->belongsTo(BusinessProject::class);
    }

    public function deliverable(): BelongsTo
    {
        return $this->belongsTo(Deliverable::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
