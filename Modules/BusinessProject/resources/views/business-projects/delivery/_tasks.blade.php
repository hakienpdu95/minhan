{{--
    Task Service — module Task hiện có, KHÔNG xây task tracker thứ hai (spec Giai đoạn 5).
    Task module bắt buộc mỗi Task có `project_id` (Modules/Project, khái niệm khác BusinessProject)
    nên không tạo Task "rút gọn" ngay tại đây — chỉ gắn thẻ Task có sẵn, hoặc link mở form Task
    gốc (prefill business_project_id qua query string). Biến: $businessProject, $tasks,
    $attachableTasks (Task[] chưa gắn business_project_id nào, cùng org).
--}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <h2 class="font-semibold mb-3">Danh sách Task ({{ $tasks->count() }})</h2>

        @forelse($tasks as $task)
        <div class="flex items-center justify-between text-xs border-b border-base-200 py-1.5 last:border-0">
            <div>
                <a href="{{ route('backend.tasks.show', $task) }}" class="font-medium hover:underline">{{ $task->title }}</a>
                @if($task->employee) <span class="text-base-content/40"> — {{ $task->employee->full_name }}</span> @endif
                @if($task->due_date) <span class="text-base-content/40"> · {{ $task->due_date->format('d/m/Y') }}</span> @endif
            </div>
            <div class="flex items-center gap-1.5">
                <span class="badge badge-xs">{{ $task->priority->label() }}</span>
                <span class="badge badge-xs badge-outline">{{ $task->status->label() }}</span>
            </div>
        </div>
        @empty
        <p class="text-xs text-base-content/40 mb-2">Chưa có Task nào gắn với Business Project này.</p>
        @endforelse

        <div class="flex flex-wrap items-center gap-2 mt-3">
            <form action="{{ route('backend.business-projects.delivery.tasks.attach', $businessProject) }}" method="POST" class="flex items-center gap-2">
                @csrf
                <select name="task_id" class="select select-bordered select-xs" required>
                    <option value="" disabled selected>Gắn Task có sẵn...</option>
                    @foreach($attachableTasks as $candidate)
                    <option value="{{ $candidate->id }}">{{ $candidate->title }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline btn-xs">Gắn</button>
            </form>

            <a href="{{ route('backend.tasks.create', ['business_project_id' => $businessProject->id]) }}"
               class="btn btn-primary btn-xs" target="_blank" rel="noopener">
                + Tạo Task mới
            </a>
        </div>
    </div>
</div>
