<?php

namespace Modules\BusinessProject\Actions\Discovery;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Data\ImportDiscoveryRecordsResult;
use Modules\BusinessProject\Data\Requests\StoreDiscoveryRecordData;
use Modules\BusinessProject\Models\BusinessProject;

/**
 * Import hàng loạt Discovery record (spec Giai đoạn 2: "nơi nhập liệu thủ công nhiều nhất") từ
 * file Excel/CSV — mỗi dòng hợp lệ đi qua ĐÚNG `AddDiscoveryRecordAction` (action dùng cho form
 * nhập tay), không tự chế logic tạo Deliverable riêng cho đường import. Lỗi từng dòng không chặn
 * các dòng còn lại — báo lại đầy đủ để Consultant sửa file và import lại phần thiếu.
 */
class ImportDiscoveryRecordsAction
{
    use AsAction;

    /** @param Collection<int, array<string, mixed>> $rows Đã đọc từ FastExcel, key = tên cột header. */
    public function handle(BusinessProject $businessProject, Collection $rows): ImportDiscoveryRecordsResult
    {
        $imported = 0;
        $errors = [];

        foreach ($rows->values() as $index => $row) {
            $rowNumber = $index + 2; // +1 (0-index → 1-index), +1 (dòng header đã bị FastExcel bóc riêng)
            $normalized = $this->normalizeRow($row);

            $validator = Validator::make(
                $normalized,
                StoreDiscoveryRecordData::rules(),
                StoreDiscoveryRecordData::messages(),
            );

            if ($validator->fails()) {
                $errors[] = "Dòng {$rowNumber}: " . $validator->errors()->first();
                continue;
            }

            try {
                $data = StoreDiscoveryRecordData::from($validator->validated());
                AddDiscoveryRecordAction::run($businessProject, $data);
                $imported++;
            } catch (\Throwable $e) {
                $errors[] = "Dòng {$rowNumber}: " . $e->getMessage();
            }
        }

        return new ImportDiscoveryRecordsResult(
            total: $rows->count(),
            imported: $imported,
            errors: $errors,
        );
    }

    /** @param array<string, mixed> $row */
    private function normalizeRow(array $row): array
    {
        // Header người dùng gõ lại có thể lệch hoa/thường hoặc dư khoảng trắng so với template
        // đã phát hành — chuẩn hoá key trước khi validate, không bắt Consultant sửa file tuyệt đối
        // khớp ký tự.
        $byKey = [];
        foreach ($row as $key => $value) {
            $byKey[strtolower(trim((string) $key))] = is_string($value) ? trim($value) : $value;
        }

        return [
            'type' => $byKey['type'] ?? null,
            'title' => $byKey['title'] ?? null,
            'notes' => $byKey['notes'] ?? null,
            'occurred_at' => $this->normalizeDate($byKey['occurred_at'] ?? null),
            'participants' => $byKey['participants'] ?? null,
        ];
    }

    private function normalizeDate(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return (string) $value;
    }
}
