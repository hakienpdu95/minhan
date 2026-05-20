@extends('layouts.backend')
@section('title', 'Danh sách tổ chức')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <span class="current">Tổ chức</span>
</nav>
@endsection

@section('content')
<div x-data="orgListPage">

    {{-- ── Page header ─────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Tổ chức</h1>
            <p class="text-sm text-base-content/50 mt-0.5">Quản lý tất cả tổ chức trong hệ thống</p>
        </div>
        <div class="flex items-center gap-2">
            {{-- Column visibility dropdown --}}
            <div class="dropdown dropdown-end">
                <label tabindex="0" class="btn btn-ghost btn-sm gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                    </svg>
                    Cột
                </label>
                <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box shadow-lg border border-base-200 w-48 z-50 p-2">
                    <template x-for="col in toggleableCols" :key="col.field">
                        <li>
                            <label class="flex items-center gap-2 cursor-pointer py-1.5 px-2 rounded-lg hover:bg-base-200">
                                <input type="checkbox" class="checkbox checkbox-xs"
                                       :checked="!hiddenCols.includes(col.field)"
                                       @change="toggleCol(col.field)"/>
                                <span x-text="col.title" class="text-sm"></span>
                            </label>
                        </li>
                    </template>
                </ul>
            </div>
            <a href="{{ route('backend.organizations.create') }}" class="btn btn-primary btn-sm gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Thêm tổ chức
            </a>
        </div>
    </div>

    {{-- ── Filter bar ───────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200 mb-4">
        <div class="card-body py-3 px-4 space-y-3">

            {{-- Row 1: text search + location dropdowns --}}
            <div class="flex flex-wrap gap-3 items-end">

                {{-- Global search --}}
                <div class="form-control flex-1 min-w-52">
                    <label class="label py-0.5">
                        <span class="label-text text-xs font-medium">Tìm kiếm</span>
                        <span class="label-text-alt text-xs text-base-content/40">Tên, MST, Email, SĐT</span>
                    </label>
                    <div class="input input-sm input-bordered flex items-center gap-2 bg-base-100">
                        <svg class="w-3.5 h-3.5 text-base-content/40 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input id="filter-search" type="text"
                               x-model="filters.search"
                               @input.debounce.350ms="onSearchInput()"
                               placeholder="Nhập từ khóa..."
                               class="grow bg-transparent outline-none text-sm"/>
                        <button x-show="filters.search" @click="clearSearch()"
                                class="text-base-content/30 hover:text-base-content transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Province TomSelect --}}
                <div class="form-control w-56">
                    <label class="label py-0.5">
                        <span class="label-text text-xs font-medium">Tỉnh / Thành phố</span>
                    </label>
                    <select id="filter-province" class="select select-sm select-bordered w-full"></select>
                </div>

                {{-- Ward TomSelect --}}
                <div class="form-control w-56">
                    <label class="label py-0.5">
                        <span class="label-text text-xs font-medium">Phường / Xã</span>
                    </label>
                    <select id="filter-ward" class="select select-sm select-bordered w-full"></select>
                </div>

                {{-- Status TomSelect --}}
                <div class="form-control w-48">
                    <label class="label py-0.5">
                        <span class="label-text text-xs font-medium">Trạng thái</span>
                    </label>
                    <select id="filter-status" class="select select-sm select-bordered w-full"></select>
                </div>

            </div>

            {{-- Row 2: date range + presets --}}
            <div class="flex flex-wrap gap-3 items-end">

                {{-- Date range picker --}}
                <div class="form-control w-64">
                    <label class="label py-0.5">
                        <span class="label-text text-xs font-medium">Ngày tạo</span>
                    </label>
                    <div class="input input-sm input-bordered flex items-center gap-2 bg-base-100">
                        <svg class="w-3.5 h-3.5 text-base-content/40 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <input id="filter-date" type="text" readonly
                               placeholder="Chọn khoảng ngày..."
                               class="grow bg-transparent outline-none text-sm cursor-pointer"/>
                        <button x-show="filters.date_from" @click="clearDate()"
                                class="text-base-content/30 hover:text-base-content transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Date presets --}}
                <div class="form-control">
                    <label class="label py-0.5 invisible"><span class="label-text text-xs">.</span></label>
                    <div class="flex gap-1">
                        <button @click="setDatePreset('today')"
                                :class="activeDatePreset === 'today' ? 'btn-primary' : 'btn-ghost'"
                                class="btn btn-xs">Hôm nay</button>
                        <button @click="setDatePreset('week')"
                                :class="activeDatePreset === 'week' ? 'btn-primary' : 'btn-ghost'"
                                class="btn btn-xs">Tuần này</button>
                        <button @click="setDatePreset('month')"
                                :class="activeDatePreset === 'month' ? 'btn-primary' : 'btn-ghost'"
                                class="btn btn-xs">Tháng này</button>
                        <button @click="setDatePreset('year')"
                                :class="activeDatePreset === 'year' ? 'btn-primary' : 'btn-ghost'"
                                class="btn btn-xs">Năm nay</button>
                    </div>
                </div>

                {{-- Reset all --}}
                <div class="form-control ml-auto">
                    <label class="label py-0.5 invisible"><span class="label-text text-xs">.</span></label>
                    <button @click="reset()" x-show="hasFilters" x-transition
                            class="btn btn-ghost btn-sm gap-1.5 text-error">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Đặt lại
                    </button>
                </div>

            </div>

            {{-- Active filter chips --}}
            <div x-show="activeChips.length > 0" x-transition class="flex flex-wrap gap-2 pt-1 border-t border-base-200">
                <span class="text-xs text-base-content/40 self-center">Đang lọc:</span>
                <template x-for="chip in activeChips" :key="chip.key">
                    <span class="badge badge-sm gap-1 cursor-pointer hover:badge-error transition-colors"
                          @click="removeChip(chip.key)">
                        <span x-text="chip.label"></span>
                        <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </span>
                </template>
            </div>

        </div>
    </div>

    {{-- ── Tabulator table ──────────────────────────────────────────────── --}}
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

@push('styles')
<style>
/* ── Tabulator — DaisyUI theme ──────────────────────────────────────────── */
#org-table .tabulator { border:none; border-radius:0; background:transparent; font-size:.8125rem; }
#org-table .tabulator-header { background:oklch(var(--b2)); border-bottom:1px solid oklch(var(--b3)); color:oklch(var(--bc)/.65); font-weight:600; font-size:.75rem; text-transform:uppercase; letter-spacing:.04em; }
#org-table .tabulator-col { background:transparent; border-right:1px solid oklch(var(--b3)); }
#org-table .tabulator-col:last-child { border-right:none; }
#org-table .tabulator-col.tabulator-sortable:hover { background:oklch(var(--b3)); }
#org-table .tabulator-row { background:oklch(var(--b1)); border-bottom:1px solid oklch(var(--b2)); }
#org-table .tabulator-row:hover { background:oklch(var(--b2)/.6); }
#org-table .tabulator-row .tabulator-cell { border-right:1px solid oklch(var(--b2)); color:oklch(var(--bc)); padding:.5rem .75rem; }
#org-table .tabulator-row .tabulator-cell:last-child { border-right:none; }
#org-table .tabulator-footer { background:oklch(var(--b2)/.5); border-top:1px solid oklch(var(--b3)); }
#org-table .tabulator-paginator { color:oklch(var(--bc)/.7); }
#org-table .tabulator-page { background:transparent; border:1px solid oklch(var(--b3)); color:oklch(var(--bc)); border-radius:.375rem; padding:.2rem .5rem; margin:0 1px; }
#org-table .tabulator-page:hover:not([disabled]) { background:oklch(var(--b3)); }
#org-table .tabulator-page.active { background:oklch(var(--p)); color:oklch(var(--pc)); border-color:oklch(var(--p)); }
#org-table .tabulator-page[disabled] { opacity:.35; }
#org-table .tabulator-page-size { background:oklch(var(--b1)); border:1px solid oklch(var(--b3)); color:oklch(var(--bc)); border-radius:.375rem; padding:.2rem .4rem; }
#org-table .tabulator-frozen.tabulator-frozen-right { box-shadow:-2px 0 4px oklch(var(--b3)/.5); }
#org-table .tabulator-frozen.tabulator-frozen-left  { box-shadow: 2px 0 4px oklch(var(--b3)/.5); }
#org-table .tabulator-tableholder::-webkit-scrollbar { width:6px; height:6px; }
#org-table .tabulator-tableholder::-webkit-scrollbar-track { background:oklch(var(--b2)); }
#org-table .tabulator-tableholder::-webkit-scrollbar-thumb { background:oklch(var(--b3)); border-radius:3px; }
/* Loading overlay */
#org-table .tabulator-loader { background:oklch(var(--b1)/.7) !important; }
#org-table .tabulator-loader-msg { background:oklch(var(--b2)) !important; border:1px solid oklch(var(--b3)) !important; border-radius:.5rem !important; color:oklch(var(--bc)) !important; }

/* ── TomSelect — DaisyUI theme ──────────────────────────────────────────── */
.ts-wrapper.single .ts-control { background:oklch(var(--b1)); border-color:oklch(var(--b3)); color:oklch(var(--bc)); border-radius:.375rem; min-height:2rem; padding:.25rem .5rem; font-size:.875rem; }
.ts-wrapper.single.focus .ts-control { border-color:oklch(var(--p)); outline:none; box-shadow:0 0 0 2px oklch(var(--p)/.2); }
.ts-dropdown { background:oklch(var(--b1)); border:1px solid oklch(var(--b3)); border-radius:.5rem; box-shadow:0 4px 16px rgba(0,0,0,.15); z-index:9999; }
.ts-dropdown .ts-option { color:oklch(var(--bc)); padding:.4rem .75rem; font-size:.875rem; }
.ts-dropdown .ts-option:hover, .ts-dropdown .ts-option.active { background:oklch(var(--b2)); color:oklch(var(--bc)); }
.ts-dropdown .ts-option.selected { background:oklch(var(--p)/.15); color:oklch(var(--p)); }
.ts-wrapper .clear-button { color:oklch(var(--bc)/.4); }
.ts-wrapper .clear-button:hover { color:oklch(var(--bc)); }
.ts-control input { color:oklch(var(--bc)) !important; }
</style>
@endpush

@push('scripts')
@vite(['resources/js/modules/tabulator.js', 'resources/js/modules/tom-select.js', 'resources/js/modules/flatpickr.js'], 'build/backend')
<script>
// ── Security: HTML escape for use in innerHTML formatters ────────────────────
function esc(v) {
    if (v == null) return '';
    return String(v).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

window.orgDeleteConfirm = function (url, name) {
    document.getElementById('deleteForm').action = url;
    document.getElementById('deleteItemName').textContent = name;
    deleteModal.showModal();
};

document.addEventListener('alpine:init', function () {
    // ── Closure-scoped lib instances (non-reactive) ──────────────────────
    var tableInst    = null;
    var provTsInst   = null;
    var wardTsInst   = null;
    var statusTsInst = null;
    var dateFpInst   = null;
    var wardsCache   = {}; // { province_code: [{ward_code, name}] }
    var settingPreset = false;

    var API_URL   = '{{ route('backend.api.organizations') }}';
    var WARDS_API = '{{ url('/api/provinces') }}';
    var PROVINCES = @json($provinces);
    var STATUSES  = @json($statuses);
    var LS_COLS   = 'org-list-hidden-cols';

    // ── Helpers ──────────────────────────────────────────────────────────
    function isoDate(d) {
        return d.getFullYear() + '-'
            + String(d.getMonth() + 1).padStart(2, '0') + '-'
            + String(d.getDate()).padStart(2, '0');
    }

    function displayDate(iso) {
        if (!iso) return '';
        var p = iso.split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    }

    function presetRange(preset) {
        var now = new Date();
        var y = now.getFullYear(), m = now.getMonth(), d = now.getDate();
        if (preset === 'today')  return [new Date(y, m, d), new Date(y, m, d)];
        if (preset === 'week') {
            var dow = now.getDay() === 0 ? 6 : now.getDay() - 1;
            return [new Date(y, m, d - dow), new Date(y, m, d - dow + 6)];
        }
        if (preset === 'month') return [new Date(y, m, 1), new Date(y, m + 1, 0)];
        if (preset === 'year')  return [new Date(y, 0, 1), new Date(y, 11, 31)];
        return [null, null];
    }

    // Update TomSelect placeholder in settings AND in the visible input element
    function tsSetPlaceholder(ts, text) {
        ts.settings.placeholder = text;
        if (ts.control_input) ts.control_input.setAttribute('placeholder', text);
    }

    // ── Column definitions ────────────────────────────────────────────────
    var COLUMNS = [
        {
            title: 'Tên tổ chức', field: 'name', minWidth: 220, sorter: 'string', frozen: true,
            formatter: function (cell) {
                var d = cell.getRow().getData();
                return '<div>'
                    + '<a href="' + esc(d.show_url) + '" class="font-semibold text-sm hover:text-primary">' + esc(d.name) + '</a>'
                    + '<p class="text-xs text-base-content/40 font-mono">' + esc(d.slug) + '</p>'
                    + '</div>';
            },
        },
        {
            title: 'Mã số thuế', field: 'tax_code', width: 140, headerSort: false,
            formatter: function (cell) {
                var v = cell.getValue();
                return v ? '<span class="font-mono text-xs">' + esc(v) + '</span>' : '<span class="opacity-30">—</span>';
            },
        },
        {
            title: 'Ngành nghề', field: 'industry', minWidth: 150, sorter: 'string',
            formatter: function (cell) { return esc(cell.getValue()) || '<span class="opacity-30">—</span>'; },
        },
        {
            title: 'Email / SĐT', field: 'email', minWidth: 160, headerSort: false,
            formatter: function (cell) {
                var d = cell.getRow().getData();
                var parts = [];
                if (d.email) parts.push('<p class="text-sm">' + esc(d.email) + '</p>');
                if (d.phone) parts.push('<p class="text-xs opacity-60">' + esc(d.phone) + '</p>');
                return parts.length ? parts.join('') : '<span class="opacity-30">—</span>';
            },
        },
        {
            title: 'Tỉnh/Thành', field: 'province_name', width: 160, sorter: 'string',
            formatter: function (cell) { return esc(cell.getValue()) || '<span class="opacity-30">—</span>'; },
        },
        {
            title: 'Phường/Xã', field: 'ward_name', width: 160, headerSort: false,
            formatter: function (cell) { return esc(cell.getValue()) || '<span class="opacity-30">—</span>'; },
        },
        {
            title: 'Thành viên', field: 'members_count', width: 110, hozAlign: 'center', sorter: 'number',
            formatter: function (cell) {
                return '<span class="badge badge-ghost badge-sm">' + esc(cell.getValue()) + ' người</span>';
            },
        },
        {
            title: 'Trạng thái', field: 'status', width: 130, hozAlign: 'center', sorter: 'string',
            formatter: function (cell) {
                var s = cell.getValue(), label = esc(cell.getRow().getData().status_label);
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
                return '<div class="flex items-center justify-center gap-1">'
                    + '<a href="' + esc(d.show_url) + '" class="btn btn-ghost btn-xs btn-square" title="Xem">'
                    +   '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>'
                    + '</a>'
                    + '<a href="' + esc(d.edit_url) + '" class="btn btn-ghost btn-xs btn-square" title="Sửa">'
                    +   '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'
                    + '</a>'
                    + '<button class="btn btn-ghost btn-xs btn-square text-error" title="Xóa"'
                    +   ' data-url="' + esc(d.delete_url) + '" data-name="' + esc(d.name) + '"'
                    +   ' onclick="window.orgDeleteConfirm(this.dataset.url,this.dataset.name)">'
                    +   '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                    + '</button>'
                    + '</div>';
            },
        },
    ];

    // ── Alpine component ──────────────────────────────────────────────────
    Alpine.data('orgListPage', function () {
        return {
            filters: { search: '', province_code: '', ward_code: '', status: '', date_from: '', date_to: '' },
            wards:           [],
            activeDatePreset: '',
            hiddenCols:      [],

            get toggleableCols() {
                return COLUMNS.filter(function (c) { return c.field !== 'id' && c.field !== 'name'; })
                              .map(function (c) { return { field: c.field, title: c.title }; });
            },

            get hasFilters() {
                var f = this.filters;
                return !!(f.search || f.province_code || f.ward_code || f.status || f.date_from);
            },

            get activeChips() {
                var chips = [], f = this.filters;
                if (f.search)        chips.push({ key: 'search',   label: 'Tìm: ' + f.search });
                if (f.province_code) {
                    var prov = PROVINCES.find(function (p) { return p.province_code === f.province_code; });
                    chips.push({ key: 'province', label: prov ? prov.name : f.province_code });
                }
                if (f.ward_code) {
                    var ward = this.wards.find(function (w) { return w.ward_code === f.ward_code; });
                    chips.push({ key: 'ward', label: ward ? ward.name : f.ward_code });
                }
                if (f.status) {
                    var st = STATUSES.find(function (s) { return s.value === f.status; });
                    chips.push({ key: 'status', label: st ? st.text : f.status });
                }
                if (f.date_from && f.date_to)
                    chips.push({ key: 'date', label: displayDate(f.date_from) + ' — ' + displayDate(f.date_to) });
                return chips;
            },

            init: function () {
                var self = this;
                // Restore filters and hidden-cols before libraries are ready
                self.loadState();
                try { self.hiddenCols = JSON.parse(localStorage.getItem(LS_COLS) || '[]'); } catch (e) {}
                // DOMContentLoaded fires after ALL deferred module scripts complete,
                // so window.Tabulator / TomSelect / initDateRangePicker are guaranteed ready.
                document.addEventListener('DOMContentLoaded', function () { self._setup(); }, { once: true });
            },

            _setup: function () {
                var self = this;

                // ── Tabulator ────────────────────────────────────────────
                tableInst = new window.Tabulator('#org-table', {
                    ajaxURL:    API_URL,
                    ajaxConfig: { headers: { 'X-Requested-With': 'XMLHttpRequest' } },
                    ajaxParams: function () {
                        var p = {}, f = self.filters;
                        if (f.search)        p.search        = f.search;
                        if (f.province_code) p.province_code = f.province_code;
                        if (f.ward_code)     p.ward_code     = f.ward_code;
                        if (f.status)        p.status        = f.status;
                        if (f.date_from)     p.date_from     = f.date_from;
                        if (f.date_to)       p.date_to       = f.date_to;
                        return p;
                    },
                    ajaxResponse: function (_u, _p, res) { return res; },

                    pagination:             true,
                    paginationMode:         'remote',
                    paginationSize:         25,
                    paginationSizeSelector: [10, 25, 50, 100],
                    paginationCounter:      'rows',
                    sortMode:               'remote',
                    initialSort:            [{ column: 'created_at', dir: 'desc' }],

                    layout:           'fitColumns',
                    responsiveLayout: 'collapse',
                    movableColumns:   true,
                    height:           '68vh',

                    locale: 'vi-VN',
                    langs: {
                        'vi-VN': {
                            pagination: {
                                page_size: 'Dòng/trang', page_title: 'Trang',
                                first: '«', last: '»', prev: '‹', next: '›',
                                first_title: 'Trang đầu', last_title: 'Trang cuối',
                                prev_title: 'Trang trước', next_title: 'Trang sau',
                                counter: { showing: '', of: 'trong', rows: 'dòng', pages: 'trang' },
                            },
                        },
                    },
                    columns: COLUMNS,
                    placeholder: '<div class="py-16 text-center opacity-40">'
                        + '<svg class="w-12 h-12 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>'
                        + '<p>Không có dữ liệu</p></div>',
                });

                // Restore hidden columns from localStorage
                self.hiddenCols.forEach(function (field) {
                    tableInst.hideColumn(field);
                });

                // ── Province TomSelect ───────────────────────────────────
                provTsInst = new window.TomSelect('#filter-province', {
                    dropdownParent: 'body',
                    placeholder:    'Tất cả tỉnh/thành...',
                    maxOptions:     null,
                    searchField:    ['text'],
                    plugins:        ['clear_button'],
                    options:        PROVINCES.map(function (p) { return { value: p.province_code, text: p.name }; }),
                    items:          self.filters.province_code ? [self.filters.province_code] : [],
                    onChange: function (val) {
                        self.filters.province_code = val || '';
                        self.onProvinceChange(val || '');
                    },
                    render: { no_results: function () { return '<div class="no-results p-3 text-sm opacity-50">Không tìm thấy</div>'; } },
                });

                // ── Ward TomSelect ───────────────────────────────────────
                wardTsInst = new window.TomSelect('#filter-ward', {
                    dropdownParent: 'body',
                    placeholder:    self.filters.province_code ? 'Tất cả phường/xã...' : 'Chọn tỉnh trước...',
                    maxOptions:     null,
                    searchField:    ['text'],
                    plugins:        ['clear_button'],
                    onChange: function (val) {
                        self.filters.ward_code = val || '';
                        self.saveState();
                        self.refresh();
                    },
                    render: { no_results: function () { return '<div class="no-results p-3 text-sm opacity-50">Không tìm thấy</div>'; } },
                });
                if (!self.filters.province_code) wardTsInst.disable();

                // ── Status TomSelect ─────────────────────────────────────
                statusTsInst = new window.TomSelect('#filter-status', {
                    dropdownParent: 'body',
                    placeholder:    'Tất cả trạng thái...',
                    plugins:        ['clear_button'],
                    options:        STATUSES,
                    items:          self.filters.status ? [self.filters.status] : [],
                    onChange: function (val) {
                        self.filters.status = val || '';
                        self.saveState();
                        self.refresh();
                    },
                });

                // ── Flatpickr date range ─────────────────────────────────
                dateFpInst = window.initDateRangePicker('#filter-date', {
                    disableMobile: true,
                    onChange: function (dates) {
                        if (settingPreset) return;
                        self.activeDatePreset = '';
                        if (dates.length === 2) {
                            self.filters.date_from = isoDate(dates[0]);
                            self.filters.date_to   = isoDate(dates[1]);
                        } else {
                            self.filters.date_from = '';
                            self.filters.date_to   = '';
                        }
                        self.saveState();
                        self.refresh();
                    },
                });

                if (self.filters.date_from && self.filters.date_to) {
                    dateFpInst.setDate([self.filters.date_from, self.filters.date_to], false);
                }

                // Restore ward list for URL state (silent — Tabulator's initial request already has the right filters)
                if (self.filters.province_code) {
                    self._loadWards(self.filters.province_code, self.filters.ward_code, true);
                }
            },

            // ── State persistence ─────────────────────────────────────────
            loadState: function () {
                var p = new URLSearchParams(location.search);
                if (p.has('q'))    this.filters.search        = p.get('q');
                if (p.has('prov')) this.filters.province_code = p.get('prov');
                if (p.has('ward')) this.filters.ward_code     = p.get('ward');
                if (p.has('st'))   this.filters.status        = p.get('st');
                if (p.has('from')) this.filters.date_from     = p.get('from');
                if (p.has('to'))   this.filters.date_to       = p.get('to');
                if (p.has('dpre')) this.activeDatePreset      = p.get('dpre');
            },

            saveState: function () {
                var p = new URLSearchParams(), f = this.filters;
                if (f.search)        p.set('q',    f.search);
                if (f.province_code) p.set('prov', f.province_code);
                if (f.ward_code)     p.set('ward', f.ward_code);
                if (f.status)        p.set('st',   f.status);
                if (f.date_from)     p.set('from', f.date_from);
                if (f.date_to)       p.set('to',   f.date_to);
                if (this.activeDatePreset) p.set('dpre', this.activeDatePreset);
                var qs = p.toString();
                history.replaceState(null, '', qs ? '?' + qs : location.pathname);
            },

            // ── Core actions ──────────────────────────────────────────────
            refresh: function () { if (tableInst) tableInst.replaceData(); },

            onSearchInput: function () { this.saveState(); this.refresh(); },

            clearSearch: function () { this.filters.search = ''; this.saveState(); this.refresh(); },

            setDatePreset: function (preset) {
                var range = presetRange(preset);
                if (!range[0]) return;
                settingPreset = true;
                this.activeDatePreset  = preset;
                this.filters.date_from = isoDate(range[0]);
                this.filters.date_to   = isoDate(range[1]);
                dateFpInst.setDate([range[0], range[1]], false);
                settingPreset = false;
                this.saveState();
                this.refresh();
            },

            clearDate: function () {
                this.activeDatePreset  = '';
                this.filters.date_from = '';
                this.filters.date_to   = '';
                if (dateFpInst) dateFpInst.clear(false);
                this.saveState();
                this.refresh();
            },

            onProvinceChange: function (code) {
                this.filters.ward_code = '';
                if (wardTsInst) { wardTsInst.clear(true); wardTsInst.clearOptions(); wardTsInst.disable(); }
                this.wards = [];
                this.saveState();
                this.refresh(); // refresh immediately — don't wait for wards to load
                if (code) this._loadWards(code, null, false);
            },

            // silent=true skips the final refresh (used during URL-state restoration
            // when Tabulator's initial request already carries the correct filters)
            _loadWards: function (code, pendingWard, silent) {
                var self = this;
                if (wardsCache[code]) {
                    self._applyWards(wardsCache[code], pendingWard, silent);
                    return;
                }
                tsSetPlaceholder(wardTsInst, 'Đang tải...');
                fetch(WARDS_API + '/' + code + '/wards')
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        wardsCache[code] = data;
                        self._applyWards(data, pendingWard, silent);
                    })
                    .catch(function () { tsSetPlaceholder(wardTsInst, 'Lỗi tải dữ liệu'); });
            },

            _applyWards: function (data, pendingWard, silent) {
                this.wards = data;
                if (!wardTsInst) return;
                data.forEach(function (w) { wardTsInst.addOption({ value: w.ward_code, text: w.name }); });
                tsSetPlaceholder(wardTsInst, 'Tất cả phường/xã...');
                wardTsInst.enable();
                if (pendingWard) {
                    wardTsInst.setValue(pendingWard, true); // silent — no onChange
                    this.filters.ward_code = pendingWard;
                }
                if (!silent) this.refresh();
            },

            removeChip: function (key) {
                if (key === 'search')   { this.filters.search = ''; }
                if (key === 'province') {
                    this.filters.province_code = '';
                    if (provTsInst) provTsInst.clear(true);
                    this.filters.ward_code = '';
                    if (wardTsInst) { wardTsInst.clear(true); wardTsInst.clearOptions(); wardTsInst.disable(); }
                    this.wards = [];
                }
                if (key === 'ward')   { this.filters.ward_code = ''; if (wardTsInst) wardTsInst.clear(true); }
                if (key === 'status') { this.filters.status = '';     if (statusTsInst) statusTsInst.clear(true); }
                if (key === 'date')   { this.clearDate(); return; }
                this.saveState();
                this.refresh();
            },

            reset: function () {
                this.filters = { search: '', province_code: '', ward_code: '', status: '', date_from: '', date_to: '' };
                this.activeDatePreset = '';
                this.wards = [];
                if (provTsInst)   provTsInst.clear(true);
                if (wardTsInst)   { wardTsInst.clear(true); wardTsInst.clearOptions(); wardTsInst.disable(); }
                if (statusTsInst) statusTsInst.clear(true);
                if (dateFpInst)   dateFpInst.clear(false);
                history.replaceState(null, '', location.pathname);
                this.refresh();
            },

            toggleCol: function (field) {
                if (this.hiddenCols.includes(field)) {
                    this.hiddenCols = this.hiddenCols.filter(function (f) { return f !== field; });
                    if (tableInst) tableInst.showColumn(field);
                } else {
                    this.hiddenCols.push(field);
                    if (tableInst) tableInst.hideColumn(field);
                }
                try { localStorage.setItem(LS_COLS, JSON.stringify(this.hiddenCols)); } catch (e) {}
            },
        };
    });
});
</script>
@endpush
