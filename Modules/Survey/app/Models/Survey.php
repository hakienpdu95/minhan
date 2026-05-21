<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Survey\Enums\SurveyStatus;

class Survey extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'surveys';

    protected $fillable = [
        'title',
        'slug',
        'status',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'status'  => SurveyStatus::class,
            'version' => 'integer',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────

    public function sections(): HasMany
    {
        return $this->hasMany(SurveySection::class)->orderBy('sort_order');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(SurveyField::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(SurveyToken::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', SurveyStatus::Active);
    }

    public function scopeBySlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }
}
