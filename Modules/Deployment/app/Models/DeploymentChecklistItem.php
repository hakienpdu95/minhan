<?php

namespace Modules\Deployment\Models;

use App\Models\User;
use App\Shared\Tenancy\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeploymentChecklistItem extends Model
{
    use BelongsToOrganization;

    public $timestamps = false;

    protected $fillable = [
        'organization_id',
        'deployment_target_id',
        'phase',
        'item_key',
        'item_label',
        'is_required',
        'is_done',
        'done_by',
        'done_at',
        'notes',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_done'     => 'boolean',
        'done_at'     => 'datetime',
    ];

    public function target(): BelongsTo
    {
        return $this->belongsTo(DeploymentTarget::class, 'deployment_target_id');
    }

    public function doneBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'done_by');
    }
}
