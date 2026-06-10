@extends('layouts.backend')
@section('title', 'Quản lý Subscriptions')

@section('content')

{{-- Flash messages --}}
@foreach(['success','error'] as $type)
    @if(session($type))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         x-transition.opacity.duration.500ms
         class="alert alert-{{ $type }} mb-4 text-sm">
        <span>{{ session($type) }}</span>
        <button @click="show = false" class="btn btn-ghost btn-xs ml-auto">✕</button>
    </div>
    @endif
@endforeach

{{-- Page header --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Subscriptions</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Quản lý gói dịch vụ của từng tổ chức</p>
    </div>
    <a href="{{ route('subscription.admin.plans.index') }}" class="btn btn-ghost btn-sm gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        Quản lý Plans
    </a>
</div>

{{-- Filters --}}
<form method="GET" class="card bg-base-100 shadow-sm border border-base-200 mb-4">
    <div class="card-body py-3 px-4">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="form-control flex-1 min-w-52">
                <label class="label py-0.5"><span class="label-text text-xs font-medium">Tìm kiếm tổ chức</span></label>
                <input type="text" name="search" value="{{ $search }}"
                       class="input input-bordered input-sm" placeholder="Tên tổ chức..."/>
            </div>
            <div class="form-control w-44">
                <label class="label py-0.5"><span class="label-text text-xs font-medium">Plan</span></label>
                <select name="plan_id" class="select select-bordered select-sm">
                    <option value="">Tất cả</option>
                    @foreach ($plans as $p)
                    <option value="{{ $p->id }}" {{ $planId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm self-end">Lọc</button>
            <a href="{{ route('subscription.admin.subscriptions.index') }}" class="btn btn-ghost btn-sm self-end">Xóa lọc</a>
        </div>
    </div>
</form>

{{-- Table --}}
<div class="card bg-base-100 shadow-sm border border-base-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table table-sm w-full">
            <thead class="bg-base-200/50">
                <tr class="text-xs text-base-content/60">
                    <th>Tổ chức</th>
                    <th>Plan hiện tại</th>
                    <th>Trạng thái</th>
                    <th>Bắt đầu</th>
                    <th>Kết thúc</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orgs as $org)
                @php
                    $sub = $org->planSubscriptions->first();
                @endphp
                <tr class="hover:bg-base-200/30">
                    <td>
                        <div class="font-medium text-sm">{{ $org->name }}</div>
                        <div class="text-xs text-base-content/40">{{ $org->slug }}</div>
                    </td>
                    <td>
                        @if ($sub?->plan)
                            <span class="badge badge-outline badge-sm">{{ $sub->plan->name }}</span>
                        @else
                            <span class="text-xs text-base-content/30">Không có</span>
                        @endif
                    </td>
                    <td>
                        @if (!$sub)
                            <span class="badge badge-ghost badge-xs">—</span>
                        @elseif ($sub->onTrial())
                            <span class="badge badge-info badge-xs">Trial</span>
                        @elseif ($sub->active())
                            <span class="badge badge-success badge-xs">Active</span>
                        @elseif ($sub->canceled())
                            <span class="badge badge-warning badge-xs">Canceled</span>
                        @else
                            <span class="badge badge-error badge-xs">Expired</span>
                        @endif
                    </td>
                    <td class="text-xs text-base-content/60">{{ $sub?->starts_at?->format('d/m/Y') ?? '—' }}</td>
                    <td class="text-xs text-base-content/60">{{ $sub?->ends_at?->format('d/m/Y') ?? '—' }}</td>
                    <td class="text-right">
                        <div x-data="{ open: false }" class="relative inline-block">
                            <button @click="open = !open" class="btn btn-ghost btn-xs">
                                Gán plan
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" @click.outside="open = false" x-cloak
                                 class="absolute right-0 top-8 z-50 bg-base-100 border border-base-200 rounded-xl shadow-lg w-72 p-4">
                                <p class="text-xs font-semibold text-base-content/60 mb-3">Gán plan cho {{ $org->name }}</p>
                                <form method="POST" action="{{ route('subscription.admin.subscriptions.assign', $org) }}">
                                    @csrf
                                    <div class="space-y-3">
                                        <div class="form-control">
                                            <label class="label py-0"><span class="label-text text-xs">Plan</span></label>
                                            <select name="plan_id" class="select select-bordered select-sm w-full" required>
                                                @foreach ($plans as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-control">
                                            <label class="label py-0"><span class="label-text text-xs">Ngày bắt đầu (tuỳ chọn)</span></label>
                                            <input type="date" name="start_date" class="input input-bordered input-sm w-full"/>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm w-full">Xác nhận</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-10 text-base-content/40 text-sm">Không tìm thấy tổ chức nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($orgs->hasPages())
    <div class="px-5 py-3 border-t border-base-200">
        {{ $orgs->links() }}
    </div>
    @endif
</div>

@endsection
