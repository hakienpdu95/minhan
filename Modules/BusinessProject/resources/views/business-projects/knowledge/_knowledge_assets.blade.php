{{--
    Knowledge Workspace (Giai đoạn 7 spec) — "khép vòng tri thức": Consultant dự án sau tra cứu
    lại Case Study/Lessons Learned/Best Practice/Industry Knowledge theo Industry ở Discovery.
    Cùng Rule R7 với Closing: KHÔNG tạo KcItem rút gọn ở đây — chỉ (a) gắn KcItem có sẵn (lọc
    theo 4 type tri thức dự án), hoặc (b) link mở form KcItem gốc (prefill business_project_id +
    type + industry qua query string). Biến: $businessProject, $knowledgeAssets (KcItem[] đã
    gắn — không lọc theo type, hiển thị mọi Knowledge Asset của project), $attachableKcItems
    (KcItem[] cùng org, chưa gắn project nào, lọc theo 4 type tri thức dự án),
    $knowledgeTypes (KcItemType[] — 4 case case_study/lessons_learned/best_practice/industry_knowledge).
--}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <h2 class="font-semibold mb-3">Knowledge Assets ({{ $knowledgeAssets->count() }})</h2>

        @forelse($knowledgeAssets as $item)
        <div class="flex items-center justify-between text-xs border-b border-base-200 py-1.5 last:border-0">
            <div>
                <a href="{{ route('backend.kc-items.show', $item) }}" class="font-medium hover:underline">{{ $item->title }}</a>
                @if($item->category) <span class="text-base-content/40"> — {{ $item->category->name }}</span> @endif
                @if($item->industry) <span class="text-base-content/30"> · {{ $item->industry }}</span> @endif
            </div>
            <span class="badge badge-xs badge-outline">{{ $item->type->label() }}</span>
        </div>
        @empty
        <p class="text-xs text-base-content/40 mb-2">Chưa có Knowledge Asset nào gắn với Business Project này.</p>
        @endforelse

        <div class="flex flex-wrap items-center gap-2 mt-3">
            <form action="{{ route('backend.business-projects.knowledge.attach', $businessProject) }}" method="POST" class="flex items-center gap-2">
                @csrf
                <select name="kc_item_id" class="select select-bordered select-xs" required>
                    <option value="" disabled selected>Gắn Knowledge Asset có sẵn...</option>
                    @foreach($attachableKcItems as $candidate)
                    <option value="{{ $candidate->id }}">{{ $candidate->title }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline btn-xs">Gắn</button>
            </form>
        </div>

        <div class="divider my-2 text-xs text-base-content/30">Tạo Knowledge Asset mới</div>

        <div class="flex flex-wrap items-center gap-2">
            @foreach($knowledgeTypes as $type)
            <a href="{{ route('backend.kc-items.create', [
                    'business_project_id' => $businessProject->id,
                    'type' => $type->value,
                    'industry' => $businessProject->customer?->industry,
                ]) }}"
               class="btn btn-primary btn-xs" target="_blank" rel="noopener">
                + {{ $type->label() }}
            </a>
            @endforeach
        </div>
    </div>
</div>
