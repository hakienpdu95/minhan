@extends('layouts.backend')
@section('title', 'Sửa mục tiêu KPI')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.kpi.goals.index') }}">Mục tiêu KPI</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.kpi.goals.show', $goal) }}">Chi tiết</a>
    <span class="sep">›</span>
    <span class="current">Sửa</span>
</nav>
@endsection

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold mb-6">Sửa mục tiêu KPI</h1>

    @if($errors->any())
    <div class="alert alert-error mb-4">
        <ul class="text-sm list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('backend.kpi.goals.update', $goal) }}" class="space-y-4">
        @csrf @method('PUT')

        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body space-y-4">
                <h2 class="card-title text-base">Thông tin mục tiêu</h2>

                <div class="form-control">
                    <label class="label"><span class="label-text">Nhân viên</span></label>
                    <input type="text" value="{{ $goal->employee?->full_name }} ({{ $goal->employee?->employee_code }})"
                           class="input input-bordered bg-base-200" readonly>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text">Tên mục tiêu <span class="text-error">*</span></span></label>
                    <input type="text" name="title" value="{{ old('title', $goal->title) }}"
                           class="input input-bordered w-full" required>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text">Mô tả</span></label>
                    <textarea name="description" rows="2" class="textarea textarea-bordered">{{ old('description', $goal->description) }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Giá trị mục tiêu <span class="text-error">*</span></span></label>
                        <input type="number" name="target_value" value="{{ old('target_value', $goal->target_value) }}"
                               step="any" class="input input-bordered" required>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Đơn vị</span></label>
                        <input type="text" name="unit" value="{{ old('unit', $goal->unit) }}"
                               class="input input-bordered" placeholder="%, VND, tasks…">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Hướng</span></label>
                        <select name="direction" class="select select-bordered">
                            @foreach($directions as $d)
                            <option value="{{ $d['value'] }}" @selected(old('direction', $goal->direction->value) === $d['value'])>
                                {{ $d['text'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Trọng số (%)</span></label>
                        <input type="number" name="weight_percent" value="{{ old('weight_percent', $goal->weight_percent) }}"
                               min="1" max="100" class="input input-bordered">
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body space-y-4">
                <h2 class="card-title text-base">Kỳ đánh giá</h2>
                <div class="grid grid-cols-3 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Nhãn kỳ</span></label>
                        <input type="text" name="cycle_label" value="{{ old('cycle_label', $goal->cycle_label) }}"
                               class="input input-bordered" required>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Bắt đầu</span></label>
                        <input type="date" name="cycle_start"
                               value="{{ old('cycle_start', $goal->cycle_start?->format('Y-m-d')) }}"
                               class="input input-bordered">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Kết thúc</span></label>
                        <input type="date" name="cycle_end"
                               value="{{ old('cycle_end', $goal->cycle_end?->format('Y-m-d')) }}"
                               class="input input-bordered">
                    </div>
                </div>
                <input type="hidden" name="parent_goal_id" value="{{ $goal->parent_goal_id }}">
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            <a href="{{ route('backend.kpi.goals.show', $goal) }}" class="btn btn-ghost">Hủy</a>
        </div>
    </form>
</div>
@endsection
