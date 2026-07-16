<?php

namespace Modules\BusinessProject\Models;

use App\Foundation\Models\TenantAwareModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\BusinessProject\Enums\RiskImpact;
use Modules\BusinessProject\Enums\RiskLikelihood;
use Modules\BusinessProject\Enums\RiskStatus;
use Spatie\Activitylog\Support\LogOptions;

class Risk extends TenantAwareModel
{
    protected $table = 'risks';

    protected $fillable = [
        'organization_id',
        'uuid',
        'business_project_id',
        'title',
        'description',
        'likelihood',
        'impact',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'likelihood' => RiskLikelihood::class,
        'impact' => RiskImpact::class,
        'status' => RiskStatus::class,
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
