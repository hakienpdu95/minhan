@extends('layouts.backend')
@section('title', 'Sửa tin tuyển dụng')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.job-posts.index') }}">Tin tuyển dụng</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.job-posts.show', $jobPost) }}">{{ $jobPost->code }}</a>
    <span class="sep">›</span>
    <span class="current">Sửa</span>
</nav>
@endsection

@section('content')
<form method="POST" action="{{ route('backend.job-posts.update', $jobPost) }}" x-data="{ submitting: false }"
      @submit="submitting = true">
    @csrf
    @method('PUT')

    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-base-content">{{ $jobPost->title }}</h1>
            <p class="text-sm text-base-content/50 mt-0.5">{{ $jobPost->code }} · <span class="badge badge-sm badge-soft {{ $jobPost->status->badgeClass() }}">{{ $jobPost->status->label() }}</span></p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('backend.job-posts.show', $jobPost) }}" class="btn btn-ghost btn-sm">Hủy</a>
            <button type="submit" class="btn btn-primary btn-sm" :disabled="submitting">
                <span x-show="!submitting">Lưu thay đổi</span>
                <span x-show="submitting">Đang lưu...</span>
            </button>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-error mb-4">
        <ul class="list-disc list-inside text-sm">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <div class="lg:col-span-2 space-y-4">

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5">
                    <h2 class="font-semibold text-base mb-4">Thông tin cơ bản</h2>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Tên vị trí <span class="text-error">*</span></span></label>
                        <input type="text" name="title" value="{{ old('title', $jobPost->title) }}"
                               class="input input-bordered input-sm w-full @error('title') input-error @enderror" required/>
                        @error('title')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Mô tả ngắn</span></label>
                        <textarea name="summary" rows="2"
                                  class="textarea textarea-bordered textarea-sm w-full">{{ old('summary', $jobPost->summary) }}</textarea>
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Mô tả công việc <span class="text-error">*</span></span></label>
                        <textarea name="description" rows="6"
                                  class="textarea textarea-bordered textarea-sm w-full" required>{{ old('description', $jobPost->description) }}</textarea>
                        @error('description')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Trách nhiệm</span></label>
                        <textarea name="responsibilities" rows="4"
                                  class="textarea textarea-bordered textarea-sm w-full">{{ old('responsibilities', $jobPost->responsibilities) }}</textarea>
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Yêu cầu ứng viên <span class="text-error">*</span></span></label>
                        <textarea name="requirements" rows="4"
                                  class="textarea textarea-bordered textarea-sm w-full" required>{{ old('requirements', $jobPost->requirements) }}</textarea>
                        @error('requirements')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Yêu cầu phụ</span></label>
                        <textarea name="nice_to_have" rows="2"
                                  class="textarea textarea-bordered textarea-sm w-full">{{ old('nice_to_have', $jobPost->nice_to_have) }}</textarea>
                    </div>

                    <div class="form-control">
                        <label class="label py-1"><span class="label-text font-medium">Bạn sẽ học được gì</span></label>
                        <textarea name="what_you_will_learn" rows="2"
                                  class="textarea textarea-bordered textarea-sm w-full">{{ old('what_you_will_learn', $jobPost->what_you_will_learn) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5">
                    <h2 class="font-semibold text-base mb-4">Lương & Phúc lợi</h2>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text font-medium">Loại lương</span></label>
                            <select name="salary_type" class="select select-bordered select-sm w-full">
                                @foreach($salaryTypes as $type)
                                <option value="{{ $type['value'] }}" {{ old('salary_type', $jobPost->salary_type?->value) === $type['value'] ? 'selected' : '' }}>{{ $type['text'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text font-medium">Đơn vị tiền tệ</span></label>
                            <input type="text" name="salary_currency" value="{{ old('salary_currency', $jobPost->salary_currency) }}"
                                   class="input input-bordered input-sm w-full" maxlength="3"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text font-medium">Lương tối thiểu</span></label>
                            <input type="number" name="salary_min" value="{{ old('salary_min', $jobPost->salary_min) }}"
                                   class="input input-bordered input-sm w-full" step="0.01" min="0"/>
                        </div>
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text font-medium">Lương tối đa</span></label>
                            <input type="number" name="salary_max" value="{{ old('salary_max', $jobPost->salary_max) }}"
                                   class="input input-bordered input-sm w-full" step="0.01" min="0"/>
                        </div>
                    </div>

                    <div class="flex gap-4 flex-wrap">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="salary_is_negotiable" value="1" class="checkbox checkbox-sm"
                                   {{ old('salary_is_negotiable', $jobPost->salary_is_negotiable) ? 'checked' : '' }}/>
                            <span class="text-sm">Thỏa thuận</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="salary_is_visible" value="1" class="checkbox checkbox-sm"
                                   {{ old('salary_is_visible', $jobPost->salary_is_visible) ? 'checked' : '' }}/>
                            <span class="text-sm">Hiển thị lương</span>
                        </label>
                    </div>

                    <div class="form-control mt-3">
                        <label class="label py-1"><span class="label-text font-medium">Ghi chú lương</span></label>
                        <input type="text" name="salary_note" value="{{ old('salary_note', $jobPost->salary_note) }}"
                               class="input input-bordered input-sm w-full" maxlength="300"/>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5">
                    <h2 class="font-semibold text-base mb-4">Cấu hình ứng tuyển</h2>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Email nhận CV</span></label>
                        <input type="email" name="application_email" value="{{ old('application_email', $jobPost->application_email) }}"
                               class="input input-bordered input-sm w-full"/>
                    </div>

                    <div class="flex gap-4 flex-wrap">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="allow_direct_apply" value="1" class="checkbox checkbox-sm"
                                   {{ old('allow_direct_apply', $jobPost->allow_direct_apply) ? 'checked' : '' }}/>
                            <span class="text-sm">Cho phép apply trực tiếp</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="require_cover_letter" value="1" class="checkbox checkbox-sm"
                                   {{ old('require_cover_letter', $jobPost->require_cover_letter) ? 'checked' : '' }}/>
                            <span class="text-sm">Bắt buộc cover letter</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="require_portfolio" value="1" class="checkbox checkbox-sm"
                                   {{ old('require_portfolio', $jobPost->require_portfolio) ? 'checked' : '' }}/>
                            <span class="text-sm">Bắt buộc portfolio</span>
                        </label>
                    </div>
                </div>
            </div>

        </div>

        <div class="space-y-4">

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5">
                    <h2 class="font-semibold text-base mb-4">Phân loại</h2>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Phòng ban</span></label>
                        <select name="department_id" class="select select-bordered select-sm w-full">
                            <option value="">-- Chọn phòng ban --</option>
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $jobPost->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Chức danh tham chiếu</span></label>
                        <select name="job_title_id" class="select select-bordered select-sm w-full">
                            <option value="">-- Chọn chức danh --</option>
                            @foreach($jobTitles as $jt)
                            <option value="{{ $jt->id }}" {{ old('job_title_id', $jobPost->job_title_id) == $jt->id ? 'selected' : '' }}>{{ $jt->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Loại hình <span class="text-error">*</span></span></label>
                        <select name="employment_type" class="select select-bordered select-sm w-full" required>
                            @foreach($employmentTypes as $type)
                            <option value="{{ $type['value'] }}" {{ old('employment_type', $jobPost->employment_type?->value) === $type['value'] ? 'selected' : '' }}>{{ $type['text'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Hình thức <span class="text-error">*</span></span></label>
                        <select name="work_arrangement" class="select select-bordered select-sm w-full" required>
                            @foreach($workArrangements as $wa)
                            <option value="{{ $wa['value'] }}" {{ old('work_arrangement', $jobPost->work_arrangement?->value) === $wa['value'] ? 'selected' : '' }}>{{ $wa['text'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Cấp độ <span class="text-error">*</span></span></label>
                        <select name="experience_level" class="select select-bordered select-sm w-full" required>
                            @foreach($experienceLevels as $el)
                            <option value="{{ $el['value'] }}" {{ old('experience_level', $jobPost->experience_level?->value) === $el['value'] ? 'selected' : '' }}>{{ $el['text'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Ngành nghề <span class="text-error">*</span></span></label>
                        <select name="industry" class="select select-bordered select-sm w-full" required>
                            @foreach($industries as $ind)
                            <option value="{{ $ind['value'] }}" {{ old('industry', $jobPost->industry?->value) === $ind['value'] ? 'selected' : '' }}>{{ $ind['text'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Hiển thị</span></label>
                        <select name="visibility" class="select select-bordered select-sm w-full">
                            @foreach($visibilities as $vis)
                            <option value="{{ $vis['value'] }}" {{ old('visibility', $jobPost->visibility?->value) === $vis['value'] ? 'selected' : '' }}>{{ $vis['text'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label py-1"><span class="label-text font-medium">Số lượng tuyển <span class="text-error">*</span></span></label>
                        <input type="number" name="headcount" value="{{ old('headcount', $jobPost->headcount) }}"
                               class="input input-bordered input-sm w-full" min="1" required/>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5">
                    <h2 class="font-semibold text-base mb-4">Địa điểm</h2>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Thành phố</span></label>
                        <input type="text" name="city" value="{{ old('city', $jobPost->city) }}"
                               class="input input-bordered input-sm w-full"/>
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Quốc gia <span class="text-error">*</span></span></label>
                        <input type="text" name="country" value="{{ old('country', $jobPost->country) }}"
                               class="input input-bordered input-sm w-full" maxlength="2" required/>
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_remote_allowed" value="1" class="checkbox checkbox-sm"
                               {{ old('is_remote_allowed', $jobPost->is_remote_allowed) ? 'checked' : '' }}/>
                        <span class="text-sm">Cho phép remote</span>
                    </label>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5">
                    <h2 class="font-semibold text-base mb-4">Thời hạn</h2>

                    <div class="form-control">
                        <label class="label py-1"><span class="label-text font-medium">Hạn nộp hồ sơ</span></label>
                        <input type="datetime-local" name="expire_at"
                               value="{{ old('expire_at', $jobPost->expire_at?->format('Y-m-d\TH:i')) }}"
                               class="input input-bordered input-sm w-full"/>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5">
                    <h2 class="font-semibold text-base mb-4">Phân phối kênh</h2>

                    <label class="flex items-center gap-2 cursor-pointer mb-2">
                        <input type="checkbox" name="publish_to_career_page" value="1" class="checkbox checkbox-sm"
                               {{ old('publish_to_career_page', $jobPost->publish_to_career_page) ? 'checked' : '' }}/>
                        <span class="text-sm">Career page</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="publish_to_marketplace" value="1" class="checkbox checkbox-sm"
                               {{ old('publish_to_marketplace', $jobPost->publish_to_marketplace) ? 'checked' : '' }}/>
                        <span class="text-sm">Marketplace</span>
                    </label>
                </div>
            </div>

        </div>
    </div>
</form>
@endsection
