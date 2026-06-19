<?php

namespace Modules\Assessment\Services\Ocr\Drivers;

use App\Shared\Ocr\Data\CccdData;
use App\Shared\Ocr\Enums\DocumentType;
use App\Shared\Ocr\OcrManager;
use App\Shared\Ocr\Parsers\CccdParser;
use App\Shared\Ocr\PostProcessors\CccdPostProcessor;
use Illuminate\Http\UploadedFile;
use Modules\Assessment\Exceptions\CccdOcrException;
use Modules\Assessment\Services\Ocr\Contracts\CccdOcrDriverInterface;

/**
 * Driver OCR tự host dùng Tesseract.
 *
 * Yêu cầu:  apt-get install tesseract-ocr tesseract-ocr-vie
 * Accuracy: ~75–85% tuỳ chất lượng ảnh chụp.
 *
 * Chiến lược extraction (front) — 3 pass, ~35–45s:
 *   Pass A — region/PSM 6       → word-level bounding boxes → crop → name, DOB (tốt với hologram)
 *   Pass B — standard + medium_contrast/PSM 6 → số CCCD, giới tính, quốc tịch, hết hạn
 *   Pass C — digits/PSM 11      → fallback cuối cho số CCCD nếu A+B thất bại
 *
 * Chiến lược extraction (back) — tối đa 2 pass, ~15–40s:
 *   Pass A — standard/PSM 6     → tìm "Ngày cấp" từ label + MRZ doc_number
 *   Pass B — standard/PSM 11    → chỉ chạy nếu Pass A không tìm được ngày cấp
 *   Fallback: expiry − 10 năm (CCCD VN có hiệu lực 10 năm)
 */
class TesseractCccdDriver implements CccdOcrDriverInterface
{
    public function __construct(
        private readonly OcrManager $ocr,
        private readonly CccdPostProcessor $postProcessor,
    ) {}

    /**
     * @throws CccdOcrException
     */
    public function extract(UploadedFile $frontImage, UploadedFile $backImage): array
    {
        $frontData = $this->readFront($frontImage);

        // Fast-fail: nếu mặt trước tìm được CCCD nhưng không đọc được tên
        // → bỏ qua readBack (tiết kiệm ~40s) vì verification vẫn sẽ thất bại
        if ($frontData->id && !$frontData->fullName) {
            throw new CccdOcrException(
                'Không nhận diện được họ tên. '
                . 'Vui lòng chụp lại mặt trước rõ nét hơn, đủ sáng và không có bóng phản chiếu.',
                'front_image'
            );
        }

        $backData = $this->readBack($backImage);

        // Số CCCD: mặt trước ưu tiên, MRZ mặt sau là fallback
        $cccdNumber = $frontData->id ?? $backData->id;

        if (!$cccdNumber) {
            throw new CccdOcrException(
                'Không nhận diện được số CCCD. '
                . 'Hãy chụp lại mặt trước rõ nét, đủ sáng, không che khuất vùng số.',
                'front_image'
            );
        }
        if (!preg_match('/^\d{9}$|^\d{12}$/', $cccdNumber)) {
            throw new CccdOcrException(
                "Số CCCD nhận diện được ({$cccdNumber}) không hợp lệ (cần 9 hoặc 12 chữ số).",
                'front_image'
            );
        }

        $fullName = $frontData->fullName;
        if (!$fullName) {
            throw new CccdOcrException(
                'Không nhận diện được họ tên. Vui lòng chụp lại mặt trước rõ nét hơn.',
                'front_image'
            );
        }

        // Ngày cấp: ưu tiên từ mặt sau, fallback từ expiry mặt trước − 10 năm
        $issueDate = $backData->issueDate
            ?? $this->calcIssueDateFromFrontExpiry($frontData->expiryDate)
            ?? $this->calcIssueDateFromMrzExpiry($backData->expiryDate);

        if (!$issueDate) {
            throw new CccdOcrException(
                'Không nhận diện được ngày cấp. '
                . 'Vui lòng chụp lại mặt sau rõ nét, không có bóng mờ hay che khuất vùng chữ ký. '
                . 'Hoặc nhập thủ công thông tin CCCD.',
                'back_image'
            );
        }

        return [
            'cccd_number' => $cccdNumber,
            'full_name'   => $fullName,
            'issue_date'  => $issueDate,
        ];
    }

    // ── Private ───────────────────────────────────────────────────────────────

