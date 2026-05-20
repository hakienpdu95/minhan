<?php

namespace Modules\Organization\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Organization\Enums\MemberRole;

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
            'role'      => MemberRole::class,
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

    // ── Role aliases (backward compat) ──────────────────────────────
    // Dùng MemberRole enum trực tiếp trong code mới.

    public const ROLE_OWNER   = MemberRole::Owner->value;
    public const ROLE_ADMIN   = MemberRole::Admin->value;
    public const ROLE_MANAGER = MemberRole::Manager->value;
    public const ROLE_MEMBER  = MemberRole::Member->value;

    public static function roles(): array
    {
        return MemberRole::values();
    }
}
