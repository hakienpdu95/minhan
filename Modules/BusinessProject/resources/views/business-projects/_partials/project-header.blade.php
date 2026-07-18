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

{{--
    Vertical Tabs = 8 Workspace (Phần 5B) — điều hướng chính, KHÔNG dùng sidebar module rời.
    8/8 workspace đã triển khai (Context/Discovery/Diagnosis/Transformation/Delivery/Closing/
    Knowledge/Customer Success) — không còn tab disabled.
--}}
<div role="tablist" class="tabs tabs-bordered mb-4">
    <a role="tab" href="{{ route('backend.business-projects.show', $businessProject) }}"
       class="tab @if(request()->routeIs('backend.business-projects.show')) tab-active @endif">Bối cảnh Doanh nghiệp</a>
    <a role="tab" href="{{ route('backend.business-projects.discovery.show', $businessProject) }}"
       class="tab @if(request()->routeIs('backend.business-projects.discovery.*')) tab-active @endif">Khảo sát</a>
    <a role="tab" href="{{ route('backend.business-projects.diagnosis.show', $businessProject) }}"
       class="tab @if(request()->routeIs('backend.business-projects.diagnosis.*')) tab-active @endif">Chẩn đoán</a>
    <a role="tab" href="{{ route('backend.business-projects.transformation.show', $businessProject) }}"
       class="tab @if(request()->routeIs('backend.business-projects.transformation.*')) tab-active @endif">Chuyển đổi</a>
    <a role="tab" href="{{ route('backend.business-projects.delivery.show', $businessProject) }}"
       class="tab @if(request()->routeIs('backend.business-projects.delivery.*')) tab-active @endif">Triển khai</a>
    <a role="tab" href="{{ route('backend.business-projects.closing.show', $businessProject) }}"
       class="tab @if(request()->routeIs('backend.business-projects.closing.*')) tab-active @endif">Đóng dự án</a>
    <a role="tab" href="{{ route('backend.business-projects.knowledge.show', $businessProject) }}"
       class="tab @if(request()->routeIs('backend.business-projects.knowledge.*')) tab-active @endif">Tri thức</a>
    <a role="tab" href="{{ route('backend.business-projects.customer-success.show', $businessProject) }}"
       class="tab @if(request()->routeIs('backend.business-projects.customer-success.*')) tab-active @endif">Chăm sóc khách hàng</a>
</div>
