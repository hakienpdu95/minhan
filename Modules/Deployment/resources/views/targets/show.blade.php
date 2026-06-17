@extends('layouts.backend')
@section('title', $target->targetOrganization?->name)

@section('content')
<div>
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <div class="text-sm text-base-content/50 mb-1">
                <a href="{{ route('deployment.targets.index', ['vertical' => $vertical->code()]) }}" class="hover:underline">
                    {{ $vertical->targetLabel() }}
                </a> /
            </div>
            <h1 class="text-2xl font-bold">{{ $target->targetOrganization?->name }}</h1>
            <p class="text-sm text-base-content/50">
                MST: {{ $target->targetOrganization?->tax_code ?? '—' }} &nbsp;·&nbsp;
                Dự án: {{ $target->project?->name ?? '—' }}
            </p>
        </div>
        <div class="flex gap-2">
            <span class="badge badge-lg">{{ $target->current_phase }}</span>
            @can('advance', $target)
            @if($target->current_phase !== last($phases))
            <form method="POST" action="{{ route('deployment.targets.advance', ['vertical' => $vertical->code(), 'target' => $target->id]) }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary"
                        onclick="return confirm('Chuyển sang phase tiếp theo?')">
                    Tiếp theo →
                </button>
            </form>
            @endif
            @endcan
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success mb-4"><span>{{ session('success') }}</span></div>
    @endif
    @if($errors->has('advance'))
    <div class="alert alert-error mb-4"><span>{{ $errors->first('advance') }}</span></div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Checklist --}}
        <div class="lg:col-span-2">
            <div class="card bg-base-100 border border-base-200 shadow-sm">
                <div class="card-body p-0">
                    <div class="p-4 border-b border-base-200 flex items-center justify-between">
                        <h2 class="font-semibold">Checklist — {{ $target->current_phase }}</h2>
                        <div class="flex items-center gap-2">
                            <progress class="progress progress-primary w-24"
                                      value="{{ $phaseProgress['pct'] }}" max="100"></progress>
                            <span class="text-xs">{{ $phaseProgress['done'] }}/{{ $phaseProgress['total'] }}</span>
                        </div>
                    </div>

                    @forelse($checklist as $item)
                    <div class="flex items-center gap-3 px-4 py-3 border-b border-base-200 last:border-0
                                {{ $item->is_done ? 'opacity-60' : '' }}">
                        <form method="POST"
                              action="{{ route('deployment.checklist.toggle', ['vertical' => $vertical->code(), 'item' => $item->id]) }}">
                            @csrf
                            <button type="submit"
                                    class="checkbox {{ $item->is_done ? 'checkbox-success' : '' }}"
                                    style="width:1.2rem;height:1.2rem;border-radius:4px;border:2px solid currentColor;
                                           background:{{ $item->is_done ? 'oklch(var(--su))' : 'transparent' }}">
                            </button>
                        </form>
                        <div class="flex-1 min-w-0">
                            <span class="text-sm {{ $item->is_done ? 'line-through' : '' }}">
                                {{ $item->item_label }}
                            </span>
                            @if($item->is_required)
                            <span class="badge badge-xs badge-error ml-1">bắt buộc</span>
                            @endif
                        </div>
                        @if($item->is_done && $item->doneBy)
                        <span class="text-xs text-base-content/40">{{ $item->doneBy?->name }}</span>
                        @endif
                    </div>
                    @empty
                    <div class="p-6 text-center text-base-content/40 text-sm">Không có checklist cho phase này.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sidebar info --}}
        <div class="space-y-4">
            {{-- Org info --}}
            <div class="card bg-base-100 border border-base-200 shadow-sm">
                <div class="card-body">
                    <h3 class="font-semibold text-sm mb-2">Thông tin {{ $vertical->targetLabel() }}</h3>
                    <dl class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-base-content/50">Tên</dt>
                            <dd class="font-medium">{{ $target->targetOrganization?->name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-base-content/50">MST</dt>
                            <dd>{{ $target->targetOrganization?->tax_code ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-base-content/50">Điện thoại</dt>
                            <dd>{{ $target->targetOrganization?->phone ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-base-content/50">Địa chỉ</dt>
                            <dd class="text-right">{{ $target->targetOrganization?->full_address ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Phase progress --}}
            <div class="card bg-base-100 border border-base-200 shadow-sm">
                <div class="card-body">
                    <h3 class="font-semibold text-sm mb-2">Tiến trình</h3>
                    <ol class="steps steps-vertical text-xs">
                        @foreach($phases as $phase)
                        <li class="step {{ $loop->index <= array_search($target->current_phase, $phases) ? 'step-primary' : '' }}">
                            {{ $phase }}
                        </li>
                        @endforeach
                    </ol>
                </div>
            </div>

            {{-- Readiness card --}}
            <div class="card bg-base-100 border border-base-200 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-sm">Readiness Assessment</h3>
                        @if($target->readiness_score !== null)
                        <a href="{{ route('deployment.readiness.fill', ['vertical' => $vertical->code(), 'target' => $target->id]) }}"
                           class="btn btn-ghost btn-xs">Làm lại</a>
                        @endif
                    </div>

                    @if($target->readiness_score !== null)
                    @php
                        $rs = $target->readiness_score;
                        $rColor = $rs >= 80 ? 'success' : ($rs >= 60 ? 'info' : ($rs >= 40 ? 'warning' : 'error'));
                        $rBand  = $rs >= 80 ? 'Sẵn sàng' : ($rs >= 60 ? 'Gần sẵn sàng' : ($rs >= 40 ? 'Sẵn sàng có hỗ trợ' : 'Chưa sẵn sàng'));
                    @endphp
                    <div class="flex items-center gap-3">
                        <div class="radial-progress text-{{ $rColor }}"
                             style="--value:{{ $rs }}; --size:3.5rem; --thickness:5px;" role="progressbar">
                            <span class="text-xs font-bold">{{ $rs }}</span>
                        </div>
                        <div>
                            <p class="font-semibold text-sm">Readiness: {{ $rs }}/100</p>
                            <p class="text-xs text-base-content/50">{{ $rBand }}</p>
                        </div>
                    </div>
                    <a href="{{ route('deployment.readiness.show', ['vertical' => $vertical->code(), 'target' => $target->id]) }}"
                       class="btn btn-outline btn-xs w-full mt-2">Xem chi tiết & Gap analysis</a>
                    @else
                    <p class="text-xs text-base-content/50 mb-3">Chưa thực hiện đánh giá sẵn sàng.</p>
                    <form method="POST"
                          action="{{ route('deployment.readiness.start', ['vertical' => $vertical->code(), 'target' => $target->id]) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-xs w-full">
                            Bắt đầu đánh giá
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Quick links --}}
            <div class="card bg-base-100 border border-base-200 shadow-sm">
                <div class="card-body">
                    <h3 class="font-semibold text-sm mb-2">Nhanh</h3>
                    <div class="flex flex-col gap-1">
                        <a href="{{ route('deployment.issues.index', ['vertical' => $vertical->code(), 'target_id' => $target->id]) }}"
                           class="btn btn-ghost btn-xs justify-start">
                            Issues
                            @if($openIssues > 0)
                            <span class="badge badge-error badge-xs">{{ $openIssues }}</span>
                            @endif
                        </a>
                        <a href="{{ route('deployment.progress.index', ['vertical' => $vertical->code(), 'target_id' => $target->id]) }}"
                           class="btn btn-ghost btn-xs justify-start">Nhật ký tiến độ</a>
                        <a href="{{ route('deployment.mobile.checklist', ['vertical' => $vertical->code(), 'target' => $target->id]) }}"
                           class="btn btn-ghost btn-xs justify-start">📱 Checklist di động</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
