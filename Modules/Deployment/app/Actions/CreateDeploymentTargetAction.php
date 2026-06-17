<?php

namespace Modules\Deployment\Actions;

use App\Foundation\Vertical\VerticalConfigService;
use App\Foundation\VerticalDefinition;
use App\Shared\Tenancy\Models\Organization;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Deployment\Data\CreateDeploymentTargetData;
use Modules\Deployment\Models\DeploymentChecklistItem;
use Modules\Deployment\Models\DeploymentTarget;
use Modules\Deployment\Notifications\TargetCreatedNotification;

class CreateDeploymentTargetAction
{
    use AsAction;

    public function handle(CreateDeploymentTargetData $data, VerticalDefinition $vertical): DeploymentTarget
    {
        return DB::transaction(function () use ($data, $vertical) {
            $orgId = TenantContext::getOrganizationId();

            // 1. Find existing org by tax_code, or create new one
            $targetOrg = $data->tax_code
                ? Organization::where('tax_code', $data->tax_code)->first()
                : null;

            if (! $targetOrg) {
                $targetOrg = Organization::forceCreate([
                    'name'          => $data->name,
                    'tax_code'      => $data->tax_code,
                    'phone'         => $data->phone,
                    'email'         => $data->email,
                    'province_code' => $data->province_code,
                    'full_address'  => $data->full_address,
                    'industry'      => $vertical->targetOrgCategory(),
                    'status'        => 'active',
                    'source'        => 'vertical_created',
                ]);
            }

            // 2. Create DeploymentTarget
            $target = DeploymentTarget::create([
                'organization_id'        => $orgId,
                'project_id'             => $data->project_id,
                'vertical_code'          => $vertical->code(),
                'target_organization_id' => $targetOrg->id,
                'current_phase'          => 'draft',
                'assigned_employee_id'   => $data->assigned_employee_id,
                'notes'                  => $data->notes,
                'created_by'             => auth()->id(),
            ]);

            // 3. Seed checklist items with resolved hierarchy labels
            $labels = VerticalConfigService::hierarchyLabels($orgId, $vertical->code(), $vertical);
            $replacements = [
                '{vertical}' => $vertical->label(),
                '{target}'   => $vertical->targetLabel(),
                '{site}'     => $labels['site'],
                '{area}'     => $labels['area'],
                '{lot}'      => $labels['lot'],
                '{item}'     => $labels['item'],
            ];

            foreach ($vertical->defaultChecklist() as $phase => $items) {
                foreach ($items as $item) {
                    DeploymentChecklistItem::create([
                        'organization_id'      => $orgId,
                        'deployment_target_id' => $target->id,
                        'phase'                => $phase,
                        'item_key'             => $item['key'],
                        'item_label'           => str_replace(
                            array_keys($replacements),
                            array_values($replacements),
                            $item['label']
                        ),
                        'is_required'          => $item['required'] ?? true,
                        'is_done'              => false,
                    ]);
                }
            }

            $loaded = $target->load(['targetOrganization', 'project']);

            // Notify creator
            auth()->user()?->notify(new TargetCreatedNotification($loaded));

            return $loaded;
        });
    }
}
