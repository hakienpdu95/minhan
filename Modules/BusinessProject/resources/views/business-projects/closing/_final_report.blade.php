{{--
    Final Project Report — Rule R6: draft → "Gửi phê duyệt nội bộ" (Approval Service, dùng lại
    đúng flow Ringlesoft như Context) → approved. KHÔNG cần Confirm (khác Proposal/SOW — R6 chỉ
    yêu cầu "đã được phê duyệt"). Biến cần truyền vào: $businessProject, $finalReport (Deliverable|null).
--}}
@php
    $reportContent = $finalReport?->versions->first()?->content ?? [];
@endphp

<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Báo cáo Tổng kết Dự án</h2>
            @if($finalReport && $finalReport->current_version > 0)
            <span class="badge {{ $finalReport->status->badgeClass() }}">
                {{ $finalReport->status->label() }} &middot; v{{ $finalReport->current_version }}
            </span>
            @endif
        </div>

        <form action="{{ route('backend.business-projects.closing.final-report.save', $businessProject) }}" method="POST" class="space-y-4"
              x-data="{
                  templates: {{ Js::from($finalReportTemplates->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'content' => $t->content])) }},
                  applyTemplate(id) {
                      const t = this.templates.find(x => x.id == id);
                      if (!t) return;
                      this.$refs.summary.value = t.content.summary ?? '';
                  }
              }">
            @csrf
            @if($finalReportTemplates->isNotEmpty())
            <div class="form-control">
                <label class="label py-0 pb-1"><span class="label-text text-xs font-medium">Bắt đầu từ Template</span></label>
                <select name="template_id" class="select select-bordered select-sm w-full" @change="applyTemplate($event.target.value)">
                    <option value="">— Không dùng template —</option>
                    @foreach($finalReportTemplates as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <label class="label label-text text-sm font-medium">Đánh giá kết quả dự án &amp; giá trị mang lại</label>
                <textarea name="summary" rows="4" class="textarea textarea-bordered w-full" x-ref="summary"
                          placeholder="Tổng kết kết quả đạt được, giá trị mang lại cho doanh nghiệp...">{{ old('summary', $reportContent['summary'] ?? '') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                {{ $finalReport ? 'Cập nhật Final Report' : 'Lưu Final Report' }}
            </button>
        </form>

        @if($finalReport)
        <div class="divider"></div>
        <div class="flex flex-wrap items-center gap-2">
            @if($finalReport->status->value === 'draft')
            <form action="{{ route('backend.business-projects.closing.final-report.submit', $businessProject) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm">Gửi phê duyệt</button>
            </form>
            @endif

            @if($finalReport->status->value === 'submitted' && auth()->user()?->can('approve', $finalReport))
            <form action="{{ route('backend.business-projects.closing.final-report.approve', $businessProject) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">Duyệt</button>
            </form>
            <form action="{{ route('backend.business-projects.closing.final-report.reject', $businessProject) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-error btn-sm btn-outline">Từ chối</button>
            </form>
            @endif
        </div>
        @endif

        @if($finalReport?->versions->isNotEmpty())
        <div class="divider"></div>
        <h3 class="font-semibold text-sm mb-2">Lịch sử phiên bản</h3>
        <ul class="text-xs space-y-1">
            @foreach($finalReport->versions as $version)
            <li class="text-base-content/60">
                v{{ $version->version_number }} — {{ $version->change_summary }}
                <span class="text-base-content/40">({{ $version->created_at->format('d/m/Y H:i') }})</span>
            </li>
            @endforeach
        </ul>
        @endif
    </div>
</div>
