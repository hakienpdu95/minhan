{{--
    Proposal — Rule R4: soạn thảo tự do khi draft, "Gửi phê duyệt nội bộ" (Approval Service,
    dùng lại đúng flow Ringlesoft như Context) trước khi gửi khách, rồi Consultant/PM tick
    "Confirmed" sau khi khách ký duyệt ngoài hệ thống. Gate R4 yêu cầu Proposal VÀ SOW cùng
    confirmed. Biến cần truyền vào: $businessProject, $proposal (Deliverable|null),
    $proposalTemplates (DeliverableTemplate[] — Template Library, Phase 2 mảng 5/5).
--}}
@php
    $proposalContent = $proposal?->versions->first()?->content ?? [];
@endphp

<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Đề xuất (Proposal)</h2>
            @if($proposal && $proposal->current_version > 0)
            <span class="badge {{ $proposal->status->badgeClass() }}">
                {{ $proposal->status->label() }} &middot; v{{ $proposal->current_version }}
            </span>
            @endif
        </div>

        @if(!$proposal || $proposal->status->value !== 'confirmed')
        <form action="{{ route('backend.business-projects.transformation.proposal.save', $businessProject) }}" method="POST" class="space-y-4"
              x-data="{
                  templates: {{ Js::from($proposalTemplates->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'content' => $t->content])) }},
                  applyTemplate(id) {
                      const t = this.templates.find(x => x.id == id);
                      if (!t) return;
                      this.$refs.solution.value = t.content.solution ?? '';
                      this.$refs.collaboration_plan.value = t.content.collaboration_plan ?? '';
                  }
              }">
            @csrf
            @if($proposalTemplates->isNotEmpty())
            <div class="form-control">
                <label class="label py-0 pb-1"><span class="label-text text-xs font-medium">Bắt đầu từ Template</span></label>
                <select name="template_id" class="select select-bordered select-sm w-full" @change="applyTemplate($event.target.value)">
                    <option value="">— Không dùng template —</option>
                    @foreach($proposalTemplates as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <label class="label label-text text-sm font-medium">Giải pháp đề xuất</label>
                <textarea name="solution" rows="3" class="textarea textarea-bordered w-full" x-ref="solution">{{ old('solution', $proposalContent['solution'] ?? '') }}</textarea>
            </div>
            <div>
                <label class="label label-text text-sm font-medium">Kế hoạch hợp tác</label>
                <textarea name="collaboration_plan" rows="3" class="textarea textarea-bordered w-full" x-ref="collaboration_plan">{{ old('collaboration_plan', $proposalContent['collaboration_plan'] ?? '') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                {{ $proposal ? 'Cập nhật Proposal' : 'Lưu Proposal' }}
            </button>
        </form>
        @else
        <div class="text-sm space-y-1">
            <p><span class="text-base-content/50">Giải pháp đề xuất:</span> {{ $proposalContent['solution'] ?? '' }}</p>
            <p><span class="text-base-content/50">Kế hoạch hợp tác:</span> {{ $proposalContent['collaboration_plan'] ?? '' }}</p>
        </div>
        @endif

        @if($proposal)
        <div class="divider"></div>
        <div class="flex flex-wrap items-center gap-2">
            @if($proposal->status->value === 'draft')
            <form action="{{ route('backend.business-projects.transformation.submit', ['businessProject' => $businessProject, 'type' => 'proposal']) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm">Gửi phê duyệt nội bộ</button>
            </form>
            @endif

            @if($proposal->status->value === 'submitted' && auth()->user()?->can('approve', $proposal))
            <form action="{{ route('backend.business-projects.transformation.approve', ['businessProject' => $businessProject, 'type' => 'proposal']) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">Duyệt nội bộ</button>
            </form>
            <form action="{{ route('backend.business-projects.transformation.reject', ['businessProject' => $businessProject, 'type' => 'proposal']) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-error btn-sm btn-outline">Từ chối</button>
            </form>
            @endif

            @if($proposal->status->value === 'approved')
            <form action="{{ route('backend.business-projects.transformation.confirm', ['businessProject' => $businessProject, 'type' => 'proposal']) }}"
                  method="POST" class="flex flex-wrap items-center gap-2">
                @csrf
                <input type="password" name="password" placeholder="Nhập lại mật khẩu để ký"
                       class="input input-bordered input-sm" required autocomplete="current-password">
                <button type="submit" class="btn btn-primary btn-sm">Xác nhận đã ký với khách (Confirmed)</button>
            </form>
            @error('password')<p class="text-error text-xs w-full">{{ $message }}</p>@enderror
            @endif

            @if($proposal->status->value === 'confirmed')
            <div class="text-xs text-base-content/50">
                <p>
                    Đã confirmed lúc {{ $proposal->confirmed_at?->format('d/m/Y H:i') }}
                    @if($proposal->confirmedBy)bởi {{ $proposal->confirmedBy->name }}@endif
                </p>
                @if($proposalSignature)
                <p class="mt-0.5">
                    @if($proposalSignatureVerified)
                    <span class="badge badge-success badge-xs">✓ Chữ ký hợp lệ</span>
                    @else
                    <span class="badge badge-error badge-xs">⚠ Chữ ký không khớp</span>
                    @endif
                    <span class="ml-1">{{ $proposalSignature->algorithm }} &middot; nội bộ, không thay thế chữ ký số pháp lý</span>
                </p>
                @endif
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
