@extends('layouts.backend')

@section('title', 'Thêm ứng viên — Recruitment')

@section('breadcrumb')
<div class="breadcrumbs text-sm px-6 pt-4 pb-0">
    <ul>
        <li><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
        <li><a href="{{ route('backend.recruitment.candidates.index') }}">Ứng viên</a></li>
        <li class="font-semibold">Thêm ứng viên</li>
    </ul>
</div>
@endsection

@section('content')
<div class="p-6 max-w-3xl mx-auto">

    <div class="mb-6">
        <h1 class="text-xl font-bold">Thêm ứng viên mới</h1>
        <p class="text-sm opacity-60 mt-0.5">Thêm thủ công hồ sơ ứng viên vào pool</p>
    </div>

    <form method="POST" action="{{ route('backend.recruitment.candidates.store') }}" class="space-y-5">
        @csrf

        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5 space-y-4">
                <h2 class="font-semibold text-sm text-base-content/70 uppercase tracking-wide">Thông tin cơ bản</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Họ và tên <span class="text-error">*</span></span></label>
                        <input type="text" name="full_name" value="{{ old('full_name') }}"
                               class="input input-bordered @error('full_name') input-error @enderror"
                               placeholder="Nguyễn Văn A" required>
                        @error('full_name')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Email <span class="text-error">*</span></span></label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="input input-bordered @error('email') input-error @enderror"
                               placeholder="email@example.com" required>
                        @error('email')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Số điện thoại</span></label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="input input-bordered @error('phone') input-error @enderror"
                               placeholder="0912345678">
                        @error('phone')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Giới tính</span></label>
                        <select name="gender" class="select select-bordered">
                            <option value="">Không xác định</option>
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Nam</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Nữ</option>
                            <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Khác</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Ngày sinh</span></label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                               class="input input-bordered">
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Số năm kinh nghiệm</span></label>
                        <input type="number" name="years_experience" value="{{ old('years_experience') }}"
                               class="input input-bordered" min="0" max="50">
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5 space-y-4">
                <h2 class="font-semibold text-sm text-base-content/70 uppercase tracking-wide">Công việc hiện tại</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Chức danh hiện tại</span></label>
                        <input type="text" name="current_title" value="{{ old('current_title') }}"
                               class="input input-bordered" placeholder="Frontend Developer">
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Công ty hiện tại</span></label>
                        <input type="text" name="current_company" value="{{ old('current_company') }}"
                               class="input input-bordered" placeholder="FPT Software">
                    </div>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Kỹ năng</span></label>
                    <textarea name="skills" class="textarea textarea-bordered"
                              placeholder="PHP, Laravel, Vue.js, MySQL (phân cách bởi dấu phẩy)" rows="2">{{ old('skills') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5 space-y-4">
                <h2 class="font-semibold text-sm text-base-content/70 uppercase tracking-wide">Nguồn & Liên kết</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Nguồn ứng viên <span class="text-error">*</span></span></label>
                        <select name="source" class="select select-bordered" required>
                            @foreach($sources as $src)
                            <option value="{{ $src['value'] }}" {{ old('source', 'direct') === $src['value'] ? 'selected' : '' }}>
                                {{ $src['text'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Người giới thiệu</span></label>
                        <select name="referred_by" class="select select-bordered">
                            <option value="">— Không có —</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('referred_by') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">LinkedIn</span></label>
                        <input type="url" name="linkedin_url" value="{{ old('linkedin_url') }}"
                               class="input input-bordered" placeholder="https://linkedin.com/in/...">
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Portfolio / Website</span></label>
                        <input type="url" name="portfolio_url" value="{{ old('portfolio_url') }}"
                               class="input input-bordered" placeholder="https://...">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('backend.recruitment.candidates.index') }}" class="btn btn-ghost">Hủy</a>
            <button type="submit" class="btn btn-primary">Thêm ứng viên</button>
        </div>
    </form>

</div>
@endsection
