<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Survey\Enums\ResponseStatus;

class SurveyResponse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'survey_responses';

    protected $fillable = [
        'survey_id',
        'respondent_ref',
        'respondent_ip',
        'status',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status'       => ResponseStatus::class,
            'submitted_at' => 'datetime',
            'deleted_at'   => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class, 'response_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopeComplete(Builder $query): Builder
    {
        return $query->where('status', ResponseStatus::Complete);
    }

    public function scopeForSurvey(Builder $query, int $surveyId): Builder
    {
        return $query->where('survey_id', $surveyId);
    }
}
