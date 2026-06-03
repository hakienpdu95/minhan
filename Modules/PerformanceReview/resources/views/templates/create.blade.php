@extends('layouts.backend')
@section('title', 'Tạo mẫu đánh giá')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.performance-reviews.index') }}">Đánh giá hiệu suất</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.review-templates.index') }}">Mẫu đánh giá</a>
    <span class="sep">›</span>
    <span class="current">Tạo mới</span>
</nav>
@endsection

@section('content')
<div x-data="{
    criteria: [
        { criteria_key: '', criteria_name: '', weight: '', max_score: 5, description: '' }
    ],
    addCriteria() {
        this.criteria.push({ criteria_key: '', criteria_name: '', weight: '', max_score: 5, description: '' });
    },
    removeCriteria(i) {
        if (this.criteria.length > 1) this.criteria.splice(i, 1);
    },
    totalWeight() {
        return this.criteria.reduce((s, c) => s + (parseFloat(c.weight) || 0), 0);
    },
    slugify(v) { return v.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, ''); },
}">

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Tạo mẫu đánh giá mới</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Định nghĩa bộ tiêu chí và trọng số</p>
    </div>
    <a href="{{ route('backend.review-templates.index') }}" class="btn btn-ghost btn-sm gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Quay lại
    </a>
</div>

@if($errors->any())
<div class="alert alert-error py-3 px-4 mb-5 text-sm">
    <ul class="list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('backend.review-templates.store') }}">
@csrf

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <div class="lg:col-span-2 space-y-5">

        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5 space-y-4">
                <h2 class="text-base font-semibold">Thông tin mẫu</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-control sm:col-span-2">
                        <label class="label py-1"><span class="label-text text-sm font-medium">Tên mẫu <span class="text-error">*</span></span></label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="VD: Đánh giá Q4/2026 — Kinh doanh"
                               class="input input-sm input-bordered w-full @error('name') input-error @enderror" required/>
                    </div>

                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-sm font-medium">Chu kỳ <span class="text-error">*</span></span></label>
                        <select name="period_type" class="select select-sm select-bordered w-full" required>
                            @foreach(\Modules\PerformanceReview\Enums\PeriodType::cases() as $pt)
                            <option value="{{ $pt->value }}" {{ old('period_type', 'quarterly') == $pt->value ? 'selected' : '' }}>{{ $pt->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-sm font-medium">Thang điểm tối đa <span class="text-error">*</span></span></label>
                        <select name="rating_scale" class="select select-sm select-bordered w-full" required>
                            <option value="5" {{ old('rating_scale', '5') == '5' ? 'selected' : '' }}>5 điểm</option>
                            <option value="10" {{ old('rating_scale') == '10' ? 'selected' : '' }}>10 điểm</option>
                        </select>
                    </div>

                    <div class="form-control sm:col-span-2">
                        <label class="label py-1"><span class="label-text text-sm font-medium">Áp dụng cho phòng ban chức năng</span></label>
                        <input type="text" name="apply_to_function" value="{{ old('apply_to_function') }}"
                               placeholder="VD: sales, marketing, hr (để trống = áp dụng tất cả)"
                               class="input input-sm input-bordered w-full"/>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-semibold">Tiêu chí đánh giá</h2>
                        <p class="text-xs text-base-content/40 mt-0.5">Tổng trọng số:
                            <span :class="Math.abs(totalWeight()-100) < 0.01 ? 'text-success font-bold' : 'text-error font-bold'" x-text="totalWeight().toFixed(1) + '%'"></span>
                            (phải bằng 100%)
                        </p>
                    </div>
                    <button type="button" @click="addCriteria()" class="btn btn-ghost btn-sm gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Thêm tiêu chí
                    </button>
                </div>

                <div class="space-y-4">
                    <template x-for="(c, i) in criteria" :key="i">
                        <div class="border border-base-200 rounded-xl p-4 relative">
                            <button type="button" @click="removeCriteria(i)" x-show="criteria.length > 1"
                                    class="absolute top-3 right-3 btn btn-ghost btn-xs btn-square text-error">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>

                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div class="form-control">
                                    <label class="label py-0.5"><span class="label-text text-xs font-medium">Tên tiêu chí *</span></label>
                                    <input type="text" :name="'criteria['+i+'][criteria_name]'" x-model="c.criteria_name"
                                           @input="c.criteria_key = slugify(c.criteria_name)"
                                           placeholder="VD: Chất lượng công việc"
                                           class="input input-xs input-bordered w-full" required/>
                                </div>
                                <div class="form-control">
                                    <label class="label py-0.5"><span class="label-text text-xs font-medium">Khóa định danh *</span></label>
                                    <input type="text" :name="'criteria['+i+'][criteria_key]'" x-model="c.criteria_key"
                                           placeholder="work_quality"
                                           class="input input-xs input-bordered w-full font-mono" required/>
                                </div>
                                <div class="form-control">
                                    <label class="label py-0.5"><span class="label-text text-xs font-medium">Trọng số (%) *</span></label>
                                    <input type="number" :name="'criteria['+i+'][weight]'" x-model="c.weight"
                                           min="0.01" max="100" step="0.5" placeholder="30"
                                           class="input input-xs input-bordered w-full" required/>
                                </div>
                                <div class="form-control">
                                    <label class="label py-0.5"><span class="label-text text-xs font-medium">Điểm tối đa *</span></label>
                                    <input type="number" :name="'criteria['+i+'][max_score]'" x-model="c.max_score"
                                           min="1" max="10" placeholder="5"
                                           class="input input-xs input-bordered w-full" required/>
                                </div>
                            </div>

                            <div class="form-control">
                                <label class="label py-0.5"><span class="label-text text-xs font-medium">Hướng dẫn chấm điểm</span></label>
                                <input type="text" :name="'criteria['+i+'][description]'" x-model="c.description"
                                       placeholder="Mô tả cách đánh giá tiêu chí này..."
                                       class="input input-xs input-bordered w-full"/>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>

    <div>
        <div class="card bg-base-100 shadow-sm border border-base-200 sticky top-4">
            <div class="card-body p-5 space-y-3">
                <h2 class="text-base font-semibold">Lưu mẫu</h2>
                <p class="text-xs text-base-content/50">Đảm bảo tổng trọng số = 100% trước khi lưu.</p>
                <button type="submit" class="btn btn-primary btn-sm w-full">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Tạo mẫu
                </button>
                <a href="{{ route('backend.review-templates.index') }}" class="btn btn-ghost btn-sm w-full">Hủy</a>
            </div>
        </div>
    </div>

</div>
</form>

</div>
@endsection
