<?php

namespace App\Foundation\Vertical;

use App\Foundation\VerticalDefinition;
use Illuminate\Support\Collection;

class VerticalConfigService
{
    public static function activityTypes(int $orgId, string $verticalCode, VerticalDefinition $vertical): array
    {
        $rows = VerticalConfigItem::withoutTenant()
            ->where('organization_id', $orgId)
            ->where('vertical_code', $verticalCode)
            ->where('config_group', 'activity_type')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('label', 'code')
            ->toArray();

        return $rows ?: ($vertical->defaultActivityTypes() ?? []);
    }

    public static function legalDocTypes(int $orgId, string $verticalCode, VerticalDefinition $vertical): array
    {
        $rows = VerticalConfigItem::withoutTenant()
            ->where('organization_id', $orgId)
            ->where('vertical_code', $verticalCode)
            ->where('config_group', 'doc_type')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('label', 'code')
            ->toArray();

        return $rows ?: ($vertical->defaultLegalDocTypes() ?? []);
    }

    public static function configItems(int $orgId, string $verticalCode, string $configGroup): Collection
    {
        return VerticalConfigItem::withoutTenant()
            ->where('organization_id', $orgId)
            ->where('vertical_code', $verticalCode)
            ->where('config_group', $configGroup)
            ->orderBy('sort_order')
            ->get();
    }

    public static function hierarchyLabels(int $orgId, string $verticalCode, VerticalDefinition $vertical): array
    {
        $db = VerticalConfigItem::withoutTenant()
            ->where('organization_id', $orgId)
            ->where('vertical_code', $verticalCode)
            ->where('config_group', 'hierarchy')
            ->where('is_active', true)
            ->pluck('label', 'code')
            ->toArray();

        $itemDefault = $vertical->itemLabel() ?? 'Đơn vị';

        return [
            'site'        => $db['site_label']        ?? $vertical->defaultSiteLabel()       ?? 'Vùng sản xuất',
            'area'        => $db['area_label']         ?? $vertical->areaLabel()              ?? 'Khu',
            'lot'         => $db['lot_label']          ?? $vertical->lotLabel()               ?? 'Lô',
            'item'        => $db['item_label']         ?? $itemDefault,
            'item_plural' => $db['item_label_plural']  ?? $itemDefault,
            'item_prefix' => $db['item_code_prefix']   ?? $vertical->defaultItemCodePrefix()  ?? 'I',
        ];
    }
}
