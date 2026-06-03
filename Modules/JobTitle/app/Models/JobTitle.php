<?php

namespace Modules\JobTitle\Models;

use App\Foundation\Models\TenantAwareModel;
use Modules\JobTitle\Enums\JobTitleCategory;

class JobTitle extends TenantAwareModel
{
    protected $fillable = [
        'uuid',
        'organization_id',
        'code',
        'name',
        'category',
        'level',
        'description',
        'is_system',
        'is_locked',
        'is_active',
    ];

    protected $casts = [
        'category'  => JobTitleCategory::class,
        'level'     => 'integer',
        'is_system' => 'boolean',
        'is_locked' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }
}
