<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SnapshotDomain extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'snapshot_id', 'domain_code', 'label', 'weight', 'min_score', 'max_score', 'sort_order',
    ];

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(AssessmentConfigSnapshot::class, 'snapshot_id');
    }
}
