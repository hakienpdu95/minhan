@extends('layouts.backend')
@section('title', 'Tạo tin tuyển dụng')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.job-posts.index') }}">Tin tuyển dụng</a>
    <span class="sep">›</span>
    <span class="current">Tạo mới</span>
</nav>
@endsection

@section('content')
<form method="POST" action="{{ route('backend.job-posts.store') }}" x-data="{ submitting: false }"
      @submit="submitting = true">
    @csrf

    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Tạo tin tuyển dụng</h1>
            <p class="text-sm text-base-content/50 mt-0.5">Soạn thảo và lưu nháp tin tuyển dụng mới</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('backend.job-posts.index') }}" class="btn btn-ghost btn-sm">Hủy</a>
            <button type="submit" class="btn btn-primary btn-sm" :disabled="submitting">
                <span x-show="!submitting">Lưu nháp</span>
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

        {{-- ── Main content (left 2/3) ──────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Thông tin cơ bản --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5">
                    <h2 class="font-semibold text-base mb-4">Thông tin cơ bản</h2>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Tên vị trí <span class="text-error">*</span></span></label>
                        <input type="text" name="title" value="{{ old('title') }}"
                               class="input input-bordered input-sm w-full @error('title') input-error @enderror"
                               placeholder="VD: Senior Backend Engineer (PHP / Laravel)" required/>
                        @error('title')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Mô tả ngắn</span></label>
                        <textarea name="summary" rows="2"
                                  class="textarea textarea-bordered textarea-sm w-full @error('summary') textarea-error @enderror"
                                  placeholder="Tóm tắt ngắn hiển thị trên listing card (max 500 ký tự)">{{ old('summary') }}</textarea>
                        @error('summary')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Mô tả công việc <span class="text-error">*</span></span></label>
                        <textarea name="description" rows="6"
                                  class="textarea textarea-bordered textarea-sm w-full @error('description') textarea-error @enderror"
                                  required>{{ old('description') }}</textarea>
                        @error('description')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Trách nhiệm / Nhiệm vụ</span></label>
                        <textarea name="responsibilities" rows="4"
                                  class="textarea textarea-bordered textarea-sm w-full">{{ old('responsibilities') }}</textarea>
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Yêu cầu ứng viên <span class="text-error">*</span></span></label>
                        <textarea name="requirements" rows="4"
                                  class="textarea textarea-bordered textarea-sm w-full @error('requirements') textarea-error @enderror"
                                  required>{{ old('requirements') }}</textarea>
                        @error('requirements')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Yêu cầu phụ (nice-to-have)</span></label>
                        <textarea name="nice_to_have" rows="2"
                                  class="textarea textarea-bordered textarea-sm w-full">{{ old('nice_to_have') }}</textarea>
                    </div>

                    <div class="form-control">
                        <label class="label py-1"><span class="label-text font-medium">Bạn sẽ học được gì</span></label>
                        <textarea name="what_you_will_learn" rows="2"
                                  class="textarea textarea-bordered textarea-sm w-full">{{ old('what_you_will_learn') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Lương --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5">
                    <h2 class="font-semibold text-base mb-4">Lương & Phúc lợi</h2>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text font-medium">Loại lương</span></label>
                            <select name="salary_type" class="select select-bordered select-sm w-full">
                                @foreach($salaryTypes as $type)
                                <option value="{{ $type['value'] }}" {{ old('salary_type', 'monthly') === $type['value'] ? 'selected' : '' }}>{{ $type['text'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text font-medium">Đơn vị tiền tệ</span></label>
                            <input type="text" name="salary_currency" value="{{ old('salary_currency', 'VND') }}"
                                   class="input input-bordered input-sm w-full" maxlength="3" placeholder="VND"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text font-medium">Lương tối thiểu</span></label>
                            <input type="number" name="salary_min" value="{{ old('salary_min') }}"
                                   class="input input-bordered input-sm w-full" placeholder="0" step="0.01" min="0"/>
                        </div>
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text font-medium">Lương tối đa</span></label>
                            <input type="number" name="salary_max" value="{{ old('salary_max') }}"
                                   class="input input-bordered input-sm w-full" placeholder="0" step="0.01" min="0"/>
                        </div>
                    </div>

                    <div class="flex gap-4 flex-wrap">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="salary_is_negotiable" value="1" class="checkbox checkbox-sm"
                                   {{ old('salary_is_negotiable') ? 'checked' : '' }}/>
                            <span class="text-sm">Thỏa thuận</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="salary_is_visible" value="1" class="checkbox checkbox-sm"
                                   {{ old('salary_is_visible', true) ? 'checked' : '' }}/>
                            <span class="text-sm">Hiển thị lương</span>
                        </label>
                    </div>

                    <div class="form-control mt-3">
                        <label class="label py-1"><span class="label-text font-medium">Ghi chú lương</span></label>
                        <input type="text" name="salary_note" value="{{ old('salary_note') }}"
                               class="input input-bordered input-sm w-full" maxlength="300" placeholder="VD: 4–5.5 LPA fixed + 3 LPA variable"/>
                    </div>
                </div>
            </div>

            {{-- Học vấn & Kinh nghiệm --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5">
                    <h2 class="font-semibold text-base mb-4">Học vấn & Kinh nghiệm</h2>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text font-medium">Kinh nghiệm tối thiểu (năm)</span></label>
                            <input type="number" name="min_experience_years" value="{{ old('min_experience_years') }}"
                                   class="input input-bordered input-sm w-full" min="0" placeholder="0"/>
                        </div>
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text font-medium">Kinh nghiệm tối đa (năm)</span></label>
                            <input type="number" name="max_experience_years" value="{{ old('max_experience_years') }}"
                                   class="input input-bordered input-sm w-full" min="0" placeholder="Để trống = không giới hạn"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text font-medium">Trình độ học vấn</span></label>
                            <select name="education_level" class="select select-bordered select-sm w-full">
                                <option value="">-- Không yêu cầu --</option>
                                @foreach($visibilities as $v){{-- reuse loop --}}@endforeach
                                <option value="none" {{ old('education_level') === 'none' ? 'selected' : '' }}>Không yêu cầu</option>
                                <option value="high_school" {{ old('education_level') === 'high_school' ? 'selected' : '' }}>THPT</option>
                                <option value="associate" {{ old('education_level') === 'associate' ? 'selected' : '' }}>Cao đẳng</option>
                                <option value="bachelor" {{ old('education_level') === 'bachelor' ? 'selected' : '' }}>Đại học</option>
                                <option value="master" {{ old('education_level') === 'master' ? 'selected' : '' }}>Thạc sĩ</option>
                                <option value="phd" {{ old('education_level') === 'phd' ? 'selected' : '' }}>Tiến sĩ</option>
                                <option value="any" {{ old('education_level') === 'any' ? 'selected' : '' }}>Không giới hạn</option>
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text font-medium">Ngành học yêu cầu</span></label>
                            <input type="text" name="education_field" value="{{ old('education_field') }}"
                                   class="input input-bordered input-sm w-full" placeholder="VD: Computer Science, IT"/>
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label py-1"><span class="label-text font-medium">Chứng chỉ bắt buộc</span></label>
                        <textarea name="certifications_required" rows="2"
                                  class="textarea textarea-bordered textarea-sm w-full">{{ old('certifications_required') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Cấu hình ứng tuyển --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5">
                    <h2 class="font-semibold text-base mb-4">Cấu hình ứng tuyển</h2>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Email nhận CV</span></label>
                        <input type="email" name="application_email" value="{{ old('application_email') }}"
                               class="input input-bordered input-sm w-full" placeholder="hr@company.com"/>
                    </div>

                    <div class="flex gap-4 flex-wrap">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="allow_direct_apply" value="1" class="checkbox checkbox-sm"
                                   {{ old('allow_direct_apply', true) ? 'checked' : '' }}/>
                            <span class="text-sm">Cho phép apply trực tiếp</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="require_cover_letter" value="1" class="checkbox checkbox-sm"
                                   {{ old('require_cover_letter') ? 'checked' : '' }}/>
                            <span class="text-sm">Bắt buộc cover letter</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="require_portfolio" value="1" class="checkbox checkbox-sm"
                                   {{ old('require_portfolio') ? 'checked' : '' }}/>
                            <span class="text-sm">Bắt buộc portfolio</span>
                        </label>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── Sidebar (right 1/3) ────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- Phân loại --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5">
                    <h2 class="font-semibold text-base mb-4">Phân loại</h2>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Phòng ban</span></label>
                        <select name="department_id" class="select select-bordered select-sm w-full">
                            <option value="">-- Chọn phòng ban --</option>
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Chức danh tham chiếu</span></label>
                        <select name="job_title_id" class="select select-bordered select-sm w-full">
                            <option value="">-- Chọn chức danh --</option>
                            @foreach($jobTitles as $jt)
                            <option value="{{ $jt->id }}" {{ old('job_title_id') == $jt->id ? 'selected' : '' }}>{{ $jt->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Loại hình <span class="text-error">*</span></span></label>
                        <select name="employment_type" class="select select-bordered select-sm w-full" required>
                            @foreach($employmentTypes as $type)
                            <option value="{{ $type['value'] }}" {{ old('employment_type', 'full_time') === $type['value'] ? 'selected' : '' }}>{{ $type['text'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Hình thức làm việc <span class="text-error">*</span></span></label>
                        <select name="work_arrangement" class="select select-bordered select-sm w-full" required>
                            @foreach($workArrangements as $wa)
                            <option value="{{ $wa['value'] }}" {{ old('work_arrangement', 'onsite') === $wa['value'] ? 'selected' : '' }}>{{ $wa['text'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Cấp độ kinh nghiệm <span class="text-error">*</span></span></label>
                        <select name="experience_level" class="select select-bordered select-sm w-full" required>
                            @foreach($experienceLevels as $el)
                            <option value="{{ $el['value'] }}" {{ old('experience_level', 'junior') === $el['value'] ? 'selected' : '' }}>{{ $el['text'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Ngành nghề <span class="text-error">*</span></span></label>
                        <select name="industry" class="select select-bordered select-sm w-full" required>
                            @foreach($industries as $ind)
                            <option value="{{ $ind['value'] }}" {{ old('industry', 'other') === $ind['value'] ? 'selected' : '' }}>{{ $ind['text'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Hiển thị</span></label>
                        <select name="visibility" class="select select-bordered select-sm w-full">
                            @foreach($visibilities as $vis)
                            <option value="{{ $vis['value'] }}" {{ old('visibility', 'public') === $vis['value'] ? 'selected' : '' }}>{{ $vis['text'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label py-1"><span class="label-text font-medium">Số lượng cần tuyển <span class="text-error">*</span></span></label>
                        <input type="number" name="headcount" value="{{ old('headcount', 1) }}"
                               class="input input-bordered input-sm w-full" min="1" required/>
                    </div>
                </div>
            </div>

            {{-- Địa điểm --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5">
                    <h2 class="font-semibold text-base mb-4">Địa điểm</h2>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Thành phố</span></label>
                        <input type="text" name="city" value="{{ old('city') }}"
                               class="input input-bordered input-sm w-full" placeholder="VD: Hồ Chí Minh"/>
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Tỉnh / Tỉnh thành</span></label>
                        <input type="text" name="province" value="{{ old('province') }}"
                               class="input input-bordered input-sm w-full"/>
                    </div>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Quốc gia <span class="text-error">*</span></span></label>
                        <input type="text" name="country" value="{{ old('country', 'VN') }}"
                               class="input input-bordered input-sm w-full" maxlength="2" required/>
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_remote_allowed" value="1" class="checkbox checkbox-sm"
                               {{ old('is_remote_allowed') ? 'checked' : '' }}/>
                        <span class="text-sm">Cho phép remote</span>
                    </label>
                </div>
            </div>

            {{-- Thời hạn --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5">
                    <h2 class="font-semibold text-base mb-4">Thời hạn</h2>

                    <div class="form-control mb-3">
                        <label class="label py-1"><span class="label-text font-medium">Hạn nộp hồ sơ</span></label>
                        <input type="datetime-local" name="expire_at" value="{{ old('expire_at') }}"
                               class="input input-bordered input-sm w-full"/>
                    </div>
                </div>
            </div>

            {{-- Phân phối --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5">
                    <h2 class="font-semibold text-base mb-4">Phân phối kênh</h2>

                    <label class="flex items-center gap-2 cursor-pointer mb-2">
                        <input type="checkbox" name="publish_to_career_page" value="1" class="checkbox checkbox-sm"
                               {{ old('publish_to_career_page', true) ? 'checked' : '' }}/>
                        <span class="text-sm">Career page của tổ chức</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="publish_to_marketplace" value="1" class="checkbox checkbox-sm"
                               {{ old('publish_to_marketplace') ? 'checked' : '' }}/>
                        <span class="text-sm">Marketplace (cổng công khai)</span>
                    </label>
                </div>
            </div>

        </div>
    </div>

</form>
@endsection
