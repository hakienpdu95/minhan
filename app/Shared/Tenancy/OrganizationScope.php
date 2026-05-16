<?php

namespace App\Shared\Tenancy;

use App\Shared\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope automatically applied to all models using BelongsToOrganization.
 * Filters queries to the current tenant's organization_id.
 */
final class OrganizationScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (TenantContext::isSet()) {
            $builder->where(
                $model->getTable() . '.organization_id',
                TenantContext::getOrganizationId()
            );
        }
    }
}
