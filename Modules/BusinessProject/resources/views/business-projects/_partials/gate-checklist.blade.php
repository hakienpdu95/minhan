{{--
    Gate checklist — render trực tiếp StageGateResultData từ CheckStageGateEligibilityQuery.
    Dùng chung cho MỌI workspace (Phần 5B spec): không có view nào tự vẽ lại điều kiện gate.
    Biến cần truyền vào: $gateResult (StageGateResultData), $businessProject.
--}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body py-3 px-4">
        <h3 class="font-semibold text-sm mb-2">Điều kiện chuyển giai đoạn</h3>
        <ul class="space-y-1.5 mb-3">
            @foreach($gateResult->conditions as $condition)
            <li class="flex items-start gap-2 text-xs">
                @if($condition->met)
                <svg class="w-3.5 h-3.5 text-success shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="text-base-content/70">{{ $condition->label }}</span>
                @else
                <svg class="w-3.5 h-3.5 text-base-content/30 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <circle cx="12" cy="12" r="9" stroke-width="2"/>
                </svg>
                <span class="text-base-content/40">{{ $condition->label }}</span>
                @endif
            </li>
            @endforeach
        </ul>

        @if($gateResult->nextStage)
        <form action="{{ route('backend.business-projects.advance-stage', $businessProject) }}" method="POST">
            @csrf
            <button type="submit"
                    @disabled(!$gateResult->canAdvance)
                    class="btn btn-primary btn-sm w-full"
                    title="{{ $gateResult->canAdvance ? '' : 'Chưa đủ điều kiện — xem checklist phía trên' }}">
                Chuyển sang {{ \Modules\BusinessProject\Enums\BusinessProjectStage::from($gateResult->nextStage)->label() }}
            </button>
        </form>
        @endif
    </div>
</div>
