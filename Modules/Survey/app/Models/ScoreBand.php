<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ScoreBand extends Model
{
    protected $table = 'score_bands';

    protected $fillable = [
        'assessment_code',
        'band_code',
        'label',
        'description',
        'min_score',
        'max_score',
        'default_min',
        'default_max',
        'is_dynamic',
        'lead_temperature',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'min_score'  => 'float',
            'max_score'  => 'float',
            'default_min' => 'float',
            'default_max' => 'float',
            'is_dynamic' => 'boolean',
        ];
    }

    public function scopeForAssessment(Builder $query, string $code): Builder
    {
        return $query->where('assessment_code', $code);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    public function contains(float $score): bool
    {
        return $score >= $this->min_score && $score <= $this->max_score;
    }
}
