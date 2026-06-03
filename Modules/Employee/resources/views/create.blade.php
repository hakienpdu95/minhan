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
        basic:    ['full_name', 'employee_code', 'email', 'branch_id', 'department_id', 'job_title_id', 'manager_id', 'status', 'employment_type'],
        personal: ['phone', 'gender', 'date_of_birth', 'national_id', 'tax_code', 'locale'],
        dates:    ['hired_at', 'left_at'],
    },
    errs: {{ Js::from($errors->keys()) }},
    errCount(t) { return this.tabFields[t].filter(f => this.errs.includes(f)).length; },
    init() {
        const order = ['basic','personal','dates'];
        for (const t of order) { if (this.errCount(t) > 0) { this.tab = t; break; } }
    }
}">

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

<form method="POST" action="{{ route('backend.employees.store') }}" novalidate>
    @csrf

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_260px] gap-6 items-start">

        {{-- ── Card chính với tabs ──────────────────────────────────────── --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">

            <div class="border-b border-base-200 px-6">
                <nav class="flex -mb-px" role="tablist">
                    @foreach([
                        ['key' => 'basic',    'label' => 'Vị trí & Trạng thái'],
                        ['key' => 'personal', 'label' => 'Thông tin cá nhân'],
                        ['key' => 'dates',    'label' => 'Ngày công tác'],
                    ] as $t)
                    <button type="button" role="tab"
                            @click="tab = '{{ $t['key'] }}'"
                            :aria-selected="tab === '{{ $t['key'] }}'"
                            class="flex items-center gap-1.5 px-1 py-4 mr-6 text-sm font-medium border-b-2 transition-colors"
                            :class="tab === '{{ $t['key'] }}'
                                ? 'border-primary text-primary'
                                : 'border-transparent text-base-content/50 hover:text-base-content hover:border-base-content/20'">
                        {{ $t['label'] }}
                        <template x-if="errCount('{{ $t['key'] }}') > 0">
                            <span class="badge badge-error badge-xs" x-text="errCount('{{ $t['key'] }}')"></span>
                        </template>
                    </button>
                    @endforeach
                </nav>
            </div>

            <div class="card-body gap-5">

                {{-- Tab: Vị trí & Trạng thái --}}
                <div x-show="tab === 'basic'" class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div class="form-control sm:col-span-2">
                        <label class="label"><span class="label-text font-medium">Họ và tên <span class="text-error">*</span></span></label>
                        <input type="text" name="full_name" value="{{ old('full_name') }}"
                               class="input input-bordered @error('full_name') input-error @enderror"
                               placeholder="Nguyễn Văn A"/>
                        @error('full_name')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Mã nhân viên <span class="text-error">*</span></span></label>
                        <input type="text" name="employee_code" value="{{ old('employee_code') }}"
                               class="input input-bordered font-mono @error('employee_code') input-error @enderror"
                               placeholder="NV001"/>
                        @error('employee_code')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Email công việc <span class="text-error">*</span></span></label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="input input-bordered @error('email') input-error @enderror"
                               placeholder="ten.ho@congty.com"/>
                        @error('email')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Chi nhánh <span class="text-error">*</span></span></label>
                        <select name="branch_id" class="select select-bordered @error('branch_id') select-error @enderror">
                            <option value="">Chọn chi nhánh...</option>
                            @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->code }})
                            </option>
                            @endforeach
                        </select>
                        @error('branch_id')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Phòng ban <span class="text-error">*</span></span></label>
                        <select name="department_id" class="select select-bordered @error('department_id') select-error @enderror">
                            <option value="">Chọn phòng ban...</option>
                            @foreach($departments as $d)
                            <option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>
                                {{ $d->name }} ({{ $d->code }})
                            </option>
                            @endforeach
                        </select>
                        @error('department_id')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Chức danh</span></label>
                        <select name="job_title_id" class="select select-bordered @error('job_title_id') select-error @enderror">
                            <option value="">Chưa xác định</option>
                            @foreach($jobTitles as $jt)
                            <option value="{{ $jt->id }}" {{ old('job_title_id') == $jt->id ? 'selected' : '' }}>
                                {{ $jt->name }} (Lv.{{ $jt->level }})
                            </option>
                            @endforeach
                        </select>
                        @error('job_title_id')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Quản lý trực tiếp</span></label>
                        <select name="manager_id" class="select select-bordered @error('manager_id') select-error @enderror">
                            <option value="">Không có</option>
                            @foreach($managers as $m)
                            <option value="{{ $m->id }}" {{ old('manager_id') == $m->id ? 'selected' : '' }}>
                                {{ $m->full_name }} ({{ $m->employee_code }})
                            </option>
                            @endforeach
                        </select>
                        @error('manager_id')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Trạng thái <span class="text-error">*</span></span></label>
                        <select name="status" class="select select-bordered @error('status') select-error @enderror">
                            @foreach($statuses as $s)
                            <option value="{{ $s['value'] }}" {{ old('status', 'active') === $s['value'] ? 'selected' : '' }}>
                                {{ $s['text'] }}
                            </option>
                            @endforeach
                        </select>
                        @error('status')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Loại hợp đồng <span class="text-error">*</span></span></label>
                        <select name="employment_type" class="select select-bordered @error('employment_type') select-error @enderror">
                            @foreach($employmentTypes as $t)
                            <option value="{{ $t['value'] }}" {{ old('employment_type', 'full_time') === $t['value'] ? 'selected' : '' }}>
                                {{ $t['text'] }}
                            </option>
                            @endforeach
                        </select>
                        @error('employment_type')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                </div>

                {{-- Tab: Thông tin cá nhân --}}
                <div x-show="tab === 'personal'" class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Số điện thoại</span></label>
                        <input type="tel" name="phone" value="{{ old('phone') }}"
                               class="input input-bordered @error('phone') input-error @enderror"
                               placeholder="0901234567"/>
                        @error('phone')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Giới tính</span></label>
                        <select name="gender" class="select select-bordered @error('gender') select-error @enderror">
                            <option value="">Không xác định</option>
                            <option value="male"   {{ old('gender') === 'male'   ? 'selected' : '' }}>Nam</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Nữ</option>
                            <option value="other"  {{ old('gender') === 'other'  ? 'selected' : '' }}>Khác</option>
                        </select>
                        @error('gender')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Ngày sinh</span></label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                               class="input input-bordered @error('date_of_birth') input-error @enderror"/>
                        @error('date_of_birth')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Số CCCD/CMND</span>
                            <span class="label-text-alt text-xs opacity-50">eKYC</span>
                        </label>
                        <input type="text" name="national_id" value="{{ old('national_id') }}"
                               class="input input-bordered font-mono @error('national_id') input-error @enderror"
                               placeholder="012345678901"/>
                        @error('national_id')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Mã số thuế cá nhân</span>
                            <span class="label-text-alt text-xs opacity-50">Quyết toán TNCN</span>
                        </label>
                        <input type="text" name="tax_code" value="{{ old('tax_code') }}"
                               class="input input-bordered font-mono @error('tax_code') input-error @enderror"
                               placeholder="0123456789"/>
                        @error('tax_code')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Ngôn ngữ giao diện</span>
                            <span class="label-text-alt text-xs opacity-50">NULL = kế thừa org</span>
                        </label>
                        <select name="locale" class="select select-bordered @error('locale') select-error @enderror">
                            <option value="">Mặc định theo org</option>
                            <option value="vi" {{ old('locale') === 'vi' ? 'selected' : '' }}>Tiếng Việt</option>
                            <option value="en" {{ old('locale') === 'en' ? 'selected' : '' }}>English</option>
                        </select>
                        @error('locale')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                </div>

                {{-- Tab: Ngày công tác --}}
                <div x-show="tab === 'dates'" class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Ngày vào làm</span></label>
                        <input type="date" name="hired_at" value="{{ old('hired_at') }}"
                               class="input input-bordered @error('hired_at') input-error @enderror"/>
                        @error('hired_at')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Ngày nghỉ việc</span>
                            <span class="label-text-alt text-xs opacity-50">Điền khi đã nghỉ</span>
                        </label>
                        <input type="date" name="left_at" value="{{ old('left_at') }}"
                               class="input input-bordered @error('left_at') input-error @enderror"/>
                        @error('left_at')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                </div>

            </div>
        </div>

        {{-- ── Sidebar: Actions ─────────────────────────────────────────── --}}
        <div class="space-y-4">
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body gap-3">
                    <h3 class="font-semibold text-sm">Thao tác</h3>
                    <button type="submit" class="btn btn-primary w-full gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Lưu nhân viên
                    </button>
                    <a href="{{ route('backend.employees.index') }}" class="btn btn-ghost w-full">Hủy</a>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body gap-2">
                    <h3 class="font-semibold text-sm">Lưu ý</h3>
                    <ul class="text-xs text-base-content/60 space-y-1.5 list-disc list-inside">
                        <li>Mã nhân viên phải duy nhất trong org (VD: NV001)</li>
                        <li>Email công việc phải duy nhất trong org</li>
                        <li>Chi nhánh và phòng ban bắt buộc điền</li>
                        <li>Quản lý trực tiếp không thể là chính nhân viên đó</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</form>
</div>
@endsection
