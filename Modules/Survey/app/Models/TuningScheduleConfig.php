<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;

class TuningScheduleConfig extends Model
{
    protected $table = 'tuning_schedule_config';

    protected $fillable = [
        'assessment_code',
        'is_auto_tuning_enabled',
        'min_feedback_to_trigger',
        'max_cooldown_days',
        'learning_rate',
        'max_weight_change_pct',
        'last_cycle_at',
    ];

    protected function casts(): array
    {
        return [
            'is_auto_tuning_enabled'  => 'boolean',
            'min_feedback_to_trigger' => 'integer',
            'max_cooldown_days'       => 'integer',
            'learning_rate'           => 'float',
            'max_weight_change_pct'   => 'float',
            'last_cycle_at'           => 'datetime',
        ];
    }

    public static function findByCode(string $code): ?self
    {
        return static::where('assessment_code', $code)->first();
    }

    public function isAutoTuningEnabled(): bool
    {
        return $this->is_auto_tuning_enabled;
    }
}
