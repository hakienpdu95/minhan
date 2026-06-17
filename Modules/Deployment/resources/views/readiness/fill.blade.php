@extends('layouts.backend')
@section('title', 'Đánh giá sẵn sàng — ' . $orgName)

@section('content')
<div class="max-w-3xl">

    <div class="mb-5">
        <h1 class="text-xl font-bold">Đánh giá sẵn sàng triển khai</h1>
        <p class="text-sm text-base-content/50 mt-0.5">
            Tổ chức: <strong>{{ $orgName }}</strong> ·
            Trả lời từ 1 (Kém) đến 5 (Xuất sắc) cho mỗi câu hỏi
        </p>
    </div>

    @if($errors->any())
    <div class="alert alert-error mb-4">
        <ul class="list-disc list-inside text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST"
          action="{{ route('deployment.readiness.submit', ['vertical' => $vertical->code(), 'target' => $target->id]) }}">
        @csrf

        @foreach($sections as $section)
        <div class="card bg-base-100 border border-base-200 shadow-sm mb-5">
            <div class="card-body">
                <h2 class="card-title text-base">
                    {{ $section->icon ?? '' }} {{ $section->title }}
                    <span class="badge badge-outline badge-sm ml-auto">{{ $section->section_code }}</span>
                </h2>

                <div class="space-y-5 mt-2">
                    @foreach($section->fields as $field)
                    @php $currentVal = $existingAnswers[$field->id] ?? null; @endphp
                    <div>
                        <p class="text-sm font-medium mb-2">{{ $loop->iteration }}. {{ $field->label }}</p>

                        {{-- Star rating --}}
                        <div class="flex gap-1" x-data="{ rating: {{ (int) ($currentVal ?? 0) }} }">
                            @for($r = 1; $r <= 5; $r++)
                            <label class="cursor-pointer">
                                <input type="radio"
                                       name="answers[{{ $field->id }}]"
                                       value="{{ $r }}"
                                       class="sr-only"
                                       x-on:change="rating = {{ $r }}"
                                       {{ (int)($currentVal ?? 0) === $r ? 'checked' : '' }}>
                                <span x-on:click="rating = {{ $r }}"
                                      class="block w-10 h-10 flex items-center justify-center rounded-lg border-2 text-sm font-bold transition-all
                                             {{ (int)($currentVal ?? 0) >= $r ? 'bg-primary border-primary text-white' : 'border-base-300 text-base-content/40' }}"
                                      :class="rating >= {{ $r }} ? 'bg-primary border-primary text-white' : 'border-base-300 text-base-content/40'">
                                    {{ $r }}
                                </span>
                            </label>
                            @endfor
                            <span class="ml-3 self-center text-xs text-base-content/50"
                                  x-text="['','Kém','Yếu','Trung bình','Tốt','Xuất sắc'][rating] || 'Chưa chọn'">
                                {{ $currentVal ? ['','Kém','Yếu','Trung bình','Tốt','Xuất sắc'][(int)$currentVal] : 'Chưa chọn' }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

        <div class="flex gap-3 mt-2">
            <button type="submit" class="btn btn-primary">Nộp đánh giá</button>
            <a href="{{ route('deployment.targets.show', ['vertical' => $vertical->code(), 'target' => $target->id]) }}"
               class="btn btn-ghost">Hủy</a>
        </div>
    </form>
</div>
@endsection
