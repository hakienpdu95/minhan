<?php

namespace Modules\WorkflowAutomation\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\ActivityLog\Core\ActivityLogger;
use Modules\WorkflowAutomation\Core\ActionRegistry;
use Modules\WorkflowAutomation\Core\ConditionEvaluator;
use Modules\WorkflowAutomation\Core\CooldownGuard;
use Modules\WorkflowAutomation\Data\TriggerPayload;
use Modules\WorkflowAutomation\Enums\WorkflowStatus;
use Modules\WorkflowAutomation\Models\Workflow;
use Modules\WorkflowAutomation\Models\WorkflowStep;

class ExecuteWorkflowAction
{
    use AsAction;

    public string $jobQueue   = 'workflows';
    public int    $jobTries   = 3;
    public array  $jobBackoff = [10, 60, 300];
    public int    $jobTimeout = 120;

    public function __construct(
        private readonly ConditionEvaluator $evaluator,
        private readonly CooldownGuard      $cooldown,
        private readonly ActionRegistry     $actions,
    ) {}

    public function handle(int $workflowId, TriggerPayload $payload, string $runId): void
    {
        if (\DB::table('workflow_executions')->where('run_id', $runId)->exists()) {
            return;
        }

        $workflow = Workflow::with(['conditions', 'steps'])->find($workflowId);
        if (!$workflow || !$workflow->is_active) return;

        $startedAt = now();

        if (!$this->cooldown->allow($workflow, $payload)) {
            $this->persist($workflow, $payload, $runId, WorkflowStatus::Skip, 'cooldown', null, [], $startedAt);
            return;
        }

        $condPass = $this->evaluator->evaluate($workflow, $payload);
        if (!$condPass) {
            $this->persist($workflow, $payload, $runId, WorkflowStatus::Skip, 'condition_failed', false, [], $startedAt);
            return;
        }

        $steps    = $workflow->steps->sortBy('sort_order');
        $stepLogs = [];
        $success  = $failed = $scheduled = 0;

        foreach ($steps as $step) {
            if ($step->delay_minutes > 0) {
                $stepLogs[] = $this->stepLog($step, 4, null, 0);
                $scheduled++;
                // executionId will be set after persist — delayed job updates the row later
                ExecuteWorkflowStepAction::dispatch($step->id, $payload, 0)
                    ->delay(now()->addMinutes($step->delay_minutes))
                    ->onQueue('workflows');
                continue;
            }

            $executor = $this->actions->get($step->action_type);
            if (!$executor) {
                $stepLogs[] = $this->stepLog($step, 3, "Unknown action: {$step->action_type}", 0);
                $failed++;
                continue;
            }

            $result = $executor->execute($step, $payload);
            $result->success ? $success++ : $failed++;
            $stepLogs[] = $this->stepLog(
                $step,
                $result->success ? 1 : 3,
                $result->errorMessage,
                $result->durationMs,
            );
        }

        $status = match(true) {
            $scheduled > 0 && $success === 0 && $failed === 0 => WorkflowStatus::Scheduled,
            $failed === 0                                      => WorkflowStatus::Pass,
            $success === 0 && $scheduled === 0                 => WorkflowStatus::Fail,
            default                                            => WorkflowStatus::Partial,
        };

        $totalMs = (int) $startedAt->diffInMilliseconds(now());
        $this->persist($workflow, $payload, $runId, $status, null, true, $stepLogs, $startedAt, $totalMs, $scheduled);

        ActivityLogger::info('WorkflowAutomation', 'workflow_executed', null, [
            'workflow_id'     => $workflow->id,
            'workflow_name'   => $workflow->name,
            'run_id'          => $runId,
            'status'          => $status->value,
            'steps_success'   => $success,
            'steps_failed'    => $failed,
            'steps_scheduled' => $scheduled,
        ]);
    }

    private function persist(
        Workflow       $workflow,
        TriggerPayload $payload,
        string         $runId,
        WorkflowStatus $status,
        ?string        $skipReason,
        ?bool          $conditionResult,
        array          $stepLogs,
        \Carbon\Carbon $startedAt,
        int            $totalMs   = 0,
        int            $scheduled = 0,
    ): void {
        $workflow->increment('run_count');
        $workflow->update(['last_run_at' => now(), 'last_run_status' => $status->value]);

        $execId = \DB::table('workflow_executions')->insertGetId([
            'workflow_id'      => $workflow->id,
            'organization_id'  => $payload->organizationId,
            'run_id'           => $runId,
            'trigger_type'     => $payload->triggerType,
            'source_module'    => $payload->sourceModule,
            'subject_type'     => $payload->subjectType,
            'subject_id'       => $payload->subjectId,
            'actor_id'         => $payload->actorId,
            'status'           => $status->value,
            'skip_reason'      => $skipReason,
            'condition_result' => $conditionResult,
            'steps_total'      => count($stepLogs),
            'steps_success'    => count(array_filter($stepLogs, fn($s) => $s['status'] === 1)),
            'steps_failed'     => count(array_filter($stepLogs, fn($s) => $s['status'] === 3)),
            'steps_scheduled'  => $scheduled,
            'duration_ms'      => $totalMs,
            'triggered_at'     => $payload->firedAt->format('Y-m-d H:i:s.v'),
            'executed_at'      => $startedAt->format('Y-m-d H:i:s.v'),
            'finished_at'      => now()->format('Y-m-d H:i:s.v'),
            'created_at'       => now(),
        ]);

        if ($execId && !empty($stepLogs)) {
            foreach ($stepLogs as &$log) {
                $log['execution_id'] = $execId;
            }
            \DB::table('workflow_execution_steps')->insert($stepLogs);
        }
    }

    private function stepLog(WorkflowStep $step, int $status, ?string $error, int $ms): array
    {
        return [
            'execution_id'  => 0,
            'step_id'       => $step->id,
            'sort_order'    => $step->sort_order,
            'action_type'   => $step->action_type,
            'status'        => $status,
            'error_message' => $error ? substr($error, 0, 500) : null,
            'duration_ms'   => $ms,
            'attempts'      => 1,
            'executed_at'   => now()->format('Y-m-d H:i:s.v'),
            'created_at'    => now(),
        ];
    }

    public function jobFailed(\Throwable $e): void
    {
        logger()->error('[Workflow] ExecuteWorkflowAction permanently failed', [
            'error' => $e->getMessage(),
        ]);
    }
}
