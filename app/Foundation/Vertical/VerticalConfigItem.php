<?php

namespace App\Foundation\Vertical;

use App\Shared\Tenancy\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class VerticalConfigItem extends Model
{
    use BelongsToOrganization;

    public $timestamps = false;

    protected $fillable = [
        'organization_id', 'vertical_code', 'config_group',
        'code', 'label', 'is_required', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active'   => 'boolean',
    ];
}
