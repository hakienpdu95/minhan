@extends('layouts.backend')

@section('title', 'Nộp đánh giá phỏng vấn')

@section('breadcrumb')
<div class="breadcrumbs text-sm px-6 pt-4 pb-0">
    <ul>
        <li><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
        <li><a href="{{ route('backend.recruitment.interviews.show', $interview) }}">Phỏng vấn</a></li>
        <li class="font-semibold">Đánh giá</li>
    </ul>
</div>
@endsection

@section('content')
<div x-data="rcEvaluationCreate" class="p-6 max-w-2xl">

    <h1 class="text-xl font-bold mb-1">Đánh giá phỏng vấn</h1>
    <p class="text-sm opacity-60 mb-5">
        Ứng viên: <strong>{{ $interview->application?->candidate?->full_name }}</strong>
        · {{ $interview->title ?: $interview->interview_type?->label() }}
        · {{ $interview->scheduled_at?->format('d/m/Y H:i') }}
    </p>

    @if($existing?->is_submitted)
    <div class="alert alert-info mb-4">
        <span class="text-sm">Bạn đã nộp đánh giá cho buổi phỏng vấn này. Nộp lại sẽ ghi đè đánh giá cũ.</span>
    </div>
    @endif

    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-5 space-y-5">

            {{-- Overall score --}}
            <div class="form-control">
                <label class="label"><span class="label-text font-medium">Điểm tổng thể (1–10)</span></label>
                <input type="range" min="1" max="10" x-model="form.overall_score" class="range range-primary range-sm">
                <div class="flex justify-between text-xs opacity-40 mt-1">
                    @for($i = 1; $i <= 10; $i++)
                    <span>{{ $i }}</span>
                    @endfor
                </div>
                <p class="text-center text-2xl font-bold mt-1" x-text="form.overall_score + '/10'"></p>
            </div>

            {{-- Verdict --}}
            <div class="form-control">
                <label class="label"><span class="label-text font-medium">Kết luận</span></label>
                <div class="flex flex-wrap gap-2">
                    @foreach($verdicts as $v)
                    <label class="cursor-pointer">
                        <input type="radio" class="hidden" :value="'{{ $v['value'] }}'" x-model="form.verdict">
                        @php
                            $vClass = match($v['value']) {
                                'strong_yes' => 'btn-success',
                                'yes'        => 'btn-primary',
                                'neutral'    => 'btn-ghost',
                                'no'         => 'btn-warning',
                                'strong_no'  => 'btn-error',
                                default      => 'btn-ghost',
                            };
                        @endphp
                        <span class="btn btn-sm {{ $vClass }}"
                              :class="form.verdict === '{{ $v['value'] }}' ? 'btn-active ring-2 ring-offset-1' : 'btn-outline'">
                            {{ $v['text'] }}
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Strengths / Weaknesses --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium text-success">Điểm mạnh</span></label>
                    <textarea x-model="form.strengths" class="textarea textarea-bordered textarea-sm" rows="3"
                              placeholder="Kỹ năng tốt, kinh nghiệm phù hợp...">{{ old('strengths', $existing?->strengths) }}</textarea>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium text-error">Điểm yếu</span></label>
                    <textarea x-model="form.weaknesses" class="textarea textarea-bordered textarea-sm" rows="3"
                              placeholder="Còn thiếu kinh nghiệm về...">{{ old('weaknesses', $existing?->weaknesses) }}</textarea>
                </div>
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-medium">Khuyến nghị</span></label>
                <textarea x-model="form.recommendation" class="textarea textarea-bordered" rows="2"
                          placeholder="Đề xuất chuyển sang vòng tiếp theo / đề nghị offer...">{{ old('recommendation', $existing?->recommendation) }}</textarea>
            </div>

            {{-- Criteria --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="font-medium text-sm">Tiêu chí đánh giá chi tiết</label>
                    <button type="button" @click="addCriterion()" class="btn btn-ghost btn-xs">+ Thêm tiêu chí</button>
                </div>

                <template x-for="(c, idx) in form.criteria" :key="idx">
                    <div class="flex gap-2 mb-2 items-start">
                        <div class="flex-1">
                            <input type="text" x-model="c.criterion_name" placeholder="VD: Kỹ năng kỹ thuật"
                                   class="input input-bordered input-sm w-full mb-1" maxlength="100">
                            <textarea x-model="c.comment" placeholder="Nhận xét..." rows="1"
                                      class="textarea textarea-bordered textarea-xs w-full"></textarea>
                        </div>
                        <div class="flex flex-col items-center gap-1 shrink-0 w-20">
                            <input type="number" x-model.number="c.score" min="1" max="10"
                                   class="input input-bordered input-sm w-full text-center">
                            <span class="text-xs opacity-40">/10</span>
                        </div>
                        <button type="button" @click="removeCriterion(idx)" class="btn btn-ghost btn-xs text-error mt-1">✕</button>
                    </div>
                </template>

                <p x-show="form.criteria.length === 0" class="text-xs opacity-40">Chưa có tiêu chí nào (tùy chọn)</p>
            </div>

        </div>
    </div>

    <div class="flex gap-3 mt-5">
        <button class="btn btn-primary" @click="submitEvaluation()">Nộp đánh giá</button>
        <a href="{{ route('backend.recruitment.interviews.show', $interview) }}" class="btn btn-ghost">Hủy</a>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', function() {
    var SUBMIT_URL = '{{ route('backend.recruitment.interviews.evaluations.store', $interview) }}';
    var REDIRECT   = '{{ route('backend.recruitment.interviews.show', $interview) }}';
    var CSRF       = '{{ csrf_token() }}';

    @php
        $existingCriteria = $existing?->criteria->map(fn($c) => [
            'criterion_name' => $c->criterion_name,
            'score'          => $c->score,
            'comment'        => $c->comment ?? '',
        ])->values()->toArray() ?? [];
    @endphp

    Alpine.data('rcEvaluationCreate', function() {
        return {
            form: {
                overall_score:  {{ $existing?->overall_score ?? 7 }},
                verdict:        '{{ $existing?->verdict?->value ?? '' }}',
                strengths:      @json($existing?->strengths ?? ''),
                weaknesses:     @json($existing?->weaknesses ?? ''),
                recommendation: @json($existing?->recommendation ?? ''),
                criteria:       @json($existingCriteria),
            },

            addCriterion: function() {
                this.form.criteria.push({ criterion_name: '', score: 7, comment: '' });
            },

            removeCriterion: function(idx) {
                this.form.criteria.splice(idx, 1);
            },

            submitEvaluation: function() {
                var self = this;
                if (!self.form.verdict) { alert('Vui lòng chọn kết luận'); return; }

                fetch(SUBMIT_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify(self.form),
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.message) { window.location.href = REDIRECT; }
                    else { alert('Có lỗi xảy ra'); }
                })
                .catch(function(e) { console.error(e); alert('Lỗi khi nộp đánh giá'); });
            },
        };
    });
});
</script>
@endpush
