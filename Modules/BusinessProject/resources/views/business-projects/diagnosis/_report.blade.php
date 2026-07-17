{{--
    Diagnosis Report — Rule R3: draft → "Gửi phê duyệt" (Approval Service, dùng lại đúng flow
    Ringlesoft như Context) → approved bởi Founder/Lead Consultant (Consultant KHÔNG được duyệt).
    Biến cần truyền vào: $businessProject, $diagnosisReport (Deliverable|null).
--}}
@php
    $content = $diagnosisReport?->versions->first()?->content ?? [];
@endphp

<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Diagnosis Report</h2>
            @if($diagnosisReport && $diagnosisReport->current_version > 0)
            <span class="badge {{ $diagnosisReport->status->badgeClass() }}">
                {{ $diagnosisReport->status->label() }} &middot; v{{ $diagnosisReport->current_version }}
            </span>
            @endif
        </div>

        <form action="{{ route('backend.business-projects.diagnosis.overview.save', $businessProject) }}" method="POST" class="space-y-3"
              x-data="{
                  templates: {{ Js::from($diagnosisTemplates->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'content' => $t->content])) }},
                  applyTemplate(id) {
                      const t = this.templates.find(x => x.id == id);
                      if (!t) return;
                      this.$refs.overview.value = t.content.overview ?? '';
                  }
              }">
            @csrf
            @if($diagnosisTemplates->isNotEmpty())
            <div class="form-control">
                <label class="label py-0 pb-1"><span class="label-text text-xs font-medium">Bắt đầu từ Template</span></label>
                <select name="template_id" class="select select-bordered select-sm w-full" @change="applyTemplate($event.target.value)">
                    <option value="">— Không dùng template —</option>
                    @foreach($diagnosisTemplates as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <label class="label label-text text-sm font-medium">Tổng hợp Diagnosis</label>
                <textarea name="overview" rows="3" class="textarea textarea-bordered w-full" x-ref="overview"
                          placeholder="Tổng hợp các vấn đề cốt lõi, đã thống nhất với doanh nghiệp...">{{ old('overview', $content['overview'] ?? '') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                {{ $diagnosisReport ? 'Cập nhật overview' : 'Lưu overview' }}
            </button>
        </form>

        @if($diagnosisReport)
        <div class="divider"></div>
        <div class="flex flex-wrap items-center gap-2">
            @if($diagnosisReport->status->value === 'draft')
            <form action="{{ route('backend.business-projects.diagnosis.submit', $businessProject) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm">Gửi phê duyệt</button>
            </form>
            @endif

            @if($diagnosisReport->status->value === 'submitted' && auth()->user()?->can('approve', $diagnosisReport))
            <form action="{{ route('backend.business-projects.diagnosis.approve', $businessProject) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">Duyệt</button>
            </form>
            <form action="{{ route('backend.business-projects.diagnosis.reject', $businessProject) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-error btn-sm btn-outline">Từ chối</button>
            </form>
            @endif
        </div>
        @endif
    </div>
</div>
