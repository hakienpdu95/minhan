{{--
    Transformation Roadmap — bản tổng quan (singleton, versioned) + các mốc cụ thể theo 4 lớp
    thời gian (bảng riêng `milestones`, spec Phần 6.1). Biến cần truyền vào: $businessProject,
    $roadmap (Deliverable|null), $milestonesByCategory (Collection keyed theo category value),
    $milestoneCategories (MilestoneCategory[]).
--}}
@php
    $roadmapContent = $roadmap?->versions->first()?->content ?? [];
@endphp

<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Lộ trình Chuyển đổi (Transformation Roadmap)</h2>
            @if($roadmap && $roadmap->current_version > 0)
            <span class="badge {{ $roadmap->status->badgeClass() }}">
                {{ $roadmap->status->label() }} &middot; v{{ $roadmap->current_version }}
            </span>
            @endif
        </div>

        <form action="{{ route('backend.business-projects.transformation.roadmap.save', $businessProject) }}" method="POST" class="space-y-3 mb-4"
              x-data="{
                  templates: {{ Js::from($roadmapTemplates->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'content' => $t->content])) }},
                  applyTemplate(id) {
                      const t = this.templates.find(x => x.id == id);
                      if (!t) return;
                      this.$refs.overview.value = t.content.overview ?? '';
                  }
              }">
            @csrf
            @if($roadmapTemplates->isNotEmpty())
            <div class="form-control">
                <label class="label py-0 pb-1"><span class="label-text text-xs font-medium">Bắt đầu từ Template</span></label>
                <select name="template_id" class="select select-bordered select-sm w-full" @change="applyTemplate($event.target.value)">
                    <option value="">— Không dùng template —</option>
                    @foreach($roadmapTemplates as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <label class="label label-text text-sm font-medium">Tổng quan lộ trình</label>
                <textarea name="overview" rows="3" class="textarea textarea-bordered w-full" x-ref="overview"
                          placeholder="Tóm tắt lộ trình chuyển đổi theo 3 tầng: Quick Wins, Capability Building, Transformation...">{{ old('overview', $roadmapContent['overview'] ?? '') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                {{ $roadmap ? 'Cập nhật Roadmap' : 'Lưu Roadmap' }}
            </button>
        </form>

        <div class="divider"></div>

        <h3 class="font-semibold text-sm mb-2">Mốc lộ trình (Milestones)</h3>
        <form action="{{ route('backend.business-projects.transformation.milestones.store', $businessProject) }}" method="POST" class="space-y-3 mb-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="label label-text text-sm font-medium">Lớp thời gian</label>
                    <select name="category" class="select select-bordered select-sm w-full">
                        @foreach($milestoneCategories as $category)
                        <option value="{{ $category->value }}">{{ $category->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="label label-text text-sm font-medium">Tiêu đề</label>
                    <input type="text" name="title" class="input input-bordered input-sm w-full"
                           placeholder="VD: Chuẩn hóa biểu mẫu báo giá" required>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="label label-text text-sm font-medium">Ngày mục tiêu</label>
                    <input type="date" name="target_date" class="input input-bordered input-sm w-full">
                </div>
                <div class="sm:col-span-2">
                    <label class="label label-text text-sm font-medium">Mô tả</label>
                    <input type="text" name="description" class="input input-bordered input-sm w-full">
                </div>
            </div>
            <button type="submit" class="btn btn-outline btn-sm">Thêm mốc</button>
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($milestoneCategories as $category)
            <div class="border border-base-200 rounded-lg p-2.5">
                <p class="font-medium text-xs mb-1.5">{{ $category->label() }}</p>
                @php $items = $milestonesByCategory->get($category->value, collect()); @endphp
                @forelse($items as $milestone)
                <div class="text-xs mb-1.5 last:mb-0">
                    <span class="font-medium">{{ $milestone->title }}</span>
                    @if($milestone->target_date)
                    <span class="text-base-content/40"> — {{ $milestone->target_date->format('d/m/Y') }}</span>
                    @endif
                    @if($milestone->description)
                    <p class="text-base-content/60">{{ $milestone->description }}</p>
                    @endif
                </div>
                @empty
                <p class="text-xs text-base-content/40">Chưa có mốc.</p>
                @endforelse
            </div>
            @endforeach
        </div>
    </div>
</div>
