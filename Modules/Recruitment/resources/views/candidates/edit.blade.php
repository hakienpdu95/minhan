@extends('layouts.backend')

@section('title', 'Chỉnh sửa — ' . $candidate->full_name)

@section('breadcrumb')
<div class="breadcrumbs text-sm px-6 pt-4 pb-0">
    <ul>
        <li><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
        <li><a href="{{ route('backend.recruitment.candidates.index') }}">Ứng viên</a></li>
        <li><a href="{{ route('backend.recruitment.candidates.show', $candidate) }}">{{ $candidate->full_name }}</a></li>
        <li class="font-semibold">Chỉnh sửa</li>
    </ul>
</div>
@endsection

@section('content')
<div class="p-6 max-w-3xl mx-auto">

    <div class="mb-6">
        <h1 class="text-xl font-bold">Chỉnh sửa ứng viên</h1>
        <p class="text-sm opacity-60 mt-0.5">{{ $candidate->email }}</p>
    </div>

    <form method="POST" action="{{ route('backend.recruitment.candidates.update', $candidate) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5 space-y-4">
                <h2 class="font-semibold text-sm text-base-content/70 uppercase tracking-wide">Thông tin cơ bản</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Họ và tên <span class="text-error">*</span></span></label>
                        <input type="text" name="full_name" value="{{ old('full_name', $candidate->full_name) }}"
                               class="input input-bordered @error('full_name') input-error @enderror" required>
                        @error('full_name')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Email <span class="text-error">*</span></span></label>
                        <input type="email" name="email" value="{{ old('email', $candidate->email) }}"
                               class="input input-bordered @error('email') input-error @enderror" required>
                        @error('email')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Số điện thoại</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $candidate->phone) }}"
                               class="input input-bordered">
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Giới tính</span></label>
                        <select name="gender" class="select select-bordered">
                            <option value="">Không xác định</option>
                            <option value="male" {{ old('gender', $candidate->gender) === 'male' ? 'selected' : '' }}>Nam</option>
                            <option value="female" {{ old('gender', $candidate->gender) === 'female' ? 'selected' : '' }}>Nữ</option>
                            <option value="other" {{ old('gender', $candidate->gender) === 'other' ? 'selected' : '' }}>Khác</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Ngày sinh</span></label>
                        <input type="date" name="date_of_birth"
                               value="{{ old('date_of_birth', $candidate->date_of_birth?->format('Y-m-d')) }}"
                               class="input input-bordered">
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Số năm kinh nghiệm</span></label>
                        <input type="number" name="years_experience"
                               value="{{ old('years_experience', $candidate->years_experience) }}"
                               class="input input-bordered" min="0" max="50">
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5 space-y-4">
                <h2 class="font-semibold text-sm text-base-content/70 uppercase tracking-wide">Công việc & Kỹ năng</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Chức danh hiện tại</span></label>
                        <input type="text" name="current_title"
                               value="{{ old('current_title', $candidate->current_title) }}"
                               class="input input-bordered">
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Công ty hiện tại</span></label>
                        <input type="text" name="current_company"
                               value="{{ old('current_company', $candidate->current_company) }}"
                               class="input input-bordered">
                    </div>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Kỹ năng</span></label>
                    <textarea name="skills" class="textarea textarea-bordered" rows="2">{{ old('skills', $candidate->skills) }}</textarea>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5 space-y-4">
                <h2 class="font-semibold text-sm text-base-content/70 uppercase tracking-wide">Nguồn & Liên kết</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Nguồn ứng viên</span></label>
                        <select name="source" class="select select-bordered">
                            @foreach($sources as $src)
                            <option value="{{ $src['value'] }}" {{ old('source', $candidate->source?->value) === $src['value'] ? 'selected' : '' }}>
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
                            <option value="{{ $user->id }}" {{ old('referred_by', $candidate->referred_by) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">LinkedIn</span></label>
                        <input type="url" name="linkedin_url"
                               value="{{ old('linkedin_url', $candidate->linkedin_url) }}"
                               class="input input-bordered">
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Portfolio</span></label>
                        <input type="url" name="portfolio_url"
                               value="{{ old('portfolio_url', $candidate->portfolio_url) }}"
                               class="input input-bordered">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('backend.recruitment.candidates.show', $candidate) }}" class="btn btn-ghost">Hủy</a>
            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
        </div>
    </form>

</div>
@endsection
