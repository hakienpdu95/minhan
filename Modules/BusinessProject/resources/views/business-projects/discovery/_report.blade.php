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
            <h2 class="font-semibold">Báo cáo Khảo sát Doanh nghiệp (Business Discovery Report)</h2>
            @if($report && $report->current_version > 0)
            <span class="badge {{ $report->status->badgeClass() }}">
                {{ $report->status->label() }} &middot; v{{ $report->current_version }}
            </span>
            @endif
        </div>

        <form action="{{ route('backend.business-projects.discovery.report.save', $businessProject) }}" method="POST" class="space-y-4"
              x-data="{
                  templates: {{ Js::from($discoveryReportTemplates->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'content' => $t->content])) }},
                  applyTemplate(id) {
                      const t = this.templates.find(x => x.id == id);
                      if (!t) return;
                      this.$refs.summary.value = t.content.summary ?? '';
                  }
              }">
            @csrf
            @if($discoveryReportTemplates->isNotEmpty())
            <div class="form-control">
                <label class="label py-0 pb-1"><span class="label-text text-xs font-medium">Bắt đầu từ Template</span></label>
                <select name="template_id" class="select select-bordered select-sm w-full" @change="applyTemplate($event.target.value)">
                    <option value="">— Không dùng template —</option>
                    @foreach($discoveryReportTemplates as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="label label-text text-sm font-medium">Tổng hợp hiện trạng &amp; phát hiện</label>
                <textarea name="summary" rows="5" class="textarea textarea-bordered w-full" x-ref="summary"
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
