<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class JobPosition extends Model
{
    protected $table = 'job_positions';

    protected $fillable = [
        'assessment_code',
        'position_code',
        'title',
        'description',
        'min_overall_score',
        'requirements',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'requirements'     => 'array',
            'min_overall_score'=> 'float',
            'is_active'        => 'boolean',
        ];
    }

    public function scopeForAssessment(Builder $query, string $code): Builder
    {
        return $query->where('assessment_code', $code);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
