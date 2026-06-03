@extends('layouts.backend')
@section('title', 'Thêm nhân viên mới')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.employees.index') }}">Nhân viên</a>
    <span class="sep">›</span>
    <span class="current">Thêm mới</span>
</nav>
@endsection

@section('content')
<div x-data="{
    tab: 'basic',
    tabFields: {
        basic:    ['full_name', 'employee_code', 'email', 'branch_id', 'department_id', 'job_title_id', 'manager_id', 'employment_type'],
        personal: ['phone', 'gender', 'date_of_birth', 'national_id', 'tax_code', 'locale'],
        dates:    ['hired_at', 'left_at'],
    },
    errs: {{ Js::from($errors->keys()) }},
    errCount(t) { return this.tabFields[t].filter(f => this.errs.includes(f)).length; },
    init() {
        const order = ['basic', 'personal', 'dates'];
        for (const t of order) { if (this.errCount(t) > 0) { this.tab = t; break; } }
    }
}">

{{-- Page header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Thêm nhân viên mới</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Điền thông tin hồ sơ nhân sự</p>
    </div>
    <a href="{{ route('backend.employees.index') }}" class="btn btn-ghost btn-sm gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Quay lại
    </a>
</div>

@if($errors->any())
<div class="alert alert-error py-3 px-4 mb-5 flex items-start gap-3 text-sm">
    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    <div>
        <p class="font-semibold">Có {{ $errors->count() }} lỗi cần kiểm tra:</p>
        <ul class="mt-1.5 list-disc list-inside space-y-0.5 text-xs opacity-90">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
</div>
@endif

<form method="POST" action="{{ route('backend.employees.store') }}" novalidate data-employee-form>
    @csrf

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_268px] gap-6 items-start">

        {{-- ── Card chính với tabs ──────────────────────────────────────── --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">

            {{-- Tab navigation --}}
            <div class="border-b border-base-200 px-6">
                <nav class="flex -mb-px" role="tablist" aria-label="Form sections">

                    <button type="button" role="tab" :aria-selected="tab === 'basic'"
                            @click="tab = 'basic'"
                            class="flex items-center gap-1.5 px-1 py-4 mr-6 text-sm font-medium border-b-2 transition-colors"
                            :class="tab === 'basic'
                                ? 'border-primary text-primary'
                                : 'border-transparent text-base-content/50 hover:text-base-content hover:border-base-content/20'">
                        Vị trí & Hợp đồng
                        <span x-show="errCount('basic') > 0" x-text="errCount('basic')"
                              class="badge badge-error badge-xs"></span>
                    </button>

                    <button type="button" role="tab" :aria-selected="tab === 'personal'"
                            @click="tab = 'personal'"
                            class="flex items-center gap-1.5 px-1 py-4 mr-6 text-sm font-medium border-b-2 transition-colors"
                            :class="tab === 'personal'
                                ? 'border-primary text-primary'
                                : 'border-transparent text-base-content/50 hover:text-base-content hover:border-base-content/20'">
                        Thông tin cá nhân
                        <span x-show="errCount('personal') > 0" x-text="errCount('personal')"
                              class="badge badge-error badge-xs"></span>
                    </button>

                    <button type="button" role="tab" :aria-selected="tab === 'dates'"
                            @click="tab = 'dates'"
                            class="flex items-center gap-1.5 px-1 py-4 text-sm font-medium border-b-2 transition-colors"
                            :class="tab === 'dates'
                                ? 'border-primary text-primary'
                                : 'border-transparent text-base-content/50 hover:text-base-content hover:border-base-content/20'">
                        Ngày công tác
                        <span x-show="errCount('dates') > 0" x-text="errCount('dates')"
                              class="badge badge-error badge-xs"></span>
                    </button>

                </nav>
            </div>

            {{-- Tab panels --}}
            <div class="p-6">

                {{-- Panel: Vị trí & Hợp đồng --}}
                <div x-show="tab === 'basic'" data-tab-label="Vị trí & Hợp đồng" class="space-y-4">

                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Họ và tên <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="full_name" value="{{ old('full_name') }}"
                               data-req="Vui lòng nhập họ và tên"
                               class="input input-bordered input-sm w-full @error('full_name') input-error @enderror"
                               placeholder="VD: Nguyễn Văn A" autofocus>
                        @error('full_name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Mã nhân viên <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="employee_code" value="{{ old('employee_code') }}"
                                   data-req="Vui lòng nhập mã nhân viên"
                                   class="input input-bordered input-sm w-full font-mono @error('employee_code') input-error @enderror"
                                   placeholder="VD: NV001">
                            @error('employee_code')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Email công việc <span class="text-error">*</span></span>
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   data-req="Vui lòng nhập email công việc"
                                   data-val-email="Email không đúng định dạng"
                                   class="input input-bordered input-sm w-full @error('email') input-error @enderror"
                                   placeholder="ten.ho@congty.com">
                            @error('email')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Chi nhánh <span class="text-error">*</span></span>
                            </label>
                            <select id="ts-branch_id" name="branch_id"
                                    data-req="Vui lòng chọn chi nhánh"
                                    class="select select-bordered select-sm w-full ts-init @error('branch_id') select-error @enderror"
                                    data-ts-placeholder="— Chọn chi nhánh —">
                                <option value="">— Chọn chi nhánh —</option>
                                @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }} ({{ $b->code }})
                                </option>
                                @endforeach
                            </select>
                            @error('branch_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Phòng ban <span class="text-error">*</span></span>
                            </label>
                            <select id="ts-department_id" name="department_id"
                                    data-req="Vui lòng chọn phòng ban"
                                    class="select select-bordered select-sm w-full ts-init @error('department_id') select-error @enderror"
                                    data-ts-placeholder="— Chọn phòng ban —">
                                <option value="">— Chọn phòng ban —</option>
                                @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>
                                    {{ $d->name }} ({{ $d->code }})
                                </option>
                                @endforeach
                            </select>
                            @error('department_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Chức danh</span>
                            </label>
                            <select id="ts-job_title_id" name="job_title_id"
                                    class="select select-bordered select-sm w-full ts-init @error('job_title_id') select-error @enderror"
                                    data-ts-placeholder="— Chưa xác định —">
                                <option value="">— Chưa xác định —</option>
                                @foreach($jobTitles as $jt)
                                <option value="{{ $jt->id }}" {{ old('job_title_id') == $jt->id ? 'selected' : '' }}>
                                    {{ $jt->name }} (Lv.{{ $jt->level }})
                                </option>
                                @endforeach
                            </select>
                            @error('job_title_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Quản lý trực tiếp</span>
                            </label>
                            <select id="ts-manager_id" name="manager_id"
                                    class="select select-bordered select-sm w-full ts-init @error('manager_id') select-error @enderror"
                                    data-ts-placeholder="— Không có —">
                                <option value="">— Không có —</option>
                                @foreach($managers as $m)
                                <option value="{{ $m->id }}" {{ old('manager_id') == $m->id ? 'selected' : '' }}>
                                    {{ $m->full_name }} ({{ $m->employee_code }})
                                </option>
                                @endforeach
                            </select>
                            @error('manager_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                    </div>

                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Loại hợp đồng <span class="text-error">*</span></span>
                        </label>
                        <select id="ts-employment_type" name="employment_type"
                                class="select select-bordered select-sm w-full ts-init @error('employment_type') select-error @enderror"
                                data-ts-placeholder="— Chọn loại hợp đồng —">
                            <option value="">— Chọn loại hợp đồng —</option>
                            @foreach($employmentTypes as $t)
                            <option value="{{ $t['value'] }}" {{ old('employment_type', 'full_time') === $t['value'] ? 'selected' : '' }}>
                                {{ $t['text'] }}
                            </option>
                            @endforeach
                        </select>
                        @error('employment_type')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Tab footer: next --}}
                    <div class="flex justify-end pt-2">
                        <button type="button" @click="tab = 'personal'"
                                class="btn btn-ghost btn-sm gap-1.5">
                            Tiếp theo: Thông tin cá nhân
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>

                </div>

                {{-- Panel: Thông tin cá nhân --}}
                <div x-show="tab === 'personal'" data-tab-label="Thông tin cá nhân" class="space-y-4">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Số điện thoại</span>
                            </label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                   class="input input-bordered input-sm w-full @error('phone') input-error @enderror"
                                   placeholder="VD: 0901234567">
                            @error('phone')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Giới tính</span>
                            </label>
                            <select id="ts-gender" name="gender"
                                    class="select select-bordered select-sm w-full ts-init @error('gender') select-error @enderror"
                                    data-ts-placeholder="— Không xác định —">
                                <option value="">— Không xác định —</option>
                                <option value="male"   {{ old('gender') === 'male'   ? 'selected' : '' }}>Nam</option>
                                <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Nữ</option>
                                <option value="other"  {{ old('gender') === 'other'  ? 'selected' : '' }}>Khác</option>
                            </select>
                            @error('gender')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Ngày sinh</span>
                            </label>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                   class="input input-bordered input-sm w-full @error('date_of_birth') input-error @enderror">
                            @error('date_of_birth')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Số CCCD/CMND</span>
                                <span class="label-text-alt text-xs text-base-content/40">eKYC</span>
                            </label>
                            <input type="text" name="national_id" value="{{ old('national_id') }}"
                                   class="input input-bordered input-sm w-full font-mono @error('national_id') input-error @enderror"
                                   placeholder="VD: 012345678901">
                            @error('national_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Mã số thuế cá nhân</span>
                                <span class="label-text-alt text-xs text-base-content/40">Quyết toán TNCN</span>
                            </label>
                            <input type="text" name="tax_code" value="{{ old('tax_code') }}"
                                   class="input input-bordered input-sm w-full font-mono @error('tax_code') input-error @enderror"
                                   placeholder="VD: 0123456789">
                            @error('tax_code')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Ngôn ngữ giao diện</span>
                                <span class="label-text-alt text-xs text-base-content/40">Mặc định theo org</span>
                            </label>
                            <select id="ts-locale" name="locale"
                                    class="select select-bordered select-sm w-full ts-init @error('locale') select-error @enderror"
                                    data-ts-placeholder="— Mặc định theo org —">
                                <option value="">— Mặc định theo org —</option>
                                <option value="vi" {{ old('locale') === 'vi' ? 'selected' : '' }}>Tiếng Việt</option>
                                <option value="en" {{ old('locale') === 'en' ? 'selected' : '' }}>English</option>
                            </select>
                            @error('locale')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                    </div>

                    {{-- Tab footer: prev / next --}}
                    <div class="flex items-center justify-between pt-2">
                        <button type="button" @click="tab = 'basic'"
                                class="btn btn-ghost btn-sm gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Vị trí & Hợp đồng
                        </button>
                        <button type="button" @click="tab = 'dates'"
                                class="btn btn-ghost btn-sm gap-1.5">
                            Tiếp theo: Ngày công tác
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>

                </div>

                {{-- Panel: Ngày công tác --}}
                <div x-show="tab === 'dates'" data-tab-label="Ngày công tác" class="space-y-4">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Ngày vào làm</span>
                            </label>
                            <input type="date" name="hired_at" value="{{ old('hired_at') }}"
                                   class="input input-bordered input-sm w-full @error('hired_at') input-error @enderror">
                            @error('hired_at')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Ngày nghỉ việc</span>
                                <span class="label-text-alt text-xs text-base-content/40">Điền khi đã nghỉ</span>
                            </label>
                            <input type="date" name="left_at" value="{{ old('left_at') }}"
                                   class="input input-bordered input-sm w-full @error('left_at') input-error @enderror">
                            @error('left_at')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                    </div>

                    {{-- Tab footer: prev --}}
                    <div class="flex items-center justify-between pt-2">
                        <button type="button" @click="tab = 'personal'"
                                class="btn btn-ghost btn-sm gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Thông tin cá nhân
                        </button>
                        <span class="text-xs text-base-content/40">Điền xong? Nhấn <strong>Tạo nhân viên</strong> ở bên phải</span>
                    </div>

                </div>

            </div>{{-- /tab panels --}}
        </div>{{-- /card chính --}}

        {{-- ── Sidebar ──────────────────────────────────────────────────── --}}
        <div class="xl:sticky xl:top-4 space-y-4">

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-4">

                    <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide mb-3">Xuất bản</p>

                    <div class="form-control mb-4">
                        <label class="label py-0 pb-1">
                            <span class="label-text text-xs font-medium">Trạng thái <span class="text-error">*</span></span>
                        </label>
                        <select id="ts-status" name="status"
                                class="select select-bordered select-sm w-full ts-init @error('status') select-error @enderror"
                                data-ts-placeholder="— Chọn trạng thái —">
                            @foreach($statuses as $s)
                            <option value="{{ $s['value'] }}" {{ old('status', 'active') === $s['value'] ? 'selected' : '' }}>
                                {{ $s['text'] }}
                            </option>
                            @endforeach
                        </select>
                        @error('status')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('backend.employees.index') }}" class="btn btn-ghost btn-sm flex-1">Hủy</a>
                        <button type="submit" class="btn btn-primary btn-sm flex-1 gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tạo nhân viên
                        </button>
                    </div>

                    <p class="text-center text-xs text-base-content/30 mt-2.5">
                        <span class="text-error">*</span> là trường bắt buộc
                    </p>

                </div>
            </div>

        </div>{{-- /sidebar --}}

    </div>{{-- /grid --}}
</form>
</div>
@endsection

@push('styles')
    @vite(['Modules/Employee/resources/assets/sass/employee.scss'], 'build/backend')
@endpush

@push('scripts')
    @vite([
        'resources/js/modules/toastify.js',
        'resources/js/modules/tom-select.js',
        'Modules/Employee/resources/assets/js/employee.js',
    ], 'build/backend')
@endpush
