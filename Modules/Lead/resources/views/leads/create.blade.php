@extends('layouts.backend')
@section('title', 'Thêm cơ hội mới')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('lead.index') }}">Cơ hội</a>
    <span class="sep">›</span>
    <span class="current">Thêm mới</span>
</nav>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-content">Thêm cơ hội mới</h1>
    <a href="{{ route('lead.index') }}" class="btn btn-ghost btn-sm">← Quay lại</a>
</div>

<form method="POST" action="{{ route('lead.store') }}"
      class="max-w-3xl space-y-4" novalidate data-lead-form>
    @csrf

    {{-- ── Contact Info ──────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <h2 class="card-title text-base mb-2">
                <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Thông tin khách hàng
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="form-control md:col-span-2">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Tên khách hàng <span class="text-error">*</span></span>
                    </label>
                    <input type="text" name="contact_name" value="{{ old('contact_name') }}" required
                           data-req="Vui lòng nhập tên khách hàng"
                           class="input input-bordered input-sm @error('contact_name') input-error @enderror"
                           placeholder="VD: Nguyễn Văn A">
                    @error('contact_name')<p class="mt-1 text-xs text-error form-val-msg">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Số điện thoại</span></label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone') }}"
                           class="input input-bordered input-sm @error('contact_phone') input-error @enderror"
                           placeholder="0901 234 567">
                    @error('contact_phone')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Email</span></label>
                    <input type="email" name="contact_email" value="{{ old('contact_email') }}"
                           data-val-email="Email không đúng định dạng"
                           class="input input-bordered input-sm @error('contact_email') input-error @enderror"
                           placeholder="email@company.com">
                    @error('contact_email')<p class="mt-1 text-xs text-error form-val-msg">{{ $message }}</p>@enderror
                </div>

                <div class="form-control md:col-span-2">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Công ty</span></label>
                    <input type="text" name="contact_company" value="{{ old('contact_company') }}"
                           class="input input-bordered input-sm"
                           placeholder="VD: Công ty TNHH ABC">
                </div>

            </div>
        </div>
    </div>

    {{-- ── Lead Info ──────────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <h2 class="card-title text-base mb-2">
                <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Chi tiết cơ hội
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="form-control md:col-span-2">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Tiêu đề cơ hội</span></label>
                    <input type="text" name="title" value="{{ old('title') }}"
                           class="input input-bordered input-sm"
                           placeholder="VD: Tư vấn giải pháp ERP — ABC Corp">
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Tình trạng <span class="text-error">*</span></span>
                    </label>
                    <select name="stage_id" id="create-stage"
                            class="select select-bordered select-sm @error('stage_id') select-error @enderror"
                            required data-req="Vui lòng chọn tình trạng">
                        <option value="">— Chọn tình trạng —</option>
                        @foreach($stages as $stage)
                        <option value="{{ $stage->id }}" {{ old('stage_id') == $stage->id ? 'selected' : '' }}>
                            {{ $stage->label }}
                            @if($stage->probability) ({{ $stage->probability }}%) @endif
                        </option>
                        @endforeach
                    </select>
                    @error('stage_id')<p class="mt-1 text-xs text-error form-val-msg">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Nguồn</span></label>
                    <select name="source_id" id="create-source"
                            class="select select-bordered select-sm">
                        <option value="">— Chọn nguồn —</option>
                        @foreach($sources as $source)
                        <option value="{{ $source->id }}" {{ old('source_id') == $source->id ? 'selected' : '' }}>
                            {{ $source->label }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Value + Currency --}}
                <div class="form-control">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Giá trị dự kiến</span></label>
                    <div class="join">
                        <input type="number" name="expected_value" value="{{ old('expected_value') }}"
                               min="0" step="1000"
                               class="input input-bordered input-sm join-item flex-1 @error('expected_value') input-error @enderror"
                               placeholder="0">
                        <select name="currency" class="select select-bordered select-sm join-item w-24">
                            <option value="VND" {{ old('currency', 'VND') === 'VND' ? 'selected' : '' }}>VND</option>
                            <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                        </select>
                    </div>
                    @error('expected_value')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Ngày chốt dự kiến</span></label>
                    <input type="date" name="expected_close_date"
                           value="{{ old('expected_close_date') }}"
                           class="input input-bordered input-sm @error('expected_close_date') input-error @enderror">
                    @error('expected_close_date')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                @can('assign', \Modules\Lead\Models\Lead::class)
                <div class="form-control md:col-span-2">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Người phụ trách</span></label>
                    <select name="assigned_to" id="create-assigned"
                            class="select select-bordered select-sm"
                            data-assignable-url="{{ route('api.api.lead.assignable-users') }}">
                        <option value="">— Chưa phân công —</option>
                    </select>
                </div>
                @endcan

                <div class="form-control md:col-span-2">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Ghi chú / Mô tả</span></label>
                    <textarea name="description" rows="4"
                              class="textarea textarea-bordered textarea-sm w-full"
                              placeholder="Mô tả ngắn về cơ hội này...">{{ old('description') }}</textarea>
                </div>

            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary btn-sm">Tạo cơ hội</button>
        <a href="{{ route('lead.index') }}" class="btn btn-ghost btn-sm">Hủy</a>
    </div>

</form>
@endsection

@push('scripts')
@vite(['resources/js/modules/tom-select.js'], 'build/backend')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initFormValidation('[data-lead-form]');

    new window.TomSelect('#create-stage', { placeholder: '— Chọn tình trạng —', create: false });
    new window.TomSelect('#create-source', { placeholder: '— Chọn nguồn —', create: false });

    var assignedEl = document.getElementById('create-assigned');
    if (assignedEl) {
        var url = assignedEl.dataset.assignableUrl;
        new window.TomSelect('#create-assigned', {
            placeholder: '— Chưa phân công —',
            create:      false,
            valueField:  'id',
            labelField:  'text',
            searchField: ['text', 'email'],
            load: function(query, callback) {
                fetch(url + '?q=' + encodeURIComponent(query))
                    .then(r => r.json()).then(callback).catch(() => callback());
            },
        });
    }
});
</script>
@endpush
