{{--
    Context Workspace body — Business Context Canvas (Rule R1: form chỉ hiện 1 lần,
    sau khi tạo chỉ còn form cập nhật, không có nút "thêm Context khác").
    Biến cần truyền vào: $businessProject.
--}}
@php
    $context = $businessProject->context;
    $deliverable = $context?->deliverable;
@endphp

<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Business Context Canvas</h2>
            @if($deliverable)
            <span class="badge {{ $deliverable->status->badgeClass() }}">
                {{ $deliverable->status->label() }} &middot; v{{ $deliverable->current_version }}
            </span>
            @endif
        </div>

        <form action="{{ $context
                ? route('backend.business-projects.context.update', $businessProject)
                : route('backend.business-projects.context.store', $businessProject) }}"
              method="POST" class="space-y-4">
            @csrf
            @if($context)
                @method('PUT')
            @endif

            <div>
                <label class="label label-text text-sm font-medium">Company Profile</label>
                <textarea name="company_profile[notes]" rows="3" class="textarea textarea-bordered w-full"
                          placeholder="Ngành nghề, quy mô, mô hình kinh doanh...">{{ $context?->company_profile['notes'] ?? '' }}</textarea>
            </div>

            <div>
                <label class="label label-text text-sm font-medium">Stakeholder Map</label>
                <textarea name="stakeholders[notes]" rows="3" class="textarea textarea-bordered w-full"
                          placeholder="Founder, Ban điều hành, người liên hệ chính...">{{ $context?->stakeholders['notes'] ?? '' }}</textarea>
            </div>

            <div>
                <label class="label label-text text-sm font-medium">Strategic Goals</label>
                <textarea name="strategic_goals[notes]" rows="3" class="textarea textarea-bordered w-full"
                          placeholder="Mục tiêu chiến lược 1-3 năm...">{{ $context?->strategic_goals['notes'] ?? '' }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-sm">
                {{ $context ? 'Cập nhật Business Context' : 'Tạo Business Context' }}
            </button>
        </form>

        @if($deliverable)
        <div class="divider"></div>
        <div class="flex flex-wrap gap-2">
            @if($deliverable->status->value === 'draft')
            <form action="{{ route('backend.business-projects.context.submit', $businessProject) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm">Gửi phê duyệt</button>
            </form>
            @endif

            @if($deliverable->status->value === 'submitted' && auth()->user()?->can('approve', $deliverable))
            <form action="{{ route('backend.business-projects.context.approve', $businessProject) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">Duyệt</button>
            </form>
            <form action="{{ route('backend.business-projects.context.reject', $businessProject) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-error btn-sm btn-outline">Từ chối</button>
            </form>
            @endif
        </div>
        @endif

        @if($deliverable?->versions->isNotEmpty())
        <div class="divider"></div>
        <h3 class="font-semibold text-sm mb-2">Lịch sử phiên bản</h3>
        <ul class="text-xs space-y-1">
            @foreach($deliverable->versions as $version)
            <li class="text-base-content/60">
                v{{ $version->version_number }} — {{ $version->change_summary }}
                <span class="text-base-content/40">({{ $version->created_at->format('d/m/Y H:i') }})</span>
            </li>
            @endforeach
        </ul>
        @endif
    </div>
</div>
