@extends('layouts.backend')
@section('title', 'Cấp chứng nhận thủ công')

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('backend.certs-admin.issued') }}" class="btn btn-ghost btn-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <h1 class="text-xl font-bold">Cấp chứng nhận thủ công</h1>
</div>

<div class="max-w-lg">
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <div class="alert alert-info alert-sm mb-4 text-sm py-2 px-3">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Sau khi cấp, hệ thống sẽ tự động cập nhật profile nhân viên và kiểm tra thăng cấp nghề nghiệp.
            </div>

            <form method="POST" action="{{ route('backend.certs-admin.issue') }}">
                @csrf

                <div class="form-control mb-4">
                    <label class="label py-1"><span class="label-text text-xs font-medium">Nhân viên <span class="text-error">*</span></span></label>
                    <select name="workforce_profile_id" class="select select-bordered select-sm @error('workforce_profile_id') select-error @enderror" required>
                        <option value="">-- Chọn nhân viên --</option>
                        @foreach($profiles as $profile)
                        <option value="{{ $profile->id }}" {{ old('workforce_profile_id') == $profile->id ? 'selected' : '' }}>
                            {{ $profile->employee?->full_name ?? 'Profile #'.$profile->id }}
                            @if($profile->employee?->employee_code)
                                ({{ $profile->employee->employee_code }})
                            @endif
                        </option>
                        @endforeach
                    </select>
                    @error('workforce_profile_id')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="form-control mb-4">
                    <label class="label py-1"><span class="label-text text-xs font-medium">Định nghĩa chứng nhận <span class="text-error">*</span></span></label>
                    <select name="cert_definition_id" class="select select-bordered select-sm @error('cert_definition_id') select-error @enderror" required>
                        <option value="">-- Chọn loại chứng nhận --</option>
                        @php $lastType = null; @endphp
                        @foreach($definitions as $def)
                        @if($def->cert_type_code !== $lastType)
                        @if($lastType !== null)</optgroup>@endif
                        <optgroup label="{{ $def->cert_type_code }}">
                        @php $lastType = $def->cert_type_code; @endphp
                        @endif
                        <option value="{{ $def->id }}" {{ old('cert_definition_id') == $def->id ? 'selected' : '' }}>
                            {{ $def->name }} — {{ $def->level_code }}
                        </option>
                        @endforeach
                        @if($lastType !== null)</optgroup>@endif
                    </select>
                    @error('cert_definition_id')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="form-control mb-5">
                    <label class="label py-1"><span class="label-text text-xs font-medium">Ghi chú (không bắt buộc)</span></label>
                    <textarea name="notes" rows="2" class="textarea textarea-bordered text-sm"
                              placeholder="Lý do cấp thủ công...">{{ old('notes') }}</textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="btn btn-primary btn-sm">Cấp chứng nhận</button>
                    <a href="{{ route('backend.certs-admin.issued') }}" class="btn btn-ghost btn-sm">Huỷ</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
