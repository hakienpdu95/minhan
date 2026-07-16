{{--
    Business Discovery Report — tổng hợp cuối Discovery Workspace (spec Giai đoạn 2), cùng
    deliverable với container cha của các bản ghi khảo sát (xem discovery/_records.blade.php).
    Biến cần truyền vào: $businessProject, $report (Deliverable|null, đã eager load versions).
--}}
@php
    $reportContent = $report?->versions->first()?->content ?? [];
@endphp

<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Business Discovery Report</h2>
            @if($report && $report->current_version > 0)
            <span class="badge {{ $report->status->badgeClass() }}">
                {{ $report->status->label() }} &middot; v{{ $report->current_version }}
            </span>
            @endif
        </div>

        <form action="{{ route('backend.business-projects.discovery.report.save', $businessProject) }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="label label-text text-sm font-medium">Tổng hợp hiện trạng &amp; phát hiện</label>
                <textarea name="summary" rows="5" class="textarea textarea-bordered w-full"
                          placeholder="Tổng hợp hiện trạng doanh nghiệp và các phát hiện chính sau Discovery...">{{ old('summary', $reportContent['summary'] ?? '') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-sm">
                {{ $report && $report->current_version > 0 ? 'Cập nhật Discovery Report' : 'Lưu Discovery Report' }}
            </button>
        </form>

        @if($report?->versions->isNotEmpty())
        <div class="divider"></div>
        <h3 class="font-semibold text-sm mb-2">Lịch sử phiên bản</h3>
        <ul class="text-xs space-y-1">
            @foreach($report->versions as $version)
            <li class="text-base-content/60">
                v{{ $version->version_number }} — {{ $version->change_summary }}
                <span class="text-base-content/40">({{ $version->created_at->format('d/m/Y H:i') }})</span>
            </li>
            @endforeach
        </ul>
        @endif
    </div>
</div>
