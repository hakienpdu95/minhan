@extends('layouts.backend')
@section('title', 'Activity Log')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <span class="current">Activity Log</span>
</nav>
@endsection

@section('content')
<div x-data="activityLogIndex()" x-init="init()">

    {{-- ── Page header ──────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Activity Log</h1>
            <p class="text-sm text-base-content/50 mt-0.5">Lịch sử hoạt động hệ thống</p>
        </div>
        <div class="flex items-center gap-2">
            {{-- Summary badges --}}
            <template x-if="stats">
                <div class="flex items-center gap-2">
                    <span class="badge badge-ghost gap-1">
                        Hôm nay: <b x-text="stats.total_today"></b>
                    </span>
                    <span class="badge badge-warning gap-1" x-show="stats.warnings > 0">
                        Warning: <b x-text="stats.warnings"></b>
                    </span>
                    <span class="badge badge-error gap-1" x-show="stats.error_today > 0">
                        Error: <b x-text="stats.error_today"></b>
                    </span>
                    <span class="badge badge-error gap-1" x-show="stats.critical_today > 0"
                          style="background:#7f1d1d;color:#fff">
                        Critical: <b x-text="stats.critical_today"></b>
                    </span>
                </div>
            </template>
            <button @click="doExport()" class="btn btn-outline btn-sm gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Xuất Excel
            </button>
        </div>
    </div>

    {{-- ── Filter bar ───────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200 mb-4">
        <div class="card-body py-3 px-4">
            <div class="flex flex-wrap gap-3 items-end">

                <div class="form-control min-w-32">
                    <label class="label py-0.5">
                        <span class="label-text text-xs font-medium">Từ ngày</span>
                    </label>
                    <input type="date" class="input input-sm input-bordered"
                           x-model="f.date_from" @change="reload()">
                </div>
                <div class="form-control min-w-32">
                    <label class="label py-0.5">
                        <span class="label-text text-xs font-medium">Đến ngày</span>
                    </label>
                    <input type="date" class="input input-sm input-bordered"
                           x-model="f.date_to" @change="reload()">
                </div>

                <div class="form-control self-end">
                    <button @click="reset()" class="btn btn-ghost btn-sm">Xóa lọc</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tabulator table ──────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-0 overflow-hidden rounded-2xl tabulator-daisy">
            <div id="actlog-table"></div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<x-tabulator-theme />
<style>
.actlog-row-critical { background-color: rgb(127 29 29 / 0.08) !important; }
.actlog-row-error    { background-color: rgb(239 68 68 / 0.06) !important; }

/* Actor avatar chip */
.actlog-actor { display:flex; align-items:center; gap:6px; }
.actlog-actor-avatar {
    width:22px; height:22px; border-radius:50%;
    background: oklch(var(--p));
    color: oklch(var(--pc));
    display:flex; align-items:center; justify-content:center;
    font-size:10px; font-weight:700; flex-shrink:0;
    text-transform:uppercase;
}
.actlog-actor-name { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

/* Subject chip */
.actlog-subject { display:flex; align-items:center; gap:4px; line-height:1.3; }
.actlog-subject-id { font-size:10px; opacity:.55; font-variant-numeric:tabular-nums; }

/* Props preview */
.actlog-props { font-size:11px; opacity:.6; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
</style>
@endpush

@push('scripts')
@vite(['resources/js/modules/tabulator.js'], 'build/backend')
<script>
function esc(v) {
    if (v == null) return '';
    return String(v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

const LEVEL_CFG = {
    1: { cls: 'badge-ghost',   label: 'Debug' },
    2: { cls: 'badge-info',    label: 'Info' },
    3: { cls: 'badge-warning', label: 'Warning' },
    4: { cls: 'badge-error',   label: 'Error' },
    5: { cls: 'badge-error',   label: 'Critical', extra: 'font-bold' },
};

function levelBadge(level) {
    const c = LEVEL_CFG[level] ?? { cls: 'badge-ghost', label: level };
    return `<span class="badge badge-sm ${c.cls} ${c.extra ?? ''}">${esc(c.label)}</span>`;
}

function actorHtml(row) {
    const name  = row.display_actor ?? '—';
    const type  = row.actor_type ?? 'anonymous';
    const ip    = row.actor_ip;
    const init  = name.charAt(0) || '?';

    const avatarColor = type === 'user'
        ? 'background:oklch(var(--p));color:oklch(var(--pc))'
        : 'background:oklch(var(--b3));color:oklch(var(--bc))';

    const ipTag = ip ? `<span class="actlog-props" title="${esc(ip)}">${esc(ip)}</span>` : '';

    return `<div class="actlog-actor" title="${esc(name)}">
        <div class="actlog-actor-avatar" style="${avatarColor}">${esc(init)}</div>
        <div style="min-width:0">
            <div class="actlog-actor-name text-sm">${esc(name)}</div>
            ${ipTag}
        </div>
    </div>`;
}

function subjectHtml(row) {
    const label = row.display_subject;
    if (!label) return '<span class="opacity-30">—</span>';
    const subType = row.subject_type ? row.subject_type.split('\\').pop() : null;
    const subId   = row.subject_id;
    return `<div class="actlog-subject">
        <span class="text-sm" title="${esc(label)}">${esc(label)}</span>
    </div>`;
}

function descHtml(row) {
    const desc  = row.description;
    const props = row.props_preview;
    let html = '';
    if (desc)  html += `<div class="text-sm">${esc(desc)}</div>`;
    if (props) html += `<div class="actlog-props" title="${esc(props)}">${esc(props)}</div>`;
    return html || '<span class="opacity-30">—</span>';
}

document.addEventListener('alpine:init', function () {
    Alpine.data('activityLogIndex', function () {
        return {
            stats: null,
            table: null,
            f: { date_from: '', date_to: '' },

            init() {
                this.loadStats();
                this.\$nextTick(() => this.initTable());
            },

            async loadStats() {
                try {
                    const r    = await fetch('/backend/api/activity-logs/stats?days=1');
                    const data = await r.json();
                    const byLevel = (v) => data.by_level?.find(l => l.level == v)?.count ?? 0;
                    this.stats = {
                        total_today:    data.by_level?.reduce((s, l) => s + parseInt(l.count), 0) ?? 0,
                        warnings:       byLevel(3),
                        error_today:    data.error_today ?? 0,
                        critical_today: data.critical_today ?? 0,
                    };
                } catch (_) {}
            },

            reload() {
                if (this.table && !this.table.destroyed) this.table.replaceData();
            },

            reset() {
                this.f = { date_from: '', date_to: '' };
                this.reload();
            },

            async doExport() {
                try {
                    const r = await fetch('/dashboard/activity-logs/export', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            date_from: this.f.date_from || null,
                            date_to:   this.f.date_to   || null,
                        }),
                    });
                    if (!r.ok) return;
                    const { key } = await r.json();
                    const poll = setInterval(async () => {
                        const check = await fetch(`/dashboard/activity-logs/export/download/${key}`, { method: 'HEAD' });
                        if (check.ok) {
                            clearInterval(poll);
                            window.location = `/dashboard/activity-logs/export/download/${key}`;
                        }
                    }, 2000);
                    setTimeout(() => clearInterval(poll), 300_000);
                } catch (_) {}
            },

            initTable() {
                // Skip if already initialized and still alive (prevents double-init from HMR/Alpine re-mount)
                if (this.table && !this.table.destroyed) return;
                this.table = null;
                const self = this;
                this.table = new window.Tabulator('#actlog-table', {
                    ajaxURL:    '/backend/api/activity-logs',
                    ajaxConfig: { headers: { 'X-Requested-With': 'XMLHttpRequest' } },
                    ajaxParams() {
                        const p = {}, f = self.f;
                        if (f.date_from) p.date_from = f.date_from;
                        if (f.date_to)   p.date_to   = f.date_to;
                        return p;
                    },
                    ajaxResponse(_u, _p, res) { return res; },
                    dataLoading() {
                        const holder = document.querySelector('#actlog-table .tabulator-tableholder');
                        if (holder) holder.style.pointerEvents = 'none';
                    },
                    dataLoaded() {
                        const holder = document.querySelector('#actlog-table .tabulator-tableholder');
                        if (holder) holder.style.pointerEvents = '';
                    },
                    pagination:             true,
                    paginationMode:         'remote',
                    paginationSize:         20,
                    paginationSizeSelector: [10, 20, 50, 100],
                    paginationCounter:      'rows',
                    sortMode:               'remote',
                    initialSort:            [{ column: 'created_at', dir: 'desc' }],
                    layout:                 'fitColumns',
                    responsiveLayout:       'hide',
                    rowFormatter(row) {
                        const l = row.getData().level;
                        if (l >= 5)      row.getElement().classList.add('actlog-row-critical');
                        else if (l >= 4) row.getElement().classList.add('actlog-row-error');
                    },
                    columns: [
                        {
                            title: 'Thời gian', field: 'created_at', width: 148, sorter: 'datetime',
                            formatter(cell) {
                                const v = cell.getValue();
                                if (!v) return '—';
                                const d = new Date(v);
                                const date = d.toLocaleDateString('vi-VN', { day:'2-digit', month:'2-digit', year:'numeric' });
                                const time = d.toLocaleTimeString('vi-VN', { hour12: false });
                                return `<div class="text-sm">${date}</div><div class="actlog-props">${time}</div>`;
                            },
                        },
                        {
                            title: 'Cấp độ', field: 'level', width: 90, hozAlign: 'center', sorter: 'number',
                            formatter(cell) { return levelBadge(cell.getValue()); },
                        },
                        {
                            title: 'Module / Action', field: 'display_module', minWidth: 160,
                            formatter(cell) {
                                const row = cell.getData();
                                const mod = esc(row.display_module ?? '—');
                                const act = esc(row.display_action ?? '—');
                                return `<div class="text-sm font-medium">${act}</div>`
                                     + `<div class="actlog-props">${mod}</div>`;
                            },
                        },
                        {
                            title: 'Actor', field: 'display_actor', minWidth: 150,
                            formatter(cell) { return actorHtml(cell.getData()); },
                        },
                        {
                            title: 'Đối tượng', field: 'display_subject', minWidth: 150,
                            formatter(cell) { return subjectHtml(cell.getData()); },
                        },
                        {
                            title: 'Mô tả / Chi tiết', field: 'description', minWidth: 200,
                            formatter(cell) { return descHtml(cell.getData()); },
                        },
                        {
                            title: '', width: 58, hozAlign: 'center', headerSort: false,
                            formatter() {
                                return '<span class="btn btn-xs btn-ghost">Xem</span>';
                            },
                            cellClick(_e, cell) {
                                window.location = `/dashboard/activity-logs/${cell.getData().id}`;
                            },
                        },
                    ],
                });
            },
        };
    });
});
</script>
@endpush
