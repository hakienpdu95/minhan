<?php

namespace Modules\Task\Actions\Backend;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Task\Data\Requests\UpdateTaskData;
use Modules\Task\Events\TaskUpdated;
use Modules\Task\Models\Task;

class UpdateTaskAction
{
    use AsAction;

    public function handle(Task $task, UpdateTaskData $data): Task
    {
        $wasCompleted = $task->status->isDone();
        $willComplete = $data->status->isDone();

        $task->fill([
            'employee_id'     => $data->employee_id,
            'title'           => $data->title,
            'description'     => $data->description,
            'task_type'       => $data->task_type->value,
            'status'          => $data->status->value,
            'priority'        => $data->priority->value,
            'story_points'    => $data->story_points,
            'start_date'      => $data->start_date,
            'due_date'        => $data->due_date,
            'estimated_hours' => $data->estimated_hours,
            'updated_by'      => auth()->id(),
        ]);

        if ($willComplete && !$wasCompleted) {
            $task->completed_at = now();
        } elseif (!$willComplete && $wasCompleted) {
            $task->completed_at = null;
        }

        $task->save();

        $task->labels()->sync($data->label_ids ?? []);

        event(new TaskUpdated($task));

        return $task;
    }
}
