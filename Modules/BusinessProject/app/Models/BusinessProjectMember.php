<?php

namespace Modules\BusinessProject\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BusinessProject\Enums\ProjectMemberRole;

class BusinessProjectMember extends Model
{
    protected $table = 'business_project_members';

    protected $fillable = [
        'business_project_id',
        'user_id',
        'project_role',
        'assigned_at',
    ];

    protected $casts = [
        'project_role' => ProjectMemberRole::class,
        'assigned_at' => 'datetime',
    ];

    public function businessProject(): BelongsTo
    {
        return $this->belongsTo(BusinessProject::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
