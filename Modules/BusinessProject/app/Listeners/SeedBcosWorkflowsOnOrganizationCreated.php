<?php

namespace Modules\BusinessProject\Listeners;

use Modules\BusinessProject\Actions\Automation\SeedBcosDefaultWorkflowAction;
use Modules\Organization\Events\OrganizationCreated;

/**
 * Cùng pattern Modules\Subscription\...\AutoSubscribeOnOrgCreated — tổ chức mới tự có sẵn
 * Workflow "đóng dự án" (xem SeedBcosDefaultWorkflowAction), không cần Founder tự tạo tay.
 */
class SeedBcosWorkflowsOnOrganizationCreated
{
    public function handle(OrganizationCreated $event): void
    {
        SeedBcosDefaultWorkflowAction::run($event->organization->id);
    }
}
