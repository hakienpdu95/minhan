<?php

namespace Modules\BusinessProject\Actions\Deliverable;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Models\Deliverable;

/**
 * Chưa có route/UI gọi tới ở Vertical Slice 1 — Diagnosis Workspace (Phase 2) là nơi
 * Consultant đính evidence từ Discovery vào Diagnosis Matrix. Action tồn tại sẵn theo
 * đúng pattern Evidence Linking (spec Phần 6.2) để Phase 2 chỉ cần nối route/UI, không
 * phải thiết kế lại quan hệ dữ liệu.
 */
class AttachEvidenceAction
{
    use AsAction;

    public function handle(Deliverable $deliverable, Deliverable $evidence, string $evidenceType, ?string $note = null): void
    {
        $deliverable->evidenceFor()->attach($evidence->id, [
            'evidence_type' => $evidenceType,
            'note' => $note,
            'created_by' => Auth::id(),
        ]);
    }
}
