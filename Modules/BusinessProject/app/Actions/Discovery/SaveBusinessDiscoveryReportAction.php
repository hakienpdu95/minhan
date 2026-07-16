<?php

namespace Modules\BusinessProject\Actions\Discovery;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Actions\Deliverable\UpsertSingletonDeliverableAction;
use Modules\BusinessProject\Data\Requests\StoreBusinessDiscoveryReportData;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\Deliverable;

/**
 * Business Discovery Report — tổng hợp cuối Discovery Workspace (spec Giai đoạn 2). Cùng
 * deliverable với container cha của Interview/Observation... (AddDiscoveryRecordAction tự
 * khởi tạo rỗng nếu chưa có) — action này chỉ ghi thêm version nội dung tổng hợp, không
 * tạo bản ghi mới nếu deliverable đã tồn tại từ trước.
 */
class SaveBusinessDiscoveryReportAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, StoreBusinessDiscoveryReportData $data): Deliverable
    {
        return UpsertSingletonDeliverableAction::run(
            $businessProject,
            DeliverableType::BusinessDiscoveryReport,
            'Business Discovery Report',
            [
                'summary' => $data->summary,
            ],
            'Cập nhật Business Discovery Report.',
        );
    }
}
