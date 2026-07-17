<?php

namespace Modules\BusinessProject\Actions\Automation;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\WorkflowAutomation\Enums\ConditionMatch;
use Modules\WorkflowAutomation\Enums\CooldownType;
use Modules\WorkflowAutomation\Enums\StepType;
use Modules\WorkflowAutomation\Models\Workflow;
use Modules\WorkflowAutomation\Models\WorkflowStep;

/**
 * Master flow Giai đoạn 6: "đóng thành công → tự động tạo Project Retrospective (gợi ý) →
 * kích hoạt Customer Success Workspace". Theo đúng nguyên tắc Phần 1 #5 ("1 service dùng mọi
 * nơi") — đây KHÔNG phải Listener hard-code, mà là 1 Workflow record đi qua chính Workflow
 * Engine hiện có (builder UI đã có sẵn, Founder/Admin có thể sửa/tắt sau này mà không cần code).
 *
 * Idempotent theo (organization_id, trigger_type) — an toàn gọi lại nhiều lần (org mới qua
 * OrganizationCreated listener, org cũ qua seeder backfill).
 */
class SeedBcosDefaultWorkflowAction
{
    use AsAction;

    private const TRIGGER_TYPE = 'business_project.closed';

    public function handle(int $organizationId): ?Workflow
    {
        $exists = Workflow::withoutTenant()
            ->where('organization_id', $organizationId)
            ->where('trigger_type', self::TRIGGER_TYPE)
            ->exists();

        if ($exists) {
            return null;
        }

        $workflow = Workflow::withoutTenant()->create([
            'organization_id' => $organizationId,
            'name' => 'BCOS — Đóng dự án: Retrospective + Kích hoạt Customer Success',
            'description' => 'Tự động theo master_flow.md Giai đoạn 6: khi Business Project đóng, '
                . 'tạo gợi ý Project Retrospective và báo Customer Success bắt đầu theo dõi.',
            'category' => 'BusinessProject',
            'trigger_type' => self::TRIGGER_TYPE,
            'condition_match' => ConditionMatch::All->value,
            'cooldown_type' => CooldownType::OncePerSubject->value,
            'definition_status' => 2, // active — xem migration 2026_06_09_300009 (1=draft 2=active 3=archived)
            'is_active' => true,
            'priority' => 5,
        ]);

        WorkflowStep::create([
            'workflow_id' => $workflow->id,
            'sort_order' => 10,
            'step_key' => 'create_retrospective',
            'label' => 'Tạo Project Retrospective (gợi ý)',
            'step_type' => StepType::Automated->value,
            'action_type' => 'business_project.create_retrospective',
            'action_config' => [],
            'halt_on_fail' => false,
        ]);

        WorkflowStep::create([
            'workflow_id' => $workflow->id,
            'sort_order' => 20,
            'step_key' => 'notify_customer_success',
            'label' => 'Thông báo Customer Success',
            'step_type' => StepType::Automated->value,
            'action_type' => 'notification.send',
            'action_config' => [
                'notif_target' => 'role:customer_success',
                'notif_title' => 'Dự án {subject.attr.name} đã đóng',
                'notif_body' => 'Business Project {subject.attr.code} ({subject.attr.name}) vừa đóng — '
                    . 'vào Customer Success Workspace để bắt đầu CSAT/NPS, follow-up và renewal.',
            ],
            'halt_on_fail' => false,
        ]);

        return $workflow;
    }
}
