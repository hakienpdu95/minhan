{{--
    TPS Canvas (THUCHOCVN Problem Solving Canvas) — công cụ tổng hợp/đồng thuận (Handbook 4.5),
    dùng sau khi đã đủ bối cảnh từ Interview/Observation, không phải bước đầu tiên (spec Giai
    đoạn 2). 1 deliverable duy nhất/project, lưu nhiều version qua UpsertSingletonDeliverableAction.
    Biến cần truyền vào: $businessProject, $tpsCanvas (Deliverable|null, đã eager load versions).
--}}
@php
    $tpsCanvasContent = $tpsCanvas?->versions->first()?->content ?? [];
@endphp

<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">TPS Canvas</h2>
            @if($tpsCanvas && $tpsCanvas->current_version > 0)
            <span class="badge {{ $tpsCanvas->status->badgeClass() }}">
                {{ $tpsCanvas->status->label() }} &middot; v{{ $tpsCanvas->current_version }}
            </span>
            @endif
        </div>

        <form action="{{ route('backend.business-projects.discovery.tps-canvas.save', $businessProject) }}" method="POST" class="space-y-4"
              x-data="{
                  templates: {{ Js::from($tpsCanvasTemplates->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'content' => $t->content])) }},
                  applyTemplate(id) {
                      const t = this.templates.find(x => x.id == id);
                      if (!t) return;
                      this.$refs.problem.value = t.content.problem ?? '';
                      this.$refs.goal.value = t.content.goal ?? '';
                      this.$refs.scope.value = t.content.scope ?? '';
                  }
              }">
            @csrf
            @if($tpsCanvasTemplates->isNotEmpty())
            <div class="form-control">
                <label class="label py-0 pb-1"><span class="label-text text-xs font-medium">Bắt đầu từ Template</span></label>
                <select name="template_id" class="select select-bordered select-sm w-full" @change="applyTemplate($event.target.value)">
                    <option value="">— Không dùng template —</option>
                    @foreach($tpsCanvasTemplates as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="label label-text text-sm font-medium">Vấn đề (Problem)</label>
                <textarea name="problem" rows="2" class="textarea textarea-bordered w-full" x-ref="problem"
                          placeholder="Bài toán chính doanh nghiệp đang gặp phải...">{{ old('problem', $tpsCanvasContent['problem'] ?? '') }}</textarea>
            </div>

            <div>
                <label class="label label-text text-sm font-medium">Mục tiêu (Goal)</label>
                <textarea name="goal" rows="2" class="textarea textarea-bordered w-full" x-ref="goal"
                          placeholder="Mục tiêu dự án tư vấn...">{{ old('goal', $tpsCanvasContent['goal'] ?? '') }}</textarea>
            </div>

            <div>
                <label class="label label-text text-sm font-medium">Phạm vi (Scope)</label>
                <textarea name="scope" rows="2" class="textarea textarea-bordered w-full" x-ref="scope"
                          placeholder="Phạm vi dự án — trong/ngoài phạm vi...">{{ old('scope', $tpsCanvasContent['scope'] ?? '') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-sm">
                {{ $tpsCanvas ? 'Cập nhật TPS Canvas' : 'Lưu TPS Canvas' }}
            </button>
        </form>

        @if($tpsCanvas?->versions->isNotEmpty())
        <div class="divider"></div>
        <h3 class="font-semibold text-sm mb-2">Lịch sử phiên bản</h3>
        <ul class="text-xs space-y-1">
            @foreach($tpsCanvas->versions as $version)
            <li class="text-base-content/60">
                v{{ $version->version_number }} — {{ $version->change_summary }}
                <span class="text-base-content/40">({{ $version->created_at->format('d/m/Y H:i') }})</span>
            </li>
            @endforeach
        </ul>
        @endif
    </div>
</div>
