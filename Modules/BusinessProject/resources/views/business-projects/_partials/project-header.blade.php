{{--
    Project Header — cố định trên mọi màn hình trong 1 Business Project (Phần 5B spec).
    Biến cần truyền vào: $businessProject.
--}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <h1 class="text-xl font-bold text-base-content">{{ $businessProject->name }}</h1>
            <span class="badge badge-sm badge-primary">{{ $businessProject->current_stage->label() }}</span>
        </div>
        <p class="text-sm text-base-content/50">
            {{ $businessProject->code }}
            @if($businessProject->customer)
                &middot; {{ $businessProject->customer->display_name }}
            @endif
        </p>
    </div>

    @if(request()->routeIs('backend.business-projects.*'))
    <div class="dropdown dropdown-end">
        <label tabindex="0" class="btn btn-ghost btn-sm gap-1.5">
            Đổi Project
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </label>
        <ul tabindex="0" class="dropdown-content menu menu-sm z-10 p-2 shadow bg-base-100 rounded-box w-56">
            <li><a href="{{ route('backend.business-projects.index') }}">Xem tất cả Business Project</a></li>
        </ul>
    </div>
    @endif
</div>

{{-- Vertical Tabs = 8 Workspace (Phần 5B) — điều hướng chính, KHÔNG dùng sidebar module rời. --}}
<div role="tablist" class="tabs tabs-bordered mb-4">
    <a role="tab" class="tab tab-active">Context</a>
    @foreach(['discovery' => 'Discovery', 'diagnosis' => 'Diagnosis', 'transformation' => 'Transformation', 'delivery' => 'Delivery', 'closing' => 'Closing', 'knowledge' => 'Knowledge', 'customer_success' => 'Customer Success'] as $key => $label)
    <a role="tab" class="tab tab-disabled text-base-content/30" title="Chưa triển khai ở Phase này">
        {{ $label }}
        <svg class="w-3 h-3 ml-1 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 10-8 0v4h8z"/>
        </svg>
    </a>
    @endforeach
</div>
