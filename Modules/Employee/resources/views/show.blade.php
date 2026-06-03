@extends('layouts.backend')
@section('title', $employee->full_name)

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.employees.index') }}">Nhân viên</a>
    <span class="sep">›</span>
    <span class="current">{{ $employee->full_name }}</span>
</nav>
@endsection

@section('content')

{{-- ── Page header ──────────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div class="flex items-center gap-4">
        @if($employee->avatar_url)
        <img src="{{ $employee->avatar_url }}" alt="{{ $employee->full_name }}"
             class="w-14 h-14 rounded-full object-cover ring-2 ring-base-300"/>
        @else
        <div class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xl">
            {{ mb_substr($employee->full_name, 0, 1) }}
        </div>
        @endif
        <div>
            <h1 class="text-2xl font-bold text-base-content">{{ $employee->full_name }}</h1>
            <div class="flex items-center gap-2 mt-1">
                <span class="font-mono text-xs text-base-content/50">{{ $employee->employee_code }}</span>
                <span class="badge badge-sm {{ $employee->status->badgeClass() }}">{{ $employee->status->label() }}</span>
                <span class="badge badge-sm {{ $employee->employment_type->badgeClass() }}">{{ $employee->employment_type->label() }}</span>
            </div>
        </div>
    </div>
    <div class="flex items-center gap-2">
        @can('update', $employee)
        <a href="{{ route('backend.employees.edit', $employee) }}" class="btn btn-primary btn-sm gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Chỉnh sửa
        </a>
        @endcan
        <a href="{{ route('backend.employees.index') }}" class="btn btn-ghost btn-sm">Quay lại</a>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_300px] gap-6">

    {{-- ── Main column ──────────────────────────────────────────────────── --}}
    <div class="space-y-5">

        {{-- Vị trí & Công việc --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body">
                <h2 class="card-title text-base">Vị trí công tác</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-2">
                    <div>
                        <p class="text-xs text-base-content/50 mb-0.5">Chi nhánh</p>
                        <p class="font-medium">{{ $employee->branch?->name ?? '—' }}</p>
                        @if($employee->branch?->code)
                        <p class="text-xs font-mono text-base-content/40">{{ $employee->branch->code }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-base-content/50 mb-0.5">Phòng ban</p>
                        <p class="font-medium">{{ $employee->department?->name ?? '—' }}</p>
                        @if($employee->department?->code)
                        <p class="text-xs font-mono text-base-content/40">{{ $employee->department->code }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-base-content/50 mb-0.5">Chức danh</p>
                        <p class="font-medium">{{ $employee->jobTitle?->name ?? '—' }}</p>
                        @if($employee->jobTitle?->level)
                        <p class="text-xs text-base-content/40">Cấp bậc: {{ $employee->jobTitle->level }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-base-content/50 mb-0.5">Quản lý trực tiếp</p>
                        @if($employee->manager)
                        <a href="{{ route('backend.employees.show', $employee->manager) }}"
                           class="font-medium text-primary hover:underline">{{ $employee->manager->full_name }}</a>
                        <p class="text-xs font-mono text-base-content/40">{{ $employee->manager->employee_code }}</p>
                        @else
                        <p class="font-medium text-base-content/40">—</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-base-content/50 mb-0.5">Ngày vào làm</p>
                        <p class="font-medium">{{ $employee->hired_at?->format('d/m/Y') ?? '—' }}</p>
                    </div>
                    @if($employee->left_at)
                    <div>
                        <p class="text-xs text-base-content/50 mb-0.5">Ngày nghỉ việc</p>
                        <p class="font-medium text-error">{{ $employee->left_at->format('d/m/Y') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Thông tin cá nhân --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body">
                <h2 class="card-title text-base">Thông tin cá nhân</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-2">
                    <div>
                        <p class="text-xs text-base-content/50 mb-0.5">Email công việc</p>
                        <p class="font-medium">{{ $employee->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-base-content/50 mb-0.5">Điện thoại</p>
                        <p class="font-medium">{{ $employee->phone ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-base-content/50 mb-0.5">Giới tính</p>
                        <p class="font-medium">
                            @switch($employee->gender)
                                @case('male') Nam @break
                                @case('female') Nữ @break
                                @case('other') Khác @break
                                @default —
                            @endswitch
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-base-content/50 mb-0.5">Ngày sinh</p>
                        <p class="font-medium">{{ $employee->date_of_birth?->format('d/m/Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-base-content/50 mb-0.5">Số CCCD/CMND</p>
                        <p class="font-medium font-mono">{{ $employee->national_id ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-base-content/50 mb-0.5">Mã số thuế cá nhân</p>
                        <p class="font-medium font-mono">{{ $employee->tax_code ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lịch sử nhân sự --}}
        @if($employee->history->count() > 0)
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body">
                <h2 class="card-title text-base">Lịch sử nhân sự</h2>
                <div class="overflow-x-auto mt-2">
                    <table class="table table-sm">
                        <thead>
                            <tr class="text-xs text-base-content/50">
                                <th>Ngày</th>
                                <th>Loại thay đổi</th>
                                <th>Cũ → Mới</th>
                                <th>Người thực hiện</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employee->history->take(20) as $h)
                            <tr>
                                <td class="text-xs">{{ $h->effective_date?->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge badge-ghost badge-sm">{{ $h->change_type }}</span>
                                </td>
                                <td class="text-xs text-base-content/70">
                                    @if($h->old_status !== $h->new_status && $h->new_status)
                                    Status: {{ $h->old_status ?? '—' }} → {{ $h->new_status }}
                                    @elseif($h->new_department_id)
                                    Dept thay đổi
                                    @elseif($h->new_branch_id)
                                    Branch thay đổi
                                    @else
                                    —
                                    @endif
                                </td>
                                <td class="text-xs">{{ $h->changedBy?->name ?? 'Hệ thống' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- ── Right sidebar ─────────────────────────────────────────────────── --}}
    <div class="space-y-4">

        {{-- Quick info --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body gap-3">
                <h3 class="font-semibold text-sm">Thông tin nhanh</h3>
                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-base-content/50">Trạng thái</span>
                        <span class="badge badge-sm {{ $employee->status->badgeClass() }}">{{ $employee->status->label() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-base-content/50">Hợp đồng</span>
                        <span class="badge badge-sm {{ $employee->employment_type->badgeClass() }}">{{ $employee->employment_type->label() }}</span>
                    </div>
                    @if($employee->snap_job_level)
                    <div class="flex justify-between items-center">
                        <span class="text-base-content/50">Cấp bậc</span>
                        <span class="font-medium">Level {{ $employee->snap_job_level }}</span>
                    </div>
                    @endif
                    @if($employee->locale)
                    <div class="flex justify-between items-center">
                        <span class="text-base-content/50">Ngôn ngữ</span>
                        <span class="font-mono text-xs">{{ $employee->locale }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kiêm nhiệm --}}
        @if($employee->employeeDepartments->count() > 1)
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body gap-2">
                <h3 class="font-semibold text-sm">Phòng ban kiêm nhiệm</h3>
                @foreach($employee->employeeDepartments->where('left_at', null) as $ed)
                <div class="flex items-center justify-between py-1">
                    <span class="text-sm">{{ $ed->department?->name }}</span>
                    @if($ed->is_primary)
                    <span class="badge badge-primary badge-xs">Chính</span>
                    @else
                    <span class="text-xs text-base-content/40">{{ $ed->role_in_dept ?? 'Kiêm nhiệm' }}</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Audit info --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body gap-2">
                <h3 class="font-semibold text-sm text-base-content/70">Audit</h3>
                <div class="text-xs text-base-content/50 space-y-1.5">
                    <p>Tạo: {{ $employee->created_at?->format('d/m/Y H:i') }}</p>
                    @if($employee->createdBy)
                    <p>Bởi: {{ $employee->createdBy->name }}</p>
                    @endif
                    <p>Cập nhật: {{ $employee->updated_at?->format('d/m/Y H:i') }}</p>
                    @if($employee->updatedBy)
                    <p>Bởi: {{ $employee->updatedBy->name }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Actions --}}
        @can('delete', $employee)
        <div class="card bg-base-100 shadow-sm border border-error/20">
            <div class="card-body gap-3">
                <h3 class="font-semibold text-sm text-error">Vùng nguy hiểm</h3>
                <form method="POST" action="{{ route('backend.employees.destroy', $employee) }}"
                      onsubmit="return confirm('Bạn có chắc muốn xóa nhân viên {{ addslashes($employee->full_name) }}?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error btn-sm w-full gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Xóa nhân viên
                    </button>
                </form>
            </div>
        </div>
        @endcan

    </div>

</div>
@endsection
