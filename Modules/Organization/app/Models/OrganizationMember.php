<?php

namespace Modules\Organization\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pivot/join model for organization membership.
 *
 * Not a TenantAwareModel — the organization_id IS the tenant scope here,
 * so a global scope would be circular.
 */
class OrganizationMember extends Model
{
    protected $table = 'organization_members';

    protected $fillable = [
        'organization_id',
        'user_id',
        'role',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'role'      => 'string',
            'joined_at' => 'datetime',
        ];
    }

    // ── Relationships ────────────────────────────────────────────────

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Role constants ───────────────────────────────────────────────

    public const ROLE_OWNER   = 'owner';
    public const ROLE_ADMIN   = 'admin';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_MEMBER  = 'member';

    public static function roles(): array
    {
        return [self::ROLE_OWNER, self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_MEMBER];
    }
}
