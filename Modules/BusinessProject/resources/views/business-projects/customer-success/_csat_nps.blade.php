{{--
    CSAT/NPS — Giai đoạn 8: "tạo khảo sát bằng Survey engine hiện có, KHÔNG xây form khảo sát
    mới". CS staff điền hộ khách hàng qua trang "take" chuẩn của Survey engine (đã
    allow_multiple_responses=true, mỗi lần điền = 1 SurveyResponse riêng), sau đó quay lại đây
    gắn đúng response vào dự án. Biến: $businessProject, $csatNpsSurvey, $attachableSurveyResponses.
--}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <h2 class="font-semibold mb-3">CSAT / NPS</h2>

        <div class="flex flex-wrap items-center gap-2 mb-3">
            <form action="{{ route('backend.business-projects.customer-success.survey.attach', $businessProject) }}" method="POST" class="flex items-center gap-2">
                @csrf
                <select name="survey_response_id" class="select select-bordered select-xs" required>
                    <option value="" disabled selected>Gắn kết quả khảo sát đã điền...</option>
                    @foreach($attachableSurveyResponses as $response)
                    <option value="{{ $response->id }}">
                        #{{ $response->id }} — {{ $response->respondent_ref }} ({{ $response->submitted_at?->format('d/m/Y H:i') }})
                    </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline btn-xs">Gắn</button>
            </form>

            <a href="{{ route('backend.surveys.take', $csatNpsSurvey) }}" class="btn btn-primary btn-xs" target="_blank" rel="noopener">
                + Điền khảo sát CSAT/NPS mới
            </a>
        </div>

        @if($attachableSurveyResponses->isEmpty())
        <p class="text-xs text-base-content/40">
            Chưa có kết quả khảo sát nào chờ gắn — bấm "Điền khảo sát CSAT/NPS mới" để bắt đầu.
        </p>
        @endif
    </div>
</div>
