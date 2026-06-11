@extends('layouts.backend')
@section('title', $task ? 'Sửa nhiệm vụ' : 'Thêm nhiệm vụ')

@push('styles')
    @vite(['Modules/Assessment/resources/assets/sass/assessment.scss'], 'build/backend')
@endpush

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('backend.sandbox-admin.tasks', $env) }}" class="btn btn-ghost btn-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-xl font-bold">{{ $task ? 'Sửa nhiệm vụ' : 'Thêm nhiệm vụ mới' }}</h1>
        <p class="text-xs text-base-content/40 mt-0.5">Môi trường: {{ $env->name }}</p>
    </div>
</div>

<div class="max-w-3xl">
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">

            <form method="POST"
                  action="{{ $task ? route('backend.sandbox-admin.task.update', $task) : route('backend.sandbox-admin.task.store', $env) }}">
                @csrf
                @if($task) @method('PUT') @endif

                {{-- Title --}}
                <div class="form-control mb-4">
                    <label class="label py-1"><span class="label-text text-xs font-medium">Tiêu đề nhiệm vụ <span class="text-error">*</span></span></label>
                    <input type="text" name="title" class="input input-bordered input-sm @error('title') input-error @enderror"
                           value="{{ old('title', $task?->title) }}"
                           placeholder="VD: Soạn email từ chối lịch họp bằng AI">
                    @error('title')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Instruction --}}
                <div class="form-control mb-4">
                    <label class="label py-1">
                        <span class="label-text text-xs font-medium">Hướng dẫn nhiệm vụ <span class="text-error">*</span></span>
                        <span class="label-text-alt text-xs text-base-content/40">Nhân viên sẽ đọc phần này trước khi làm</span>
                    </label>
                    <textarea name="instruction" rows="6" class="textarea textarea-bordered text-sm @error('instruction') textarea-error @enderror"
                              placeholder="Mô tả tình huống cụ thể, yêu cầu đầu ra, ràng buộc cần tuân theo...">{{ old('instruction', $task?->instruction) }}</textarea>
                    <label class="label py-0.5"><span class="label-text-alt text-xs text-base-content/40">Mô tả rõ: bối cảnh, yêu cầu, ràng buộc. Càng cụ thể nhân viên càng biết mình cần làm gì.</span></label>
                    @error('instruction')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Expected output --}}
                <div class="form-control mb-4">
                    <label class="label py-1">
                        <span class="label-text text-xs font-medium">Kết quả mong đợi</span>
                        <span class="label-text-alt text-xs text-base-content/40">Mô tả một bài làm tốt trông như thế nào</span>
                    </label>
                    <textarea name="expected_output" rows="3" class="textarea textarea-bordered text-sm"
                              placeholder="VD: Email 150-200 từ, tiêu đề rõ ràng, nội dung chuyên nghiệp lịch sự, đề xuất thời gian họp thay thế...">{{ old('expected_output', $task?->expected_output) }}</textarea>
                </div>

                {{-- Scoring rubric --}}
                <div class="form-control mb-4">
                    <label class="label py-1">
                        <span class="label-text text-xs font-medium">Tiêu chí chấm điểm (Rubric)</span>
                        <span class="label-text-alt text-xs text-base-content/40">Phân cách bằng |</span>
                    </label>
                    <input type="text" name="scoring_rubric" class="input input-bordered input-sm"
                           value="{{ old('scoring_rubric', $task?->scoring_rubric) }}"
                           placeholder="Ngữ điệu chuyên nghiệp|Cấu trúc rõ ràng|Đề xuất giải pháp thay thế|Không có lỗi chính tả">
                    <label class="label py-0.5"><span class="label-text-alt text-xs text-base-content/40">Được hiển thị cho nhân viên để họ biết được đánh giá theo tiêu chí nào</span></label>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    {{-- Time limit --}}
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-xs font-medium">Giới hạn thời gian (phút) <span class="text-error">*</span></span></label>
                        <input type="number" name="time_limit_minutes" min="5" max="180" class="input input-bordered input-sm @error('time_limit_minutes') input-error @enderror"
                               value="{{ old('time_limit_minutes', $task?->time_limit_minutes ?? 30) }}">
                        <label class="label py-0.5"><span class="label-text-alt text-xs text-base-content/40">5–180 phút. Ảnh hưởng đến điểm Năng suất.</span></label>
                        @error('time_limit_minutes')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Sort order --}}
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-xs font-medium">Thứ tự (độ khó)</span></label>
                        <input type="number" name="sort_order" min="0" class="input input-bordered input-sm"
                               value="{{ old('sort_order', $task?->sort_order ?? 0) }}">
                        <label class="label py-0.5"><span class="label-text-alt text-xs text-base-content/40">0 = đầu tiên. Dùng để sắp xếp theo độ khó tăng dần.</span></label>
                    </div>
                </div>

                {{-- AI Tools --}}
                <div class="form-control mb-4">
                    <label class="label py-1">
                        <span class="label-text text-xs font-medium">Công cụ AI cho phép</span>
                        <span class="label-text-alt text-xs text-base-content/40">Phân cách bằng |</span>
                    </label>
                    <input type="text" name="ai_tools_allowed" class="input input-bordered input-sm"
                           value="{{ old('ai_tools_allowed', $task?->ai_tools_allowed ?? 'ChatGPT|Claude|Gemini') }}"
                           placeholder="ChatGPT|Claude|Gemini|Copilot|NotebookLM">
                    <label class="label py-0.5"><span class="label-text-alt text-xs text-base-content/40">Khai báo rõ để nhân viên biết nên dùng công cụ nào. Ảnh hưởng đến điểm AI Adoption.</span></label>
                </div>

                {{-- Target position --}}
                <div class="form-control mb-5">
                    <label class="label py-1">
                        <span class="label-text text-xs font-medium">Vị trí mục tiêu (tuỳ chọn)</span>
                        <span class="label-text-alt text-xs text-base-content/40">Mã vị trí phù hợp với nhiệm vụ này</span>
                    </label>
                    <input type="text" name="target_position_code" class="input input-bordered input-sm font-mono"
                           value="{{ old('target_position_code', $task?->target_position_code) }}"
                           placeholder="VD: SALES_EXEC, HR_STAFF, ALL">
                </div>

                {{-- Active --}}
                <div class="form-control mb-5">
                    <label class="label cursor-pointer justify-start gap-3 py-1">
                        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm checkbox-primary"
                               {{ old('is_active', $task?->is_active ?? true) ? 'checked' : '' }}>
                        <span class="label-text text-sm">Kích hoạt (nhân viên có thể thực hành)</span>
                    </label>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        {{ $task ? 'Cập nhật nhiệm vụ' : 'Tạo nhiệm vụ' }}
                    </button>
                    <a href="{{ route('backend.sandbox-admin.tasks', $env) }}" class="btn btn-ghost btn-sm">Huỷ</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
