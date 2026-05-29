<?php

namespace Modules\WorkflowAutomation\Listeners;

use Modules\Assessment\Events\AssessmentCompleted;
use Modules\WorkflowAutomation\Core\WorkflowDispatcher;
use Modules\WorkflowAutomation\Data\TriggerPayload;

class FireWorkflowOnAssessmentCompleted
{
    public function handle(AssessmentCompleted $event): void
    {
        WorkflowDispatcher::fire(
            TriggerPayload::forAssessmentResult($event->result)
        );
    }
}
