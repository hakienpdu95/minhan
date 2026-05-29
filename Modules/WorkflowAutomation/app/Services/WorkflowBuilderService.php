<?php

namespace Modules\WorkflowAutomation\Services;

use App\Shared\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Modules\WorkflowAutomation\Models\Workflow;
use Modules\WorkflowAutomation\Models\WorkflowStepHeader;

class WorkflowBuilderService
{
    public function createFromRequest(Request $request): Workflow
    {
        $data     = $this->validate($request);
        $workflow = Workflow::create($this->workflowAttributes($data));
        $this->syncTriggerParams($workflow, $data['trigger_params'] ?? []);
        $this->syncConditions($workflow, $data['conditions'] ?? []);
        $this->syncSteps($workflow, $data['steps'] ?? []);
        return $workflow;
    }

    public function updateFromRequest(Request $request, Workflow $workflow): Workflow
    {
        $data = $this->validate($request);
        $workflow->update($this->workflowAttributes($data));
        $this->syncTriggerParams($workflow, $data['trigger_params'] ?? []);
        $this->syncConditions($workflow, $data['conditions'] ?? []);
        $this->syncSteps($workflow, $data['steps'] ?? []);
        return $workflow;
    }

    private function validate(Request $request): array
    {
        return $request->validate([
            'name'                             => 'required|string|max:191',
            'description'                      => 'nullable|string|max:500',
            'trigger_type'                     => 'required|string|max:64',
            'trigger_params'                   => 'nullable|array',
            'trigger_params.*.param_key'       => 'required|string|max:64',
            'trigger_params.*.param_value'     => 'nullable|string|max:255',
            'trigger_params.*.param_type'      => 'required|integer|in:1,2,3,4',
            'condition_match'                  => 'required|integer|in:1,2,3',
            'cooldown_type'                    => 'required|integer|in:0,1,2,3,4',
            'is_active'                        => 'boolean',
            'priority'                         => 'integer|min:1|max:10',
            'conditions'                       => 'nullable|array',
            'conditions.*.field'               => 'required|string|max:128',
            'conditions.*.operator'            => 'required|string|max:32',
            'conditions.*.value'               => 'nullable|string|max:500',
            'conditions.*.value_type'          => 'required|integer|in:1,2,3,4',
            'steps'                            => 'nullable|array',
            'steps.*.action_type'              => 'required|string|max:64',
            'steps.*.delay_minutes'            => 'integer|min:0',
            'steps.*.headers'                  => 'nullable|array',
            'steps.*.headers.*.header_key'     => 'required|string|max:128',
            'steps.*.headers.*.header_value'   => 'required|string|max:500',
        ]);
    }

    private function workflowAttributes(array $data): array
    {
        return [
            'organization_id' => TenantContext::getOrganizationId(),
            'name'            => $data['name'],
            'description'     => $data['description'] ?? null,
            'trigger_type'    => $data['trigger_type'],
            'condition_match' => $data['condition_match'],
            'cooldown_type'   => $data['cooldown_type'],
            'is_active'       => $data['is_active'] ?? false,
            'priority'        => $data['priority'] ?? 5,
            'created_by'      => auth()->id(),
            'updated_by'      => auth()->id(),
        ];
    }

    private function syncTriggerParams(Workflow $workflow, array $params): void
    {
        $workflow->triggerParams()->delete();
        foreach ($params as $param) {
            $workflow->triggerParams()->create([
                'param_key'   => $param['param_key'],
                'param_value' => $param['param_value'] ?? null,
                'param_type'  => $param['param_type'] ?? 1,
            ]);
        }
    }

    private function syncConditions(Workflow $workflow, array $conditions): void
    {
        $workflow->conditions()->delete();
        foreach (array_values($conditions) as $i => $cond) {
            $workflow->conditions()->create(array_merge($cond, ['sort_order' => $i]));
        }
    }

    private function syncSteps(Workflow $workflow, array $steps): void
    {
        $oldStepIds = $workflow->steps()->pluck('id');
        WorkflowStepHeader::whereIn('step_id', $oldStepIds)->delete();
        $workflow->steps()->delete();

        foreach (array_values($steps) as $i => $stepData) {
            $headers = $stepData['headers'] ?? [];
            unset($stepData['headers']);

            $step = $workflow->steps()->create(array_merge($stepData, ['sort_order' => $i]));

            foreach ($headers as $h) {
                $step->headers()->create([
                    'header_key'   => $h['header_key'],
                    'header_value' => $h['header_value'],
                ]);
            }
        }
    }
}
