<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SnapshotRule extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'snapshot_id', 'field_key', 'domain_code', 'feature_code', 'signal_flag',
        'score_if_true', 'score_if_false', 'question_scoring_type', 'condition_type',
        'min_score_cap', 'max_score_cap',
    ];

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(AssessmentConfigSnapshot::class, 'snapshot_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(SnapshotRuleOption::class, 'snapshot_rule_id')->orderBy('sort_order');
    }

    public function ranges(): HasMany
    {
        return $this->hasMany(SnapshotRuleRange::class, 'snapshot_rule_id')->orderBy('sort_order');
    }
}
