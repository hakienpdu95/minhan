<?php

namespace App\Foundation\Vertical;

use App\Shared\Tenancy\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Organization\Models\Organization;

class OrganizationVertical extends Model
{
    use BelongsToOrganization;

    public $timestamps = false;

    protected $fillable = [
        'organization_id', 'vertical_code', 'status', 'config', 'activated_at', 'activated_by',
    ];

    protected $casts = [
        'config'       => 'array',
        'activated_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
