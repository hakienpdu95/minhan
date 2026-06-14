<?php

namespace Modules\Assessment\Services;

use Illuminate\Http\UploadedFile;
use Modules\Assessment\Exceptions\CccdOcrException;
use Modules\Assessment\Services\Ocr\Contracts\CccdOcrDriverInterface;

/**
 * Coordinator cho luồng xác minh CCCD qua OCR.
 *
 * Nhận ảnh từ controller, uỷ quyền nhận dạng cho driver (FPT.AI hoặc driver khác),
 * rồi enrich kết quả với province_code trước khi trả về.
 *
 * Để đổi provider OCR: bind CccdOcrDriverInterface sang driver mới
 * trong AssessmentServiceProvider — không cần sửa controller hay service này.
 */
class CccdOcrService
{
    public function __construct(private readonly CccdOcrDriverInterface $driver) {}

    /**
     * @return array{
     *   cccd_number:   string,
     *   full_name:     string,
     *   issue_date:    string,  // DD/MM/YYYY
     *   province_code: string|null,
     * }
     * @throws CccdOcrException
     */
    public function extract(UploadedFile $frontImage, UploadedFile $backImage): array
    {
        $result = $this->driver->extract($frontImage, $backImage);

        // Province code derived từ 3 chữ số đầu của CCCD VN.
        // Toàn bộ mã tỉnh CCCD (001–096) có dạng "0xx" → substr(1,2) = province_code 2 ký tự.
        // Ví dụ: "001..." → "01" (Hà Nội), "079..." → "79" (TP.HCM)
        $result['province_code'] = strlen($result['cccd_number']) >= 3
            ? substr($result['cccd_number'], 1, 2)
            : null;

        return $result;
    }
}
