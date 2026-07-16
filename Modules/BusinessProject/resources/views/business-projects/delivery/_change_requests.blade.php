{{--
    Change Request — duyệt qua Approval Service (flow riêng "Change Request Approval", khác
    Deliverable). Nếu impacts_scope=true và được duyệt, SOW đang confirmed tự mở khóa về draft
    (xem ChangeRequest::onApprovalCompleted()) — Consultant cập nhật lại qua tab Transformation.
    Biến: $businessProject, $changeRequests (đã eager load issue/risk).
--}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <h2 class="font-semibold mb-3">Change Requests ({{ $changeRequests->count() }})</h2>

        @forelse($changeRequests as $cr)
        <div class="border border-base-200 rounded-lg p-3 mb-2 last:mb-0 text-xs">
            <div class="flex items-center justify-between mb-1">
                <span class="font-medium">{{ $cr->title }}</span>
                <div class="flex gap-1">
                    @if($cr->impacts_scope)<span class="badge badge-xs badge-warning">Ảnh hưởng SOW</span>@endif
                    <span class="badge badge-xs {{ $cr->status->badgeClass() }}">{{ $cr->status->label() }}</span>
                </div>
            </div>
            <p class="text-base-content/50 mb-1">
                Nguồn: {{ $cr->source_type->label() }} — {{ $cr->issue?->title ?? $cr->risk?->title }}
            </p>
            @if($cr->description)<p class="text-base-content/60 mb-2">{{ $cr->description }}</p>@endif

            <div class="flex flex-wrap gap-2">
                @if($cr->status->value === 'draft')
                <form action="{{ route('backend.business-projects.delivery.change-requests.submit', ['businessProject' => $businessProject, 'changeRequest' => $cr->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-xs">Gửi phê duyệt</button>
                </form>
                @endif

                @if($cr->status->value === 'submitted' && auth()->user()?->can('approve', $cr))
                <form action="{{ route('backend.business-projects.delivery.change-requests.approve', ['businessProject' => $businessProject, 'changeRequest' => $cr->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success btn-xs">Duyệt</button>
                </form>
                <form action="{{ route('backend.business-projects.delivery.change-requests.reject', ['businessProject' => $businessProject, 'changeRequest' => $cr->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-error btn-xs btn-outline">Từ chối</button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <p class="text-xs text-base-content/40">Chưa có Change Request nào.</p>
        @endforelse
    </div>
</div>
