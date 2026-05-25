<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SnapshotPainPoint extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'snapshot_id', 'pain_point_code', 'label', 'required_flags',
    ];

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(AssessmentConfigSnapshot::class, 'snapshot_id');
    }
}
