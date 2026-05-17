<?php

namespace Modules\Organization\Models;

use App\Models\User;
use App\Shared\Tenancy\Models\Organization as BaseOrganization;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Extended Organization model for the Organization module.
 *
 * Adds: members, invitations, settings table relations.
 * Overrides getSetting()/setSetting() to use organization_settings table.
 */
class Organization extends BaseOrganization
{
    /**
     * Additional fillable fields beyond what the base model declares.
     */
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'status',
        'owner_id',
        'settings',
        // Business fields
        'tax_code',
        'phone',
        'email',
        'website',
        'industry',
        'address',
        'city',
        'country',
        'postal_code',
        'description',
        'logo_path',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function members(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(OrganizationInvitation::class);
    }

    public function orgSettings(): HasMany
    {
        return $this->hasMany(OrganizationSetting::class);
    }

    /**
     * Users belonging to this organization via the members pivot table.
     */
    public function memberUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_members', 'organization_id', 'user_id')
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    // ── Settings (table-backed) ──────────────────────────────────────

    /**
     * Get a setting value from the organization_settings table.
     * Falls back to JSON settings column, then to $default.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        $setting = $this->orgSettings()->where('key', $key)->first();

        if ($setting === null) {
            // Backward-compat: fall back to JSON settings column
            return data_get($this->settings, $key, $default);
        }

        return $setting->getCastedValue();
    }

    /**
     * Upsert a setting in the organization_settings table.
     */
    public function setSetting(string $key, mixed $value, string $type = 'string'): void
    {
        $serialized = match ($type) {
            'json'    => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default   => (string) $value,
        };

        $this->orgSettings()->updateOrCreate(
            ['key' => $key],
            ['value' => $serialized, 'type' => $type]
        );
    }
}
