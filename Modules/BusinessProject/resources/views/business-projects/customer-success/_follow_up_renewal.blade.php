{{--
    Follow-up định kỳ + Renewal — Giai đoạn 8. Ghi nhận độc lập từng phần (chỉ follow-up, chỉ
    renewal, hoặc cả hai) — tạo 1 hàng success_reviews mới mỗi lần submit (lịch sử, không ghi
    đè). Biến: $businessProject.
--}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <h2 class="font-semibold mb-3">Follow-up &amp; Renewal</h2>

        @if($errors->any())
        <div class="alert alert-error mb-3 text-xs">
            <ul class="list-disc pl-4">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('backend.business-projects.customer-success.notes.store', $businessProject) }}" method="POST" class="space-y-3">
            @csrf

            <div class="grid grid-cols-2 gap-3">
                <div class="form-control">
                    <label class="label py-0 pb-1"><span class="label-text text-xs font-medium">Lịch follow-up tiếp theo</span></label>
                    <input type="date" name="follow_up_at" class="input input-bordered input-sm w-full">
                </div>
                <div class="form-control">
                    <label class="label py-0 pb-1"><span class="label-text text-xs font-medium">Trạng thái Renewal</span></label>
                    <select name="renewal_status" class="select select-bordered select-sm w-full">
                        <option value="">— Không đổi —</option>
                        @foreach(\Modules\BusinessProject\Enums\RenewalStatus::cases() as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-control">
                <label class="label py-0 pb-1"><span class="label-text text-xs font-medium">Ghi chú follow-up</span></label>
                <textarea name="follow_up_note" rows="2" class="textarea textarea-bordered textarea-sm w-full"></textarea>
            </div>

            <div class="form-control">
                <label class="label py-0 pb-1"><span class="label-text text-xs font-medium">Ghi chú Renewal</span></label>
                <textarea name="renewal_note" rows="2" class="textarea textarea-bordered textarea-sm w-full"></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-sm">Ghi nhận</button>
        </form>
    </div>
</div>
