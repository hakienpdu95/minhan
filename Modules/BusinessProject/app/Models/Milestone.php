<?php

namespace Modules\BusinessProject\Models;

use App\Foundation\Models\TenantAwareModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BusinessProject\Enums\MilestoneCategory;
use Spatie\Activitylog\Support\LogOptions;

class Milestone extends TenantAwareModel
{
    protected $table = 'milestones';

    protected $fillable = [
        'organization_id',
        'uuid',
        'business_project_id',
        'category',
        'title',
        'description',
        'target_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'category' => MilestoneCategory::class,
        'target_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontLogEmptyChanges();
    }

    public function businessProject(): BelongsTo
    {
        return $this->belongsTo(BusinessProject::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
