<?php

namespace Modules\Assessment\Services\Ocr\Drivers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Modules\Assessment\Exceptions\CccdOcrException;
use Modules\Assessment\Services\Ocr\Contracts\CccdOcrDriverInterface;

/**
 * Driver OCR dùng FPT.AI ID Recognition API.
 *
 * Tài liệu: https://docs.fpt.ai/vision/id-recognition
 *
 * Luồng:
 *  1. POST front_image → nhận { id, name, dob, doe, [issued_date nếu CMND cũ] }
 *  2. POST back_image  → nhận { issued_date, issued_at } (CCCD mới)
 *  3. Merge kết quả: issued_date lấy từ back (CCCD mới) hoặc từ front (CMND cũ)
 *
 * Mỗi ảnh là 1 request riêng vì FPT.AI không hỗ trợ batch.
 * Cả 2 request chạy độc lập; tổng timeout ≈ 2× timeout đơn.
 */
class FptAiOcrDriver implements CccdOcrDriverInterface
{
    // FPT.AI error codes
    private const ERR_OK            = 0;
    private const ERR_CANNOT_READ   = 1;
    private const ERR_NO_ID_FOUND   = 2;

    // Card types trả về trong response['data'][0]['type']
    private const TYPE_NEW_FRONT = 'new_front';
    private const TYPE_NEW_BACK  = 'new_back';
    private const TYPE_OLD_FRONT = 'old_front';
    private const TYPE_OLD_BACK  = 'old_back';

    private string $apiKey;
    private string $baseUrl;
    private int    $timeout;

    public function __construct()
    {
        $this->apiKey  = config('services.fpt_ai.api_key', '');
        $this->baseUrl = config('services.fpt_ai.base_url', 'https://api.fpt.ai/vision/idr/vnm');
        $this->timeout = (int) config('services.fpt_ai.timeout', 20);
    }

    public function extract(UploadedFile $frontImage, UploadedFile $backImage): array
    {
        if (!$this->apiKey) {
            throw new CccdOcrException(
                'Dịch vụ OCR chưa được cấu hình (thiếu FPT_AI_API_KEY). Vui lòng liên hệ quản trị viên.',
                'ocr'
            );
        }

        $frontData = $this->callApi($frontImage, 'front_image');
        $backData  = $this->callApi($backImage,  'back_image');

        return $this->merge($frontData, $backData);
    }

    // ── Private: API call ────────────────────────────────────────────────────

    /**
     * Gọi FPT.AI với 1 ảnh, trả về data[0] từ response.
     *
     * @throws CccdOcrException
     */
    private function callApi(UploadedFile $image, string $errorField): array
    {
        try {
            $response = Http::withHeaders(['api-key' => $this->apiKey])
                ->timeout($this->timeout)
                ->attach(
                    'image',
                    file_get_contents($image->getRealPath()),
                    $image->getClientOriginalName() ?: 'cccd.jpg'
                )
                ->post($this->baseUrl);
        } catch (\Throwable $e) {
            throw new CccdOcrException(
                'Lỗi kết nối đến FPT.AI OCR. Vui lòng thử lại sau.',
                $errorField
            );
        }

        if ($response->failed()) {
            throw new CccdOcrException(
                'FPT.AI OCR trả về lỗi HTTP ' . $response->status() . '. Vui lòng thử lại sau.',
                $errorField
            );
        }

        $errorCode = $response->json('errorCode');
        $data      = $response->json('data.0', []);

        return match ($errorCode) {
            self::ERR_OK          => $data,
            self::ERR_CANNOT_READ => throw new CccdOcrException(
                'Ảnh không đọc được. Vui lòng chụp lại rõ nét, đủ sáng và nằm ngang.',
                $errorField
            ),
            self::ERR_NO_ID_FOUND => throw new CccdOcrException(
                'Không tìm thấy thẻ CCCD trong ảnh. Hãy đảm bảo toàn bộ 4 góc thẻ nằm trong khung.',
                $errorField
            ),
            default => throw new CccdOcrException(
                'FPT.AI OCR thất bại (code=' . $errorCode . '). Vui lòng thử lại hoặc liên hệ hỗ trợ.',
                $errorField
            ),
        };
    }

    // ── Private: Merge front + back ──────────────────────────────────────────

    /**
     * Ghép kết quả front + back:
     *  - CCCD mới (new_front/new_back): issued_date lấy từ back
     *  - CMND cũ (old_front): issued_date nằm trên front
     *
     * @throws CccdOcrException
     */
    private function merge(array $frontData, array $backData): array
    {
        $cccdNumber = $this->require($frontData, 'id',   'front_image', 'Không nhận diện được số CCCD. Vui lòng chụp lại ảnh mặt trước rõ nét hơn.');
        $fullName   = $this->require($frontData, 'name', 'front_image', 'Không nhận diện được họ tên. Vui lòng chụp lại ảnh mặt trước rõ nét hơn.');

        // issued_date: trên mặt sau (CCCD mới) hoặc mặt trước (CMND cũ)
        $cardType   = $frontData['type'] ?? '';
        $issueDate  = in_array($cardType, [self::TYPE_OLD_FRONT, self::TYPE_OLD_BACK], true)
            ? ($frontData['issued_date'] ?? null)
            : ($backData['issued_date']  ?? null);

        if (!$issueDate) {
            throw new CccdOcrException(
                'Không nhận diện được ngày cấp. Vui lòng chụp lại ảnh mặt sau rõ nét hơn.',
                'back_image'
            );
        }

        // Validate CCCD phải là 12 chữ số
        if (!preg_match('/^\d{12}$/', $cccdNumber)) {
            throw new CccdOcrException(
                "Số CCCD nhận diện được ({$cccdNumber}) không hợp lệ (phải đủ 12 chữ số). Vui lòng chụp lại ảnh mặt trước.",
                'front_image'
            );
        }

        return [
            'cccd_number' => $cccdNumber,
            'full_name'   => trim($fullName),
            'issue_date'  => $issueDate,  // DD/MM/YYYY
        ];
    }

    private function require(array $data, string $key, string $field, string $message): string
    {
        $value = $data[$key] ?? null;
        if (!$value || !trim($value)) {
            throw new CccdOcrException($message, $field);
        }
        return trim($value);
    }
}
