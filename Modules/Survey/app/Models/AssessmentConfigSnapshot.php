<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentConfigSnapshot extends Model
{
    protected $fillable = [
        'assessment_code',
        'version',
        'has_scoring',
        'aggregation_model',
        'classification_type',
        'passing_score',
        'label_pass',
        'label_fail',
        'created_by',
        'change_note',
    ];

    protected $casts = [
        'has_scoring'   => 'boolean',
        'passing_score' => 'decimal:2',
    ];

    public function domains(): HasMany
    {
        return $this->hasMany(SnapshotDomain::class, 'snapshot_id');
    }

    public function bands(): HasMany
    {
        return $this->hasMany(SnapshotBand::class, 'snapshot_id');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(SnapshotRule::class, 'snapshot_id');
    }

    public function personas(): HasMany
    {
        return $this->hasMany(SnapshotPersona::class, 'snapshot_id');
    }

    public function painPoints(): HasMany
    {
        return $this->hasMany(SnapshotPainPoint::class, 'snapshot_id');
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(SnapshotRecommendation::class, 'snapshot_id');
    }

    public function roadmapPhases(): HasMany
    {
        return $this->hasMany(SnapshotRoadmapPhase::class, 'snapshot_id');
    }
}
