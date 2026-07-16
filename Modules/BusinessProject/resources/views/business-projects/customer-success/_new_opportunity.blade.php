{{--
    New Opportunity -> Tạo Lead — Giai đoạn 8 "khép vòng lặp toàn hệ thống": chiều ngược của
    Convert Lead -> Business Project (Context Workspace). Tái dùng nguyên CreateLeadAction của
    module Lead qua CreateLeadFromOpportunityAction — không tự chế logic tạo Lead.
    Biến: $businessProject.
--}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <h2 class="font-semibold mb-3">New Opportunity</h2>
        <p class="text-xs text-base-content/50 mb-3">
            Phát hiện cơ hội mới từ khách hàng cũ? Tạo Lead để đội Sales tiếp tục theo dõi —
            nguồn Lead sẽ ghi rõ xuất phát từ dự án {{ $businessProject->code }}.
        </p>

        <form action="{{ route('backend.business-projects.customer-success.lead.create', $businessProject) }}" method="POST" class="space-y-3">
            @csrf
            <div class="form-control">
                <label class="label py-0 pb-1"><span class="label-text text-xs font-medium">Tiêu đề cơ hội</span></label>
                <input type="text" name="title" class="input input-bordered input-sm w-full" placeholder="VD: Mở rộng hợp đồng năm sau">
            </div>
            <div class="form-control">
                <label class="label py-0 pb-1"><span class="label-text text-xs font-medium">Giá trị dự kiến (VNĐ)</span></label>
                <input type="number" name="expected_value" min="0" step="1" class="input input-bordered input-sm w-full">
            </div>
            <div class="form-control">
                <label class="label py-0 pb-1"><span class="label-text text-xs font-medium">Mô tả</span></label>
                <textarea name="description" rows="2" class="textarea textarea-bordered textarea-sm w-full"></textarea>
            </div>
            <button type="submit" class="btn btn-secondary btn-sm">Tạo Lead mới</button>
        </form>
    </div>
</div>
