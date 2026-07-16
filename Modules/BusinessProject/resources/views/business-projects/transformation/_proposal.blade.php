{{--
    Proposal — Rule R4: soạn thảo tự do khi draft, "Gửi phê duyệt nội bộ" (Approval Service,
    dùng lại đúng flow Ringlesoft như Context) trước khi gửi khách, rồi Consultant/PM tick
    "Confirmed" sau khi khách ký duyệt ngoài hệ thống. Gate R4 yêu cầu Proposal VÀ SOW cùng
    confirmed. Biến cần truyền vào: $businessProject, $proposal (Deliverable|null).
--}}
@php
    $proposalContent = $proposal?->versions->first()?->content ?? [];
@endphp

<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Proposal</h2>
            @if($proposal && $proposal->current_version > 0)
            <span class="badge {{ $proposal->status->badgeClass() }}">
                {{ $proposal->status->label() }} &middot; v{{ $proposal->current_version }}
            </span>
            @endif
        </div>

        @if(!$proposal || $proposal->status->value !== 'confirmed')
        <form action="{{ route('backend.business-projects.transformation.proposal.save', $businessProject) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="label label-text text-sm font-medium">Giải pháp đề xuất</label>
                <textarea name="solution" rows="3" class="textarea textarea-bordered w-full">{{ old('solution', $proposalContent['solution'] ?? '') }}</textarea>
            </div>
            <div>
                <label class="label label-text text-sm font-medium">Kế hoạch hợp tác</label>
                <textarea name="collaboration_plan" rows="3" class="textarea textarea-bordered w-full">{{ old('collaboration_plan', $proposalContent['collaboration_plan'] ?? '') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                {{ $proposal ? 'Cập nhật Proposal' : 'Lưu Proposal' }}
            </button>
        </form>
        @else
        <div class="text-sm space-y-1">
            <p><span class="text-base-content/50">Giải pháp đề xuất:</span> {{ $proposalContent['solution'] ?? '' }}</p>
            <p><span class="text-base-content/50">Kế hoạch hợp tác:</span> {{ $proposalContent['collaboration_plan'] ?? '' }}</p>
        </div>
        @endif

        @if($proposal)
        <div class="divider"></div>
        <div class="flex flex-wrap items-center gap-2">
            @if($proposal->status->value === 'draft')
            <form action="{{ route('backend.business-projects.transformation.submit', ['businessProject' => $businessProject, 'type' => 'proposal']) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm">Gửi phê duyệt nội bộ</button>
            </form>
            @endif

            @if($proposal->status->value === 'submitted' && auth()->user()?->can('approve', $proposal))
            <form action="{{ route('backend.business-projects.transformation.approve', ['businessProject' => $businessProject, 'type' => 'proposal']) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">Duyệt nội bộ</button>
            </form>
            <form action="{{ route('backend.business-projects.transformation.reject', ['businessProject' => $businessProject, 'type' => 'proposal']) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-error btn-sm btn-outline">Từ chối</button>
            </form>
            @endif

            @if($proposal->status->value === 'approved')
            <form action="{{ route('backend.business-projects.transformation.confirm', ['businessProject' => $businessProject, 'type' => 'proposal']) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">Xác nhận đã ký với khách (Confirmed)</button>
            </form>
            @endif

            @if($proposal->status->value === 'confirmed')
            <span class="text-xs text-base-content/50">
                Đã confirmed lúc {{ $proposal->confirmed_at?->format('d/m/Y H:i') }}
                @if($proposal->confirmedBy)bởi {{ $proposal->confirmedBy->name }}@endif
            </span>
            @endif
        </div>
        @endif
    </div>
</div>
