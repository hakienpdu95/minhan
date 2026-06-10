{{--
  Shared plan form partial.
  Variables: $plan (Plan|null), $featureSlugs (array), $action (string), $method (string)
--}}

@php
    $isEdit       = isset($plan) && $plan !== null;
    $featureNames = [
        'module.task'          => 'Module: Công việc',
        'module.sop'           => 'Module: SOP',
        'module.hr'            => 'Module: Nhân sự',
        'module.crm'           => 'Module: CRM / Lead',
        'module.workflow'      => 'Module: Workflow',
        'module.ai'            => 'Module: AI',
        'module.recruitment'   => 'Module: Tuyển dụng',
        'module.assessment'    => 'Module: Assessment',
        'module.project'       => 'Module: Dự án',
        'module.kc'            => 'Module: Kho tri thức',
        'module.marketplace'   => 'Module: Marketplace',
        'limit.employees'      => 'Giới hạn: Nhân viên',
        'limit.members'        => 'Giới hạn: Người dùng',
        'limit.workflows'      => 'Giới hạn: Workflow',
        'limit.projects'       => 'Giới hạn: Dự án',
        'limit.storage_gb'     => 'Giới hạn: Dung lượng (GB)',
        'flag.api_access'      => 'Flag: API Access',
        'flag.audit_log'       => 'Flag: Audit Log',
        'flag.advanced_reports'=> 'Flag: Báo cáo nâng cao',
        'flag.sso'             => 'Flag: SSO',
        'flag.white_label'     => 'Flag: White Label',
        'flag.custom_domain'   => 'Flag: Custom Domain',
        'quota.ai_requests'    => 'Quota: AI requests / tháng',
        'quota.workflow_runs'  => 'Quota: Workflow runs / tháng',
        'quota.email_notifications' => 'Quota: Email notifications / tháng',
    ];
    $existingFeatures = $isEdit ? $plan->features->keyBy('slug') : collect();
@endphp

