@extends('layouts.backend')
@section('title', 'Thêm chính sách nghỉ phép')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.leave.policies.index') }}">Chính sách nghỉ phép</a>
    <span class="sep">›</span>
    <span class="current">Thêm mới</span>
</nav>
@endsection

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold mb-6">Thêm chính sách nghỉ phép</h1>

    @if($errors->any())
    <div class="alert alert-error mb-4">
        <ul class="list-disc list-inside text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('backend.leave.policies.store') }}" class="space-y-4">
        @csrf

        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body space-y-4">
                <h2 class="card-title text-base">Thông tin chính sách</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control col-span-2">
                        <label class="label"><span class="label-text">Loại nghỉ <span class="text-error">*</span></span></label>
                        <select name="leave_type" class="select select-bordered w-full" required>
                            <option value="">-- Chọn loại --</option>
                            @foreach($leaveTypes as $type)
                            <option value="{{ $type['value'] }}" @selected(old('leave_type') === $type['value'])>
                                {{ $type['text'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control col-span-2">
                        <label class="label"><span class="label-text">Tên chính sách <span class="text-error">*</span></span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="input input-bordered w-full" placeholder="VD: Nghỉ phép năm toàn công ty" required>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text">Số ngày/năm <span class="text-error">*</span></span></label>
                        <input type="number" name="days_per_year" value="{{ old('days_per_year', 12) }}"
                               step="0.5" min="0" class="input input-bordered" required>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text">Ngày chuyển tiếp sang năm sau</span></label>
                        <input type="number" name="carry_over_days" value="{{ old('carry_over_days', 0) }}"
                               step="0.5" min="0" class="input input-bordered">
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text">Báo trước tối thiểu (ngày)</span></label>
                        <input type="number" name="min_advance_days" value="{{ old('min_advance_days', 1) }}"
                               min="0" class="input input-bordered">
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text">Tối đa liên tiếp (ngày)</span></label>
                        <input type="number" name="max_consecutive_days" value="{{ old('max_consecutive_days') }}"
                               min="1" class="input input-bordered" placeholder="Không giới hạn">
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text">Hiệu lực từ ngày <span class="text-error">*</span></span></label>
                        <input type="date" name="effective_from" value="{{ old('effective_from', now()->toDateString()) }}"
                               class="input input-bordered" required>
                    </div>

                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="hidden" name="requires_approval" value="0">
                            <input type="checkbox" name="requires_approval" value="1" class="checkbox"
                                   @checked(old('requires_approval', true))>
                            <span class="label-text">Yêu cầu duyệt</span>
                        </label>
                        <label class="label cursor-pointer justify-start gap-3 mt-1">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-success"
                                   @checked(old('is_active', true))>
                            <span class="label-text">Đang áp dụng</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body space-y-3">
                <h2 class="card-title text-base">Phạm vi áp dụng</h2>
                <p class="text-sm text-base-content/60">Ưu tiên: Chức danh > Phòng ban > Toàn công ty</p>

                <div class="form-control">
                    <label class="label"><span class="label-text">Áp dụng cho chức danh</span></label>
                    <select name="job_title_id" class="select select-bordered w-full">
                        <option value="">-- Toàn bộ (không giới hạn chức danh) --</option>
                        @foreach($jobTitles as $jt)
                        <option value="{{ $jt->id }}" @selected(old('job_title_id') == $jt->id)>{{ $jt->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text">Áp dụng cho phòng ban</span></label>
                    <select name="department_id" class="select select-bordered w-full">
                        <option value="">-- Toàn bộ (không giới hạn phòng ban) --</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Lưu chính sách</button>
            <a href="{{ route('backend.leave.policies.index') }}" class="btn btn-ghost">Hủy</a>
        </div>
    </form>
</div>
@endsection
