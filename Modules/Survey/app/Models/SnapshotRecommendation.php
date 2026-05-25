<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SnapshotRecommendation extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'snapshot_id', 'recommendation_code', 'label', 'description',
        'trigger_domain', 'threshold_score', 'priority',
    ];

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(AssessmentConfigSnapshot::class, 'snapshot_id');
    }
}
