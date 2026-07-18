{{--
    Weekly Report — Rule R5: luôn tạo trong context project (không có trang report độc lập).
    Mỗi lần bấm "Tạo Weekly Report" là 1 bản ghi MỚI (không phải sửa report cũ), số liệu prefill
    snapshot tại thời điểm tạo (CreateWeeklyReportAction). Biến: $businessProject, $weeklyReports
    (Deliverable[], đã eager load versions).
--}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <h2 class="font-semibold mb-3">Báo cáo tuần</h2>

        <form action="{{ route('backend.business-projects.delivery.weekly-reports.store', $businessProject) }}" method="POST" class="space-y-3 mb-4">
            @csrf
            <div>
                <label class="label label-text text-sm font-medium">Nhận định của Consultant</label>
                <textarea name="narrative" rows="2" class="textarea textarea-bordered w-full"
                          placeholder="Nhận định thêm ngoài số liệu tự động..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Tạo báo cáo tuần (số liệu tự prefill)</button>
        </form>

        @forelse($weeklyReports as $report)
        @php $content = $report->versions->first()?->content ?? []; $prefill = $content['prefill'] ?? []; @endphp
        <div class="border border-base-200 rounded-lg p-3 mb-2 last:mb-0 text-xs">
            <p class="font-medium mb-1">{{ $report->title }}</p>
            <div class="flex flex-wrap gap-3 text-base-content/60 mb-1">
                <span>Task hoàn thành: {{ $prefill['tasks_done'] ?? 0 }}</span>
                <span>Task còn tồn: {{ $prefill['tasks_pending'] ?? 0 }}</span>
                <span>Issue mới: {{ $prefill['new_issues'] ?? 0 }}</span>
                <span>Issue đang mở: {{ $prefill['open_issues'] ?? 0 }}</span>
                <span>Risk đang mở: {{ $prefill['open_risks'] ?? 0 }}</span>
            </div>
            @if(!empty($content['narrative']))
            <p class="text-base-content/70">{{ $content['narrative'] }}</p>
            @endif
        </div>
        @empty
        <p class="text-xs text-base-content/40">Chưa có báo cáo tuần nào.</p>
        @endforelse
    </div>
</div>
