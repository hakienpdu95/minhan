<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;

class TuningCycle extends Model
{
    protected $table = 'tuning_cycles';

    protected $fillable = [
        'assessment_code',
        'cycle_number',
        'method',
        'feedback_count',
        'error_before',
        'error_after',
        'status',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'error_before' => 'float',
            'error_after'  => 'float',
            'started_at'   => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function wasImproved(): bool
    {
        return $this->error_after !== null
            && $this->error_before !== null
            && $this->error_after < $this->error_before;
    }
}
