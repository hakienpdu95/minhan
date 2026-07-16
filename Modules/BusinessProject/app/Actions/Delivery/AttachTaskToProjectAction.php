<?php

namespace Modules\BusinessProject\Actions\Delivery;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Data\Requests\AttachTaskData;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\Task\Models\Task;

/**
 * Task module bắt buộc `project_id` (Modules/Project, khái niệm khác BusinessProject) — không
 * thể tạo Task "rút gọn" chỉ bằng dữ liệu BCOS. Action này chỉ GẮN THẺ business_project_id lên
 * 1 Task đã tồn tại; task mới hoàn toàn phải tạo qua route `backend.tasks.create` (prefill
 * business_project_id qua query string, xem TaskController).
 */
class AttachTaskToProjectAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, AttachTaskData $data): Task
    {
        $task = Task::where('organization_id', $businessProject->organization_id)
            ->findOrFail($data->task_id);

        $task->update([
            'business_project_id' => $businessProject->id,
            'updated_by' => Auth::id(),
        ]);

        return $task;
    }
}
