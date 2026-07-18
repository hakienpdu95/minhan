{{--
    Statement of Work (SOW) — cùng luồng Rule R4 như Proposal (xem _proposal.blade.php).
    Biến cần truyền vào: $businessProject, $sow (Deliverable|null), $sowTemplates
    (DeliverableTemplate[] — Template Library, Phase 2 mảng 5/5).
--}}
@php
    $sowContent = $sow?->versions->first()?->content ?? [];
@endphp

<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Thỏa thuận Phạm vi Công việc (SOW)</h2>
            @if($sow && $sow->current_version > 0)
            <span class="badge {{ $sow->status->badgeClass() }}">
                {{ $sow->status->label() }} &middot; v{{ $sow->current_version }}
            </span>
            @endif
        </div>

        @if(!$sow || $sow->status->value !== 'confirmed')
        <form action="{{ route('backend.business-projects.transformation.sow.save', $businessProject) }}" method="POST" class="space-y-4"
              x-data="{
                  templates: {{ Js::from($sowTemplates->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'content' => $t->content])) }},
                  applyTemplate(id) {
                      const t = this.templates.find(x => x.id == id);
                      if (!t) return;
                      this.$refs.scope.value = t.content.scope ?? '';
                      this.$refs.deliverables.value = t.content.deliverables ?? '';
                      this.$refs.responsibilities.value = t.content.responsibilities ?? '';
                  }
              }">
            @csrf
            @if($sowTemplates->isNotEmpty())
            <div class="form-control">
                <label class="label py-0 pb-1"><span class="label-text text-xs font-medium">Bắt đầu từ Template</span></label>
                <select name="template_id" class="select select-bordered select-sm w-full" @change="applyTemplate($event.target.value)">
                    <option value="">— Không dùng template —</option>
                    @foreach($sowTemplates as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <label class="label label-text text-sm font-medium">Phạm vi (Scope)</label>
                <textarea name="scope" rows="2" class="textarea textarea-bordered w-full" x-ref="scope">{{ old('scope', $sowContent['scope'] ?? '') }}</textarea>
            </div>
            <div>
                <label class="label label-text text-sm font-medium">Kết quả bàn giao (Deliverables)</label>
                <textarea name="deliverables" rows="2" class="textarea textarea-bordered w-full" x-ref="deliverables">{{ old('deliverables', $sowContent['deliverables'] ?? '') }}</textarea>
            </div>
            <div>
                <label class="label label-text text-sm font-medium">Trách nhiệm các bên (Responsibilities)</label>
                <textarea name="responsibilities" rows="2" class="textarea textarea-bordered w-full" x-ref="responsibilities">{{ old('responsibilities', $sowContent['responsibilities'] ?? '') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                {{ $sow ? 'Cập nhật SOW' : 'Lưu SOW' }}
            </button>
        </form>
        @else
        <div class="text-sm space-y-1">
            <p><span class="text-base-content/50">Phạm vi:</span> {{ $sowContent['scope'] ?? '' }}</p>
            <p><span class="text-base-content/50">Kết quả bàn giao:</span> {{ $sowContent['deliverables'] ?? '' }}</p>
            <p><span class="text-base-content/50">Trách nhiệm các bên:</span> {{ $sowContent['responsibilities'] ?? '' }}</p>
        </div>
        @endif

        @if($sow)
        <div class="divider"></div>
        <div class="flex flex-wrap items-center gap-2">
            @if($sow->status->value === 'draft')
            <form action="{{ route('backend.business-projects.transformation.submit', ['businessProject' => $businessProject, 'type' => 'sow']) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm">Gửi phê duyệt nội bộ</button>
            </form>
            @endif

            @if($sow->status->value === 'submitted' && auth()->user()?->can('approve', $sow))
            <form action="{{ route('backend.business-projects.transformation.approve', ['businessProject' => $businessProject, 'type' => 'sow']) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">Duyệt nội bộ</button>
            </form>
            <form action="{{ route('backend.business-projects.transformation.reject', ['businessProject' => $businessProject, 'type' => 'sow']) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-error btn-sm btn-outline">Từ chối</button>
            </form>
            @endif

            @if($sow->status->value === 'approved')
            <form action="{{ route('backend.business-projects.transformation.confirm', ['businessProject' => $businessProject, 'type' => 'sow']) }}"
                  method="POST" class="flex flex-wrap items-center gap-2">
                @csrf
                <input type="password" name="password" placeholder="Nhập lại mật khẩu để ký"
                       class="input input-bordered input-sm" required autocomplete="current-password">
                <button type="submit" class="btn btn-primary btn-sm">Xác nhận đã ký với khách (Confirmed)</button>
            </form>
            @error('password')<p class="text-error text-xs w-full">{{ $message }}</p>@enderror
            @endif

            @if($sow->status->value === 'confirmed')
            <div class="text-xs text-base-content/50">
                <p>
                    Đã confirmed lúc {{ $sow->confirmed_at?->format('d/m/Y H:i') }}
                    @if($sow->confirmedBy)bởi {{ $sow->confirmedBy->name }}@endif
                </p>
                @if($sowSignature)
                <p class="mt-0.5">
                    @if($sowSignatureVerified)
                    <span class="badge badge-success badge-xs">✓ Chữ ký hợp lệ</span>
                    @else
                    <span class="badge badge-error badge-xs">⚠ Chữ ký không khớp</span>
                    @endif
                    <span class="ml-1">{{ $sowSignature->algorithm }} &middot; nội bộ, không thay thế chữ ký số pháp lý</span>
                </p>
                @endif
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
