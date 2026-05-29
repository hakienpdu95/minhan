<?php

namespace Modules\WorkflowAutomation\Core;

use Modules\WorkflowAutomation\Contracts\ActionExecutor;

final class ActionRegistry
{
    private array $executors = [];

    public function register(ActionExecutor $executor): void
    {
        $this->executors[$executor->type()] = $executor;
    }

    public function get(string $type): ?ActionExecutor
    {
        return $this->executors[$type] ?? null;
    }

    public function groupedByModule(): array
    {
        $grouped = [];
        foreach ($this->executors as $executor) {
            $grouped[$executor->module()][] = [
                'type'          => $executor->type(),
                'label'         => $executor->label(),
                'config_fields' => $executor->stepConfigFields(),
            ];
        }
        return $grouped;
    }
}
