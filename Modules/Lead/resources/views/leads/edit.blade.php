@extends('layouts.backend')
@section('title', 'Sửa cơ hội')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('lead.index') }}">Cơ hội</a>
    <span class="sep">›</span>
    <a href="{{ route('lead.show', $lead) }}">{{ $lead->displayTitle() }}</a>
    <span class="sep">›</span>
    <span class="current">Chỉnh sửa</span>
</nav>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-content">Chỉnh sửa cơ hội</h1>
    <a href="{{ route('lead.show', $lead) }}" class="btn btn-ghost btn-sm">← Quay lại</a>
</div>

{{-- Contact read-only banner --}}
<div class="card bg-base-200/50 border border-base-200 mb-4">
    <div class="card-body py-3 px-4 flex flex-row items-center gap-4">
        <div class="avatar placeholder">
            <div class="w-9 rounded-full bg-primary/20 text-primary text-sm font-bold">
                <span>{{ mb_substr($lead->contact_name, 0, 1) }}</span>
            </div>
        </div>
        <div class="flex-1">
            <p class="font-semibold text-sm">{{ $lead->contact_name }}</p>
            <p class="text-xs text-base-content/50">
                @if($lead->contact_phone) {{ $lead->contact_phone }} &bull; @endif
                @if($lead->contact_company) {{ $lead->contact_company }} @endif
            </p>
        </div>
        <span class="badge badge-ghost badge-sm">Khách hàng — chỉ đọc</span>
    </div>
</div>

<form method="POST" action="{{ route('lead.update', $lead) }}"
      class="max-w-3xl space-y-4" novalidate data-lead-form>
    @csrf
    @method('PUT')

    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <h2 class="card-title text-base mb-2">Chi tiết cơ hội</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="form-control md:col-span-2">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Tiêu đề cơ hội</span></label>
                    <input type="text" name="title" value="{{ old('title', $lead->title) }}"
                           class="input input-bordered input-sm"
                           placeholder="VD: Tư vấn giải pháp ERP — ABC Corp">
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Tình trạng <span class="text-error">*</span></span>
                    </label>
                    <select name="stage_id" id="edit-stage"
                            class="select select-bordered select-sm @error('stage_id') select-error @enderror"
                            required>
                        @foreach($stages as $stage)
                        <option value="{{ $stage->id }}"
                            {{ old('stage_id', $lead->stage_id) == $stage->id ? 'selected' : '' }}>
                            {{ $stage->label }}
                            @if($stage->probability) ({{ $stage->probability }}%) @endif
                        </option>
                        @endforeach
                    </select>
                    @error('stage_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Nguồn</span></label>
                    <select name="source_id" id="edit-source" class="select select-bordered select-sm">
                        <option value="">— Chọn nguồn —</option>
                        @foreach($sources as $source)
                        <option value="{{ $source->id }}"
                            {{ old('source_id', $lead->source_id) == $source->id ? 'selected' : '' }}>
                            {{ $source->label }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Giá trị dự kiến</span></label>
                    <div class="join">
                        <input type="number" name="expected_value"
                               value="{{ old('expected_value', $lead->expected_value) }}"
                               min="0" step="1000"
                               class="input input-bordered input-sm join-item flex-1"
                               placeholder="0">
                        <select name="currency" class="select select-bordered select-sm join-item w-24">
                            <option value="VND" {{ old('currency', $lead->currency) === 'VND' ? 'selected' : '' }}>VND</option>
                            <option value="USD" {{ old('currency', $lead->currency) === 'USD' ? 'selected' : '' }}>USD</option>
                        </select>
                    </div>
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Ngày chốt dự kiến</span></label>
                    <input type="date" name="expected_close_date"
                           value="{{ old('expected_close_date', $lead->expected_close_date?->format('Y-m-d')) }}"
                           class="input input-bordered input-sm">
                </div>

                @can('assign', $lead)
                <div class="form-control md:col-span-2">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Người phụ trách</span></label>
                    <select name="assigned_to" id="edit-assigned" class="select select-bordered select-sm"
                            data-assignable-url="{{ route('api.api.lead.assignable-users') }}"
                            data-current-id="{{ $lead->assigned_to }}"
                            data-current-name="{{ $lead->assignee?->name ?? '' }}">
                        @if($lead->assignee)
                        <option value="{{ $lead->assigned_to }}" selected>{{ $lead->assignee->name }}</option>
                        @else
                        <option value="">— Chưa phân công —</option>
                        @endif
                    </select>
                </div>
                @endcan

                <div class="form-control md:col-span-2">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Ghi chú / Mô tả</span></label>
                    <textarea name="description" rows="4"
                              class="textarea textarea-bordered textarea-sm w-full"
                              placeholder="Mô tả ngắn về cơ hội này...">{{ old('description', $lead->description) }}</textarea>
                </div>

            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary btn-sm">Lưu thay đổi</button>
        <a href="{{ route('lead.show', $lead) }}" class="btn btn-ghost btn-sm">Hủy</a>
        @can('delete', $lead)
        <form method="POST" action="{{ route('lead.destroy', $lead) }}" class="ml-auto"
              onsubmit="return confirm('Bạn có chắc muốn xóa cơ hội này?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-ghost btn-sm text-error">Xóa cơ hội</button>
        </form>
        @endcan
    </div>

</form>
@endsection

@push('scripts')
@vite(['resources/js/modules/tom-select.js'], 'build/backend')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initFormValidation('[data-lead-form]');
    new window.TomSelect('#edit-stage',  { create: false });
    new window.TomSelect('#edit-source', { placeholder: '— Chọn nguồn —', create: false });

    var assignedEl = document.getElementById('edit-assigned');
    if (assignedEl) {
        var url = assignedEl.dataset.assignableUrl;
        new window.TomSelect('#edit-assigned', {
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
