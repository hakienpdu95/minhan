@extends('layouts.backend')

@section('title', 'Tạo Offer Letter')

@section('breadcrumb')
<div class="breadcrumbs text-sm px-6 pt-4 pb-0">
    <ul>
        <li><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
        <li><a href="{{ route('backend.recruitment.applications.show', $application) }}">{{ $application->candidate?->full_name }}</a></li>
        <li class="font-semibold">Tạo Offer</li>
    </ul>
</div>
@endsection

@section('content')
<div class="p-6 max-w-2xl">

    <h1 class="text-xl font-bold mb-1">Tạo Offer Letter</h1>
    <p class="text-sm opacity-60 mb-5">Ứng viên: <strong>{{ $application->candidate?->full_name }}</strong></p>

    <form method="POST" action="{{ route('backend.recruitment.applications.offers.store', $application) }}">
        @csrf

        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5 space-y-4">

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Mức lương đề xuất</span></label>
                        <input type="number" name="salary_offered" value="{{ old('salary_offered') }}"
                               class="input input-bordered" min="0" step="100000" required>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Đơn vị tiền tệ</span></label>
                        <select name="currency" class="select select-bordered">
                            <option value="VND" {{ old('currency', 'VND') === 'VND' ? 'selected' : '' }}>VND</option>
                            <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Ngày bắt đầu</span></label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}"
                               class="input input-bordered" required min="{{ now()->toDateString() }}">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Thử việc (ngày)</span></label>
                        <input type="number" name="probation_days" value="{{ old('probation_days', 60) }}"
                               class="input input-bordered" min="0" max="365" required>
                    </div>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Hạn trả lời</span></label>
                    <input type="date" name="expire_at" value="{{ old('expire_at') }}"
                           class="input input-bordered" min="{{ now()->toDateString() }}">
                    <label class="label"><span class="label-text-alt opacity-50">Để trống nếu không giới hạn</span></label>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Ghi chú phúc lợi</span></label>
                    <textarea name="benefits_note" class="textarea textarea-bordered" rows="4"
                              placeholder="Thưởng KPI, bảo hiểm sức khỏe, 13 tháng lương...">{{ old('benefits_note') }}</textarea>
                </div>

            </div>
        </div>

        @if($errors->any())
        <div class="alert alert-error mt-4">
            <ul class="list-disc pl-4 text-sm">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="flex gap-3 mt-5">
            <button type="submit" class="btn btn-primary">Tạo offer</button>
            <a href="{{ route('backend.recruitment.applications.show', $application) }}" class="btn btn-ghost">Hủy</a>
        </div>
    </form>
</div>
@endsection
