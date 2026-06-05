@extends('layouts.backend')
@section('title', 'Đăng ký nghỉ phép')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.leave.requests.index') }}">Đơn nghỉ phép</a>
    <span class="sep">›</span>
    <span class="current">Đăng ký nghỉ</span>
</nav>
@endsection

@section('content')
<div class="max-w-xl">
    <h1 class="text-2xl font-bold mb-6">Đăng ký nghỉ phép</h1>

    @if($errors->any())
    <div class="alert alert-error mb-4">
        <ul class="list-disc list-inside text-sm">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('backend.leave.requests.store') }}" class="space-y-4">
        @csrf

        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body space-y-4">

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

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Từ ngày <span class="text-error">*</span></span></label>
                        <input type="date" name="date_from" value="{{ old('date_from', now()->toDateString()) }}"
                               class="input input-bordered" required>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Đến ngày <span class="text-error">*</span></span></label>
                        <input type="date" name="date_to" value="{{ old('date_to', now()->toDateString()) }}"
                               class="input input-bordered" required>
                    </div>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text">Lý do</span></label>
                    <textarea name="reason" rows="3" class="textarea textarea-bordered"
                              placeholder="Mô tả lý do nghỉ...">{{ old('reason') }}</textarea>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text">Tệp đính kèm (URL)</span></label>
                    <input type="text" name="attachment_url" value="{{ old('attachment_url') }}"
                           class="input input-bordered" placeholder="https://...">
                </div>
            </div>
        </div>

        <div class="alert alert-info text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="shrink-0 w-5 h-5 stroke-current">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Số ngày nghỉ được tính tự động (trừ Chủ nhật). Hệ thống sẽ kiểm tra số dư còn lại của bạn.
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Gửi đơn</button>
            <a href="{{ route('backend.leave.requests.index') }}" class="btn btn-ghost">Hủy</a>
        </div>
    </form>
</div>
@endsection
