@extends('layouts.backend')
@section('title', 'Danh sách cơ hội')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <span class="current">Cơ hội (Lead)</span>
</nav>
@endsection

@section('content')
<div x-data="leadListPage">

    {{-- ── Page header ──────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Cơ hội bán hàng</h1>
            <p class="text-sm text-base-content/50 mt-0.5">Quản lý và theo dõi tất cả cơ hội trong tổ chức</p>
        </div>
        <div class="flex items-center gap-2">

            {{-- View toggle --}}
            <div class="join">
                <button class="join-item btn btn-sm"
                        :class="view === 'list' ? 'btn-primary' : 'btn-ghost'"
                        @click="view = 'list'" title="Danh sách">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </button>
                <button class="join-item btn btn-sm"
                        :class="view === 'kanban' ? 'btn-primary' : 'btn-ghost'"
                        @click="switchToKanban()" title="Kanban">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </button>
            </div>

            {{-- Column toggle (list only) --}}
            <div class="dropdown dropdown-end" x-show="view === 'list'" x-transition>
                <label tabindex="0" class="btn btn-ghost btn-sm gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                    </svg>
                    Cột
                </label>
                <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box shadow-lg border border-base-200 w-52 z-50 p-2">
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

            @can('create', \Modules\Lead\Models\Lead::class)
            <a href="{{ route('lead.create') }}" class="btn btn-primary btn-sm gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Thêm cơ hội
            </a>
            @endcan

        </div>
    </div>

    {{-- ── Flash messages ───────────────────────────────────────────── --}}
    @foreach(['success','info','error'] as $type)
    @if(session($type))
    <div class="alert alert-{{ $type }} text-sm py-2 px-4 rounded-lg mb-4">{{ session($type) }}</div>
    @endif
    @endforeach

    {{-- ── Filter bar ───────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200 mb-4">
        <div class="card-body py-3 px-4 space-y-3">

            <div class="flex flex-wrap gap-3 items-end">

                {{-- Search --}}
                <div class="form-control flex-1 min-w-52">
                    <label class="label py-0.5">
                        <span class="label-text text-xs font-medium">Tìm kiếm</span>
                        <span class="label-text-alt text-xs text-base-content/40">Tên, SĐT, Công ty, Tiêu đề</span>
                    </label>
                    <div class="input input-sm input-bordered flex items-center gap-2 bg-base-100">
                        <svg class="w-3.5 h-3.5 text-base-content/40 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input id="filter-search" type="text"
                               x-model="filters.search"
                               @input.debounce.350ms="onFilterChange()"
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

                {{-- Stage --}}
                <div class="form-control w-48">
                    <label class="label py-0.5"><span class="label-text text-xs font-medium">Tình trạng</span></label>
                    <select id="filter-stage" class="select select-sm select-bordered w-full"></select>
                </div>

                {{-- Source --}}
                <div class="form-control w-44">
                    <label class="label py-0.5"><span class="label-text text-xs font-medium">Nguồn</span></label>
                    <select id="filter-source" class="select select-sm select-bordered w-full"></select>
                </div>

                {{-- Status --}}
                <div class="form-control w-40">
                    <label class="label py-0.5"><span class="label-text text-xs font-medium">Trạng thái</span></label>
                    <select id="filter-status" class="select select-sm select-bordered w-full"></select>
                </div>

            </div>

            <div class="flex flex-wrap gap-3 items-end">

                {{-- Closing date range --}}
                <div class="form-control w-64">
                    <label class="label py-0.5">
                        <span class="label-text text-xs font-medium">Ngày chốt dự kiến</span>
                    </label>
                    <div class="input input-sm input-bordered flex items-center gap-2 bg-base-100">
                        <svg class="w-3.5 h-3.5 text-base-content/40 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <input id="filter-closing" type="text" readonly
                               placeholder="Chọn khoảng ngày..."
                               class="grow bg-transparent outline-none text-sm cursor-pointer"/>
                        <button x-show="filters.closing_after" @click="clearClosing()"
                                class="text-base-content/30 hover:text-base-content transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Quick presets --}}
                <div class="form-control">
                    <label class="label py-0.5 invisible"><span class="label-text text-xs">.</span></label>
                    <div class="flex gap-1">
                        <button @click="setClosingPreset('week')"
                                :class="closingPreset === 'week' ? 'btn-primary' : 'btn-ghost'"
                                class="btn btn-xs">7 ngày</button>
                        <button @click="setClosingPreset('month')"
                                :class="closingPreset === 'month' ? 'btn-primary' : 'btn-ghost'"
                                class="btn btn-xs">Tháng này</button>
                        <button @click="setClosingPreset('overdue')"
                                :class="closingPreset === 'overdue' ? 'btn-warning' : 'btn-ghost'"
                                class="btn btn-xs">Quá hạn</button>
                    </div>
                </div>

                {{-- Reset --}}
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

            {{-- Active chips --}}
            <div x-show="activeChips.length > 0" x-transition
                 class="flex flex-wrap gap-2 pt-1 border-t border-base-200">
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

    {{-- ── LIST VIEW: Tabulator ──────────────────────────────────────── --}}
    <div x-show="view === 'list'" x-transition>
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-0 overflow-hidden rounded-2xl tabulator-daisy">
                <div id="lead-table"></div>
            </div>
        </div>
    </div>

    {{-- ── KANBAN VIEW ───────────────────────────────────────────────── --}}
    <div x-show="view === 'kanban'" x-transition>

        <div x-show="kanbanLoading" class="flex justify-center items-center py-24">
            <span class="loading loading-spinner loading-lg text-primary"></span>
        </div>

        <div x-show="!kanbanLoading" class="flex gap-4 overflow-x-auto pb-4">
            <template x-for="col in kanbanBoard" :key="col.stage.id">
                <div class="flex-none w-72">

                    {{-- Column header --}}
                    <div class="flex items-center justify-between mb-3 px-1">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full"
                                  :style="'background:' + (col.stage.color || '#94a3b8')"></span>
                            <span class="font-semibold text-sm" x-text="col.stage.label"></span>
                        </div>
                        <span class="badge badge-ghost badge-sm" x-text="col.total"></span>
                    </div>

                    {{-- Cards --}}
                    <div class="space-y-2 min-h-16">
                        <template x-for="lead in col.leads" :key="lead.id">
                            <a :href="lead.show_url || ('/leads/' + lead.id)"
                               class="block card bg-base-100 border border-base-200 shadow-sm hover:shadow-md hover:border-primary/30 transition-all p-3">
                                <p class="font-medium text-sm line-clamp-2 mb-2" x-text="lead.title || lead.contact_name"></p>
                                <p class="text-xs text-base-content/50 mb-2" x-text="lead.contact_company || ''"></p>
                                <div class="flex items-center justify-between gap-1 mt-auto">
                                    <span class="text-xs font-medium text-success" x-show="lead.expected_value"
                                          x-text="formatCurrency(lead.expected_value, lead.currency)"></span>
                                    <span class="ml-auto">
                                        <template x-if="lead.lead_score >= 70">
                                            <span class="badge badge-xs badge-error" x-text="lead.lead_score + 'đ'"></span>
                                        </template>
                                        <template x-if="lead.lead_score >= 40 && lead.lead_score < 70">
                                            <span class="badge badge-xs badge-warning" x-text="lead.lead_score + 'đ'"></span>
                                        </template>
                                        <template x-if="lead.lead_score < 40 && lead.lead_score != null">
                                            <span class="badge badge-xs badge-ghost" x-text="lead.lead_score + 'đ'"></span>
                                        </template>
                                    </span>
                                </div>
                                <div class="flex items-center justify-between mt-2 pt-2 border-t border-base-200">
                                    <span class="text-xs text-base-content/40" x-text="lead.assignee_name || 'Chưa phân công'"></span>
                                    <span class="text-xs text-base-content/40" x-show="lead.expected_close_date"
                                          x-text="formatDate(lead.expected_close_date)"></span>
                                </div>
                            </a>
                        </template>

                        <div x-show="col.leads.length === 0"
                             class="rounded-xl border-2 border-dashed border-base-200 p-6 text-center">
                            <p class="text-xs text-base-content/25">Không có cơ hội</p>
                        </div>
                    </div>

                </div>
            </template>

            <div x-show="kanbanBoard.length === 0 && !kanbanLoading"
                 class="flex-1 flex items-center justify-center py-24">
                <p class="text-base-content/30 text-sm">Không có dữ liệu kanban</p>
            </div>
        </div>

    </div>

</div>

{{-- ── Delete confirm modal ──────────────────────────────────────────────── --}}
<dialog id="deleteModal" class="modal">
    <div class="modal-box max-w-sm">
        <h3 class="font-bold text-lg text-error">Xác nhận xóa</h3>
        <p class="py-3 text-sm text-base-content/70">
            Bạn có chắc muốn xóa cơ hội
            <strong id="deleteItemName" class="text-base-content"></strong>?
        </p>
        <p class="text-xs text-error/70">Toàn bộ lịch sử và hoạt động liên quan sẽ bị xóa theo.</p>
        <div class="modal-action mt-4">
            <button id="confirmDeleteBtn" class="btn btn-error btn-sm">Xóa</button>
            <button class="btn btn-ghost btn-sm" onclick="deleteModal.close()">Hủy</button>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

@endsection

@push('styles')
<x-tabulator-theme />
@endpush

@push('scripts')
@vite(['resources/js/modules/tabulator.js', 'resources/js/modules/tom-select.js', 'resources/js/modules/flatpickr.js'], 'build/backend')
<script>
// ── Constants ─────────────────────────────────────────────────────────────────
var CSRF_TOKEN    = '{{ csrf_token() }}';
var API_LISTING   = '{{ route('api.api.lead.listing') }}';
var API_KANBAN    = '{{ route('api.api.lead.kanban') }}';
var STAGES        = @json($stages->values());
var SOURCES       = @json($sources->values());
var MASK_CONTACT  = {{ $maskContact ? 'true' : 'false' }};
var CAN_DELETE    = {{ auth()->user()->can('delete', new \Modules\Lead\Models\Lead) ? 'true' : 'false' }};
var LS_COLS       = 'lead-list-hidden-cols';
var LS_VIEW       = 'lead-list-view';

var STATUSES = [
    { value: '1', text: 'Đang hoạt động' },
    { value: '2', text: 'Đã chốt' },
    { value: '3', text: 'Đã lưu trữ' },
    { value: '4', text: 'Tạm hoãn' },
];

// ── Helpers ───────────────────────────────────────────────────────────────────
function esc(v) {
    if (v == null) return '';
    return String(v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function isoDate(d) {
    return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
}
function formatMoney(val, currency) {
    if (val == null) return '';
    var n = parseFloat(val);
    if (isNaN(n)) return '';
    return n.toLocaleString('vi-VN') + ' ' + (currency || 'VND');
}
function stageColor(id) {
    var s = STAGES.find(function(s){ return s.id == id; });
    return s ? s.color : '#94a3b8';
}
function stageLabel(id) {
    var s = STAGES.find(function(s){ return s.id == id; });
    return s ? s.label : '—';
}
function sourceLabel(id) {
    var s = SOURCES.find(function(s){ return s.id == id; });
    return s ? s.label : '—';
}

// ── Column definitions ────────────────────────────────────────────────────────
var COLUMNS = [
    {
        title: 'Cơ hội / Khách hàng', field: 'title', minWidth: 220, sorter: 'string', frozen: true,
        formatter: function(cell) {
            var d = cell.getRow().getData();
            var name = MASK_CONTACT ? '<span class="text-base-content/30 italic text-xs">Ẩn</span>' : esc(d.contact_name);
            var company = MASK_CONTACT ? '' : (d.contact_company ? '<p class="text-xs text-base-content/40">' + esc(d.contact_company) + '</p>' : '');
            return '<div>'
                + '<a href="' + esc(d.show_url) + '" class="font-semibold text-sm hover:text-primary transition-colors">' + esc(d.title || d.contact_name) + '</a>'
                + (d.title && !MASK_CONTACT ? '<p class="text-xs text-base-content/50 mt-0.5">' + name + '</p>' : '')
                + company
                + '</div>';
        },
    },
    {
        title: 'SĐT', field: 'contact_phone', width: 130, headerSort: false,
        formatter: function(cell) {
            if (MASK_CONTACT) return '<span class="text-base-content/25 text-xs">***</span>';
            return cell.getValue() ? esc(cell.getValue()) : '<span class="text-base-content/25 text-xs">—</span>';
        },
    },
    {
        title: 'Tình trạng', field: 'stage_id', width: 150, headerSort: false,
        formatter: function(cell) {
            var d = cell.getRow().getData();
            var color = d.stage_color || stageColor(d.stage_id);
            var label = d.stage_label || stageLabel(d.stage_id);
            return '<div class="flex items-center gap-1.5">'
                + '<span class="w-2 h-2 rounded-full shrink-0" style="background:' + esc(color) + '"></span>'
                + '<span class="text-sm">' + esc(label) + '</span>'
                + '</div>';
        },
    },
    {
        title: 'Nguồn', field: 'source_label', width: 130, headerSort: false,
        formatter: function(cell) {
            var d = cell.getRow().getData();
            var label = d.source_label || sourceLabel(d.source_id);
            return label ? esc(label) : '<span class="text-base-content/25 text-xs">—</span>';
        },
    },
    {
        title: 'Người phụ trách', field: 'assignee_name', width: 150, headerSort: false,
        formatter: function(cell) {
            var v = cell.getValue();
            return v ? '<div class="flex items-center gap-1.5"><div class="avatar placeholder"><div class="w-5 rounded-full bg-primary/20 text-primary text-xs"><span>' + esc(v.charAt(0).toUpperCase()) + '</span></div></div><span class="text-sm">' + esc(v) + '</span></div>'
                     : '<span class="text-base-content/25 text-xs">Chưa phân công</span>';
        },
    },
    {
        title: 'Giá trị', field: 'expected_value', width: 130, sorter: 'number', hozAlign: 'right',
        formatter: function(cell) {
            var d = cell.getRow().getData();
            return d.expected_value
                ? '<span class="font-medium text-success text-sm">' + esc(formatMoney(d.expected_value, d.currency)) + '</span>'
                : '<span class="text-base-content/25 text-xs">—</span>';
        },
    },
    {
        title: 'Điểm', field: 'lead_score', width: 80, sorter: 'number', hozAlign: 'center',
        formatter: function(cell) {
            var v = cell.getValue();
            if (v == null) return '<span class="text-base-content/25 text-xs">—</span>';
            var cls = v >= 70 ? 'badge-error' : (v >= 40 ? 'badge-warning' : 'badge-ghost');
            return '<span class="badge badge-sm ' + cls + '">' + v + '</span>';
        },
    },
    {
        title: 'Trạng thái', field: 'status_value', width: 130, hozAlign: 'center', headerSort: false,
        formatter: function(cell) {
            var d = cell.getRow().getData();
            return d.status_badge
                ? '<span class="badge badge-sm badge-soft ' + esc(d.status_badge) + '">' + esc(d.status_label) + '</span>'
                : '';
        },
    },
    {
        title: 'Ngày chốt', field: 'expected_close_date', width: 110, hozAlign: 'center', sorter: 'string',
        formatter: function(cell) {
            var v = cell.getValue();
            if (!v) return '<span class="text-base-content/25 text-xs">—</span>';
            var parts = v.split('-');
            var today = isoDate(new Date());
            var cls = v < today ? 'text-error font-medium' : 'text-base-content/70';
            return '<span class="text-xs ' + cls + '">' + parts[2] + '/' + parts[1] + '/' + parts[0] + '</span>';
        },
    },
    {
        title: 'Hoạt động', field: 'last_activity_at', width: 110, sorter: 'string', hozAlign: 'center',
        formatter: function(cell) {
            return cell.getValue()
                ? '<span class="text-xs text-base-content/60">' + esc(cell.getValue()) + '</span>'
                : '<span class="text-base-content/25 text-xs">—</span>';
        },
    },
    {
        title: 'Thao tác', field: 'id', width: 100, hozAlign: 'center', headerSort: false, frozen: true,
        formatter: function(cell) {
            var d = cell.getRow().getData();
            var html = '<div class="flex items-center justify-center gap-1">';

            html += '<a href="' + esc(d.show_url) + '" class="btn btn-ghost btn-xs btn-square text-base-content/40 hover:text-info" title="Xem">'
                + '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>'
                + '</a>';

            html += '<a href="' + esc(d.edit_url) + '" class="btn btn-ghost btn-xs btn-square text-base-content/40 hover:text-warning" title="Sửa">'
                + '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'
                + '</a>';

            if (CAN_DELETE) {
                html += '<button class="btn btn-ghost btn-xs btn-square text-error/30 hover:text-error" title="Xóa"'
                    + ' onclick="leadDeleteConfirm(\'' + esc(d.delete_url) + '\', \'' + esc(d.title || d.contact_name) + '\')">'
                    + '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                    + '</button>';
            }

            html += '</div>';
            return html;
        },
    },
];

// ── Delete handler ────────────────────────────────────────────────────────────
var pendingDeleteUrl = null;

window.leadDeleteConfirm = function(url, name) {
    pendingDeleteUrl = url;
    document.getElementById('deleteItemName').textContent = '"' + name + '"';
    document.getElementById('deleteModal').showModal();
};

document.getElementById('confirmDeleteBtn').addEventListener('click', async function() {
    if (!pendingDeleteUrl) return;
    var btn = this;
    btn.disabled    = true;
    btn.textContent = 'Đang xóa...';

    try {
        var res = await fetch(pendingDeleteUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN':     CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
                'Content-Type':     'application/x-www-form-urlencoded',
            },
            body: '_method=DELETE',
        });

        if (res.ok) {
            document.getElementById('deleteModal').close();
            if (window.leadTable) window.leadTable.replaceData();
        } else {
            var data = await res.json().catch(function() { return {}; });
            alert(data.message || 'Xóa thất bại. Vui lòng thử lại.');
        }
    } catch(e) {
        alert('Lỗi kết nối.');
    } finally {
        btn.disabled    = false;
        btn.textContent = 'Xóa';
        pendingDeleteUrl = null;
    }
});

// ── Alpine component ──────────────────────────────────────────────────────────
document.addEventListener('alpine:init', function() {
    var tableInst    = null;
    var stageTsInst  = null;
    var sourceTsInst = null;
    var statusTsInst = null;
    var closingFpInst = null;

    Alpine.data('leadListPage', function() {
        return {
            view:          localStorage.getItem(LS_VIEW) || 'list',
            filters: {
                search: '', stage_id: '', source_id: '',
                status: '', closing_after: '', closing_before: '',
            },
            closingPreset:  '',
            hiddenCols:     [],
            kanbanBoard:    [],
            kanbanLoading:  false,

            get toggleableCols() {
                return COLUMNS
                    .filter(function(c) { return c.field !== 'id' && c.field !== 'title'; })
                    .map(function(c) { return { field: c.field, title: c.title }; });
            },

            get hasFilters() {
                var f = this.filters;
                return !!(f.search || f.stage_id || f.source_id || f.status || f.closing_after);
            },

            get activeChips() {
                var chips = [], f = this.filters;
                if (f.search)    chips.push({ key: 'search', label: 'Tìm: ' + f.search });
                if (f.stage_id) {
                    var s = STAGES.find(function(s){ return String(s.id) === String(f.stage_id); });
                    chips.push({ key: 'stage', label: 'TT: ' + (s ? s.label : f.stage_id) });
                }
                if (f.source_id) {
                    var src = SOURCES.find(function(s){ return String(s.id) === String(f.source_id); });
                    chips.push({ key: 'source', label: 'Nguồn: ' + (src ? src.label : f.source_id) });
                }
                if (f.status) {
                    var st = STATUSES.find(function(s){ return s.value === f.status; });
                    chips.push({ key: 'status', label: st ? st.text : f.status });
                }
                if (f.closing_after && f.closing_before)
                    chips.push({ key: 'closing', label: 'Chốt: ' + f.closing_after + ' — ' + f.closing_before });
                return chips;
            },

            init() {
                this.loadState();
                try { this.hiddenCols = JSON.parse(localStorage.getItem(LS_COLS) || '[]'); } catch(e) {}
                document.addEventListener('DOMContentLoaded', () => this._setup(), { once: true });
            },

            _setup() {
                var self = this;

                // ── Tabulator ────────────────────────────────────────────
                tableInst = new window.Tabulator('#lead-table', {
                    ajaxURL:    API_LISTING,
                    ajaxConfig: {
                        headers: {
                            'X-CSRF-TOKEN':     CSRF_TOKEN,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept':           'application/json',
                        },
                    },
                    ajaxParams: function() {
                        var p = {}, f = self.filters;
                        if (f.search)         p.search         = f.search;
                        if (f.stage_id)       p.stage_id       = f.stage_id;
                        if (f.source_id)      p.source_id      = f.source_id;
                        if (f.status)         p.status         = f.status;
                        if (f.closing_after)  p.closing_after  = f.closing_after;
                        if (f.closing_before) p.closing_before = f.closing_before;
                        return p;
                    },
                    ajaxResponse: function(_u, _p, res) { return res; },

                    pagination:             true,
                    paginationMode:         'remote',
                    paginationSize:         25,
                    paginationSizeSelector: [10, 25, 50, 100],
                    paginationCounter:      'rows',
                    sortMode:               'remote',
                    initialSort:            [{ column: 'updated_at', dir: 'desc' }],

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
                    placeholder: '<div class="py-16 text-center opacity-40"><svg class="w-12 h-12 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg><p class="text-sm">Không tìm thấy cơ hội nào</p></div>',
                });

                window.leadTable = tableInst;
                self.hiddenCols.forEach(function(f) { tableInst.hideColumn(f); });

                // ── Stage TomSelect ──────────────────────────────────────
                stageTsInst = new window.TomSelect('#filter-stage', {
                    dropdownParent: 'body',
                    placeholder:    'Tất cả tình trạng...',
                    plugins:        ['clear_button'],
                    options:        STAGES.map(function(s) { return { value: String(s.id), text: s.label }; }),
                    items:          self.filters.stage_id ? [String(self.filters.stage_id)] : [],
                    onChange: function(val) {
                        self.filters.stage_id = val || '';
                        self.saveState(); self.refresh();
                    },
                    render: { no_results: function() { return '<div class="no-results p-3 text-sm opacity-50">Không tìm thấy</div>'; } },
                });

                // ── Source TomSelect ─────────────────────────────────────
                sourceTsInst = new window.TomSelect('#filter-source', {
                    dropdownParent: 'body',
                    placeholder:    'Tất cả nguồn...',
                    plugins:        ['clear_button'],
                    options:        SOURCES.map(function(s) { return { value: String(s.id), text: s.label }; }),
                    items:          self.filters.source_id ? [String(self.filters.source_id)] : [],
                    onChange: function(val) {
                        self.filters.source_id = val || '';
                        self.saveState(); self.refresh();
                    },
                    render: { no_results: function() { return '<div class="no-results p-3 text-sm opacity-50">Không tìm thấy</div>'; } },
                });

                // ── Status TomSelect ─────────────────────────────────────
                statusTsInst = new window.TomSelect('#filter-status', {
                    dropdownParent: 'body',
                    placeholder:    'Tất cả trạng thái...',
                    plugins:        ['clear_button'],
                    options:        STATUSES,
                    items:          self.filters.status ? [self.filters.status] : [],
                    onChange: function(val) {
                        self.filters.status = val || '';
                        self.saveState(); self.refresh();
                    },
                });

                // ── Flatpickr closing date ───────────────────────────────
                closingFpInst = window.initDateRangePicker('#filter-closing', {
                    disableMobile: true,
                    onChange: function(dates) {
                        self.closingPreset = '';
                        if (dates.length === 2) {
                            self.filters.closing_after  = isoDate(dates[0]);
                            self.filters.closing_before = isoDate(dates[1]);
                        } else {
                            self.filters.closing_after  = '';
                            self.filters.closing_before = '';
                        }
                        self.saveState(); self.refresh();
                    },
                });

                if (self.filters.closing_after && self.filters.closing_before) {
                    closingFpInst.setDate([self.filters.closing_after, self.filters.closing_before], false);
                }
            },

            // ── State ─────────────────────────────────────────────────────
            loadState() {
                var p = new URLSearchParams(location.search);
                if (p.has('q'))  this.filters.search        = p.get('q');
                if (p.has('sg')) this.filters.stage_id      = p.get('sg');
                if (p.has('sr')) this.filters.source_id     = p.get('sr');
                if (p.has('st')) this.filters.status        = p.get('st');
                if (p.has('ca')) this.filters.closing_after = p.get('ca');
                if (p.has('cb')) this.filters.closing_before= p.get('cb');
                if (p.has('cp')) this.closingPreset         = p.get('cp');
            },

            saveState() {
                var p = new URLSearchParams(), f = this.filters;
                if (f.search)         p.set('q',  f.search);
                if (f.stage_id)       p.set('sg', f.stage_id);
                if (f.source_id)      p.set('sr', f.source_id);
                if (f.status)         p.set('st', f.status);
                if (f.closing_after)  p.set('ca', f.closing_after);
                if (f.closing_before) p.set('cb', f.closing_before);
                if (this.closingPreset) p.set('cp', this.closingPreset);
                var qs = p.toString();
                history.replaceState(null, '', qs ? '?' + qs : location.pathname);
            },

            // ── Core actions ──────────────────────────────────────────────
            refresh()        { if (tableInst) tableInst.replaceData(); },
            onFilterChange() { this.saveState(); this.refresh(); },
            clearSearch()    { this.filters.search = ''; this.saveState(); this.refresh(); },

            clearClosing() {
                this.closingPreset = '';
                this.filters.closing_after  = '';
                this.filters.closing_before = '';
                if (closingFpInst) closingFpInst.clear(false);
                this.saveState(); this.refresh();
            },

            setClosingPreset(preset) {
                var now = new Date(), y = now.getFullYear(), m = now.getMonth(), d = now.getDate();
                var from, to;
                if (preset === 'week') {
                    from = isoDate(now);
                    to   = isoDate(new Date(y, m, d + 7));
                } else if (preset === 'month') {
                    from = isoDate(new Date(y, m, 1));
                    to   = isoDate(new Date(y, m + 1, 0));
                } else if (preset === 'overdue') {
                    from = '2020-01-01';
                    to   = isoDate(new Date(y, m, d - 1));
                } else return;

                this.closingPreset          = preset;
                this.filters.closing_after  = from;
                this.filters.closing_before = to;
                if (closingFpInst) closingFpInst.setDate([from, to], false);
                this.saveState(); this.refresh();
            },

            async switchToKanban() {
                localStorage.setItem(LS_VIEW, 'kanban');
                this.view = 'kanban';
                if (this.kanbanBoard.length === 0) {
                    await this.loadKanban();
                }
            },

            async loadKanban() {
                this.kanbanLoading = true;
                try {
                    var res = await fetch(API_KANBAN, {
                        headers: {
                            'X-CSRF-TOKEN':     CSRF_TOKEN,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept':           'application/json',
                        },
                    });
                    var json = await res.json();
                    this.kanbanBoard = json.data || [];
                } catch(e) {
                    console.error('[lead] kanban load failed', e);
                } finally {
                    this.kanbanLoading = false;
                }
            },

            removeChip(key) {
                if (key === 'search')  { this.filters.search = ''; }
                if (key === 'stage')   { this.filters.stage_id = ''; if (stageTsInst) stageTsInst.clear(true); }
                if (key === 'source')  { this.filters.source_id = ''; if (sourceTsInst) sourceTsInst.clear(true); }
                if (key === 'status')  { this.filters.status = ''; if (statusTsInst) statusTsInst.clear(true); }
                if (key === 'closing') { this.clearClosing(); return; }
                this.saveState(); this.refresh();
            },

            reset() {
                this.filters = { search: '', stage_id: '', source_id: '', status: '', closing_after: '', closing_before: '' };
                this.closingPreset = '';
                if (stageTsInst)  stageTsInst.clear(true);
                if (sourceTsInst) sourceTsInst.clear(true);
                if (statusTsInst) statusTsInst.clear(true);
                if (closingFpInst) closingFpInst.clear(false);
                history.replaceState(null, '', location.pathname);
                this.refresh();
            },

            toggleCol(field) {
                if (this.hiddenCols.includes(field)) {
                    this.hiddenCols = this.hiddenCols.filter(function(f) { return f !== field; });
                    if (tableInst) tableInst.showColumn(field);
                } else {
                    this.hiddenCols.push(field);
                    if (tableInst) tableInst.hideColumn(field);
                }
                try { localStorage.setItem(LS_COLS, JSON.stringify(this.hiddenCols)); } catch(e) {}
            },

            // ── Kanban helpers ────────────────────────────────────────────
            formatCurrency(val, currency) {
                if (!val) return '';
                return parseFloat(val).toLocaleString('vi-VN') + ' ' + (currency || 'VND');
            },
            formatDate(iso) {
                if (!iso) return '';
                var p = iso.split('-');
                return p[2] + '/' + p[1];
            },
        };
    });
});
</script>
@endpush
