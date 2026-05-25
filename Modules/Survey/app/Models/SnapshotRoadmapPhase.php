<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SnapshotRoadmapPhase extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'snapshot_id', 'band_code', 'phase_code', 'title', 'description', 'duration_weeks', 'sort_order',
    ];

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(AssessmentConfigSnapshot::class, 'snapshot_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(SnapshotRoadmapMilestone::class, 'snapshot_phase_id')->orderBy('sort_order');
    }
}
