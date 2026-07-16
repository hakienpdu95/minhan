<?php

namespace Modules\BusinessProject\Models;

use App\Foundation\Models\TenantAwareModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BusinessProject\Enums\MeetingType;
use Spatie\Activitylog\Support\LogOptions;

class Meeting extends TenantAwareModel
{
    protected $table = 'meetings';

    protected $fillable = [
        'organization_id',
        'uuid',
        'business_project_id',
        'type',
        'title',
        'held_at',
        'deliverable_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'type' => MeetingType::class,
        'held_at' => 'datetime',
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
}
