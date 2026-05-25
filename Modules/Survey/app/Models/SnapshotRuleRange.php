<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SnapshotRuleRange extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'snapshot_rule_id', 'min_value', 'max_value', 'score', 'signal_flag', 'sort_order',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(SnapshotRule::class, 'snapshot_rule_id');
    }
}
