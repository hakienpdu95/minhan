<?php

namespace App\Foundation\Vertical;

use App\Foundation\VerticalDefinition;
use App\Foundation\VerticalRegistry;
use Illuminate\Support\Facades\DB;

class ActivateVerticalAction
{
    public function execute(int $orgId, string $verticalCode): void
    {
        $vertical = VerticalRegistry::resolve($verticalCode);

        if (! $vertical) {
            throw new \InvalidArgumentException("Vertical '{$verticalCode}' không tồn tại.");
        }

        DB::transaction(function () use ($orgId, $vertical) {
            // 1. Đánh dấu vertical active — bypass tenant scope (action receives explicit $orgId)
            OrganizationVertical::withoutTenant()->firstOrCreate(
                ['organization_id' => $orgId, 'vertical_code' => $vertical->code()],
                ['status' => 'active', 'activated_at' => now(), 'activated_by' => auth()->id()]
            );

            // 2. Seed activity_type defaults
            foreach ($vertical->defaultActivityTypes() ?? [] as $code => $label) {
                VerticalConfigItem::withoutTenant()->firstOrCreate(
                    ['organization_id' => $orgId, 'vertical_code' => $vertical->code(), 'config_group' => 'activity_type', 'code' => $code],
                    ['label' => $label, 'is_active' => true, 'sort_order' => 0]
                );
            }

            // 3. Seed doc_type defaults
            foreach ($vertical->defaultLegalDocTypes() ?? [] as $code => $label) {
                VerticalConfigItem::withoutTenant()->firstOrCreate(
                    ['organization_id' => $orgId, 'vertical_code' => $vertical->code(), 'config_group' => 'doc_type', 'code' => $code],
                    ['label' => $label, 'is_active' => true, 'sort_order' => 0]
                );
            }

            // 4. Seed hierarchy labels
            $itemDefault = $vertical->itemLabel() ?? 'Đơn vị';
            $hierarchyDefaults = [
                'site_label'        => $vertical->defaultSiteLabel()       ?? 'Vùng sản xuất',
                'area_label'        => $vertical->areaLabel()              ?? 'Khu',
                'lot_label'         => $vertical->lotLabel()               ?? 'Lô',
                'item_label'        => $itemDefault,
                'item_label_plural' => $itemDefault,
                'item_code_prefix'  => $vertical->defaultItemCodePrefix()  ?? 'I',
            ];
            foreach ($hierarchyDefaults as $code => $label) {
                VerticalConfigItem::withoutTenant()->firstOrCreate(
                    ['organization_id' => $orgId, 'vertical_code' => $vertical->code(), 'config_group' => 'hierarchy', 'code' => $code],
                    ['label' => $label, 'is_active' => true, 'sort_order' => 0]
                );
            }

            // 5. Tạo Spatie roles động: {vertical_code}_{suffix}
            foreach ($vertical->verticalRoles() as $suffix) {
                $roleName = $vertical->code() . '_' . $suffix;
                \Spatie\Permission\Models\Role::firstOrCreate(
                    ['name' => $roleName, 'guard_name' => 'web']
                );
            }
        });
    }
}
