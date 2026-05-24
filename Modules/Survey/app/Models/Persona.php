<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Persona extends Model
{
    protected $table = 'personas';

    protected $fillable = [
        'assessment_code',
        'persona_code',
        'label',
        'description',
        'sort_order',
    ];

    public function conditions(): HasMany
    {
        return $this->hasMany(PersonaCondition::class, 'persona_id')->orderBy('sort_order');
    }

    public function scopeForAssessment(Builder $query, string $code): Builder
    {
        return $query->where('assessment_code', $code)->orderBy('sort_order');
    }
}
