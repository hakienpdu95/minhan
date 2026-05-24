<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RecommendationRule extends Model
{
    protected $table = 'recommendation_rules';

    protected $fillable = [
        'assessment_code',
        'recommendation_code',
        'label',
        'description',
        'trigger_domain',
        'threshold_score',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'threshold_score' => 'float',
            'priority'        => 'integer',
            'is_active'       => 'boolean',
        ];
    }

    public function scopeForAssessment(Builder $query, string $assessmentCode): Builder
    {
        return $query->where('assessment_code', $assessmentCode)
            ->where('is_active', true)
            ->orderBy('priority');
    }
}
