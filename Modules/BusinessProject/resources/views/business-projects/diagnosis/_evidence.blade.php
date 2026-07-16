{{--
    Evidence — trích dẫn Deliverable Discovery làm bằng chứng cho Diagnosis Report (Handbook 4.6:
    cột "Bằng chứng"), LIÊN KẾT chứ không copy nội dung (spec Phần 6.2, `deliverable_evidence_links`,
    dùng lại `AttachEvidenceAction` đã có sẵn từ Vertical Slice 1). Biến: $businessProject,
    $diagnosisReport (Deliverable|null, đã eager load evidenceFor), $evidenceCandidates (Deliverable[]
    từ Discovery Workspace).
--}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <h2 class="font-semibold mb-3">Evidence ({{ $diagnosisReport?->evidenceFor->count() ?? 0 }})</h2>

        <form action="{{ route('backend.business-projects.diagnosis.evidence.attach', $businessProject) }}" method="POST" class="space-y-3 mb-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="label label-text text-sm font-medium">Deliverable từ Discovery</label>
                    <select name="evidence_deliverable_id" class="select select-bordered select-sm w-full" required>
                        <option value="" disabled selected>Chọn deliverable...</option>
                        @foreach($evidenceCandidates as $candidate)
                        <option value="{{ $candidate->id }}">{{ $candidate->title }} ({{ \Modules\BusinessProject\Enums\DeliverableType::from($candidate->type)->label() }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label label-text text-sm font-medium">Loại bằng chứng</label>
                    <select name="evidence_type" class="select select-bordered select-sm w-full">
                        <option value="interview">Interview</option>
                        <option value="observation">Observation</option>
                        <option value="document_review">Document Review</option>
                        <option value="data_review">Data Review</option>
                        <option value="task">Task</option>
                        <option value="metric">Metric</option>
                    </select>
                </div>
            </div>
            <input type="text" name="note" class="input input-bordered input-sm w-full" placeholder="Ghi chú tại sao evidence này liên quan (tùy chọn)">
            <button type="submit" class="btn btn-outline btn-sm">Đính evidence</button>
        </form>

        @forelse($diagnosisReport?->evidenceFor ?? [] as $evidence)
        <div class="flex items-center justify-between text-xs border-b border-base-200 py-1.5 last:border-0">
            <div>
                <span class="font-medium">{{ $evidence->title }}</span>
                @if($evidence->pivot->note) <span class="text-base-content/50"> — {{ $evidence->pivot->note }}</span> @endif
            </div>
            <span class="badge badge-xs badge-outline">{{ $evidence->pivot->evidence_type }}</span>
        </div>
        @empty
        <p class="text-xs text-base-content/40">Chưa có evidence nào được đính kèm.</p>
        @endforelse
    </div>
</div>
