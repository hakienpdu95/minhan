<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SnapshotBand extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'snapshot_id', 'band_code', 'label', 'description', 'min_score', 'max_score', 'sort_order',
    ];

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(AssessmentConfigSnapshot::class, 'snapshot_id');
    }
}
