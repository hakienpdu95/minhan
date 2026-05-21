@extends('layouts.backend')
@section('title', 'Thêm tài khoản')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.users.index') }}">Tài khoản</a>
    <span class="sep">›</span>
    <span class="current">Thêm mới</span>
</nav>
@endsection

@section('content')
<div x-data="createUserPage">

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Thêm tài khoản mới</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Tạo tài khoản và phân quyền theo ma trận vai trò</p>
    </div>
    <a href="{{ route('backend.users.index') }}" class="btn btn-ghost btn-sm">← Quay lại</a>
</div>

<form method="POST" action="{{ route('backend.users.store') }}" id="create-user-form" @submit="handleSubmit($event)">
    @csrf

    <div class="grid grid-cols-1 xl:grid-cols-5 gap-5">

        {{-- ── Left: Basic info + Password ──────────────────────────────── --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- Basic info card --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body">
                    <h2 class="card-title text-base mb-4">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Thông tin cơ bản
                    </h2>

                    {{-- Inline identity preview --}}
                    <div class="flex items-center gap-3 p-3 bg-base-200/50 rounded-xl mb-4">
                        <div class="relative shrink-0">
                            <div class="w-12 h-12 rounded-full ring-2 ring-offset-1 ring-offset-base-100 overflow-hidden flex items-center justify-center bg-base-300 transition-all"
                                 :class="name ? 'ring-primary' : 'ring-base-300'">
                                <img x-show="avatarUrl" :src="avatarUrl" class="w-full h-full object-cover" alt="">
                                <svg x-show="!avatarUrl" class="w-6 h-6 text-base-content/20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold truncate transition-colors"
                               x-text="name || 'Họ và tên'"
                               :class="name ? 'text-base-content' : 'text-base-content/25'"></p>
                            <p class="text-xs truncate mt-0.5"
                               x-text="email || 'email@congty.com'"
                               :class="email ? 'text-base-content/60' : 'text-base-content/25'"></p>
                        </div>
                        <span x-show="selectedRoleLabel"
                              x-text="selectedRoleLabel"
                              x-transition
                              class="badge badge-primary badge-sm shrink-0"></span>
                    </div>

                    <div class="space-y-3">
                        {{-- Name --}}
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Họ và tên <span class="text-error">*</span></span>
                                <span x-show="showOk('name')" class="label-text-alt text-success text-xs flex items-center gap-0.5" x-transition>
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    Hợp lệ
                                </span>
                            </label>
                            <input type="text" name="name" id="input-name"
                                   x-model="name"
                                   @input.debounce.200ms="updateAvatar()"
                                   @blur="touch('name')"
                                   :class="fieldCls('name')"
                                   class="input input-bordered input-sm w-full transition-colors"
                                   placeholder="Nguyễn Văn A" autocomplete="off">
                            <p x-show="showErr('name')" x-text="errors.name" class="mt-1 text-xs text-error" x-transition></p>
                            @error('name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        {{-- Email --}}
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Email <span class="text-error">*</span></span>
                                <span x-show="showOk('email')" class="label-text-alt text-success text-xs flex items-center gap-0.5" x-transition>
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    Hợp lệ
                                </span>
                            </label>
                            <input type="email" name="email"
                                   x-model="email"
                                   @blur="touch('email')"
                                   :class="fieldCls('email')"
                                   class="input input-bordered input-sm w-full transition-colors"
                                   placeholder="ten@congty.com" autocomplete="off">
                            <p x-show="showErr('email')" x-text="errors.email" class="mt-1 text-xs text-error" x-transition></p>
                            @error('email')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        {{-- Department --}}
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Phòng ban</span>
                            </label>
                            <input type="text" name="department" value="{{ old('department') }}"
                                   class="input input-bordered input-sm w-full"
                                   placeholder="VD: Kinh doanh, IT, HR...">
                            @error('department')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Password card --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="card-title text-base">
                            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Mật khẩu
                        </h2>
                        <button type="button" @click="generatePassword()"
                                class="btn btn-xs btn-outline gap-1.5">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Tạo ngẫu nhiên
                        </button>
                    </div>

                    <div class="space-y-3">
                        {{-- Password --}}
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Mật khẩu <span class="text-error">*</span></span>
                                <span x-show="showOk('password')" class="label-text-alt text-success text-xs flex items-center gap-0.5" x-transition>
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    Đủ mạnh
                                </span>
                            </label>
                            <div class="relative">
                                <input :type="showPw ? 'text' : 'password'"
                                       name="password" id="password-input"
                                       x-model="password"
                                       @blur="touch('password')"
                                       :class="fieldCls('password')"
                                       class="input input-bordered input-sm w-full pr-10 transition-colors"
                                       placeholder="Tối thiểu 8 ký tự">
                                <button type="button" @click="showPw = !showPw"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-base-content/40 hover:text-base-content transition-colors">
                                    <svg x-show="!showPw" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="showPw" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>

                            {{-- Strength bar --}}
                            <div x-show="password.length > 0" class="mt-2 space-y-1" x-transition>
                                <progress class="progress w-full h-1.5 transition-all"
                                          :class="strength.barCls" :value="strength.pct" max="100"></progress>
                                <div class="flex items-center justify-between">
                                    <div class="flex gap-2 flex-wrap">
                                        <span class="text-xs transition-colors" :class="pwChecks.length  ? 'text-success' : 'text-base-content/30'"><span x-text="pwChecks.length  ? '✓' : '○'"></span> ≥8 ký tự</span>
                                        <span class="text-xs transition-colors" :class="pwChecks.upper   ? 'text-success' : 'text-base-content/30'"><span x-text="pwChecks.upper   ? '✓' : '○'"></span> HOA</span>
                                        <span class="text-xs transition-colors" :class="pwChecks.lower   ? 'text-success' : 'text-base-content/30'"><span x-text="pwChecks.lower   ? '✓' : '○'"></span> thường</span>
                                        <span class="text-xs transition-colors" :class="pwChecks.number  ? 'text-success' : 'text-base-content/30'"><span x-text="pwChecks.number  ? '✓' : '○'"></span> Số</span>
                                        <span class="text-xs transition-colors" :class="pwChecks.special ? 'text-success' : 'text-base-content/30'"><span x-text="pwChecks.special ? '✓' : '○'"></span> Ký tự đặc biệt</span>
                                    </div>
                                    <span class="text-xs font-semibold transition-colors" :class="strength.textCls" x-text="strength.label"></span>
                                </div>
                            </div>

                            <p x-show="showErr('password')" x-text="errors.password" class="mt-1 text-xs text-error" x-transition></p>
                            @error('password')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        {{-- Confirm password --}}
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Xác nhận mật khẩu <span class="text-error">*</span></span>
                                <span x-show="pwConfirm && password && pwConfirm === password"
                                      class="label-text-alt text-success text-xs flex items-center gap-0.5" x-transition>
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    Khớp
                                </span>
                            </label>
                            <input :type="showPw ? 'text' : 'password'"
                                   name="password_confirmation" id="password-confirm-input"
                                   x-model="pwConfirm"
                                   :class="pwConfirm && password ? (pwConfirm === password ? 'input-success' : 'input-error') : ''"
                                   class="input input-bordered input-sm w-full transition-colors"
                                   placeholder="Nhập lại mật khẩu">
                            <p x-show="pwConfirm.length > 0 && password.length > 0 && pwConfirm !== password"
                               class="mt-1 text-xs text-error">Mật khẩu không khớp</p>
                        </div>

                        {{-- Options --}}
                        <div class="space-y-2 pt-2 border-t border-base-200">
                            <label class="flex items-start gap-2.5 cursor-pointer group select-none">
                                <input type="checkbox" name="send_welcome_email" value="1"
                                       x-model="sendWelcomeEmail"
                                       class="checkbox checkbox-sm checkbox-info mt-0.5 shrink-0">
                                <div>
                                    <span class="text-sm font-medium group-hover:text-info transition-colors">Gửi email chào mừng</span>
                                    <p class="text-xs text-base-content/50">Email chứa thông tin đăng nhập và mật khẩu</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-2.5 cursor-pointer group select-none">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1"
                                       class="checkbox checkbox-sm checkbox-primary mt-0.5 shrink-0"
                                       {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                <div>
                                    <span class="text-sm font-medium">Kích hoạt tài khoản ngay</span>
                                    <p class="text-xs text-base-content/50">Bỏ chọn nếu muốn tạo nhưng chưa cho đăng nhập</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── Right: Org & Role + Permission Matrix ─────────────────────── --}}
        <div class="xl:col-span-3 space-y-5">

            {{-- Org & Role card --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body">
                    <h2 class="card-title text-base mb-4">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Tổ chức & Vai trò
                    </h2>

                    {{-- Organization --}}
                    <div class="form-control mb-5">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Tổ chức <span class="text-error">*</span></span>
                            <span x-show="showOk('organization_id')" class="label-text-alt text-success text-xs flex items-center gap-0.5" x-transition>
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Đã chọn
                            </span>
                        </label>
                        <select id="org-select" name="organization_id"
                                class="select select-bordered select-sm @error('organization_id') select-error @enderror"></select>
                        <p x-show="showErr('organization_id')" x-text="errors.organization_id" class="mt-1 text-xs text-error" x-transition></p>
                        @error('organization_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Role presets --}}
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-semibold text-base-content/50 uppercase tracking-wide">Chọn nhanh vai trò</p>
                            <p x-show="showErr('system_role')" x-text="errors.system_role" class="text-xs text-error" x-transition></p>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5">
                            <template x-for="preset in rolePresets" :key="preset.role">
                                <button type="button"
                                        @click="selectPreset(preset.role)"
                                        :class="selectedRole === preset.role
                                            ? 'border-primary bg-primary/10 text-primary ring-1 ring-primary/30'
                                            : 'border-base-300 hover:border-primary/40 hover:bg-base-200 text-base-content'"
                                        class="flex items-center gap-1.5 p-2 rounded-lg border text-xs font-medium transition-all text-left">
                                    <span x-text="preset.icon" class="text-sm leading-none shrink-0"></span>
                                    <span x-text="preset.label" class="leading-tight"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Role TomSelect --}}
                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Vai trò hệ thống <span class="text-error">*</span></span>
                            <span class="label-text-alt text-xs text-base-content/40">Quyết định quyền truy cập</span>
                        </label>
                        <select id="role-select" name="system_role"
                                class="select select-bordered select-sm @error('system_role') select-error @enderror"></select>
                        @error('system_role')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Permission matrix --}}
            <div class="card bg-base-100 shadow-sm border border-base-200" x-show="selectedRole" x-transition>
                <div class="card-body">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Quyền hạn sẽ được cấp
                        </h3>
                        <span class="badge badge-primary badge-sm" x-text="selectedRoleLabel"></span>
                    </div>

                    <div class="mb-3 p-2.5 bg-base-200/60 rounded-lg" x-show="sidebarModules.length > 0">
                        <p class="text-xs text-base-content/50 font-medium mb-1.5">Hiển thị trong sidebar:</p>
                        <div class="flex flex-wrap gap-1">
                            <template x-for="mod in sidebarModules" :key="mod">
                                <span class="badge badge-xs badge-ghost border border-base-300 font-normal" x-text="mod"></span>
                            </template>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table table-xs w-full">
                            <thead>
                                <tr class="text-xs uppercase text-base-content/40">
                                    <th>Module</th>
                                    <th>Mức quyền</th>
                                    <th class="hidden sm:table-cell">Mô tả ngắn</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="row in currentMatrix" :key="row.module">
                                    <tr>
                                        <td class="font-medium text-sm py-1.5" x-text="row.module"></td>
                                        <td class="py-1.5"><span :class="row.badgeClass" x-text="row.level"></span></td>
                                        <td class="text-xs text-base-content/50 hidden sm:table-cell py-1.5" x-text="row.desc"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Empty state --}}
            <div class="card bg-base-100 border border-base-200 border-dashed" x-show="!selectedRole">
                <div class="card-body py-10 text-center text-base-content/25">
                    <svg class="w-10 h-10 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <p class="font-medium text-sm">Chọn vai trò để xem quyền hạn</p>
                    <p class="text-xs mt-1">Ma trận phân quyền sẽ hiển thị theo vai trò đã chọn</p>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Submit bar ───────────────────────────────────────────────────────── --}}
    <div id="submit-bar" class="flex items-center gap-3 pt-4 mt-4 border-t border-base-200">

        {{-- Validation summary (client-side) --}}
        <div x-show="attempted && !isValid" x-transition class="flex items-center gap-2 text-sm text-error">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>Vui lòng kiểm tra lại các trường bắt buộc trước khi tiếp tục</span>
        </div>

        {{-- Confirmation preview (shown when all required fields are filled) --}}
        <div x-show="isValid && name && email && selectedRole" x-transition
             class="text-xs text-base-content/50 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Sẵn sàng tạo tài khoản</span>
            <strong x-text="name" class="text-base-content"></strong>
            <span>·</span>
            <span x-text="selectedRoleLabel" class="text-primary font-medium"></span>
            <span x-show="sendWelcomeEmail" class="text-info">· Sẽ gửi email</span>
        </div>

        <div class="ml-auto flex gap-2">
            <a href="{{ route('backend.users.index') }}" class="btn btn-ghost btn-sm">Hủy</a>
            <button type="submit" class="btn btn-sm gap-2 transition-all"
                    :class="attempted && !isValid ? 'btn-error' : 'btn-primary'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tạo tài khoản
            </button>
        </div>
    </div>

