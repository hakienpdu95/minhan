{{--
    Industry Knowledge Search — Giai đoạn 7 spec: "khép vòng tri thức". Consultant ở Discovery
    tra cứu nhanh Case Study/Lessons Learned/Best Practice/Industry Knowledge của các Business
    Project trước cùng Industry với khách hàng hiện tại — không xây search engine riêng trong
    BCOS, chỉ đếm nhanh rồi link-out sang KcItem index (đã có filter `ind`, xem
    Modules/KcItem/resources/assets/js/pages/kc-item-index.js). Biến cần truyền vào:
    $projectIndustry (string|null — industry của Customer gắn với Business Project),
    $industryKcCount (int — số KcItem tri thức dự án khớp industry).
--}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body py-3 px-4">
        <h3 class="font-semibold text-sm mb-2">Tra cứu tri thức theo Ngành</h3>

        @if($projectIndustry)
        <p class="text-xs text-base-content/50 mb-2">
            Ngành của khách hàng: <span class="font-medium text-base-content">{{ $projectIndustry }}</span>
        </p>
        <p class="text-xs mb-3">
            <span class="badge badge-sm {{ $industryKcCount > 0 ? 'badge-success' : 'badge-ghost' }}">{{ $industryKcCount }}</span>
            Knowledge Asset cùng ngành từ các dự án trước
        </p>
        <a href="{{ route('backend.kc-items.index', ['ind' => $projectIndustry]) }}"
           class="btn btn-outline btn-xs w-full" target="_blank" rel="noopener">
            Xem Case Study / Lessons Learned...
        </a>
        @else
        <p class="text-xs text-base-content/40">
            Khách hàng chưa khai báo Ngành — bổ sung ở hồ sơ Customer để tra cứu tri thức liên quan.
        </p>
        @endif
    </div>
</div>
