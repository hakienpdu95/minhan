<?php

namespace Modules\BusinessProject\Models;

use App\Foundation\Models\TenantAwareModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\BusinessProject\Enums\IssueSeverity;
use Modules\BusinessProject\Enums\IssueStatus;
use Spatie\Activitylog\Support\LogOptions;

class Issue extends TenantAwareModel
{
    protected $table = 'issues';

    protected $fillable = [
        'organization_id',
        'uuid',
        'business_project_id',
        'title',
        'description',
        'severity',
        'status',
        'resolved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'severity' => IssueSeverity::class,
        'status' => IssueStatus::class,
        'resolved_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontLogEmptyChanges();
    }

    public function businessProject(): BelongsTo
    {
        return $this->belongsTo(BusinessProject::class);
    }

    public function changeRequests(): HasMany
    {
        return $this->hasMany(ChangeRequest::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
