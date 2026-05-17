<?php

namespace App\Shared\Tenancy\Models;

use App\Models\User;
use App\Shared\Tenancy\Enums\OrganizationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'status',
        'owner_id',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'status'   => OrganizationStatus::class,
            'settings' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Organization $org): void {
            if (empty($org->uuid)) {
                $org->uuid = (string) Str::uuid();
            }
            if (empty($org->slug)) {
                $org->slug = static::generateSlug($org->name);
            }
        });
    }

    // ── Relationships ────────────────────────────────────────────────

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', OrganizationStatus::Active->value);
    }

    public function scopeBySlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === OrganizationStatus::Active;
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public function setSetting(string $key, mixed $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->update(['settings' => $settings]);
    }

    public static function generateSlug(string $name): string
    {
        $slug = Str::slug($name);
        $count = static::where('slug', 'like', "{$slug}%")->count();
        return $count > 0 ? "{$slug}-{$count}" : $slug;
    }
}
