@extends('layouts.backend')

@section('title', 'Bảng điều khiển BCOS')

@section('content')
<div>
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Bảng điều khiển Quản trị BCOS</h1>
            <p class="text-sm text-base-content/50 mt-0.5">
                "BCOS tự đo chính nó" (Phần 10 spec) — KPI cấp toàn hệ thống, chỉ Founder/Admin truy cập.
            </p>
        </div>
        <a href="{{ route('backend.business-projects.index') }}" class="btn btn-ghost btn-sm">← Danh sách Dự án</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">

        {{-- 1. Gate Compliance Rate --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold">Tỷ lệ tuân thủ điều kiện chuyển giai đoạn (Gate)</h2>
                    <a href="{{ route('backend.business-projects.bcos-dashboard.export.gate-compliance') }}" class="btn btn-ghost btn-xs">Xuất CSV</a>
                </div>
                <p class="text-3xl font-bold mt-1">{{ $gateCompliance['rate'] !== null ? $gateCompliance['rate'].'%' : '—' }}</p>
                <p class="text-xs text-base-content/50 mt-1">
                    {{ $gateCompliance['stuck'] }}/{{ $gateCompliance['total_active'] }} dự án đang active
                    bị trễ gate (đủ điều kiện qua nhưng chưa ai bấm chuyển ≥7 ngày).
                </p>
            </div>
        </div>

        {{-- 2. Knowledge Reuse Rate --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold">Tỷ lệ tái sử dụng tri thức</h2>
                    <a href="{{ route('backend.business-projects.bcos-dashboard.export.knowledge-reuse') }}" class="btn btn-ghost btn-xs">Xuất CSV</a>
                </div>
                <p class="text-3xl font-bold mt-1">{{ $knowledgeReuse['rate'] !== null ? $knowledgeReuse['rate'].'%' : '—' }}</p>
                <p class="text-xs text-base-content/50 mt-1">
                    {{ $knowledgeReuse['reused'] }}/{{ $knowledgeReuse['total'] }} Knowledge Asset được tra cứu
                    ở dự án khác (ước tính qua lượt xem — chưa có bảng theo dõi tra cứu theo project).
                </p>
            </div>
        </div>

        {{-- 3. Average Cycle Time --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold">Thời gian trung bình mỗi giai đoạn</h2>
                    <a href="{{ route('backend.business-projects.bcos-dashboard.export.cycle-time') }}" class="btn btn-ghost btn-xs">Xuất CSV</a>
                </div>
                <table class="table table-xs mt-2">
                    <tbody>
                        @foreach($cycleTime as $row)
                        <tr>
                            <td>{{ $row['Giai đoạn'] }}</td>
                            <td class="text-right">
                                @if(is_numeric($row['Thời gian trung bình (ngày)']))
                                    {{ $row['Thời gian trung bình (ngày)'] }} ngày
                                @else
                                    <span class="text-base-content/30">{{ $row['Thời gian trung bình (ngày)'] }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 4. Deliverable Version Discipline --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold">Kỷ luật phiên bản Deliverable</h2>
                    <a href="{{ route('backend.business-projects.bcos-dashboard.export.deliverable-discipline') }}" class="btn btn-ghost btn-xs">Xuất CSV</a>
                </div>
                <p class="text-3xl font-bold mt-1">{{ $deliverableDiscipline['rate'] !== null ? $deliverableDiscipline['rate'].'%' : '—' }}</p>
                <p class="text-xs text-base-content/50 mt-1">
                    {{ $deliverableDiscipline['multi_version'] }}/{{ $deliverableDiscipline['total_confirmed'] }} deliverable
                    confirmed có ≥2 version (chứng minh có review thật).
                </p>
                <p class="text-xs text-base-content/30 mt-1">
                    Sub-metric "% dùng Template chuẩn" chưa đo được — Thư viện Mẫu (Template Library) chưa xây (Phase 2 mảng 5/5).
                </p>
            </div>
        </div>

        {{-- 5. CSAT/NPS + Renewal Rate --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold">CSAT / NPS / Gia hạn</h2>
                    <a href="{{ route('backend.business-projects.bcos-dashboard.export.csat-nps') }}" class="btn btn-ghost btn-xs">Xuất CSV</a>
                </div>
                <div class="grid grid-cols-3 gap-2 mt-1">
                    <div>
                        <p class="text-2xl font-bold">{{ $csatNps['csat_avg'] ?? '—' }}</p>
                        <p class="text-xs text-base-content/50">CSAT TB (/5)</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $csatNps['nps_avg'] ?? '—' }}</p>
                        <p class="text-xs text-base-content/50">NPS TB (/10)</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $csatNps['renewal_rate'] !== null ? $csatNps['renewal_rate'].'%' : '—' }}</p>
                        <p class="text-xs text-base-content/50">Gia hạn ({{ $csatNps['renewal_decided'] }} đã quyết định)</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 6. R7 Fulfillment Rate --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold">Tỷ lệ hoàn thành Rule R7</h2>
                    <a href="{{ route('backend.business-projects.bcos-dashboard.export.r7-fulfillment') }}" class="btn btn-ghost btn-xs">Xuất CSV</a>
                </div>
                <p class="text-3xl font-bold mt-1">{{ $r7Fulfillment['rate'] !== null ? $r7Fulfillment['rate'].'%' : '—' }}</p>
                <p class="text-xs text-base-content/50 mt-1">
                    {{ $r7Fulfillment['with_asset'] }}/{{ $r7Fulfillment['total_closed'] }} dự án đã đóng có ≥1 Knowledge Asset
                    — trung bình {{ $r7Fulfillment['avg_assets_per_project'] ?? '—' }} asset/dự án.
                </p>
            </div>
        </div>

    </div>
</div>
@endsection