<form method="POST" action="{{ $action }}" novalidate>
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_340px] gap-5">

        {{-- Left: core settings --}}
        <div class="space-y-5">

            {{-- Basic info --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body">
                    <h2 class="card-title text-base mb-4">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Thông tin cơ bản
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label py-0.5"><span class="label-text text-xs font-medium">Slug <span class="text-error">*</span></span></label>
                            <input type="text" name="slug" value="{{ old('slug', $plan?->slug) }}"
                                   class="input input-bordered input-sm font-mono @error('slug') input-error @enderror"
                                   placeholder="growth" {{ $isEdit ? 'readonly' : '' }}/>
                            @error('slug')<span class="text-error text-xs mt-0.5">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label py-0.5"><span class="label-text text-xs font-medium">Tên plan <span class="text-error">*</span></span></label>
                            <input type="text" name="name" value="{{ old('name', $plan?->name) }}"
                                   class="input input-bordered input-sm @error('name') input-error @enderror"
                                   placeholder="Gói Tăng trưởng"/>
                            @error('name')<span class="text-error text-xs mt-0.5">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-control sm:col-span-2">
                            <label class="label py-0.5"><span class="label-text text-xs font-medium">Mô tả</span></label>
                            <textarea name="description" rows="2"
                                      class="textarea textarea-bordered textarea-sm @error('description') textarea-error @enderror"
                                      placeholder="Mô tả ngắn về plan...">{{ old('description', $plan?->description) }}</textarea>
                            @error('description')<span class="text-error text-xs mt-0.5">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pricing --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body">
                    <h2 class="card-title text-base mb-4">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Giá & Chu kỳ
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label py-0.5"><span class="label-text text-xs font-medium">Giá theo tháng (VND) <span class="text-error">*</span></span></label>
                            <input type="number" name="price" value="{{ old('price', $plan?->price ?? 0) }}"
                                   class="input input-bordered input-sm @error('price') input-error @enderror"
                                   min="0" step="1000"/>
                            @error('price')<span class="text-error text-xs mt-0.5">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label py-0.5"><span class="label-text text-xs font-medium">Giá theo năm (VND)</span></label>
                            <input type="number" name="annual_price" value="{{ old('annual_price', $plan?->annual_price) }}"
                                   class="input input-bordered input-sm @error('annual_price') input-error @enderror"
                                   min="0" step="1000" placeholder="Để trống nếu không có"/>
                            @error('annual_price')<span class="text-error text-xs mt-0.5">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label py-0.5"><span class="label-text text-xs font-medium">Đơn vị tiền tệ</span></label>
                            <input type="text" name="currency" value="{{ old('currency', $plan?->currency ?? 'VND') }}"
                                   class="input input-bordered input-sm" maxlength="10"/>
                        </div>

                        <div class="form-control">
                            <label class="label py-0.5"><span class="label-text text-xs font-medium">Chu kỳ hóa đơn</span></label>
                            <div class="flex gap-2">
                                <input type="number" name="invoice_period" value="{{ old('invoice_period', $plan?->invoice_period ?? 1) }}"
                                       class="input input-bordered input-sm w-20" min="1"/>
                                <select name="invoice_interval" class="select select-bordered select-sm flex-1">
                                    @foreach(['day' => 'Ngày', 'week' => 'Tuần', 'month' => 'Tháng', 'year' => 'Năm'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('invoice_interval', $plan?->invoice_interval ?? 'month') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-control">
                            <label class="label py-0.5"><span class="label-text text-xs font-medium">Trial</span></label>
                            <div class="flex gap-2">
                                <input type="number" name="trial_period" value="{{ old('trial_period', $plan?->trial_period ?? 0) }}"
                                       class="input input-bordered input-sm w-20" min="0"/>
                                <select name="trial_interval" class="select select-bordered select-sm flex-1">
                                    @foreach(['day' => 'Ngày', 'week' => 'Tuần', 'month' => 'Tháng'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('trial_interval', $plan?->trial_interval ?? 'day') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-control">
                            <label class="label py-0.5"><span class="label-text text-xs font-medium">Grace period</span></label>
                            <div class="flex gap-2">
                                <input type="number" name="grace_period" value="{{ old('grace_period', $plan?->grace_period ?? 3) }}"
                                       class="input input-bordered input-sm w-20" min="0"/>
                                <select name="grace_interval" class="select select-bordered select-sm flex-1">
                                    @foreach(['day' => 'Ngày', 'week' => 'Tuần', 'month' => 'Tháng'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('grace_interval', $plan?->grace_interval ?? 'day') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Features --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body">
                    <h2 class="card-title text-base mb-1">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Features
                    </h2>
                    <p class="text-xs text-base-content/50 mb-4">Định nghĩa các tính năng cho plan. Bool: 1/0 — Limit: số nguyên (0=vô hạn) — Quota: số nguyên / tháng.</p>

                    <div class="overflow-x-auto">
                        <table class="table table-xs w-full">
                            <thead>
                                <tr class="text-xs text-base-content/60">
                                    <th class="w-56">Feature slug</th>
                                    <th>Tên hiển thị</th>
                                    <th class="w-28">Giá trị</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($featureNames as $slug => $fname)
                                @php
                                    $existing = $existingFeatures->get($slug);
                                    $idx      = array_search($slug, array_keys($featureNames));
                                @endphp
                                <tr>
                                    <td>
                                        <input type="hidden" name="features[{{ $idx }}][slug]" value="{{ $slug }}"/>
                                        <input type="hidden" name="features[{{ $idx }}][name]" value="{{ $fname }}"/>
                                        <span class="font-mono text-xs">{{ $slug }}</span>
                                    </td>
                                    <td class="text-xs text-base-content/70">{{ $fname }}</td>
                                    <td>
                                        @if (str_starts_with($slug, 'module.') || str_starts_with($slug, 'flag.'))
                                        <select name="features[{{ $idx }}][value]" class="select select-bordered select-xs w-20">
                                            <option value="1" {{ old("features.$idx.value", $existing?->value) === '1' ? 'selected' : '' }}>✓</option>
                                            <option value="0" {{ old("features.$idx.value", $existing?->value ?? '0') === '0' ? 'selected' : '' }}>✗</option>
                                        </select>
                                        @else
                                        <input type="number" name="features[{{ $idx }}][value]"
                                               value="{{ old("features.$idx.value", $existing?->value ?? '0') }}"
                                               class="input input-bordered input-xs w-24" min="0"/>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right: meta --}}
        <div class="space-y-5">

            {{-- Visibility & tier --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body">
                    <h2 class="card-title text-base mb-4">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        Hiển thị & Tier
                    </h2>

                    <div class="space-y-4">
                        <div class="form-control">
                            <label class="label py-0.5"><span class="label-text text-xs font-medium">Tier</span></label>
                            <select name="tier" class="select select-bordered select-sm">
                                @foreach(['starter' => 'Starter (miễn phí)', 'growth' => 'Growth', 'scale' => 'Scale', 'enterprise' => 'Enterprise'] as $val => $label)
                                <option value="{{ $val }}" {{ old('tier', $plan?->tier ?? 'growth') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label py-0.5"><span class="label-text text-xs font-medium">Tag line</span></label>
                            <input type="text" name="tag_line" value="{{ old('tag_line', $plan?->tag_line) }}"
                                   class="input input-bordered input-sm" placeholder="Phổ biến nhất" maxlength="120"/>
                            @error('tag_line')<span class="text-error text-xs mt-0.5">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label py-0.5">
                                <span class="label-text text-xs font-medium">Badge color</span>
                                <span class="label-text-alt text-xs text-base-content/40">DaisyUI class</span>
                            </label>
                            <input type="text" name="badge_color" value="{{ old('badge_color', $plan?->badge_color) }}"
                                   class="input input-bordered input-sm font-mono" placeholder="badge-primary"/>
                            @error('badge_color')<span class="text-error text-xs mt-0.5">{{ $message }}</span>@enderror
                        </div>

                        <div class="flex flex-col gap-3 pt-1">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="hidden" name="is_active" value="0"/>
                                <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm checkbox-primary"
                                       {{ old('is_active', $plan?->is_active ?? true) ? 'checked' : '' }}/>
                                <span class="text-sm">Kích hoạt</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="hidden" name="is_public" value="0"/>
                                <input type="checkbox" name="is_public" value="1" class="checkbox checkbox-sm checkbox-primary"
                                       {{ old('is_public', $plan?->is_public ?? true) ? 'checked' : '' }}/>
                                <span class="text-sm">Hiển thị công khai</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex flex-col gap-2">
                <button type="submit" class="btn btn-primary w-full">
                    {{ $isEdit ? 'Lưu thay đổi' : 'Tạo plan' }}
                </button>
                <a href="{{ route('subscription.admin.plans.index') }}" class="btn btn-ghost btn-sm w-full">
                    Hủy
                </a>
            </div>

        </div>
    </div>
</form>
