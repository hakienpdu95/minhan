@extends('layouts.backend')
@section('title', 'Tạo mục tiêu KPI')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.kpi.goals.index') }}">Mục tiêu KPI</a>
    <span class="sep">›</span>
    <span class="current">Tạo mới</span>
</nav>
@endsection

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold mb-6">Tạo mục tiêu KPI</h1>

    @if($errors->any())
    <div class="alert alert-error mb-4">
        <ul class="text-sm list-disc list-inside">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('backend.kpi.goals.store') }}" class="space-y-4">
        @csrf

        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body space-y-4">
                <h2 class="card-title text-base">Thông tin mục tiêu</h2>

                <div class="form-control">
                    <label class="label"><span class="label-text">Nhân viên <span class="text-error">*</span></span></label>
                    <select name="employee_id" class="select select-bordered w-full" required>
                        <option value="">-- Chọn nhân viên --</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" @selected(old('employee_id') == $emp->id)>
                            {{ $emp->full_name }} ({{ $emp->employee_code }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text">Tên mục tiêu <span class="text-error">*</span></span></label>
                    <input type="text" name="title" value="{{ old('title') }}"
                           class="input input-bordered w-full" placeholder="VD: Đạt doanh số 500 triệu Q3" required>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text">Mô tả</span></label>
                    <textarea name="description" rows="2" class="textarea textarea-bordered">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Giá trị mục tiêu <span class="text-error">*</span></span></label>
                        <input type="number" name="target_value" value="{{ old('target_value') }}"
                               step="any" class="input input-bordered" required>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Đơn vị</span></label>
                        <input type="text" name="unit" value="{{ old('unit') }}"
                               class="input input-bordered" placeholder="%, VND, tasks…">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Hướng <span class="text-error">*</span></span></label>
                        <select name="direction" class="select select-bordered" required>
                            @foreach($directions as $d)
                            <option value="{{ $d['value'] }}" @selected(old('direction', 'higher_better') === $d['value'])>
                                {{ $d['text'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Trọng số (%) <span class="text-error">*</span></span></label>
                        <input type="number" name="weight_percent" value="{{ old('weight_percent', 10) }}"
                               min="1" max="100" class="input input-bordered" required>
                        <span class="label-text-alt mt-1">Tổng tất cả mục tiêu active = 100%</span>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Loại mục tiêu</span></label>
                        <select name="goal_type" class="select select-bordered">
                            @foreach($goalTypes as $t)
                            <option value="{{ $t['value'] }}" @selected(old('goal_type', 'manual') === $t['value'])>
                                {{ $t['text'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body space-y-4">
                <h2 class="card-title text-base">Kỳ đánh giá</h2>
                <div class="grid grid-cols-3 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Nhãn kỳ <span class="text-error">*</span></span></label>
                        <input type="text" name="cycle_label" value="{{ old('cycle_label') }}"
                               class="input input-bordered" placeholder="Q3-2024" required>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Bắt đầu <span class="text-error">*</span></span></label>
                        <input type="date" name="cycle_start" value="{{ old('cycle_start') }}"
                               class="input input-bordered" required>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Kết thúc <span class="text-error">*</span></span></label>
                        <input type="date" name="cycle_end" value="{{ old('cycle_end') }}"
                               class="input input-bordered" required>
                    </div>
                </div>
                <input type="hidden" name="parent_goal_id" value="">
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Tạo mục tiêu</button>
            <a href="{{ route('backend.kpi.goals.index') }}" class="btn btn-ghost">Hủy</a>
        </div>
    </form>
</div>
@endsection
