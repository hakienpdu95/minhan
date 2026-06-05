@extends('layouts.backend')

@section('title', 'Tạo đơn ứng tuyển')

@section('breadcrumb')
<div class="breadcrumbs text-sm px-6 pt-4 pb-0">
    <ul>
        <li><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
        <li><a href="{{ route('backend.recruitment.candidates.index') }}">Ứng viên</a></li>
        <li><a href="{{ route('backend.recruitment.candidates.show', $candidate) }}">{{ $candidate->full_name }}</a></li>
        <li class="font-semibold">Tạo đơn ứng tuyển</li>
    </ul>
</div>
@endsection

@section('content')
<div class="p-6 max-w-2xl mx-auto">

    <div class="mb-6">
        <h1 class="text-xl font-bold">Tạo đơn ứng tuyển</h1>
        <p class="text-sm opacity-60 mt-0.5">Ứng viên: <span class="font-medium">{{ $candidate->full_name }}</span> · {{ $candidate->email }}</p>
    </div>

    <form method="POST" action="{{ route('backend.recruitment.applications.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="candidate_id" value="{{ $candidate->id }}">

        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5 space-y-4">

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Stage bắt đầu <span class="text-error">*</span></span></label>
                    <select name="stage_id" class="select select-bordered" required>
                        @foreach($stages as $stage)
                        @if(!in_array($stage->stage_type->value, ['hired', 'rejected']))
                        <option value="{{ $stage->id }}" {{ old('stage_id') == $stage->id ? 'selected' : '' }}>
                            {{ $stage->name }}
                        </option>
                        @endif
                        @endforeach
                    </select>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">ID Tin tuyển dụng (UUID)</span></label>
                    <input type="text" name="jp_job_post_id" value="{{ old('jp_job_post_id', request('jp_job_post_id')) }}"
                           class="input input-bordered font-mono text-sm"
                           placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                    <div class="label"><span class="label-text-alt opacity-60">UUID của tin tuyển dụng từ Job Posting Center (tùy chọn nhưng khuyến nghị)</span></div>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Nguồn ứng tuyển <span class="text-error">*</span></span></label>
                    <select name="apply_source" class="select select-bordered" required>
                        @foreach($sources as $src)
                        <option value="{{ $src['value'] }}" {{ old('apply_source', 'direct') === $src['value'] ? 'selected' : '' }}>
                            {{ $src['text'] }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Lương kỳ vọng (VNĐ)</span></label>
                        <input type="number" name="expected_salary" value="{{ old('expected_salary') }}"
                               class="input input-bordered" min="0" step="500000">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Thời gian thông báo (ngày)</span></label>
                        <input type="number" name="notice_period_days" value="{{ old('notice_period_days') }}"
                               class="input input-bordered" min="0">
                    </div>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Thư xin việc</span></label>
                    <textarea name="cover_letter" class="textarea textarea-bordered" rows="4"
                              placeholder="Nội dung thư xin việc...">{{ old('cover_letter') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('backend.recruitment.candidates.show', $candidate) }}" class="btn btn-ghost">Hủy</a>
            <button type="submit" class="btn btn-primary">Tạo đơn ứng tuyển</button>
        </div>
    </form>

</div>
@endsection
