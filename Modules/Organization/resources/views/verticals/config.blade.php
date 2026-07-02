@extends('layouts.backend')
@section('title', 'Cấu hình ' . $vertical->label() . ' — ' . $organization->name)

@section('content')
<div class="max-w-4xl" x-data="{ tab: 'hierarchy' }">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-base-content/40 mb-1">
        <a href="{{ route('backend.organizations.show', $organization) }}" class="hover:text-primary">{{ $organization->name }}</a>
        <span>/</span>
        <a href="{{ route('backend.organizations.verticals.index', $organization) }}" class="hover:text-primary">Dịch vụ</a>
        <span>/</span>
        <span>{{ $vertical->label() }}</span>
    </div>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Cấu hình — {{ $vertical->label() }}</h1>
            <p class="text-sm text-base-content/50 mt-0.5">Tuỳ chỉnh nhãn, danh mục và phân cấp cho tổ chức này</p>
        </div>
        <a href="{{ route('backend.organizations.verticals.index', $organization) }}"
           class="btn btn-ghost btn-sm gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Quay lại
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success mb-4 py-2.5 text-sm">{{ session('success') }}</div>
    @endif

    {{-- Builder phase/checklist — bản riêng của tổ chức, độc lập với thư viện --}}
    <div class="card bg-base-100 border border-base-200 shadow-sm mb-6">
        <div class="card-body">
            @include('backend.vertical-templates.builder', ['template' => $orgVertical, 'phasesData' => $phasesData])
        </div>
    </div>

    <h2 class="font-bold text-sm text-base-content/70 uppercase tracking-wide mb-3">Danh mục cấu hình</h2>

    {{-- Tab bar --}}
    <div class="tabs tabs-box mb-5">
        @php
            $tabs = [
                'hierarchy'      => ['label' => 'Phân cấp địa lý',       'icon' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                'activity_type'  => ['label' => 'Loại hoạt động',         'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                'doc_type'       => ['label' => 'Loại giấy tờ',           'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ];
        @endphp
        @foreach($tabs as $key => $t)
        @php $hasItems = ($configItems->get($key)?->count() ?? 0) > 0; @endphp
        <button type="button"
                class="tab gap-1.5 {{ !$hasItems ? 'opacity-40' : '' }}"
                :class="tab === '{{ $key }}' ? 'tab-active' : ''"
                x-on:click="tab = '{{ $key }}'">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $t['icon'] }}"/>
            </svg>
            {{ $t['label'] }}
            @if($hasItems)
            <span class="badge badge-xs">{{ $configItems->get($key)->count() }}</span>
            @endif
        </button>
        @endforeach
    </div>

    {{-- Tab panels — each submits its own form --}}
    @foreach($tabs as $tabKey => $tabMeta)
    @php $items = $configItems->get($tabKey) ?? collect(); @endphp
    <div x-show="tab === '{{ $tabKey }}'" x-cloak>

        @if($items->isEmpty())
        <div class="card bg-base-100 border border-base-200">
            <div class="card-body py-10 text-center text-sm text-base-content/40">
                Không có mục nào cho nhóm này.
            </div>
        </div>
        @else
        <form method="POST"
              action="{{ route('backend.organizations.verticals.updateConfig', [$organization, $vertical->code()]) }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="_tab" value="{{ $tabKey }}">

            <div class="card bg-base-100 border border-base-200 shadow-sm">
                <div class="card-body p-0">
                    {{-- Column headers --}}
                    <div class="grid grid-cols-12 gap-3 px-4 py-2 border-b border-base-200 bg-base-50 text-xs font-semibold text-base-content/40 uppercase tracking-wide rounded-t-2xl">
                        <div class="col-span-3">Mã</div>
                        <div class="col-span-6">Nhãn hiển thị</div>
                        <div class="col-span-2 text-center">Bật</div>
                        <div class="col-span-1"></div>
                    </div>

                    <div class="divide-y divide-base-200">
                        @foreach($items as $item)
                        <div class="grid grid-cols-12 gap-3 items-center px-4 py-3 hover:bg-base-50/50 transition-colors">
                            {{-- Code (read-only) --}}
                            <div class="col-span-3">
                                <code class="text-xs text-base-content/50 font-mono break-all">{{ $item->code }}</code>
                                @if($item->is_required)
                                <span class="block text-xs text-warning mt-0.5">bắt buộc</span>
                                @endif
                            </div>
                            {{-- Label (editable) --}}
                            <div class="col-span-6">
                                <input type="text"
                                       name="items[{{ $item->id }}][label]"
                                       value="{{ old('items.' . $item->id . '.label', $item->label) }}"
                                       class="input input-sm input-bordered w-full text-sm"
                                       {{ $item->is_required ? 'required' : '' }}>
                            </div>
                            {{-- is_active toggle --}}
                            <div class="col-span-2 flex justify-center">
                                <input type="checkbox"
                                       name="items[{{ $item->id }}][is_active]"
                                       value="1"
                                       class="toggle toggle-sm toggle-success"
                                       {{ $item->is_active ? 'checked' : '' }}
                                       {{ $item->is_required ? 'disabled' : '' }}>
                                @if($item->is_required)
                                <input type="hidden" name="items[{{ $item->id }}][is_active]" value="1">
                                @endif
                            </div>
                            {{-- Status indicator --}}
                            <div class="col-span-1 flex justify-end">
                                <div class="w-2 h-2 rounded-full {{ $item->is_active ? 'bg-success' : 'bg-base-300' }}"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-4">
                <button type="submit" class="btn btn-primary btn-sm gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Lưu {{ $tabMeta['label'] }}
                </button>
            </div>
        </form>
        @endif
    </div>
    @endforeach

</div>
@endsection

@push('styles')
    @vite(['Modules/Deployment/resources/assets/sass/deployment.scss'], 'build/backend')
@endpush

@push('scripts')
    @vite([
        'resources/js/modules/tom-select.js',
        'Modules/Deployment/resources/assets/js/deployment.js',
    ], 'build/backend')
@endpush
