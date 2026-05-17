<?php

namespace App\Shared\Support;

use App\Shared\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Support\ActivityLogger as SpatieLogger;

/**
 * Thin wrapper around spatie/laravel-activitylog.
 *
 * Automatically injects the current authenticated user as causer
 * and the current organization_id as a property when a tenant is active.
 *
 * Usage:
 *   ActivityLogger::log('created', $lead);
 *   ActivityLogger::log('status_changed', $lead, ['from' => 'new', 'to' => 'qualified']);
 *   ActivityLogger::on('crm')->log('bulk_assign', $lead);
 */
final class ActivityLogger
{
    /**
     * Log an event against a model with optional extra properties.
     * Causer = auth()->user(). Organization_id injected if tenant is set.
     */
    public static function log(string $event, Model $model, array $properties = []): void
    {
        static::builder()
            ->performedOn($model)
            ->withProperties(static::injectTenant($properties))
            ->log($event);
    }

    /**
     * Log an event without a specific subject model.
     */
    public static function logEvent(string $event, array $properties = []): void
    {
        static::builder()
            ->withProperties(static::injectTenant($properties))
            ->log($event);
    }

    /**
     * Return a logger scoped to a named log (e.g., 'crm', 'ai', 'workflow').
     */
    public static function on(string $logName): SpatieLogger
    {
        return static::builder()->useLog($logName);
    }

    private static function builder(): SpatieLogger
    {
        return activity()->causedBy(auth()->user());
    }

    private static function injectTenant(array $properties): array
    {
        if (TenantContext::isSet()) {
            $properties['organization_id'] = TenantContext::getOrganizationId();
        }

        return $properties;
    }
}
