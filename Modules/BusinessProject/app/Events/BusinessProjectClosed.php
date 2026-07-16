<?php

namespace Modules\BusinessProject\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\BusinessProject\Models\BusinessProject;

/**
 * Spec Giai đoạn 6: "Hệ thống phản ứng khi đóng thành công: bắn event BusinessProjectClosed →
 * tự động tạo Project Retrospective (gợi ý) → khởi động vòng lặp CLS; đồng thời kích hoạt
 * Customer Success Workspace." Retrospective tự động + Customer Success là Phase 2 (Knowledge
 * Workspace) — event tồn tại sẵn để Phase 2 chỉ cần thêm Listener, không cần sửa lại chỗ bắn.
 */
class BusinessProjectClosed
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly BusinessProject $businessProject) {}
}
