<?php

namespace App\Models;

use App\Shared\Tenancy\Models\Organization;
use App\Shared\Tenancy\TenantContext;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Organization\Models\OrganizationMember;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'organization_id', 'department', 'is_active', 'last_active_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_active_at'    => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ── Relationships ────────────────────────────────────────────────

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function organizationMembership(): HasOne
    {
        return $this->hasOne(OrganizationMember::class);
    }

    // ── Tenant Helpers ───────────────────────────────────────────────

    /**
     * Returns the organization ID for the current request context.
     * Used by policies for same-tenant checks.
     *
     * Priority: TenantContext (middleware-resolved) → user's own organization_id.
     */
    public function getCurrentOrganizationIdAttribute(): ?int
    {
        return TenantContext::getOrganizationId() ?? $this->organization_id;
    }

    public function getCurrentOrganizationAttribute(): ?Organization
    {
        return TenantContext::get() ?? $this->organization;
    }

    public function belongsToOrganization(int $organizationId): bool
    {
        return $this->organization_id === $organizationId;
    }
}
