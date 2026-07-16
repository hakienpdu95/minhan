{{--
    Discovery Records — Interview/Observation/Document Review/Data Review/Process Map nhập
    trực tiếp trong Workspace (spec Giai đoạn 2), mỗi bản ghi tự động là 1 Deliverable con của
    Business Discovery Report (không dùng file Word rời). Biến cần truyền vào: $businessProject,
    $report (Deliverable|null, đã eager load children.versions), $recordTypes (DeliverableType[]).
--}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <h2 class="font-semibold mb-3">Bản ghi khảo sát (Interview / Observation / Document Review / Data Review / Process Map)</h2>

        <form action="{{ route('backend.business-projects.discovery.records.store', $businessProject) }}" method="POST" class="space-y-3 mb-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="label label-text text-sm font-medium">Loại bản ghi</label>
                    <select name="type" class="select select-bordered select-sm w-full">
                        @foreach($recordTypes as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label label-text text-sm font-medium">Tiêu đề</label>
                    <input type="text" name="title" class="input input-bordered input-sm w-full"
                           placeholder="VD: Phỏng vấn Founder về quy trình bán hàng" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="label label-text text-sm font-medium">Ngày thực hiện</label>
                    <input type="date" name="occurred_at" class="input input-bordered input-sm w-full">
                </div>
                <div>
                    <label class="label label-text text-sm font-medium">Người tham gia</label>
                    <input type="text" name="participants" class="input input-bordered input-sm w-full"
                           placeholder="VD: Founder, Trưởng phòng Sales">
                </div>
            </div>

            <div>
                <label class="label label-text text-sm font-medium">Ghi chú / Nội dung</label>
                <textarea name="notes" rows="3" class="textarea textarea-bordered w-full"
                          placeholder="Nội dung phỏng vấn, quan sát, tài liệu đã xem, dữ liệu đã thu thập..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-sm">Thêm bản ghi</button>
        </form>

        @if($report?->children->isNotEmpty())
        <div class="divider"></div>
        <h3 class="font-semibold text-sm mb-2">Danh sách bản ghi ({{ $report->children->count() }})</h3>
        <ul class="space-y-2">
            @foreach($report->children as $record)
            @php $content = $record->versions->first()?->content ?? []; @endphp
            <li class="border border-base-200 rounded-lg p-2.5 text-xs">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-medium">{{ $record->title }}</span>
                    <span class="badge badge-xs">{{ \Modules\BusinessProject\Enums\DeliverableType::from($record->type)->label() }}</span>
                </div>
                @if(!empty($content['occurred_at']) || !empty($content['participants']))
                <p class="text-base-content/50 mb-1">
                    @if(!empty($content['occurred_at'])) {{ \Illuminate\Support\Carbon::parse($content['occurred_at'])->format('d/m/Y') }} @endif
                    @if(!empty($content['participants'])) &middot; {{ $content['participants'] }} @endif
                </p>
                @endif
                @if(!empty($content['notes']))
                <p class="text-base-content/70">{{ $content['notes'] }}</p>
                @endif
            </li>
            @endforeach
        </ul>
        @else
        <p class="text-xs text-base-content/40">Chưa có bản ghi khảo sát nào.</p>
        @endif
    </div>
</div>
