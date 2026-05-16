<?php

namespace App\Shared\Tenancy\Traits;

use App\Shared\Tenancy\Models\Organization;
use App\Shared\Tenancy\OrganizationScope;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Add to any Eloquent model that should be scoped to the current tenant.
 *
 * - Auto-applies OrganizationScope as a global scope.
 * - Auto-assigns organization_id on create when context is set.
 * - Provides organization() relationship.
 */
trait BelongsToOrganization
{
    public static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope(new OrganizationScope());

        static::creating(function (Model $model): void {
            if (TenantContext::isSet() && empty($model->organization_id)) {
                $model->organization_id = TenantContext::getOrganizationId();
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** Query builder macro: bypass tenant scope for cross-tenant operations (admin only). */
    public function scopeWithoutTenant($query)
    {
        return $query->withoutGlobalScope(OrganizationScope::class);
    }

    /** Explicit scope to switch to a specific organization's data. */
    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->withoutGlobalScope(OrganizationScope::class)
            ->where($this->getTable() . '.organization_id', $organizationId);
    }
}
