<?php

namespace App\Foundation;

use App\Foundation\Vertical\DatabaseVertical;
use App\Foundation\Vertical\VerticalTemplate;
use Illuminate\Support\Facades\Cache;

class VerticalRegistry
{
    public static function resolve(string $code): ?VerticalDefinition
    {
        // Cache array of attributes — Eloquent model không serialize tốt qua cache
        $attributes = Cache::remember(
            "vertical_template:{$code}",
            now()->addHour(),
            fn() => VerticalTemplate::where('code', $code)->where('is_active', true)->first()?->toArray()
        );

        if (! $attributes) return null;

        $template = (new VerticalTemplate())->forceFill($attributes);
        return new DatabaseVertical($template);
    }

    public static function all(): array
    {
        $rows = Cache::remember(
            'vertical_templates_all',
            now()->addHour(),
            fn() => VerticalTemplate::where('is_active', true)->get()->map->toArray()->values()->all()
        );

        return collect($rows)
            ->mapWithKeys(fn($attrs) => [
                $attrs['code'] => new DatabaseVertical((new VerticalTemplate())->forceFill($attrs))
            ])
            ->all();
    }

    public static function exists(string $code): bool
    {
        return static::resolve($code) !== null;
    }

    public static function clearCache(?string $code = null): void
    {
        if ($code) {
            Cache::forget("vertical_template:{$code}");
        }
        Cache::forget('vertical_templates_all');
    }
}
