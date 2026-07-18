{{--
    Lịch sử touchpoint Customer Success — mỗi hàng success_reviews là 1 lần chạm (CSAT/NPS,
    follow-up, renewal, hoặc New Opportunity đã tạo Lead), không ép về 1 bản ghi/project ("vòng
    đời không kết thúc ở Closed"). Biến: $businessProject, $successReviews (SuccessReview[]).
--}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <h2 class="font-semibold mb-3">Lịch sử Customer Success ({{ $successReviews->count() }})</h2>

        @forelse($successReviews as $review)
        <div class="border-b border-base-200 py-2.5 last:border-0 text-xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-base-content/40">{{ $review->created_at->format('d/m/Y H:i') }}</span>
                <span class="text-base-content/30">bởi {{ $review->createdBy?->name ?? '—' }}</span>
            </div>

            @if($review->survey_response_id)
            <div class="flex items-center gap-2">
                <span class="badge badge-info badge-sm">CSAT: {{ (int) $review->csat_score }}/5</span>
                <span class="badge badge-primary badge-sm">NPS: {{ (int) $review->nps_score }}/10</span>
            </div>
            @endif

            @if($review->follow_up_at)
            <p>
                <span class="font-medium">Theo dõi:</span> {{ $review->follow_up_at->format('d/m/Y') }}
                @if($review->followed_up_at)
                    <span class="badge badge-success badge-xs">Đã xong ({{ $review->followed_up_at->format('d/m/Y') }})</span>
                @else
                    <span class="badge badge-warning badge-xs">Chưa xong</span>
                    <form action="{{ route('backend.business-projects.customer-success.reviews.follow-up-done', [$businessProject, $review]) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-xs">Đánh dấu hoàn thành</button>
                    </form>
                @endif
                @if($review->follow_up_note) <span class="text-base-content/50">— {{ $review->follow_up_note }}</span> @endif
            </p>
            @endif

            @if($review->renewal_status && $review->renewal_status->value !== 'none')
            <p>
                <span class="font-medium">Gia hạn:</span>
                <span class="badge {{ $review->renewal_status->badgeClass() }} badge-xs">{{ $review->renewal_status->label() }}</span>
                @if($review->renewal_note) <span class="text-base-content/50">— {{ $review->renewal_note }}</span> @endif
            </p>
            @endif

            @if($review->new_lead_id)
            <p>
                <span class="font-medium">Cơ hội mới:</span>
                <a href="{{ route('lead.show', $review->newLead) }}" class="link link-primary">{{ $review->newLead?->title }}</a>
            </p>
            @endif
        </div>
        @empty
        <p class="text-xs text-base-content/40">Chưa có touchpoint nào được ghi nhận.</p>
        @endforelse
    </div>
</div>
