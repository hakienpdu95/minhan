@extends('layouts.backend')
@section('title', 'Triển khai')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-base-content">Triển khai</h1>
        <p class="text-sm text-base-content/50 mt-1">Chọn lĩnh vực để xem tổng quan và quản lý dữ liệu</p>
    </div>

    @if($verticals->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <svg class="w-16 h-16 text-base-content/20 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p class="text-base-content/40 text-sm font-medium">Chưa có lĩnh vực nào được kích hoạt</p>
            <p class="text-base-content/30 text-xs mt-1">Liên hệ quản trị viên để kích hoạt dịch vụ triển khai</p>
        </div>
    @else
        @foreach($verticals as $vertical)
        @php
            $dashboardUrl = null;
            try {
                if (\Illuminate\Support\Facades\Route::has('deployment.dashboard')) {
                    $dashboardUrl = route('deployment.dashboard', ['vertical' => $vertical->code()]);
                }
            } catch (\Throwable) {}
            $vTargets = $targetsByVertical[$vertical->code()] ?? collect();
            $avgScore = $vTargets->whereNotNull('readiness_score')->avg('readiness_score');
            $withScore = $vTargets->whereNotNull('readiness_score')->count();
        @endphp

        <div class="mb-8">
            {{-- Vertical header card --}}
            <a href="{{ $dashboardUrl ?? '#' }}"
               class="group flex items-center gap-4 bg-base-100 border border-base-200 rounded-xl p-5 shadow-sm hover:shadow-md hover:border-primary/30 transition-all duration-150 mb-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                    <svg class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-base-content leading-snug">{{ $vertical->label() }}</p>
                    <p class="text-xs text-base-content/40 uppercase tracking-wide">{{ $vertical->code() }}</p>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    @if($vTargets->isNotEmpty())
                    <div class="text-center">
                        <div class="font-bold text-lg">{{ $vTargets->count() }}</div>
                        <div class="text-xs text-base-content/40">{{ $vertical->targetLabel() }}</div>
                    </div>
                    @if($withScore > 0)
                    <div class="text-center">
                        @php
                            $avgInt = (int) round($avgScore);
                            $avgColor = $avgInt >= 80 ? 'text-success' : ($avgInt >= 60 ? 'text-info' : ($avgInt >= 40 ? 'text-warning' : 'text-error'));
                        @endphp
                        <div class="font-bold text-lg {{ $avgColor }}">{{ $avgInt }}</div>
                        <div class="text-xs text-base-content/40">Readiness TB</div>
                    </div>
                    @endif
                    @endif
                    <div class="flex items-center gap-1 text-xs text-primary font-medium">
                        <span>Xem tổng quan</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Readiness table --}}
            @if($vTargets->isNotEmpty())
            <div class="card bg-base-100 border border-base-200 shadow-sm md:col-span-2">
                <div class="card-body p-0">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-base-200">
                        <h3 class="font-semibold text-sm">Readiness — {{ $vertical->targetLabel() }}</h3>
                        <a href="{{ route('deployment.targets.index', ['vertical' => $vertical->code()]) }}"
                           class="btn btn-ghost btn-xs">Tất cả →</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-xs">
                            <thead>
                                <tr>
                                    <th>{{ $vertical->targetLabel() }}</th>
                                    <th>Phase</th>
                                    <th class="text-center">Readiness</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vTargets as $t)
                                @php
                                    $rs = $t->readiness_score;
                                    $rColor = $rs !== null ? ($rs >= 80 ? 'success' : ($rs >= 60 ? 'info' : ($rs >= 40 ? 'warning' : 'error'))) : null;
                                    $rBand  = $rs !== null ? ($rs >= 80 ? 'Sẵn sàng' : ($rs >= 60 ? 'Gần sẵn sàng' : ($rs >= 40 ? 'Có hỗ trợ' : 'Chưa sẵn sàng'))) : '—';
                                @endphp
                                <tr class="hover">
                                    <td>
                                        <span class="font-medium">{{ $t->targetOrganization?->name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-outline badge-xs">{{ $t->current_phase }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($rs !== null)
                                        <div class="flex items-center justify-center gap-2">
                                            <div class="radial-progress text-{{ $rColor }}"
                                                 style="--value:{{ $rs }}; --size:2.5rem; --thickness:3px;" role="progressbar">
                                                <span class="text-xs font-bold">{{ $rs }}</span>
                                            </div>
                                            <span class="text-xs text-base-content/60">{{ $rBand }}</span>
                                        </div>
                                        @else
                                        <span class="text-xs text-base-content/30">Chưa đánh giá</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('deployment.targets.show', ['vertical' => $vertical->code(), 'target' => $t->id]) }}"
                                           class="btn btn-ghost btn-xs">Chi tiết</a>
                                        @if($rs !== null)
                                        <a href="{{ route('deployment.readiness.show', ['vertical' => $vertical->code(), 'target' => $t->id]) }}"
                                           class="btn btn-ghost btn-xs text-primary">Readiness</a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- Projects panel --}}
            <div class="card bg-base-100 border border-base-200 shadow-sm md:col-span-2">
                <div class="card-body p-0">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-base-200">
                        <h3 class="font-semibold text-sm">Dự án</h3>
                        <div class="flex gap-2">
                            <a href="{{ route('deployment.projects.index', ['vertical' => $vertical->code()]) }}"
                               class="btn btn-ghost btn-xs">Tất cả →</a>
                            <a href="{{ route('deployment.projects.create', ['vertical' => $vertical->code()]) }}"
                               class="btn btn-primary btn-xs">+ Tạo dự án</a>
                        </div>
                    </div>
                    @php $vProjects = $projectsByVertical[$vertical->code()] ?? collect(); @endphp
                    @if($vProjects->isEmpty())
                    <div class="px-4 py-6 text-center text-sm text-base-content/40">
                        Chưa có dự án nào.
                        <a href="{{ route('deployment.projects.create', ['vertical' => $vertical->code()]) }}"
                           class="link link-primary ml-1">Tạo dự án đầu tiên</a>
                    </div>
                    @else
                    <div class="divide-y divide-base-200">
                        @foreach($vProjects as $proj)
                        @php
                            $statusColor = match($proj->status?->value ?? '') {
                                'active'     => 'badge-success',
                                'planning'   => 'badge-info',
                                'on_hold'    => 'badge-warning',
                                'completed'  => 'badge-neutral',
                                'cancelled'  => 'badge-error',
                                default      => 'badge-ghost',
                            };
                            $statusLabel = match($proj->status?->value ?? '') {
                                'active'    => 'Đang chạy',
                                'planning'  => 'Lập kế hoạch',
                                'on_hold'   => 'Tạm dừng',
                                'completed' => 'Hoàn thành',
                                'cancelled' => 'Đã hủy',
                                default     => $proj->status?->value ?? '—',
                            };
                        @endphp
                        <div class="flex items-center gap-3 px-4 py-2.5 hover:bg-base-50">
                            <div class="flex-1 min-w-0">
                                <span class="font-medium text-sm truncate block">{{ $proj->name }}</span>
                                <span class="text-xs text-base-content/40">{{ $proj->code }}</span>
                            </div>
                            <span class="badge badge-xs {{ $statusColor }}">{{ $statusLabel }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            </div>{{-- /grid --}}
        </div>
        @endforeach
    @endif

</div>
@endsection
