<?php

namespace Modules\WorkflowAutomation\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowExecutionStep extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'execution_id', 'step_id', 'sort_order',
        'action_type', 'status', 'error_message',
        'duration_ms', 'attempts', 'executed_at', 'created_at',
    ];

    protected $casts = [
        'status'      => 'integer',
        'duration_ms' => 'integer',
        'attempts'    => 'integer',
        'executed_at' => 'datetime',
        'created_at'  => 'datetime',
    ];

    public function execution(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WorkflowExecution::class);
    }
}
