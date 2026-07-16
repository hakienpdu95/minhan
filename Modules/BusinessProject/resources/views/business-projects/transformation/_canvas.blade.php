{{--
    Transformation Design Canvas (Handbook 5.5 ⭐) — 8 mục, dùng để thống nhất với doanh nghiệp
    trước khi lập Roadmap (spec Giai đoạn 4). Không có Approval/Confirm, chỉ singleton editable.
    Biến cần truyền vào: $businessProject, $canvas (Deliverable|null, đã eager load versions).
--}}
@php
    $canvasContent = $canvas?->versions->first()?->content ?? [];
    $fields = [
        'business_goal' => 'Business Goal',
        'priority_problems' => 'Priority Problems',
        'transformation_objectives' => 'Transformation Objectives',
        'key_initiatives' => 'Key Initiatives',
        'quick_wins' => 'Quick Wins',
        'resources' => 'Resources',
        'risks' => 'Risks',
        'success_metrics' => 'Success Metrics',
    ];
@endphp

<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Transformation Design Canvas</h2>
            @if($canvas && $canvas->current_version > 0)
            <span class="badge {{ $canvas->status->badgeClass() }}">
                {{ $canvas->status->label() }} &middot; v{{ $canvas->current_version }}
            </span>
            @endif
        </div>

        <form action="{{ route('backend.business-projects.transformation.canvas.save', $businessProject) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @csrf
            @foreach($fields as $key => $label)
            <div>
                <label class="label label-text text-sm font-medium">{{ $label }}</label>
                <textarea name="{{ $key }}" rows="2" class="textarea textarea-bordered w-full">{{ old($key, $canvasContent[$key] ?? '') }}</textarea>
            </div>
            @endforeach
            <div class="sm:col-span-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    {{ $canvas ? 'Cập nhật Canvas' : 'Lưu Canvas' }}
                </button>
            </div>
        </form>
    </div>
</div>
