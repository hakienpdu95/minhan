<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeatureWeight extends Model
{
    protected $table = 'feature_weights';

    protected $fillable = [
        'assessment_code',
        'feature_code',
        'domain_code',
        'weight_level',
        'weight',
        'default_weight',
        'weight_min',
        'weight_max',
        'version',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'weight'         => 'float',
            'default_weight' => 'float',
            'weight_min'     => 'float',
            'weight_max'     => 'float',
            'version'        => 'integer',
        ];
    }

    public function history(): HasMany
    {
        return $this->hasMany(FeatureWeightHistory::class, 'feature_weight_id');
    }

    public function scopeForAssessment(Builder $query, string $code): Builder
    {
        return $query->where('assessment_code', $code);
    }

    public function scopeDomainLevel(Builder $query): Builder
    {
        return $query->where('weight_level', 'domain');
    }

    public function maxVersion(string $assessmentCode): int
    {
        return static::forAssessment($assessmentCode)->max('version') ?? 1;
    }
}