    /**
     * Mặt trước: 3 pass, merge kết quả field-by-field.
     *
     * Pass A (region-based, TSV+crop): PSM 6 TSV → word bounding boxes → crop+PSM 7.
     *   Ưu điểm: name và DOB đọc chính xác ngay cả khi hologram che text.
     *   Lý do: PSM 6 ở contrast=60 merge "ĐỖHÀKIÊN" thành 1 word → tokenize đúng.
     *
     * Pass B (readCccdBest, 2 strategies):
     *   - standard (contrast=60): đọc tên nếu region bỏ sót
     *   - medium_contrast (contrast=75): đọc số CCCD (contrast=60 không nhận ra)
     *   Merge field-by-field → lấy giá trị tốt nhất từng trường.
     *
     * Pass C (PSM 11, digits-only): fallback cuối cho số CCCD nếu A+B thất bại.
     *
     * ~35–45s total (Pass A ~15s + Pass B ~25s).
     */
    private function readFront(UploadedFile $image): CccdData
    {
        try {
            // Pass A: region-based OCR (word-level bounding boxes + crop PSM 7 per field)
            // Tốt nhất cho fullName (tokenize merged word) và dateOfBirth.
            $regionData = $this->ocr->readCccdByRegion($image, DocumentType::CCCD_FRONT);

            // Pass B: text-based OCR — bổ sung mọi trường mà region bỏ sót
            // 2 strategies: standard (contrast=60, tốt cho tên) + medium_contrast (contrast=75, tốt cho số CCCD)
            // readCccdBest() merge field-by-field → lấy giá trị tốt nhất từ mỗi strategy
            // Ghi chú: 'imagick_cccd' strategy có sẵn nhưng thêm +25s mà không cải thiện đáng kể
            // khi Pass A đã dùng cropAndEnhanceForHologram() cho từng trường riêng.
            $textData   = $this->ocr->readCccdBest(
                image:      $image,
                side:       DocumentType::CCCD_FRONT,
                strategies: ['standard', 'medium_contrast'],
                psmModes:   [6],
            );
            $regionData = $regionData->mergeWith($textData);

            // Pass C: digits-only fallback cho CCCD number nếu cả A+B thất bại
            if (!$regionData->id) {
                $idData = $this->extractCccdIdPass($image);
                if ($idData) {
                    $regionData = $regionData->mergeWith($idData);
                }
            }

            // Post-processing: validate + correct CCCD structure (province code, century digit, etc.)
            return $this->postProcessor->process($regionData);
        } catch (\Throwable $e) {
            throw new CccdOcrException(
                'Không thể xử lý ảnh mặt trước: ' . $e->getMessage(),
                'front_image'
            );
        }
    }

    /**
     * Pass chuyên biệt tìm số CCCD:
     * PSM 11 (sparse text) + whitelist digits + high-contrast preprocessing
     * → Tesseract chỉ tìm các nhóm số rời rạc, bỏ qua text thường và hologram.
     */
    private function extractCccdIdPass(UploadedFile $image): ?CccdData
    {
        try {
            $result = $this->ocr->extract(
                image:           $image,
                preprocess:      true,
                driverOverrides: [
                    'psm'       => 11,
                    'whitelist' => '0123456789',
                ],
            );
            return app(CccdParser::class)->parse($result->rawText, DocumentType::CCCD_FRONT);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Tính ngày cấp từ ngày hết hạn mặt trước (expiry − 10 năm).
     * CCCD VN luôn có hiệu lực 10 năm từ ngày cấp.
     */
    private function calcIssueDateFromFrontExpiry(?string $expiryDMY): ?string
    {
        if (!$expiryDMY || !preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $expiryDMY, $m)) {
            return null;
        }
        $issueYear = (int)$m[3] - 10;
        if ($issueYear < 2010 || $issueYear > (int)date('Y')) return null;
        return "{$m[1]}/{$m[2]}/{$issueYear}";
    }

    /** Tính ngày cấp từ expiry date trong MRZ (logic tương tự). */
    private function calcIssueDateFromMrzExpiry(?string $expiryDMY): ?string
    {
        return $this->calcIssueDateFromFrontExpiry($expiryDMY);
    }

    /**
     * Mặt sau: 2 pass tuần tự với early-exit.
     *
     * Pass A (standard/PSM 6): tìm "Ngày cấp" label → issueDate trực tiếp.
     *   Nếu tìm được issueDate → done, không chạy Pass B.
     *
     * Pass B (mrz/PSM 11): chỉ chạy khi Pass A không tìm được issueDate.
     *   Tìm MRZ zone → lấy doc_number (fallback cho CCCD) + expiry để tính issue date.
     *
     * 2 passes × ~15s/pass = ~30s worst-case (chấp nhận được trong web request).
     * Không dùng readCccdBest (4 passes) để tránh timeout > 2 phút.
     */
    private function readBack(UploadedFile $image): CccdData
    {
        try {
            // Pass A: standard PSM 6 — tìm label "Ngày cấp"
            $dataA = $this->ocr->readCccd(
                image:      $image,
                side:       DocumentType::CCCD_BACK,
                preprocess: true,
            );

            // Early-exit nếu Pass A đã tìm được ngày cấp
            if ($dataA->issueDate) return $dataA;

            // Pass B: standard strategy PSM 11 (sparse-text) — MRZ zone + expiry fallback.
            // Dùng 'standard' (scale 2×) thay vì 'mrz' (scale 3×) để tránh 50s+ OCR time.
            // PSM 11 sparse-text mode đọc các nhóm ký tự rời rạc tốt hơn PSM 6 trên MRZ zone.
            $dataB = $this->ocr->readCccdBest(
                image:      $image,
                side:       DocumentType::CCCD_BACK,
                strategies: ['standard'],
                psmModes:   [11],
            );

            return $dataA->mergeWith($dataB);
        } catch (\Throwable $e) {
            throw new CccdOcrException(
                'Không thể xử lý ảnh mặt sau: ' . $e->getMessage(),
                'back_image'
            );
        }
    }
}
