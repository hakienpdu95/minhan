@extends('layouts.backend')
@section('title', $snap ? 'Sửa bản ghi tác động AI' : 'Ghi nhận tác động AI')

@push('styles')
    @vite(['Modules/Assessment/resources/assets/sass/assessment.scss'], 'build/backend')
@endpush

@section('content')

<div class="flex items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">
            {{ $snap ? 'Sửa bản ghi tác động AI' : 'Ghi nhận tác động AI' }}
        </h1>
        <p class="text-sm text-base-content/50 mt-0.5">Ghi lại kết quả cải thiện từ việc ứng dụng AI vào công việc</p>
    </div>
    <a href="{{ route('backend.ai-impact.index') }}" class="btn btn-ghost btn-sm gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Danh sách
    </a>
</div>

<div x-data="aiImpactForm()" class="max-w-2xl">
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">

            <form method="POST"
                  action="{{ $snap ? route('backend.ai-impact.update', $snap) : route('backend.ai-impact.store') }}"
                  id="ai-impact-form">
                @csrf
                @if($snap) @method('PUT') @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    {{-- Nhân viên --}}
                    <div class="form-control sm:col-span-2">
                        <label class="label py-1" for="ts-employee_id">
                            <span class="label-text text-xs font-medium">Nhân viên</span>
                        </label>
                        <select id="ts-employee_id" name="employee_id" class="ts-init w-full"
                                placeholder="— Tất cả / chính mình —">
                            <option value=""></option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}"
                                    {{ old('employee_id', $snap?->employee_id) == $emp->id ? 'selected' : '' }}>
                                {{ $emp->full_name }}
                            </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                        <label class="label py-0.5"><span class="label-text-alt text-error text-xs">{{ $message }}</span></label>
                        @enderror
                    </div>

                    {{-- Danh mục tác động --}}
                    <div class="form-control">
                        <label class="label py-1" for="ts-impact_category">
                            <span class="label-text text-xs font-medium">Danh mục <span class="text-error">*</span></span>
                        </label>
                        <select id="ts-impact_category" name="impact_category" class="ts-init w-full" required>
                            <option value=""></option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->value }}"
                                    {{ old('impact_category', $snap?->impact_category) === $cat->value ? 'selected' : '' }}>
                                {{ match($cat->value) {
                                    'learning'     => 'Học tập & Phát triển',
                                    'productivity' => 'Năng suất làm việc',
                                    'quality'      => 'Chất lượng sản phẩm',
                                    'ai_adoption'  => 'Ứng dụng AI',
                                    'business'     => 'Tác động kinh doanh',
                                    default        => $cat->value,
                                } }}
                            </option>
                            @endforeach
                        </select>
                        @error('impact_category')
                        <label class="label py-0.5"><span class="label-text-alt text-error text-xs">{{ $message }}</span></label>
                        @enderror
                    </div>

                    {{-- Loại chỉ số --}}
                    <div class="form-control">
                        <label class="label py-1" for="impact_type">
                            <span class="label-text text-xs font-medium">Chỉ số đo lường <span class="text-error">*</span></span>
                        </label>
                        <input type="text" id="impact_type" name="impact_type"
                               class="input input-bordered input-sm w-full"
                               value="{{ old('impact_type', $snap?->impact_type) }}"
                               required
                               placeholder="VD: time_saving, productivity_gain...">
                        @error('impact_type')
                        <label class="label py-0.5"><span class="label-text-alt text-error text-xs">{{ $message }}</span></label>
                        @enderror
                    </div>

                    {{-- Kỳ từ --}}
                    <div class="form-control">
                        <label class="label py-1" for="period_start">
                            <span class="label-text text-xs font-medium">Kỳ bắt đầu <span class="text-error">*</span></span>
                        </label>
                        <input type="date" id="period_start" name="period_start"
                               class="input input-bordered input-sm w-full"
                               value="{{ old('period_start', $snap?->period_start?->format('Y-m-d')) }}"
                               required>
                        @error('period_start')
                        <label class="label py-0.5"><span class="label-text-alt text-error text-xs">{{ $message }}</span></label>
                        @enderror
                    </div>

                    {{-- Kỳ đến --}}
                    <div class="form-control">
                        <label class="label py-1" for="period_end">
                            <span class="label-text text-xs font-medium">Kỳ kết thúc <span class="text-error">*</span></span>
                        </label>
                        <input type="date" id="period_end" name="period_end"
                               class="input input-bordered input-sm w-full"
                               value="{{ old('period_end', $snap?->period_end?->format('Y-m-d')) }}"
                               required>
                        @error('period_end')
                        <label class="label py-0.5"><span class="label-text-alt text-error text-xs">{{ $message }}</span></label>
                        @enderror
                    </div>

                    {{-- Giá trị baseline --}}
                    <div class="form-control">
                        <label class="label py-1" for="baseline_value">
                            <span class="label-text text-xs font-medium">Giá trị trước AI <span class="text-error">*</span></span>
                        </label>
                        <input type="number" id="baseline_value" name="baseline_value" step="0.01" min="0"
                               class="input input-bordered input-sm w-full"
                               value="{{ old('baseline_value', $snap?->baseline_value) }}"
                               x-model.number="baselineVal"
                               required placeholder="0">
                        @error('baseline_value')
                        <label class="label py-0.5"><span class="label-text-alt text-error text-xs">{{ $message }}</span></label>
                        @enderror
                    </div>

                    {{-- Giá trị đạt được --}}
                    <div class="form-control">
                        <label class="label py-1" for="achieved_value">
                            <span class="label-text text-xs font-medium">Giá trị sau AI <span class="text-error">*</span></span>
                        </label>
                        <input type="number" id="achieved_value" name="achieved_value" step="0.01" min="0"
                               class="input input-bordered input-sm w-full"
                               value="{{ old('achieved_value', $snap?->achieved_value) }}"
                               x-model.number="achievedVal"
                               required placeholder="0">
                        <label class="label py-0.5">
                            <span class="label-text-alt text-xs text-base-content/40">Hệ thống tự tính % cải thiện</span>
                        </label>
                        @error('achieved_value')
                        <label class="label py-0.5"><span class="label-text-alt text-error text-xs">{{ $message }}</span></label>
                        @enderror
                    </div>

                    {{-- Chi phí đầu tư --}}
                    <div class="form-control">
                        <label class="label py-1" for="investment_cost">
                            <span class="label-text text-xs font-medium">Chi phí đầu tư AI (VND)</span>
                        </label>
                        <input type="number" id="investment_cost" name="investment_cost" step="1000" min="0"
                               class="input input-bordered input-sm w-full"
                               value="{{ old('investment_cost', $snap?->investment_cost) }}"
                               x-model.number="investCost"
                               placeholder="0">
                        @error('investment_cost')
                        <label class="label py-0.5"><span class="label-text-alt text-error text-xs">{{ $message }}</span></label>
                        @enderror
                    </div>

                    {{-- Giá trị lợi ích --}}
                    <div class="form-control">
                        <label class="label py-1" for="benefit_value">
                            <span class="label-text text-xs font-medium">Giá trị lợi ích thu về (VND)</span>
                        </label>
                        <input type="number" id="benefit_value" name="benefit_value" step="1000" min="0"
                               class="input input-bordered input-sm w-full"
                               value="{{ old('benefit_value', $snap?->benefit_value) }}"
                               x-model.number="benefitVal"
                               placeholder="0">
                        <label class="label py-0.5">
                            <span class="label-text-alt text-xs text-base-content/40">ROI = (lợi ích − đầu tư) / đầu tư × 100%</span>
                        </label>
                        @error('benefit_value')
                        <label class="label py-0.5"><span class="label-text-alt text-error text-xs">{{ $message }}</span></label>
                        @enderror
                    </div>

                    {{-- Ghi chú --}}
                    <div class="form-control sm:col-span-2">
                        <label class="label py-1" for="notes">
                            <span class="label-text text-xs font-medium">Ghi chú / Mô tả</span>
                        </label>
                        <textarea id="notes" name="notes" rows="3"
                                  class="textarea textarea-bordered text-sm w-full"
                                  placeholder="Mô tả cách bạn đã ứng dụng AI và kết quả cụ thể...">{{ old('notes', $snap?->notes) }}</textarea>
                        @error('notes')
                        <label class="label py-0.5"><span class="label-text-alt text-error text-xs">{{ $message }}</span></label>
                        @enderror
                    </div>

                </div>

                {{-- Preview ROI --}}
                <div class="mt-4 p-4 bg-base-200/60 rounded-xl" x-show="previewVisible" x-cloak>
                    <p class="text-xs font-semibold text-base-content/50 uppercase tracking-wide mb-2">Xem trước kết quả tính toán</p>
                    <div class="flex flex-wrap gap-6 text-sm">
                        <div>
                            <span class="text-base-content/40 text-xs block">% Cải thiện</span>
                            <span class="font-bold text-success" x-text="improvementLabel">—</span>
                        </div>
                        <div x-show="roiVisible">
                            <span class="text-base-content/40 text-xs block">ROI</span>
                            <span class="font-bold text-accent" x-text="roiLabel">—</span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" class="btn btn-primary btn-sm">
                        {{ $snap ? 'Cập nhật' : 'Lưu bản ghi' }}
                    </button>
                    <a href="{{ route('backend.ai-impact.index') }}" class="btn btn-ghost btn-sm">Hủy</a>
                </div>
            </form>

        </div>
    </div>
</div>

@endsection

@push('scripts')
    @vite(['Modules/Assessment/resources/assets/js/assessment.js'], 'build/backend')
@endpush
