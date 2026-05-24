<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PainPointRule extends Model
{
    protected $table = 'pain_point_rules';

    protected $fillable = [
        'assessment_code',
        'pain_point_code',
        'label',
        'required_flags',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeForAssessment(Builder $query, string $assessmentCode): Builder
    {
        return $query->where('assessment_code', $assessmentCode)->where('is_active', true);
    }
}
