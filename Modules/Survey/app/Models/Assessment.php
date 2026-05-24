<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $table = 'assessments';

    protected $fillable = [
        'assessment_code',
        'name',
        'version',
        'is_active',
        'has_scoring',
        'aggregation_model',
        'classification_type',
    ];

    protected function casts(): array
    {
        return [
            'is_active'   => 'boolean',
            'has_scoring' => 'boolean',
        ];
    }

    public static function findByCode(string $code): ?self
    {
        return static::where('assessment_code', $code)->where('is_active', true)->first();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function usesWeightedDomain(): bool
    {
        return $this->aggregation_model === 'weighted_domain';
    }

    public function usesSectioned(): bool
    {
        return $this->aggregation_model === 'sectioned';
    }

    public function usesFlatSum(): bool
    {
        return $this->aggregation_model === 'flat_sum';
    }
}
