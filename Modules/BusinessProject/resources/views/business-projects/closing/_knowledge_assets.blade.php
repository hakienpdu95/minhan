{{--
    Knowledge Asset — Rule R7: KcItem module bắt buộc `category_id` (Modules/KcCategory, khái
    niệm khác BusinessProject) nên KHÔNG tạo KcItem rút gọn ở đây — chỉ (a) gắn KcItem có sẵn,
    hoặc (b) link mở form KcItem gốc (prefill business_project_id qua query string). Cùng pattern
    Task integration ở Delivery Workspace. Biến: $businessProject, $knowledgeAssets (KcItem[]),
    $attachableKcItems (KcItem[] chưa gắn business_project_id nào, cùng org).
--}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <h2 class="font-semibold mb-3">Tài sản Tri thức ({{ $knowledgeAssets->count() }})</h2>

        @forelse($knowledgeAssets as $item)
        <div class="flex items-center justify-between text-xs border-b border-base-200 py-1.5 last:border-0">
            <div>
                <a href="{{ route('backend.kc-items.show', $item) }}" class="font-medium hover:underline">{{ $item->title }}</a>
                @if($item->category) <span class="text-base-content/40"> — {{ $item->category->name }}</span> @endif
            </div>
            <span class="badge badge-xs badge-outline">{{ $item->type->value }}</span>
        </div>
        @empty
        <p class="text-xs text-base-content/40 mb-2">Chưa có Knowledge Asset nào gắn với Business Project này.</p>
        @endforelse

        <div class="flex flex-wrap items-center gap-2 mt-3">
            <form action="{{ route('backend.business-projects.closing.knowledge-assets.attach', $businessProject) }}" method="POST" class="flex items-center gap-2">
                @csrf
                <select name="kc_item_id" class="select select-bordered select-xs" required>
                    <option value="" disabled selected>Gắn Knowledge Asset có sẵn...</option>
                    @foreach($attachableKcItems as $candidate)
                    <option value="{{ $candidate->id }}">{{ $candidate->title }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline btn-xs">Gắn</button>
            </form>

            <a href="{{ route('backend.kc-items.create', ['business_project_id' => $businessProject->id]) }}"
               class="btn btn-primary btn-xs" target="_blank" rel="noopener">
                + Tạo Knowledge Asset mới
            </a>
        </div>
    </div>
</div>
