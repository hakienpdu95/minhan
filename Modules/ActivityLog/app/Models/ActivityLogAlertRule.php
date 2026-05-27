<?php

namespace Modules\ActivityLog\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLogAlertRule extends Model
{
    protected $table = 'activity_log_alert_rules';

    protected $fillable = [
        'name', 'module', 'action', 'level_min',
        'condition_type', 'threshold_count', 'window_minutes',
        'notify_channel', 'notify_target',
        'cooldown_minutes', 'last_triggered_at', 'is_active',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    /**
     * condition_type:
     *   1 = first_occurrence — trigger ngay lần đầu match
     *   2 = count_threshold  — trigger khi đạt threshold_count trong window_minutes
     */
    public function isCountThreshold(): bool
    {
        return $this->condition_type === 2;
    }
}