</form>
</div>
@endsection

@push('styles')
<style>
.ts-wrapper.single .ts-control { background:oklch(var(--b1)); border-color:oklch(var(--b3)); color:oklch(var(--bc)); border-radius:.375rem; min-height:2.25rem; padding:.3rem .6rem; font-size:.875rem; }
.ts-wrapper.single.focus .ts-control { border-color:oklch(var(--p)); outline:none; box-shadow:0 0 0 2px oklch(var(--p)/.2); }
.ts-dropdown { background:oklch(var(--b1)); border:1px solid oklch(var(--b3)); border-radius:.5rem; box-shadow:0 4px 16px rgba(0,0,0,.15); z-index:9999; }
.ts-dropdown .ts-option { color:oklch(var(--bc)); padding:.45rem .75rem; font-size:.875rem; }
.ts-dropdown .ts-option:hover, .ts-dropdown .ts-option.active { background:oklch(var(--b2)); }
.ts-dropdown .ts-option.selected { background:oklch(var(--p)/.15); color:oklch(var(--p)); }
.ts-control input { color:oklch(var(--bc)) !important; }
</style>
@endpush

@push('scripts')
@vite(['resources/js/modules/tom-select.js'], 'build/backend')
<script>
document.addEventListener('alpine:init', function () {
    var ORGANIZATIONS = @json($organizations->values());
    var ROLES         = @json($roles);
    var OLD_ORG       = @json(old('organization_id', ''));
    var OLD_ROLE      = @json(old('system_role', ''));
    var HAS_ERRORS    = @json($errors->any());

    // ── Permission matrix data ────────────────────────────────────────────────

    var LEVEL_META = {
        'Full':          { cls: 'badge badge-xs badge-success',                      desc: 'Toàn quyền CRUD + gán' },
        'Assigned':      { cls: 'badge badge-xs badge-info',                         desc: 'Chỉ record được gán' },
        'Full team':     { cls: 'badge badge-xs badge-success',                      desc: 'Toàn quyền cả team' },
        'Limited':       { cls: 'badge badge-xs badge-warning',                      desc: 'Xem, không sửa/xóa' },
        'Source view':   { cls: 'badge badge-xs badge-primary',                      desc: 'Xem nguồn, ẩn thông tin cá nhân' },
        'Config':        { cls: 'badge badge-xs badge-ghost border border-base-300', desc: 'Cấu hình module' },
        'Config prompt': { cls: 'badge badge-xs badge-ghost border border-base-300', desc: 'Chỉnh prompt AI' },
        'Admin config':  { cls: 'badge badge-xs badge-ghost border border-base-300', desc: 'Cấu hình admin' },
        'Full config':   { cls: 'badge badge-xs badge-ghost border border-base-300', desc: 'Toàn quyền cấu hình' },
        'AI config':     { cls: 'badge badge-xs badge-ghost border border-base-300', desc: 'Cấu hình AI' },
        'Use':           { cls: 'badge badge-xs badge-accent',                       desc: 'Dùng AI output, không config' },
        'Monitor':       { cls: 'badge badge-xs badge-warning',                      desc: 'Xem trạng thái, không sửa' },
        'Monitor/Edit':  { cls: 'badge badge-xs badge-warning',                      desc: 'Xem + sửa workflow' },
        'Approve/View':  { cls: 'badge badge-xs badge-secondary',                    desc: 'Xem + phê duyệt' },
        'View':          { cls: 'badge badge-xs badge-ghost',                        desc: 'Chỉ xem' },
        'View related':  { cls: 'badge badge-xs badge-ghost',                        desc: 'Xem tài liệu liên quan' },
        'View summary':  { cls: 'badge badge-xs badge-ghost',                        desc: 'Xem tóm tắt' },
        'View ltd':      { cls: 'badge badge-xs badge-ghost',                        desc: 'Xem giới hạn' },
        'HR tasks':      { cls: 'badge badge-xs badge-info',                         desc: 'Chỉ task bộ phận HR' },
        'Create/Edit':   { cls: 'badge badge-xs badge-success',                      desc: 'Tạo và sửa' },
        'Create HR SOP': { cls: 'badge badge-xs badge-info',                         desc: 'Tạo SOP HR' },
        'Personal/team': { cls: 'badge badge-xs badge-info',                         desc: 'Báo cáo cá nhân/team' },
        'Operations':    { cls: 'badge badge-xs badge-info',                         desc: 'Báo cáo vận hành' },
        'Marketing':     { cls: 'badge badge-xs badge-info',                         desc: 'Báo cáo marketing' },
        'HR':            { cls: 'badge badge-xs badge-info',                         desc: 'Báo cáo nhân sự' },
        'AI usage':      { cls: 'badge badge-xs badge-info',                         desc: 'Báo cáo dùng AI' },
        'Shared only':   { cls: 'badge badge-xs badge-ghost',                        desc: 'Chỉ dữ liệu được chia sẻ' },
    };

    var MATRIX = @json($matrix);

    var SIDEBAR = {
        'ceo':          ['CEO Dashboard','CRM','Sales AI','Tasks','SOP','Workflow','Prompt Mgmt','AI Logs','Users','Reports'],
        'sales':        ['CRM','Sales AI','Tasks','SOP','Reports'],
        'ops':          ['CEO Dashboard','CRM','Tasks','SOP','Workflow','AI Logs','Reports'],
        'marketing':    ['CRM','Sales AI','Tasks','SOP','Reports'],
        'hr':           ['Tasks','SOP','Users','Reports'],
        'ai_operator':  ['CEO Dashboard','CRM','Tasks','SOP','Workflow','Prompt Mgmt','AI Logs','Reports'],
        'system_admin': ['CEO Dashboard','CRM','Sales AI','Tasks','SOP','Workflow','Prompt Mgmt','AI Logs','Users','Roles/Perms','Reports'],
        'viewer':       ['CEO Dashboard','Tasks','SOP','Reports'],
    };

    var ALL_PRESETS = [
        { role: 'ceo',          label: 'CEO / Điều hành', icon: '👔' },
        { role: 'sales',        label: 'Kinh doanh',       icon: '💼' },
        { role: 'ops',          label: 'Vận hành',         icon: '⚙️' },
        { role: 'marketing',    label: 'Marketing',        icon: '📢' },
        { role: 'hr',           label: 'Nhân sự (HR)',     icon: '👥' },
        { role: 'ai_operator',  label: 'AI Operator',      icon: '🤖' },
        { role: 'system_admin', label: 'Quản trị HT',      icon: '🛡️' },
        { role: 'viewer',       label: 'Xem giới hạn',     icon: '👁️' },
    ];

    var allowedRoles  = ROLES.map(function (r) { return r.value; });
    var filteredPresets = ALL_PRESETS.filter(function (p) { return allowedRoles.indexOf(p.role) !== -1; });

    // ── Password generator ────────────────────────────────────────────────────
    function genPassword() {
        var upper   = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        var lower   = 'abcdefghjkmnpqrstuvwxyz';
        var digits  = '23456789';
        var special = '!@#$%&*';
        var all     = upper + lower + digits + special;
        var pwd     = upper[Math.floor(Math.random() * upper.length)]
                    + lower[Math.floor(Math.random() * lower.length)]
                    + digits[Math.floor(Math.random() * digits.length)]
                    + special[Math.floor(Math.random() * special.length)];
        for (var i = 4; i < 14; i++) pwd += all[Math.floor(Math.random() * all.length)];
        return pwd.split('').sort(function () { return 0.5 - Math.random(); }).join('');
    }

    var roleTsInst = null;

    // ── Alpine component ──────────────────────────────────────────────────────
    Alpine.data('createUserPage', function () {
        return {
            // Form fields
            name:           @json(old('name', '')),
            email:          @json(old('email', '')),
            password:       '',
            pwConfirm:      '',
            showPw:         false,
            sendWelcomeEmail: false,
            avatarUrl:      '',

            // Org & Role
            selectedOrg:       OLD_ORG,
            selectedRole:      OLD_ROLE,
            selectedRoleLabel: '',
            rolePresets:       filteredPresets,

            // Validation tracking
            touched: {
                name:            @json($errors->has('name')),
                email:           @json($errors->has('email')),
                password:        @json($errors->has('password')),
                organization_id: @json($errors->has('organization_id')),
                system_role:     @json($errors->has('system_role')),
            },
            attempted: HAS_ERRORS,

            // ── Validation rules ──────────────────────────────────────────
            get errors() {
                var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
                return {
                    name:            !this.name.trim()                 ? 'Họ và tên là bắt buộc'
                                   : this.name.trim().length < 2       ? 'Tên phải có ít nhất 2 ký tự' : null,
                    email:           !this.email.trim()                ? 'Email là bắt buộc'
                                   : !emailRe.test(this.email.trim())  ? 'Địa chỉ email không hợp lệ' : null,
                    password:        !this.password                    ? 'Mật khẩu là bắt buộc'
                                   : this.password.length < 8         ? 'Phải có ít nhất 8 ký tự'
                                   : !/[A-Z]/.test(this.password)     ? 'Cần ít nhất 1 chữ HOA'
                                   : !/[a-z]/.test(this.password)     ? 'Cần ít nhất 1 chữ thường'
                                   : !/[0-9]/.test(this.password)     ? 'Cần ít nhất 1 chữ số' : null,
                    organization_id: !this.selectedOrg                 ? 'Vui lòng chọn tổ chức' : null,
                    system_role:     !this.selectedRole                ? 'Vui lòng chọn vai trò' : null,
                };
            },

            get isValid() {
                var e = this.errors;
                return !e.name && !e.email && !e.password && !e.organization_id && !e.system_role;
            },

            // ── Validation helpers ────────────────────────────────────────
            touch: function (field) { this.touched[field] = true; },

            showErr: function (field) {
                return (this.touched[field] || this.attempted) && !!this.errors[field];
            },

            showOk: function (field) {
                var val = { name: this.name, email: this.email, password: this.password,
                            organization_id: this.selectedOrg, system_role: this.selectedRole }[field] || '';
                return this.touched[field] && !this.errors[field] && !!val.toString().trim();
            },

            fieldCls: function (field) {
                return {
                    'input-error':   this.showErr(field),
                    'input-success': this.showOk(field),
                };
            },

            // ── Password computed ─────────────────────────────────────────
            get pwChecks() {
                var p = this.password;
                return {
                    length:  p.length >= 8,
                    upper:   /[A-Z]/.test(p),
                    lower:   /[a-z]/.test(p),
                    number:  /[0-9]/.test(p),
                    special: /[^A-Za-z0-9]/.test(p),
                };
            },

            get strength() {
                var c     = this.pwChecks;
                var score = [c.length, c.upper && c.lower, c.number, c.special, this.password.length >= 12]
                    .filter(Boolean).length;
                return [
                    { label: '',           barCls: 'progress-base-300', textCls: 'text-base-content/30', pct: 0 },
                    { label: 'Yếu',        barCls: 'progress-error',    textCls: 'text-error',            pct: 20 },
                    { label: 'Trung bình', barCls: 'progress-warning',  textCls: 'text-warning',          pct: 40 },
                    { label: 'Tốt',        barCls: 'progress-info',     textCls: 'text-info',             pct: 65 },
                    { label: 'Mạnh',       barCls: 'progress-success',  textCls: 'text-success',          pct: 85 },
                    { label: 'Rất mạnh',   barCls: 'progress-success',  textCls: 'text-success',          pct: 100 },
                ][Math.min(score, 5)];
            },

            // ── Permission matrix computed ────────────────────────────────
            get currentMatrix() {
                var role = this.selectedRole;
                if (!role) return [];
                return Object.keys(MATRIX)
                    .filter(function (m) { return MATRIX[m][role]; })
                    .map(function (m) {
                        var level = MATRIX[m][role];
                        var meta  = LEVEL_META[level] || { cls: 'badge badge-xs badge-ghost', desc: '' };
                        return { module: m, level: level, badgeClass: meta.cls, desc: meta.desc };
                    });
            },

            get sidebarModules() { return SIDEBAR[this.selectedRole] || []; },

            // ── Form submit ───────────────────────────────────────────────
            handleSubmit: function (e) {
                this.attempted = true;
                var self = this;
                Object.keys(this.touched).forEach(function (k) { self.touched[k] = true; });

                if (!this.isValid) {
                    e.preventDefault();
                    this.$nextTick(function () {
                        var el = document.querySelector('.input-error') || document.getElementById('submit-bar');
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    });
                }
            },

            // ── Actions ───────────────────────────────────────────────────
            updateAvatar: function () {
                this.avatarUrl = this.name.trim()
                    ? 'https://api.dicebear.com/9.x/initials/svg?seed='
                        + encodeURIComponent(this.name.trim())
                        + '&backgroundColor=6366f1&fontFamily=Arial&fontSize=40&fontWeight=700'
                    : '';
            },

            generatePassword: function () {
                var pwd = genPassword();
                this.password  = pwd;
                this.pwConfirm = pwd;
                this.showPw    = true;
                this.touched.password = true;
                var a = document.getElementById('password-input');
                var b = document.getElementById('password-confirm-input');
                if (a) a.value = pwd;
                if (b) b.value = pwd;
            },

            selectPreset: function (role) {
                this.selectedRole = role;
                this.touched.system_role = true;
                var found = ROLES.find(function (r) { return r.value === role; });
                this.selectedRoleLabel = found ? found.label : '';
                if (roleTsInst) roleTsInst.setValue(role, false);
            },

            // ── Lifecycle ─────────────────────────────────────────────────
            init: function () {
                var self = this;
                if (OLD_ROLE) {
                    var f = ROLES.find(function (r) { return r.value === OLD_ROLE; });
                    if (f) self.selectedRoleLabel = f.label;
                }
                if (self.name) self.updateAvatar();
                document.addEventListener('DOMContentLoaded', function () { self._setup(); }, { once: true });
            },

            _setup: function () {
                var self = this;

                new window.TomSelect('#org-select', {
                    dropdownParent: 'body',
                    placeholder:    'Chọn tổ chức...',
                    maxOptions:     null,
                    searchField:    ['text'],
                    options:        ORGANIZATIONS.map(function (o) { return { value: String(o.id), text: o.name }; }),
                    items:          OLD_ORG ? [String(OLD_ORG)] : [],
                    render: { no_results: function () { return '<div class="p-3 text-sm opacity-50">Không tìm thấy</div>'; } },
                    onChange: function (val) {
                        self.selectedOrg = val || '';
                        self.touched.organization_id = true;
                    },
                });

                roleTsInst = new window.TomSelect('#role-select', {
                    dropdownParent: 'body',
                    placeholder:    'Chọn vai trò...',
                    searchField:    ['text'],
                    options:        ROLES.map(function (r) { return { value: r.value, text: r.label }; }),
                    items:          OLD_ROLE ? [OLD_ROLE] : [],
                    render: { no_results: function () { return '<div class="p-3 text-sm opacity-50">Không tìm thấy</div>'; } },
                    onChange: function (val) {
                        self.selectedRole = val || '';
                        self.touched.system_role = true;
                        var found = ROLES.find(function (r) { return r.value === val; });
                        self.selectedRoleLabel = found ? found.label : '';
                    },
                });
            },
        };
    });
});
</script>
@endpush
