<?php

namespace Modules\Assessment\Services\Ocr\Contracts;

use Illuminate\Http\UploadedFile;
use Modules\Assessment\Exceptions\CccdOcrException;

interface CccdOcrDriverInterface
{
    /**
     * Trích xuất thông tin từ 2 ảnh CCCD (mặt trước + mặt sau).
     *
     * Ảnh chỉ được đọc vào memory, gửi API, rồi discard — không lưu vào disk.
     *
     * @return array{
     *   cccd_number: string,   // 12 chữ số
     *   full_name:   string,   // họ tên như trên thẻ
     *   issue_date:  string,   // DD/MM/YYYY
     * }
     *
     * @throws CccdOcrException với $field = 'front_image' | 'back_image' | 'ocr'
     */
    public function extract(UploadedFile $frontImage, UploadedFile $backImage): array;
}
