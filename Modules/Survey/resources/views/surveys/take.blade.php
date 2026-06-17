@extends('layouts.backend')
@section('title', $survey->title)

@section('content')
<div class="max-w-2xl mx-auto py-6 px-4"
     x-data="surveyForm()"
     x-init="init()">

    {{-- Preview banner for admin viewing inactive survey --}}
    @if($survey->status !== \Modules\Survey\Enums\SurveyStatus::Active)
    <div class="alert alert-warning mb-4 py-2">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="text-sm">Chế độ xem trước — khảo sát đang ở trạng thái <strong>{{ $survey->status->label() }}</strong>, chưa mở cho người dùng.</span>
    </div>
    @endif

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-base-content">{{ $survey->title }}</h1>
        @if($survey->description)
        <p class="text-base-content/60 text-sm mt-1">{{ $survey->description }}</p>
        @endif
    </div>

    {{-- Already done --}}
    @if($alreadyDone)
    <div class="alert alert-info">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Bạn đã hoàn thành khảo sát này rồi.</span>
    </div>
    @else

    {{-- Validation errors --}}
    @if($errors->any())
    <div class="alert alert-error mb-4">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="font-medium">Vui lòng kiểm tra lại các câu trả lời:</p>
            <ul class="text-sm mt-1 list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <form method="POST"
          action="{{ route('backend.surveys.take.submit', $survey->slug) }}"
          @submit.prevent="handleSubmit($event)">
        @csrf

        @forelse($sections as $section)
        <div class="mb-8">

            @if($sections->count() > 1)
            <div class="flex items-center gap-3 mb-4">
                <span class="badge badge-primary badge-outline text-xs">Phần {{ $loop->iteration }}</span>
                @if($section->title)
                <h2 class="font-semibold text-base-content">{{ $section->title }}</h2>
                @endif
            </div>
            @elseif($section->title)
            <h2 class="font-semibold text-base-content mb-4">{{ $section->title }}</h2>
            @endif

            <div class="space-y-5">
                @foreach($section->fields as $field)
                @php
                    $fieldKey  = $field->field_key;
                    $inputName = "answers[{$fieldKey}]";
                    $oldVal    = old("answers.{$fieldKey}");
                    $hasError  = $errors->has($fieldKey);
                @endphp

                <div class="form-control @if($hasError) has-error @endif"
                     id="field-{{ $fieldKey }}">

                    {{-- Label --}}
                    <label class="label pb-1">
                        <span class="label-text font-medium text-sm">
                            {{ $field->label }}
                            @if($field->is_required)
                            <span class="text-error ml-0.5">*</span>
                            @endif
                        </span>
                    </label>

                    {{-- Field hint / placeholder --}}
                    @if($field->placeholder)
                    <p class="text-xs text-base-content/40 mb-1.5">{{ $field->placeholder }}</p>
                    @endif

                    {{-- Error message --}}
                    @error($fieldKey)
                    <p class="text-xs text-error mb-1">{{ $message }}</p>
                    @enderror

                    {{-- ── Field types ── --}}

                    @if($field->field_type->value === 1) {{-- Text --}}
                        <input type="text"
                               name="{{ $inputName }}"
                               value="{{ $oldVal }}"
                               class="input input-bordered input-sm w-full @if($hasError) input-error @endif"
                               @if($field->is_required) required @endif>

                    @elseif($field->field_type->value === 2) {{-- Textarea --}}
                        <textarea name="{{ $inputName }}"
                                  rows="4"
                                  class="textarea textarea-bordered w-full text-sm @if($hasError) textarea-error @endif"
                                  @if($field->is_required) required @endif>{{ $oldVal }}</textarea>

                    @elseif($field->field_type->value === 3) {{-- Number --}}
                        <input type="number"
                               name="{{ $inputName }}"
                               value="{{ $oldVal }}"
                               class="input input-bordered input-sm w-40 @if($hasError) input-error @endif"
                               @if($field->rule_min !== null) min="{{ $field->rule_min }}" @endif
                               @if($field->rule_max !== null) max="{{ $field->rule_max }}" @endif
                               @if($field->is_required) required @endif>

                    @elseif($field->field_type->value === 4) {{-- Select --}}
                        <select name="{{ $inputName }}"
                                class="select select-bordered select-sm w-full max-w-xs @if($hasError) select-error @endif"
                                @if($field->is_required) required @endif>
                            <option value="">— Chọn —</option>
                            @foreach($field->options as $opt)
                            <option value="{{ $opt->option_value }}"
                                    @selected($oldVal === $opt->option_value)>
                                {{ $opt->label }}
                            </option>
                            @endforeach
                        </select>

                    @elseif($field->field_type->value === 5) {{-- Radio --}}
                        <div class="flex flex-col gap-1.5 mt-0.5">
                            @foreach($field->options as $opt)
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio"
                                       name="{{ $inputName }}"
                                       value="{{ $opt->option_value }}"
                                       class="radio radio-primary radio-sm"
                                       @checked($oldVal === $opt->option_value)
                                       @if($field->is_required) required @endif>
                                <span class="text-sm group-hover:text-primary transition-colors">{{ $opt->label }}</span>
                                @if($opt->is_other)
                                <input type="text"
                                       name="answers_other[{{ $fieldKey }}]"
                                       placeholder="Nhập câu trả lời..."
                                       class="input input-bordered input-xs ml-2 w-40"
                                       value="{{ old("answers_other.{$fieldKey}") }}">
                                @endif
                            </label>
                            @endforeach
                        </div>

                    @elseif($field->field_type->value === 6) {{-- Checkbox --}}
                        <div class="flex flex-col gap-1.5 mt-0.5">
                            @foreach($field->options as $opt)
                            @php
                                $checked = is_array($oldVal) && in_array($opt->option_value, $oldVal);
                            @endphp
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox"
                                       name="{{ $inputName }}[]"
                                       value="{{ $opt->option_value }}"
                                       class="checkbox checkbox-primary checkbox-sm"
                                       @checked($checked)>
                                <span class="text-sm group-hover:text-primary transition-colors">{{ $opt->label }}</span>
                                @if($opt->is_other)
                                <input type="text"
                                       name="answers_other[{{ $fieldKey }}]"
                                       placeholder="Nhập câu trả lời..."
                                       class="input input-bordered input-xs ml-2 w-40"
                                       value="{{ old("answers_other.{$fieldKey}") }}">
                                @endif
                            </label>
                            @endforeach
                        </div>
                        @if($field->rule_max_select)
                        <p class="text-xs text-base-content/40 mt-1">Chọn tối đa {{ $field->rule_max_select }} lựa chọn</p>
                        @endif

                    @elseif($field->field_type->value === 7) {{-- Rating --}}
                        @php $maxStars = $field->rule_max ?? 5; @endphp
                        <div x-data="{ rating: {{ (int)($oldVal ?? 0) }} }" class="flex items-center gap-1 mt-1">
                            <input type="hidden" name="{{ $inputName }}" :value="rating">
                            @for($s = 1; $s <= $maxStars; $s++)
                            <button type="button"
                                    @click="rating = {{ $s }}"
                                    class="text-2xl leading-none transition-colors"
                                    :class="rating >= {{ $s }} ? 'text-yellow-400' : 'text-base-content/20 hover:text-yellow-300'">
                                ★
                            </button>
                            @endfor
                            <span class="text-xs text-base-content/40 ml-2" x-text="rating > 0 ? rating + '/{{ $maxStars }}' : 'Chưa chọn'"></span>
                        </div>

                    @elseif($field->field_type->value === 8) {{-- Date --}}
                        <input type="date"
                               name="{{ $inputName }}"
                               value="{{ $oldVal }}"
                               class="input input-bordered input-sm w-44 @if($hasError) input-error @endif"
                               @if($field->is_required) required @endif>

                    @elseif($field->field_type->value === 9) {{-- Boolean --}}
                        <div x-data="{ val: '{{ $oldVal ?? '' }}' }" class="flex gap-2 mt-1">
                            <input type="hidden" name="{{ $inputName }}" :value="val">
                            <button type="button"
                                    @click="val = '1'"
                                    :class="val === '1' ? 'btn-success' : 'btn-outline'"
                                    class="btn btn-sm gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Có
                            </button>
                            <button type="button"
                                    @click="val = '0'"
                                    :class="val === '0' ? 'btn-error' : 'btn-outline'"
                                    class="btn btn-sm gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Không
                            </button>
                        </div>

                    @elseif($field->field_type->value === 12) {{-- NPS 0–10 --}}
                        <div x-data="{ val: {{ $oldVal ?? 'null' }} }" class="mt-1">
                            <input type="hidden" name="{{ $inputName }}" :value="val">
                            <div class="flex flex-wrap gap-1">
                                @for($n = 0; $n <= 10; $n++)
                                <button type="button"
                                        @click="val = {{ $n }}"
                                        :class="val === {{ $n }}
                                            ? ({{ $n }} <= 6 ? 'btn-error' : ({{ $n }} <= 8 ? 'btn-warning' : 'btn-success'))
                                            : 'btn-outline'"
                                        class="btn btn-xs w-9">{{ $n }}</button>
                                @endfor
                            </div>
                            <div class="flex justify-between text-xs text-base-content/40 mt-1 px-0.5">
                                <span>Hoàn toàn không</span>
                                <span>Chắc chắn sẽ</span>
                            </div>
                        </div>

                    @elseif($field->field_type->value === 10) {{-- Matrix --}}
                        <div class="overflow-x-auto mt-1">
                            <table class="table table-xs border border-base-200">
                                <thead>
                                    <tr>
                                        <th class="w-1/3"></th>
                                        @foreach($field->options as $opt)
                                        <th class="text-center text-xs font-medium">{{ $opt->label }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($field->rows as $row)
                                    <tr class="hover">
                                        <td class="text-sm">{{ $row->label }}</td>
                                        @foreach($field->options as $opt)
                                        <td class="text-center">
                                            <input type="radio"
                                                   name="{{ $inputName }}[{{ $row->row_key }}]"
                                                   value="{{ $opt->option_value }}"
                                                   class="radio radio-primary radio-xs">
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    @elseif($field->field_type->value === 11) {{-- Ranking --}}
                        <div class="flex flex-col gap-1.5 mt-1" id="ranking-{{ $fieldKey }}">
                            @foreach($field->options as $idx => $opt)
                            <div class="flex items-center gap-2 bg-base-200 rounded-lg px-3 py-2">
                                <span class="text-base-content/40 text-xs w-5">{{ $idx + 1 }}.</span>
                                <select name="{{ $inputName }}[{{ $opt->option_value }}]"
                                        class="select select-bordered select-xs flex-1">
                                    <option value="">— Vị trí —</option>
                                    @for($r = 1; $r <= $field->options->count(); $r++)
                                    <option value="{{ $r }}">{{ $r }}</option>
                                    @endfor
                                </select>
                                <span class="text-sm flex-1">{{ $opt->label }}</span>
                            </div>
                            @endforeach
                        </div>

                    @else
                        <input type="text"
                               name="{{ $inputName }}"
                               value="{{ $oldVal }}"
                               class="input input-bordered input-sm w-full"
                               @if($field->is_required) required @endif>
                    @endif

                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="alert alert-warning">Khảo sát này chưa có câu hỏi nào.</div>
        @endforelse

        @if($sections->flatMap->fields->isNotEmpty())
        <div class="flex justify-end mt-8 pt-4 border-t border-base-200">
            @if($survey->status !== \Modules\Survey\Enums\SurveyStatus::Active)
            <span class="text-xs text-base-content/40 self-center mr-3">Chế độ xem trước — không thể nộp</span>
            @endif
            <button type="submit"
                    @if($survey->status !== \Modules\Survey\Enums\SurveyStatus::Active) disabled @endif
                    class="btn btn-primary gap-2"
                    :disabled="submitting"
                    :class="submitting ? 'loading' : ''">
                <svg x-show="!submitting" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                <span x-text="submitting ? 'Đang gửi...' : 'Nộp khảo sát'">Nộp khảo sát</span>
            </button>
        </div>
        @endif

    </form>
    @endif

</div>

@push('scripts')
<script>
function surveyForm() {
    return {
        submitting: false,
        init() {},
        handleSubmit(e) {
            this.submitting = true;
            e.target.submit();
        },
    };
}
</script>
@endpush
@endsection
