@extends('layouts.backend')
@section('title', 'Danh sách tổ chức')

@vite(['resources/js/modules/tabulator.js'], 'build/backend')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <span class="current">Tổ chức</span>
</nav>
@endsection

@section('content')
<div x-data="orgListPage">

    {{-- Page header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Tổ chức</h1>
            <p class="text-sm text-base-content/50 mt-0.5">Quản lý tất cả tổ chức trong hệ thống</p>
        </div>
        <a href="{{ route('backend.organizations.create') }}" class="btn btn-primary btn-sm gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Thêm tổ chức
        </a>
    </div>

    {{-- Filter bar --}}
    <div class="card bg-base-100 shadow-sm border border-base-200 mb-4">
        <div class="card-body py-3 px-4">
            <div class="flex flex-wrap gap-3 items-end">

                {{-- Name search --}}
                <div class="form-control flex-1 min-w-48">
                    <label class="label py-0.5">
                        <span class="label-text text-xs font-medium">Tìm kiếm tên</span>
                    </label>
                    <div class="input input-sm input-bordered flex items-center gap-2 bg-base-100">
                        <svg class="w-3.5 h-3.5 text-base-content/40 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" x-model="filters.name"
                               @input.debounce.350ms="refresh()"
                               placeholder="Nhập tên tổ chức..."
                               class="grow bg-transparent outline-none text-sm"/>
                    </div>
                </div>

                {{-- Province filter --}}
                <div class="form-control w-52">
                    <label class="label py-0.5">
                        <span class="label-text text-xs font-medium">Tỉnh / Thành phố</span>
                    </label>
                    <select class="select select-sm select-bordered"
                            x-model="filters.province_code"
                            @change="onProvinceChange()">
                        <option value="">Tất cả tỉnh/thành</option>
                        <template x-for="p in provinces" :key="p.province_code">
                            <option :value="p.province_code" x-text="p.name"></option>
                        </template>
                    </select>
                </div>

                {{-- Ward filter --}}
                <div class="form-control w-52">
                    <label class="label py-0.5">
                        <span class="label-text text-xs font-medium">Phường / Xã</span>
                    </label>
                    <select class="select select-sm select-bordered"
                            x-model="filters.ward_code"
                            @change="refresh()"
                            :disabled="!filters.province_code || loadingWards">
                        <option value="" x-text="loadingWards ? 'Đang tải...' : 'Tất cả phường/xã'"></option>
                        <template x-for="w in wards" :key="w.ward_code">
                            <option :value="w.ward_code" x-text="w.name"></option>
                        </template>
                    </select>
                </div>

                {{-- Reset button --}}
                <div class="form-control">
                    <label class="label py-0.5 invisible"><span class="label-text text-xs">.</span></label>
                    <button class="btn btn-ghost btn-sm gap-1.5"
                            @click="reset()"
                            x-show="filters.name || filters.province_code || filters.ward_code"
                            x-transition>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Xóa bộ lọc
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Tabulator table --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-0 overflow-hidden rounded-2xl">
            <div id="org-table"></div>
        </div>
    </div>

</div>

{{-- Delete confirm modal --}}
<dialog id="deleteModal" class="modal">
    <div class="modal-box max-w-sm">
        <h3 class="font-bold text-lg text-error">Xác nhận xóa</h3>
        <p class="py-3 text-sm text-base-content/70">
            Bạn có chắc muốn xóa tổ chức <strong id="deleteItemName" class="text-base-content"></strong>?
        </p>
        <p class="text-xs text-error/70">Toàn bộ thành viên và dữ liệu liên quan sẽ bị xóa theo.</p>
        <div class="modal-action mt-4">
            <form method="POST" id="deleteForm">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-error btn-sm">Xóa</button>
            </form>
            <button class="btn btn-ghost btn-sm" onclick="deleteModal.close()">Hủy</button>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
@endsection

@push('scripts')
<script>
// ── Delete helper (global, callable from Tabulator HTML formatters) ──────────
window.orgDeleteConfirm = function (url, name) {
    document.getElementById('deleteForm').action = url;
    document.getElementById('deleteItemName').textContent = name;
    deleteModal.showModal();
};

// ── Register Alpine component BEFORE Alpine starts ───────────────────────────
document.addEventListener('alpine:init', function () {
    Alpine.data('orgListPage', function () {
        var tableInstance = null;
        var apiUrl        = '{{ route('backend.api.organizations') }}';
        var wardsApiBase  = '/api/provinces';
        var provinces     = @json($provinces);

        // ── Tabulator column definitions ───────────────────────────
        var columns = [
            {
                title: 'Tên tổ chức', field: 'name', minWidth: 220, sorter: 'string', frozen: true,
                formatter: function (cell) {
                    var d = cell.getRow().getData();
                    return '<div>'
                        + '<a href="' + d.show_url + '" class="font-semibold text-sm hover:text-primary">' + d.name + '</a>'
                        + '<p class="text-xs text-base-content/40 font-mono">' + d.slug + '</p>'
                        + '</div>';
                },
            },
            {
                title: 'Mã số thuế', field: 'tax_code', width: 140, headerSort: false,
                formatter: function (cell) {
                    var v = cell.getValue();
                    return v ? '<span class="font-mono text-xs">' + v + '</span>' : '<span class="opacity-30">—</span>';
                },
            },
            {
                title: 'Ngành nghề', field: 'industry', minWidth: 150, sorter: 'string',
                formatter: function (cell) {
                    return cell.getValue() || '<span class="opacity-30">—</span>';
                },
            },
            {
                title: 'Email / SĐT', field: 'email', minWidth: 160, headerSort: false,
                formatter: function (cell) {
                    var d = cell.getRow().getData();
                    var parts = [];
                    if (d.email) parts.push('<p class="text-sm">' + d.email + '</p>');
                    if (d.phone) parts.push('<p class="text-xs opacity-60">' + d.phone + '</p>');
                    return parts.length ? parts.join('') : '<span class="opacity-30">—</span>';
                },
            },
            {
                title: 'Tỉnh/Thành', field: 'province_name', width: 160, sorter: 'string',
                formatter: function (cell) {
                    return cell.getValue() || '<span class="opacity-30">—</span>';
                },
            },
            {
                title: 'Phường/Xã', field: 'ward_name', width: 160, headerSort: false,
                formatter: function (cell) {
                    return cell.getValue() || '<span class="opacity-30">—</span>';
                },
            },
            {
                title: 'Thành viên', field: 'members_count', width: 110, hozAlign: 'center', sorter: 'number',
                formatter: function (cell) {
                    return '<span class="badge badge-ghost badge-sm">' + cell.getValue() + ' người</span>';
                },
            },
            {
                title: 'Trạng thái', field: 'status', width: 130, hozAlign: 'center', sorter: 'string',
                formatter: function (cell) {
                    var s     = cell.getValue();
                    var label = cell.getRow().getData().status_label;
                    if (s === 'active')    return '<span class="badge badge-success badge-sm gap-1"><span class="w-1.5 h-1.5 rounded-full bg-current"></span>' + label + '</span>';
                    if (s === 'suspended') return '<span class="badge badge-error badge-sm">' + label + '</span>';
                    return '<span class="badge badge-ghost badge-sm">' + label + '</span>';
                },
            },
            {
                title: 'Ngày tạo', field: 'created_at', width: 110, hozAlign: 'center', sorter: 'string',
            },
            {
                title: 'Thao tác', field: 'id', width: 110, hozAlign: 'center', headerSort: false, frozen: true,
                formatter: function (cell) {
                    var d = cell.getRow().getData();
                    // Store delete data in data-* attributes — avoids any quote escaping in JS source
                    return '<div class="flex items-center justify-center gap-1">'
                        + '<a href="' + d.show_url + '" class="btn btn-ghost btn-xs btn-square" title="Xem">'
                        +   '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>'
                        + '</a>'
                        + '<a href="' + d.edit_url + '" class="btn btn-ghost btn-xs btn-square" title="Sửa">'
                        +   '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'
                        + '</a>'
                        + '<button class="btn btn-ghost btn-xs btn-square text-error" title="Xóa"'
                        +   ' data-url="' + d.delete_url + '"'
                        +   ' data-name="' + d.name.replace(/&/g, '&amp;').replace(/"/g, '&quot;') + '"'
                        +   ' onclick="window.orgDeleteConfirm(this.dataset.url, this.dataset.name)">'
                        +   '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                        + '</button>'
                        + '</div>';
                },
            },
        ];

        return {
            filters:      { name: '', province_code: '', ward_code: '' },
            provinces:    provinces,
            wards:        [],
            loadingWards: false,

            init: function () {
                var self = this;
                var TabulatorFull = window.Tabulator;

                if (!TabulatorFull) {
                    console.error('[OrgList] Tabulator not loaded');
                    return;
                }

                tableInstance = new TabulatorFull('#org-table', {
                    ajaxURL:    apiUrl,
                    ajaxConfig: { headers: { 'X-Requested-With': 'XMLHttpRequest' } },
                    ajaxParams: function () {
                        var p = {};
                        if (self.filters.name)          p.name          = self.filters.name;
                        if (self.filters.province_code) p.province_code = self.filters.province_code;
                        if (self.filters.ward_code)     p.ward_code     = self.filters.ward_code;
                        return p;
                    },
                    ajaxResponse: function (_url, _params, res) { return res; },

                    pagination:             true,
                    paginationMode:         'remote',
                    paginationSize:         25,
                    paginationSizeSelector: [10, 25, 50, 100],
                    paginationCounter:      'rows',

                    sortMode:    'remote',
                    initialSort: [{ column: 'created_at', dir: 'desc' }],

                    layout:           'fitColumns',
                    responsiveLayout: 'collapse',
                    movableColumns:   true,
                    height:           '70vh',

                    locale: 'vi-VN',
                    langs: {
                        'vi-VN': {
                            pagination: {
                                page_size:  'Dòng/trang',
                                page_title: 'Trang',
                                first: '«', last: '»', prev: '‹', next: '›',
                                first_title: 'Trang đầu', last_title:  'Trang cuối',
                                prev_title:  'Trang trước', next_title: 'Trang sau',
                                counter: { showing: '', of: 'trong', rows: 'dòng', pages: 'trang' },
                            },
                        },
                    },

                    columns: columns,

                    placeholder: '<div class="py-16 text-center opacity-40">'
                        + '<svg class="w-12 h-12 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>'
                        + '<p>Không có dữ liệu</p></div>',
                });
            },

            refresh: function () {
                if (tableInstance) tableInstance.replaceData();
            },

            onProvinceChange: function () {
                var self = this;
                self.filters.ward_code = '';
                self.wards = [];
                if (!self.filters.province_code) { self.refresh(); return; }

                self.loadingWards = true;
                fetch(wardsApiBase + '/' + self.filters.province_code + '/wards')
                    .then(function (r) { return r.json(); })
                    .then(function (data) { self.wards = data; })
                    .finally(function () { self.loadingWards = false; self.refresh(); });
            },

            reset: function () {
                this.filters = { name: '', province_code: '', ward_code: '' };
                this.wards   = [];
                this.refresh();
            },
        };
    });
});
</script>

{{-- Tabulator theme override — integrates with DaisyUI CSS tokens ───────── --}}
<style>
#org-table .tabulator { border: none; border-radius: 0; background: transparent; font-size: 0.8125rem; }
#org-table .tabulator-header { background: oklch(var(--b2)); border-bottom: 1px solid oklch(var(--b3)); color: oklch(var(--bc) / 0.65); font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; }
#org-table .tabulator-col { background: transparent; border-right: 1px solid oklch(var(--b3)); }
#org-table .tabulator-col:last-child { border-right: none; }
#org-table .tabulator-col.tabulator-sortable:hover { background: oklch(var(--b3)); }
#org-table .tabulator-row { background: oklch(var(--b1)); border-bottom: 1px solid oklch(var(--b2)); }
#org-table .tabulator-row:hover { background: oklch(var(--b2) / 0.6); }
#org-table .tabulator-row .tabulator-cell { border-right: 1px solid oklch(var(--b2)); color: oklch(var(--bc)); padding: 0.5rem 0.75rem; }
#org-table .tabulator-row .tabulator-cell:last-child { border-right: none; }
#org-table .tabulator-footer { background: oklch(var(--b2) / 0.5); border-top: 1px solid oklch(var(--b3)); }
#org-table .tabulator-paginator { color: oklch(var(--bc) / 0.7); }
#org-table .tabulator-page { background: transparent; border: 1px solid oklch(var(--b3)); color: oklch(var(--bc)); border-radius: 0.375rem; padding: 0.2rem 0.5rem; margin: 0 1px; }
#org-table .tabulator-page:hover:not([disabled]) { background: oklch(var(--b3)); }
#org-table .tabulator-page.active { background: oklch(var(--p)); color: oklch(var(--pc)); border-color: oklch(var(--p)); }
#org-table .tabulator-page[disabled] { opacity: 0.35; }
#org-table .tabulator-page-size { background: oklch(var(--b1)); border: 1px solid oklch(var(--b3)); color: oklch(var(--bc)); border-radius: 0.375rem; padding: 0.2rem 0.4rem; }
#org-table .tabulator-frozen.tabulator-frozen-right { box-shadow: -2px 0 4px oklch(var(--b3) / 0.5); }
#org-table .tabulator-frozen.tabulator-frozen-left  { box-shadow:  2px 0 4px oklch(var(--b3) / 0.5); }
#org-table .tabulator-tableholder::-webkit-scrollbar { width: 6px; height: 6px; }
#org-table .tabulator-tableholder::-webkit-scrollbar-track { background: oklch(var(--b2)); }
#org-table .tabulator-tableholder::-webkit-scrollbar-thumb { background: oklch(var(--b3)); border-radius: 3px; }
</style>
@endpush
