<?php

namespace App\Foundation;

use App\Foundation\Vertical\DatabaseVertical;
use App\Foundation\Vertical\VerticalTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class VerticalRegistry
{
    /** Bản mẫu thư viện dùng chung (organization_id IS NULL) theo code. */
    public static function resolve(string $code): ?VerticalDefinition
    {
        return static::resolveForOrganization(null, $code);
    }

    /** Bản instance của 1 tổ chức cụ thể theo code — $organizationId null = thư viện dùng chung. */
    public static function resolveForOrganization(?int $organizationId, string $code): ?VerticalDefinition
    {
        // Cache array of attributes — Eloquent model không serialize tốt qua cache
        $attributes = Cache::remember(
            static::cacheKey($organizationId, $code),
            now()->addHour(),
            function () use ($organizationId, $code) {
                $query = VerticalTemplate::where('code', $code)->where('is_active', true);
                $query = $organizationId === null
                    ? $query->whereNull('organization_id')
                    : $query->where('organization_id', $organizationId);

                return $query->first()?->toArray();
            }
        );

        if (! $attributes) return null;

        $template = (new VerticalTemplate())->forceFill($attributes);
        return new DatabaseVertical($template);
    }

    /** Danh mục bản mẫu thư viện dùng chung (organization_id IS NULL, active) — cho UI System Admin. */
    public static function libraryTemplates(): Collection
    {
        return VerticalTemplate::whereNull('organization_id')
            ->where('is_active', true)
            ->orderBy('label')
            ->with('phases')
            ->get();
    }

    public static function all(): array
    {
        $rows = Cache::remember(
            'vertical_templates_all',
            now()->addHour(),
            fn () => VerticalTemplate::whereNull('organization_id')->where('is_active', true)->get()->map->toArray()->values()->all()
        );

        return collect($rows)
            ->mapWithKeys(fn ($attrs) => [
                $attrs['code'] => new DatabaseVertical((new VerticalTemplate())->forceFill($attrs))
            ])
            ->all();
    }

    public static function exists(string $code): bool
    {
        return static::resolve($code) !== null;
    }

    public static function clearCache(?int $organizationId = null, ?string $code = null): void
    {
        if ($code) {
            Cache::forget(static::cacheKey($organizationId, $code));
        }
        Cache::forget('vertical_templates_all');
    }

    private static function cacheKey(?int $organizationId, string $code): string
    {
        return 'vertical_template:' . ($organizationId ?? 'lib') . ':' . $code;
    }
}
