{{--
    Meeting — Weekly Review/Interview/Workshop/Retrospective (spec Giai đoạn 5). Minutes là
    Deliverable con-1-1 của đúng Meeting (SaveMeetingMinutesAction). Action items hiển thị kèm
    link "Tạo Task" (prefill title + business_project_id qua query string) — KHÔNG tự động tạo
    Task (Task module bắt buộc chọn project_id/Modules-Project, xem _tasks.blade.php).
    Biến: $businessProject, $meetings (đã eager load deliverable.versions).
--}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <h2 class="font-semibold mb-3">Cuộc họp</h2>

        <form action="{{ route('backend.business-projects.delivery.meetings.store', $businessProject) }}" method="POST" class="space-y-3 mb-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="label label-text text-sm font-medium">Loại</label>
                    <select name="type" class="select select-bordered select-sm w-full">
                        @foreach(\Modules\BusinessProject\Enums\MeetingType::cases() as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="label label-text text-sm font-medium">Tiêu đề</label>
                    <input type="text" name="title" class="input input-bordered input-sm w-full" placeholder="VD: Weekly Review tuần 3" required>
                </div>
            </div>
            <div>
                <label class="label label-text text-sm font-medium">Thời gian</label>
                <input type="datetime-local" name="held_at" class="input input-bordered input-sm w-full sm:w-64">
            </div>
            <button type="submit" class="btn btn-outline btn-sm">Ghi nhận cuộc họp</button>
        </form>

        @forelse($meetings as $meeting)
        @php
            $content = $meeting->deliverable?->versions->first()?->content ?? [];
            $actionItems = $content['action_items'] ?? [];
        @endphp
        <div class="border border-base-200 rounded-lg p-3 mb-3 last:mb-0">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <span class="font-medium text-sm">{{ $meeting->title }}</span>
                    <span class="badge badge-xs ml-1">{{ $meeting->type->label() }}</span>
                </div>
                @if($meeting->held_at)
                <span class="text-xs text-base-content/40">{{ $meeting->held_at->format('d/m/Y H:i') }}</span>
                @endif
            </div>

            <form action="{{ route('backend.business-projects.delivery.meetings.minutes.save', ['businessProject' => $businessProject, 'meeting' => $meeting->id]) }}" method="POST" class="space-y-2">
                @csrf
                <textarea name="minutes" rows="2" class="textarea textarea-bordered textarea-sm w-full"
                          placeholder="Nội dung biên bản...">{{ $content['minutes'] ?? '' }}</textarea>
                <textarea name="action_items" rows="2" class="textarea textarea-bordered textarea-sm w-full"
                          placeholder="Việc cần làm (Action items) — mỗi dòng 1 việc">{{ implode("\n", $actionItems) }}</textarea>
                <button type="submit" class="btn btn-ghost btn-xs">Lưu biên bản</button>
            </form>

            @if(!empty($actionItems))
            <ul class="mt-2 space-y-1">
                @foreach($actionItems as $item)
                <li class="flex items-center justify-between text-xs">
                    <span>{{ $item }}</span>
                    <a href="{{ route('backend.tasks.create', ['business_project_id' => $businessProject->id, 'title' => $item]) }}"
                       class="link link-primary" target="_blank" rel="noopener">Tạo Task ↗</a>
                </li>
                @endforeach
            </ul>
            @endif
        </div>
        @empty
        <p class="text-xs text-base-content/40">Chưa có cuộc họp nào.</p>
        @endforelse
    </div>
</div>
