@extends('layouts.backend')
@section('title', $organization->name)

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.organizations.index') }}">Tổ chức</a>
    <span class="sep">›</span>
    <span class="current">{{ $organization->name }}</span>
</nav>
@endsection

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">{{ $organization->name }}</h1>
        <p class="text-sm text-base-content/50 mt-0.5 font-mono">{{ $organization->slug }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('backend.users.index', ['organization_id' => $organization->id]) }}" class="btn btn-ghost btn-sm gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Tài khoản ({{ $organization->members_count }})
        </a>
        <a href="{{ route('backend.organizations.edit', $organization) }}" class="btn btn-primary btn-sm">Chỉnh sửa</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Stats --}}
    <div class="stats stats-vertical shadow border border-base-200 lg:col-span-1">
        <div class="stat">
            <div class="stat-title">Thành viên</div>
            <div class="stat-value text-primary">{{ $organization->members_count }}</div>
            <div class="stat-desc">
                <a href="{{ route('backend.users.index', ['organization_id' => $organization->id]) }}" class="link link-primary">Quản lý tài khoản →</a>
            </div>
        </div>
        <div class="stat">
            <div class="stat-title">Trạng thái</div>
            <div class="stat-value text-2xl">
                @if ($organization->status->value === 'active')
                    <span class="text-success">Hoạt động</span>
                @elseif ($organization->status->value === 'suspended')
                    <span class="text-error">Tạm khóa</span>
                @else
                    <span class="text-base-content/50">Không HĐ</span>
                @endif
            </div>
        </div>
        <div class="stat">
            <div class="stat-title">Ngày tạo</div>
            <div class="stat-value text-xl">{{ $organization->created_at->format('d/m/Y') }}</div>
        </div>
    </div>

    {{-- Details --}}
    <div class="card bg-base-100 shadow-sm border border-base-200 lg:col-span-2">
        <div class="card-body">
            <h2 class="card-title text-base mb-3">Thông tin chi tiết</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                @if ($organization->industry)
                <div><dt class="text-base-content/50 text-xs uppercase tracking-wide mb-0.5">Ngành nghề</dt><dd class="font-medium">{{ $organization->industry }}</dd></div>
                @endif
                @if ($organization->tax_code)
                <div><dt class="text-base-content/50 text-xs uppercase tracking-wide mb-0.5">Mã số thuế</dt><dd class="font-medium font-mono">{{ $organization->tax_code }}</dd></div>
                @endif
                @if ($organization->phone)
                <div><dt class="text-base-content/50 text-xs uppercase tracking-wide mb-0.5">Điện thoại</dt><dd>{{ $organization->phone }}</dd></div>
                @endif
                @if ($organization->email)
                <div><dt class="text-base-content/50 text-xs uppercase tracking-wide mb-0.5">Email</dt><dd>{{ $organization->email }}</dd></div>
                @endif
                @if ($organization->website)
                <div class="sm:col-span-2"><dt class="text-base-content/50 text-xs uppercase tracking-wide mb-0.5">Website</dt><dd><a href="{{ $organization->website }}" target="_blank" class="link link-primary">{{ $organization->website }}</a></dd></div>
                @endif
                @if ($organization->full_address)
                <div class="sm:col-span-2"><dt class="text-base-content/50 text-xs uppercase tracking-wide mb-0.5">Địa chỉ</dt><dd>{{ $organization->full_address }}{{ $organization->country ? ' (' . $organization->country . ')' : '' }}</dd></div>
                @elseif ($organization->address)
                <div class="sm:col-span-2"><dt class="text-base-content/50 text-xs uppercase tracking-wide mb-0.5">Địa chỉ</dt><dd>{{ implode(', ', array_filter([$organization->address, $organization->ward?->name, $organization->province?->name])) }}{{ $organization->country ? ' (' . $organization->country . ')' : '' }}</dd></div>
                @endif
                @if ($organization->description)
                <div class="sm:col-span-2">
                    <dt class="text-base-content/50 text-xs uppercase tracking-wide mb-0.5">Mô tả</dt>
                    <dd class="text-base-content/80 rich-content">{!! sanitize_rich_text($organization->description) !!}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

</div>

{{-- Recent members --}}
@if ($members->isNotEmpty())
<div class="card bg-base-100 shadow-sm border border-base-200 mt-6">
    <div class="card-body p-0">
        <div class="flex items-center justify-between px-6 py-4 border-b border-base-200">
            <h2 class="font-semibold">Thành viên gần đây</h2>
            <a href="{{ route('backend.users.index', ['organization_id' => $organization->id]) }}" class="btn btn-ghost btn-xs">Xem tất cả →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead><tr><th>Tên</th><th>Email</th><th>Phòng ban</th><th>Vai trò</th><th>Ngày tham gia</th></tr></thead>
                <tbody>
                    @foreach ($members as $m)
                    <tr class="hover">
                        <td class="font-medium text-sm">{{ $m->user?->name ?? '—' }}</td>
                        <td class="text-sm text-base-content/70">{{ $m->user?->email ?? '—' }}</td>
                        <td class="text-sm">{{ $m->user?->department ?? '—' }}</td>
                        <td><span class="badge badge-ghost badge-sm">{{ $m->role }}</span></td>
                        <td class="text-sm text-base-content/60">{{ $m->joined_at?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
