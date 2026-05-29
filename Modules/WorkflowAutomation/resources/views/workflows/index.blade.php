@extends('layouts.backend')
@section('title', 'Workflow Automation')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <span class="current">Workflow</span>
</nav>
@endsection

@section('content')
<div x-data="wfListPage">

    {{-- ── Page header ──────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Workflow Automation</h1>
            <p class="text-sm text-base-content/50 mt-0.5">Tự động hóa quy trình theo sự kiện</p>
        </div>
        <div class="flex items-center gap-2">
            @can(\App\Enums\PermissionEnum::WORKFLOW_EDIT->value)
            <a href="{{ route('workflows.create') }}" class="btn btn-primary btn-sm gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tạo Workflow
            </a>
            @endcan
        </div>
    </div>

    {{-- ── Flash messages ────────────────────────────────────────────────────── --}}
    @foreach(['success','info','error'] as $type)
    @if(session($type))
    <div class="alert alert-{{ $type }} text-sm py-2 px-4 rounded-lg mb-4">{{ session($type) }}</div>
    @endif
    @endforeach

    {{-- ── Filter bar ──────────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200 mb-4">
        <div class="card-body py-3 px-4">
            <div class="flex flex-wrap gap-3 items-end">
                <div class="form-control flex-1 min-w-52">
                    <label class="label py-0.5">
                        <span class="label-text text-xs font-medium">Tìm kiếm</span>
                    </label>
                    <div class="input input-sm input-bordered flex items-center gap-2 bg-base-100">
                        <svg class="w-3.5 h-3.5 text-base-content/40 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" x-model="filters.search" @input.debounce.350ms="refresh()"
                               placeholder="Tên, trigger type..." class="grow bg-transparent outline-none text-sm"/>
                    </div>
                </div>
                <div class="form-control w-44">
                    <label class="label py-0.5"><span class="label-text text-xs font-medium">Trạng thái</span></label>
                    <select class="select select-sm select-bordered" x-model="filters.is_active" @change="refresh()">
                        <option value="">Tất cả</option>
                        <option value="1">Đang bật</option>
                        <option value="0">Đang tắt</option>
                    </select>
                </div>
                <div class="form-control ml-auto">
                    <label class="label py-0.5 invisible"><span class="label-text text-xs">.</span></label>
                    <button @click="resetFilters()" x-show="hasFilters" x-transition
                            class="btn btn-ghost btn-sm gap-1.5 text-error">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Đặt lại
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tabulator table ──────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-0 overflow-hidden rounded-2xl tabulator-daisy">
            <div id="wf-table"></div>
        </div>
    </div>

</div>

{{-- ── Delete confirm modal ──────────────────────────────────────────────────── --}}
<dialog id="wfDeleteModal" class="modal">
    <div class="modal-box max-w-sm">
        <h3 class="font-bold text-lg text-error">Xác nhận xóa</h3>
        <p class="py-3 text-sm text-base-content/70">
            Bạn có chắc muốn xóa workflow
            <strong id="wfDeleteName" class="text-base-content"></strong>?
        </p>
        <div class="modal-action mt-4">
            <button id="wfConfirmDelete" class="btn btn-error btn-sm">Xóa</button>
            <button class="btn btn-ghost btn-sm" onclick="wfDeleteModal.close()">Hủy</button>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
@endsection

@push('styles')
<x-tabulator-theme />
@endpush

@push('scripts')
@vite(['resources/js/modules/tabulator.js'], 'build/backend')
<script>
var CSRF_TOKEN = '{{ csrf_token() }}';
var API_URL    = '{{ route('backend.api.workflows.index') }}';
var CAN_EDIT   = {{ auth()->user()->can(\App\Enums\PermissionEnum::WORKFLOW_EDIT->value) ? 'true' : 'false' }};
var CAN_DELETE = {{ auth()->user()->can(\App\Enums\PermissionEnum::WORKFLOW_FULL_CONFIG->value) ? 'true' : 'false' }};

function esc(v) {
    if (v == null) return '';
    return String(v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

var COLUMNS = [
    {
        title: 'Tên workflow', field: 'name', minWidth: 220, sorter: 'string',
        formatter: function (cell) {
            var d = cell.getRow().getData();
            var badge = d.is_active
                ? '<span class="badge badge-xs badge-success ml-1.5">Bật</span>'
                : '<span class="badge badge-xs badge-ghost ml-1.5">Tắt</span>';
            return '<div>'
                + '<a href="/dashboard/workflows/' + d.id + '" class="font-semibold text-sm hover:text-primary">' + esc(d.name) + '</a>'
                + badge
                + '<p class="text-xs text-base-content/40 mt-0.5">' + esc(d.trigger_type) + '</p>'
                + '</div>';
        },
    },
    {
        title: 'Lần chạy', field: 'run_count', width: 110, hozAlign: 'center', sorter: 'number',
        formatter: function (cell) {
            return '<span class="badge badge-ghost badge-sm">' + esc(cell.getValue() || 0) + '</span>';
        },
    },
    {
        title: 'Trạng thái gần nhất', field: 'last_run_badge', width: 170, hozAlign: 'center', headerSort: false,
        formatter: function (cell) {
            var d = cell.getRow().getData();
            if (!d.last_run_badge) return '<span class="text-base-content/25 text-xs">Chưa chạy</span>';
            return '<span class="badge badge-sm badge-soft ' + esc(d.last_run_badge) + '">' + esc(d.last_run_label) + '</span>';
        },
    },
    {
        title: 'Lần chạy gần nhất', field: 'last_run_at', width: 160, hozAlign: 'center', sorter: 'string',
        formatter: function (cell) {
            var v = cell.getValue();
            if (!v) return '<span class="text-base-content/25 text-xs">—</span>';
            return '<span class="text-xs">' + esc(new Date(v).toLocaleString('vi-VN')) + '</span>';
        },
    },
    {
        title: 'Ưu tiên', field: 'priority', width: 90, hozAlign: 'center', sorter: 'number',
        formatter: function (cell) {
            return '<span class="text-sm font-mono">' + esc(cell.getValue()) + '</span>';
        },
    },
    {
        title: 'Thao tác', field: 'id', width: 130, hozAlign: 'center', headerSort: false,
        formatter: function (cell) {
            var d   = cell.getRow().getData();
            var html = '<div class="flex items-center justify-center gap-1">';

            html += '<a href="/dashboard/workflows/' + d.id + '" class="btn btn-ghost btn-xs btn-square text-base-content/40 hover:text-info" title="Xem">'
                + '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>'
                + '</a>';

            if (CAN_EDIT) {
                html += '<a href="/dashboard/workflows/' + d.id + '/edit" class="btn btn-ghost btn-xs btn-square text-base-content/40 hover:text-warning" title="Sửa">'
                    + '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'
                    + '</a>';
                html += '<button class="btn btn-ghost btn-xs btn-square text-base-content/40 hover:text-base-content" title="Toggle"'
                    + ' onclick="wfToggle(' + d.id + ', this)">'
                    + (d.is_active
                        ? '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>'
                        : '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>')
                    + '</button>';
            }

            if (CAN_DELETE) {
                html += '<button class="btn btn-ghost btn-xs btn-square text-error/30 hover:text-error" title="Xóa"'
                    + ' onclick="wfDeleteConfirm(' + d.id + ', \'' + esc(d.name).replace(/'/g,"\\'") + '\')">'
                    + '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                    + '</button>';
            }

            html += '</div>';
            return html;
        },
    },
];

var wfTableInst    = null;
var pendingDeleteId = null;

window.wfToggle = async function (id, btn) {
    btn.disabled = true;
    try {
        var res = await fetch('/dashboard/workflows/' + id + '/toggle', {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
        });
        if (res.ok) wfTableInst && wfTableInst.replaceData();
    } catch (e) { console.error(e); }
    finally { btn.disabled = false; }
};

window.wfDeleteConfirm = function (id, name) {
    pendingDeleteId = id;
    document.getElementById('wfDeleteName').textContent = '"' + name + '"';
    document.getElementById('wfDeleteModal').showModal();
};

document.getElementById('wfConfirmDelete').addEventListener('click', async function () {
    if (!pendingDeleteId) return;
    var btn = this;
    btn.disabled = true; btn.textContent = 'Đang xóa...';
    try {
        var res = await fetch('/dashboard/workflows/' + pendingDeleteId, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest',
                       'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: '_method=DELETE',
        });
        if (res.ok) {
            document.getElementById('wfDeleteModal').close();
            wfTableInst && wfTableInst.replaceData();
        } else {
            var d = await res.json().catch(() => ({}));
            alert(d.message || 'Xóa thất bại.');
        }
    } catch (e) { alert('Lỗi kết nối.'); }
    finally { btn.disabled = false; btn.textContent = 'Xóa'; pendingDeleteId = null; }
});

document.addEventListener('alpine:init', function () {
    Alpine.data('wfListPage', function () {
        return {
            filters: { search: '', is_active: '' },

            get hasFilters() { return !!(this.filters.search || this.filters.is_active !== ''); },

            init() {
                this.$nextTick(() => this._setup());
            },

            _setup() {
                var self = this;
                wfTableInst = new window.Tabulator('#wf-table', {
                    ajaxURL:    API_URL,
                    ajaxConfig: { headers: { 'X-Requested-With': 'XMLHttpRequest' } },
                    ajaxParams: function () {
                        var p = {}, f = self.filters;
                        if (f.search)              p.search    = f.search;
                        if (f.is_active !== '')    p.is_active = f.is_active;
                        return p;
                    },
                    ajaxResponse: function (_u, _p, res) { return res; },

                    pagination:             true,
                    paginationMode:         'remote',
                    paginationSize:         20,
                    paginationSizeSelector: [10, 20, 50],
                    paginationCounter:      'rows',
                    sortMode:               'remote',
                    initialSort:            [{ column: 'created_at', dir: 'desc' }],

                    layout:   'fitColumns',
                    height:   '65vh',
                    locale:   'vi-VN',
                    langs: { 'vi-VN': { pagination: {
                        page_size: 'Dòng/trang', first: '«', last: '»', prev: '‹', next: '›',
                        counter: { showing: '', of: 'trong', rows: 'dòng', pages: 'trang' },
                    }}},

                    columns: COLUMNS,
                    placeholder: '<div class="py-16 text-center opacity-40">'
                        + '<p class="text-sm">Chưa có workflow nào</p></div>',
                });
            },

            refresh() { wfTableInst && wfTableInst.replaceData(); },

            resetFilters() {
                this.filters = { search: '', is_active: '' };
                this.refresh();
            },
        };
    });
});
</script>
@endpush
