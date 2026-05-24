<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultJobPosition extends Model
{
    protected $table = 'result_job_positions';

    protected $fillable = [
        'result_id',
        'position_code',
        'match_score',
    ];

    protected function casts(): array
    {
        return [
            'match_score' => 'float',
        ];
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(SurveyResult::class, 'result_id');
    }
}
