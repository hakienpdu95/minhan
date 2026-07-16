{{--
    Statement of Work (SOW) — cùng luồng Rule R4 như Proposal (xem _proposal.blade.php).
    Biến cần truyền vào: $businessProject, $sow (Deliverable|null).
--}}
@php
    $sowContent = $sow?->versions->first()?->content ?? [];
@endphp

<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Statement of Work (SOW)</h2>
            @if($sow && $sow->current_version > 0)
            <span class="badge {{ $sow->status->badgeClass() }}">
                {{ $sow->status->label() }} &middot; v{{ $sow->current_version }}
            </span>
            @endif
        </div>

        @if(!$sow || $sow->status->value !== 'confirmed')
        <form action="{{ route('backend.business-projects.transformation.sow.save', $businessProject) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="label label-text text-sm font-medium">Phạm vi (Scope)</label>
                <textarea name="scope" rows="2" class="textarea textarea-bordered w-full">{{ old('scope', $sowContent['scope'] ?? '') }}</textarea>
            </div>
            <div>
                <label class="label label-text text-sm font-medium">Kết quả bàn giao (Deliverables)</label>
                <textarea name="deliverables" rows="2" class="textarea textarea-bordered w-full">{{ old('deliverables', $sowContent['deliverables'] ?? '') }}</textarea>
            </div>
            <div>
                <label class="label label-text text-sm font-medium">Trách nhiệm các bên (Responsibilities)</label>
                <textarea name="responsibilities" rows="2" class="textarea textarea-bordered w-full">{{ old('responsibilities', $sowContent['responsibilities'] ?? '') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                {{ $sow ? 'Cập nhật SOW' : 'Lưu SOW' }}
            </button>
        </form>
        @else
        <div class="text-sm space-y-1">
            <p><span class="text-base-content/50">Phạm vi:</span> {{ $sowContent['scope'] ?? '' }}</p>
            <p><span class="text-base-content/50">Kết quả bàn giao:</span> {{ $sowContent['deliverables'] ?? '' }}</p>
            <p><span class="text-base-content/50">Trách nhiệm các bên:</span> {{ $sowContent['responsibilities'] ?? '' }}</p>
        </div>
        @endif

        @if($sow)
        <div class="divider"></div>
        <div class="flex flex-wrap items-center gap-2">
            @if($sow->status->value === 'draft')
            <form action="{{ route('backend.business-projects.transformation.submit', ['businessProject' => $businessProject, 'type' => 'sow']) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm">Gửi phê duyệt nội bộ</button>
            </form>
            @endif

            @if($sow->status->value === 'submitted' && auth()->user()?->can('approve', $sow))
            <form action="{{ route('backend.business-projects.transformation.approve', ['businessProject' => $businessProject, 'type' => 'sow']) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">Duyệt nội bộ</button>
            </form>
            <form action="{{ route('backend.business-projects.transformation.reject', ['businessProject' => $businessProject, 'type' => 'sow']) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-error btn-sm btn-outline">Từ chối</button>
            </form>
            @endif

            @if($sow->status->value === 'approved')
            <form action="{{ route('backend.business-projects.transformation.confirm', ['businessProject' => $businessProject, 'type' => 'sow']) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">Xác nhận đã ký với khách (Confirmed)</button>
            </form>
            @endif

            @if($sow->status->value === 'confirmed')
            <span class="text-xs text-base-content/50">
                Đã confirmed lúc {{ $sow->confirmed_at?->format('d/m/Y H:i') }}
                @if($sow->confirmedBy)bởi {{ $sow->confirmedBy->name }}@endif
            </span>
            @endif
        </div>
        @endif
    </div>
</div>
