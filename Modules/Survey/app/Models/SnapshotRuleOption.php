<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SnapshotRuleOption extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'snapshot_rule_id', 'option_value', 'option_label', 'score', 'signal_flag', 'sort_order',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(SnapshotRule::class, 'snapshot_rule_id');
    }
}
