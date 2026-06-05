@extends('layouts.backend')

@section('title', $listing->title . ' — Marketplace')

@section('breadcrumb')
<div class="breadcrumbs text-sm px-6 pt-4">
    <ul>
        <li><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
        <li><a href="{{ route('backend.marketplace.listings.index') }}">Marketplace</a></li>
        <li>{{ Str::limit($listing->title, 50) }}</li>
    </ul>
</div>
@endsection

@section('content')
<div class="px-6 py-4 max-w-4xl mx-auto space-y-4">

    {{-- ── Header ──────────────────────────────────────────────── --}}
    <div class="flex items-start justify-between">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-xl font-bold">{{ $listing->title }}</h1>
                <span class="badge {{ $listing->status?->badgeClass() }} badge-sm">
                    {{ $listing->status?->label() }}
                </span>
                @if($listing->jp_sync_status?->value === 'out_of_sync')
                <span class="badge badge-warning badge-sm">Lỗi thời với JP</span>
                @endif
            </div>
            <div class="flex items-center gap-3 mt-1 text-sm opacity-60">
                <span>{{ $listing->listing_type?->label() }}</span>
                @if($listing->location)
                <span>• {{ $listing->location }}</span>
                @endif
                <span>• {{ $listing->created_at?->format('d/m/Y') }}</span>
            </div>
        </div>
        <div class="flex gap-2">
            @can('update', $listing)
            <a href="{{ route('backend.marketplace.listings.edit', $listing) }}"
               class="btn btn-outline btn-sm">Sửa</a>
            @endcan
            @can('close', $listing)
            @if($listing->isActive())
            <form action="{{ route('backend.marketplace.listings.close', $listing) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm"
                        onclick="return confirm('Đóng tin này?')">Đóng tin</button>
            </form>
            @endif
            @endcan
        </div>
    </div>

    {{-- ── Stats row ────────────────────────────────────────────── --}}
    <div class="stats shadow w-full">
        <div class="stat place-items-center">
            <div class="stat-title">Lượt xem</div>
            <div class="stat-value text-2xl">{{ number_format($listing->view_count) }}</div>
        </div>
        <div class="stat place-items-center">
            <div class="stat-title">Ứng viên</div>
            <div class="stat-value text-2xl text-primary">{{ number_format($listing->application_count) }}</div>
        </div>
        <div class="stat place-items-center">
            <div class="stat-title">Bookmark</div>
            <div class="stat-value text-2xl">{{ number_format($listing->bookmark_count) }}</div>
        </div>
        <div class="stat place-items-center">
            <div class="stat-title">Hết hạn</div>
            <div class="stat-value text-lg">{{ $listing->expire_at?->format('d/m/Y') ?? '—' }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- ── Main content ─────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">

            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="font-semibold mb-2">Mô tả</h3>
                    <div class="prose prose-sm max-w-none text-base-content/80">
                        {!! nl2br(e($listing->description)) !!}
                    </div>
                </div>
            </div>

            @if($listing->requirements)
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="font-semibold mb-2">Yêu cầu ứng viên</h3>
                    <div class="prose prose-sm max-w-none text-base-content/80">
                        {!! nl2br(e($listing->requirements)) !!}
                    </div>
                </div>
            </div>
            @endif

            @if($listing->benefits)
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="font-semibold mb-2">Quyền lợi</h3>
                    <div class="prose prose-sm max-w-none text-base-content/80">
                        {!! nl2br(e($listing->benefits)) !!}
                    </div>
                </div>
            </div>
            @endif

        </div>

        {{-- ── Sidebar info ─────────────────────────────────────── --}}
        <div class="space-y-4">

            <div class="card bg-base-100 shadow-sm">
                <div class="card-body p-4 space-y-3">
                    <h3 class="font-semibold text-sm">Chi tiết</h3>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="opacity-60">Loại</span>
                            <span>{{ $listing->listing_type?->label() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="opacity-60">Hình thức</span>
                            <span>{{ $listing->work_type?->label() }}</span>
                        </div>
                        @if($listing->employment_type)
                        <div class="flex justify-between">
                            <span class="opacity-60">Hợp đồng</span>
                            <span>{{ $listing->employment_type?->label() }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="opacity-60">Cấp độ</span>
                            <span>{{ $listing->experience_level?->label() }}</span>
                        </div>
                        @if($listing->salary_is_visible && ($listing->salary_min || $listing->salary_max))
                        <div class="flex justify-between">
                            <span class="opacity-60">Lương</span>
                            <span>
                                @if($listing->salary_min && $listing->salary_max)
                                    {{ number_format($listing->salary_min) }} – {{ number_format($listing->salary_max) }} {{ $listing->salary_currency }}
                                @elseif($listing->salary_min)
                                    Từ {{ number_format($listing->salary_min) }} {{ $listing->salary_currency }}
                                @else
                                    Đến {{ number_format($listing->salary_max) }} {{ $listing->salary_currency }}
                                @endif
                            </span>
                        </div>
                        @endif
                        @if($listing->salary_is_negotiable)
                        <div class="flex justify-between">
                            <span class="opacity-60">Lương</span>
                            <span class="badge badge-outline badge-xs">Thỏa thuận</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="opacity-60">Số lượng</span>
                            <span>{{ $listing->headcount }} người</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="opacity-60">Hiển thị</span>
                            <span>{{ $listing->visibility?->label() }}</span>
                        </div>
                        @if($listing->poster_type?->value === 'org' && $listing->organization)
                        <div class="flex justify-between">
                            <span class="opacity-60">Tổ chức</span>
                            <span>{{ $listing->organization->name }}</span>
                        </div>
                        @endif
                        @if($listing->jp_job_post_id)
                        <div class="flex justify-between">
                            <span class="opacity-60">Nguồn JP</span>
                            <span class="badge badge-ghost badge-xs">
                                {{ $listing->jp_sync_status?->label() ?? 'Từ JP' }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($listing->tags->isNotEmpty())
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm mb-2">Tags</h3>
                    <div class="flex flex-wrap gap-1">
                        @foreach($listing->tags as $tag)
                        <span class="badge badge-outline badge-sm">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

</div>
@endsection
