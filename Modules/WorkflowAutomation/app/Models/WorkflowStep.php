<?php

namespace Modules\WorkflowAutomation\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    protected $fillable = [
        'workflow_id', 'sort_order', 'action_type',
        'email_to', 'email_subject', 'email_template',
        'notif_title', 'notif_body', 'notif_target',
        'update_model', 'update_field', 'update_value',
        'webhook_url', 'webhook_method', 'webhook_secret',
        'lead_status', 'lead_source', 'lead_assigned_to',
        'user_tag', 'user_status',
        'delay_minutes',
    ];

    protected $casts = [
        'delay_minutes'  => 'integer',
        'webhook_method' => 'integer',
        'sort_order'     => 'integer',
    ];

    public function workflow(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function headers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkflowStepHeader::class, 'step_id');
    }
}
