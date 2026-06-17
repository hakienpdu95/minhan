@extends('layouts.backend')
@section('title', 'Thêm ' . $vertical->targetLabel())

@section('content')
<div class="max-w-2xl"
     x-data="{
        taxCode: '{{ old('tax_code', '') }}',
        foundOrg: null,
        useExisting: false,
        searching: false,
        async lookup() {
            this.foundOrg = null;
            this.useExisting = false;
            if (!this.taxCode || this.taxCode.length < 8) return;
            this.searching = true;
            try {
                const res = await fetch(
                    '{{ route('deployment.targets.lookup', ['vertical' => $vertical->code()]) }}'
                    + '?tax_code=' + encodeURIComponent(this.taxCode),
                    { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
                );
                const data = await res.json();
                this.foundOrg = data.found ? data.org : null;
            } finally {
                this.searching = false;
            }
        },
        applyOrg() {
            this.useExisting = true;
            this.$refs.orgName.value    = this.foundOrg.name    || '';
            this.$refs.orgPhone.value   = this.foundOrg.phone   || '';
            this.$refs.orgEmail.value   = this.foundOrg.email   || '';
            this.$refs.orgAddress.value = this.foundOrg.full_address || '';
        }
     }">

    <div class="mb-5">
        <h1 class="text-2xl font-bold">Thêm {{ $vertical->targetLabel() }}</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Nhập thông tin tổ chức được triển khai</p>
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

    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="card-body">
            <form method="POST"
                  action="{{ route('deployment.targets.store', ['vertical' => $vertical->code()]) }}">
                @csrf

                <h3 class="font-semibold text-sm mb-3 text-base-content/70">Thông tin dự án</h3>

                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Dự án <span class="text-error">*</span></span></label>
                    <div class="flex gap-2 items-center">
                        <select name="project_id" class="select select-bordered flex-1 @error('project_id') select-error @enderror">
                            <option value="">— Chọn dự án —</option>
                            @foreach($projects as $p)
                            <option value="{{ $p->id }}" @selected(old('project_id') == $p->id)>{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <a href="{{ route('deployment.projects.create', ['vertical' => $vertical->code()]) }}"
                           class="btn btn-ghost btn-xs text-xs whitespace-nowrap" target="_blank">+ Tạo mới</a>
                    </div>
                    @error('project_id')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Người phụ trách</span></label>
                    <select name="assigned_employee_id" class="select select-bordered select-sm">
                        <option value="">— Không chỉ định —</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" @selected(old('assigned_employee_id') == $emp->id)>
                            {{ $emp->full_name }} ({{ $emp->employee_code }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="divider my-4 text-xs">Thông tin {{ $vertical->targetLabel() }}</div>

                {{-- MST lookup --}}
                <div class="grid grid-cols-2 gap-4 mb-2">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Mã số thuế</span></label>
                        <div class="relative">
                            <input type="text" name="tax_code"
                                   x-model="taxCode"
                                   x-on:input.debounce.600ms="lookup()"
                                   class="input input-bordered input-sm w-full pr-8 @error('tax_code') input-error @enderror"
                                   placeholder="0123456789">
                            <span x-show="searching"
                                  class="absolute right-2 top-2.5 loading loading-spinner loading-xs"></span>
                        </div>
                        <span class="text-xs text-base-content/40 mt-1">Nhập MST để tìm tổ chức đã có</span>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Điện thoại</span></label>
                        <input type="text" name="phone" x-ref="orgPhone" value="{{ old('phone') }}"
                               class="input input-bordered input-sm">
                    </div>
                </div>

                {{-- Found-org confirm banner --}}
                <template x-if="foundOrg && !useExisting">
                    <div class="alert alert-warning mb-4 flex-col items-start gap-1 py-3">
                        <p class="text-sm font-semibold">
                            Tổ chức "<span x-text="foundOrg.name"></span>" đã tồn tại trong hệ thống.
                        </p>
                        <p class="text-xs opacity-70" x-text="foundOrg.full_address || ''"></p>
                        <div class="flex gap-2 mt-1">
                            <button type="button" class="btn btn-warning btn-xs"
                                    x-on:click="applyOrg()">Dùng tổ chức này</button>
                            <button type="button" class="btn btn-ghost btn-xs"
                                    x-on:click="foundOrg = null">Tạo mới</button>
                        </div>
                    </div>
                </template>

                <template x-if="useExisting">
                    <div class="alert alert-success mb-4 text-sm py-2">
                        Đã chọn tổ chức có sẵn. Thông tin được điền tự động từ hệ thống.
                    </div>
                </template>

                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Tên tổ chức <span class="text-error">*</span></span></label>
                    <input type="text" name="name" x-ref="orgName" value="{{ old('name') }}"
                           class="input input-bordered @error('name') input-error @enderror"
                           placeholder="Ví dụ: HTX Nông nghiệp An Bình">
                    @error('name')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Email</span></label>
                    <input type="email" name="email" x-ref="orgEmail" value="{{ old('email') }}"
                           class="input input-bordered input-sm">
                </div>

                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Địa chỉ</span></label>
                    <input type="text" name="full_address" x-ref="orgAddress" value="{{ old('full_address') }}"
                           class="input input-bordered input-sm">
                </div>

                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Người đại diện</span></label>
                    <input type="text" name="representative_name" value="{{ old('representative_name') }}"
                           class="input input-bordered input-sm">
                </div>

                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Ghi chú</span></label>
                    <textarea name="notes" rows="3"
                              class="textarea textarea-bordered">{{ old('notes') }}</textarea>
                </div>

                <div class="flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Thêm {{ $vertical->targetLabel() }}</button>
                    <a href="{{ route('deployment.targets.index', ['vertical' => $vertical->code()]) }}"
                       class="btn btn-ghost">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
