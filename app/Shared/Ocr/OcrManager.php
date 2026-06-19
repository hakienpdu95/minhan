<?php

namespace App\Shared\Ocr;

use App\Shared\Ocr\Contracts\OcrDriverContract;
use App\Shared\Ocr\Data\CccdData;
use App\Shared\Ocr\Data\OcrResult;
use App\Shared\Ocr\Enums\DocumentType;
use App\Shared\Ocr\Parsers\CccdParser;
use App\Shared\Ocr\Preprocessors\ImagePreprocessor;
use App\Shared\Ocr\Region\CccdRegionExtractor;
use Illuminate\Http\UploadedFile;

class OcrManager
{
    public function __construct(
        private readonly OcrDriverContract $driver,
        private readonly ImagePreprocessor $preprocessor,
        private readonly CccdParser        $cccdParser,
    ) {}

    /**
     * Trích xuất text thô từ ảnh (1 lần chạy).
     */
    public function extract(
        string|UploadedFile $image,
        bool   $preprocess = true,
        array  $driverOverrides = [],
    ): OcrResult {
        $path  = $image instanceof UploadedFile ? $image->getRealPath() : $image;
        $start = microtime(true);
        $temp  = null;

        try {
            if ($preprocess) {
                $temp = $this->preprocessor->prepare($path);
                $path = $temp;
            }
            $text = $this->driver->extractText($path, $driverOverrides);
        } finally {
            if ($temp) $this->preprocessor->cleanup($temp);
        }

        return new OcrResult(
            rawText:        $text,
            driver:         class_basename($this->driver),
            processingTime: round(microtime(true) - $start, 3),
        );
    }

    /**
     * Trích xuất text với nhiều chiến lược tiền xử lý và nhiều PSM mode.
     * Trả về kết quả có nhiều text hợp lệ nhất (loại bỏ OCR noise).
     *
     * @param  string[]  $strategies   Chiến lược preprocessing: 'standard', 'high_contrast', 'mrz'
     * @param  int[]     $psmModes     Tesseract PSM modes để thử (6=block, 3=auto, 11=sparse)
     */
    public function extractBest(
        string|UploadedFile $image,
        array $strategies  = ['standard', 'high_contrast'],
        array $psmModes    = [6, 3],
    ): OcrResult {
        $sourcePath = $image instanceof UploadedFile ? $image->getRealPath() : $image;
        $start      = microtime(true);
        $tempPaths  = [];
        $best       = null;

        try {
            // Chuẩn bị tất cả biến thể ảnh cùng lúc
            $tempPaths = $this->preprocessor->prepareAll($sourcePath, $strategies);

            foreach ($tempPaths as $strategy => $tempPath) {
                foreach ($psmModes as $psm) {
                    try {
                        $text = $this->driver->extractText($tempPath, ['psm' => $psm]);
                    } catch (\Throwable) {
                        continue;
                    }

                    // Chấm điểm: ưu tiên text có nhiều chữ thực (loại noise ký tự đặc biệt)
                    $score = $this->scoreText($text);
                    if ($best === null || $score > $best['score']) {
                        $best = ['text' => $text, 'score' => $score, 'strategy' => $strategy, 'psm' => $psm];
                    }
                }
            }
        } finally {
            $this->preprocessor->cleanup(...array_values($tempPaths));
        }

        return new OcrResult(
            rawText:        $best['text'] ?? '',
            driver:         class_basename($this->driver) . "[{$best['strategy']}:psm{$best['psm']}]",
            processingTime: round(microtime(true) - $start, 3),
        );
    }

    /**
     * Đọc CCCD và trả về data có cấu trúc (1 lần chạy).
     */
    public function readCccd(
        string|UploadedFile $image,
        DocumentType        $side       = DocumentType::CCCD_FRONT,
        bool                $preprocess = true,
    ): CccdData {
        $result = $this->extract($image, $preprocess);
        return $this->cccdParser->parse($result->rawText, $side);
    }

    /**
     * Đọc CCCD với multi-pass — merge field-by-field từ tất cả các pass.
     *
     * Mỗi pass (strategy × PSM) có thể trích tốt các trường khác nhau.
     * Thay vì chọn 1 pass có điểm cao nhất, ta lấy giá trị tốt nhất từng trường
     * qua mergeWith() (so sánh confidence từng field để decide).
     */
    public function readCccdBest(
        string|UploadedFile $image,
        DocumentType        $side      = DocumentType::CCCD_FRONT,
        array $strategies              = ['standard', 'high_contrast'],
        array $psmModes                = [6, 3],
    ): CccdData {
        $sourcePath = $image instanceof UploadedFile ? $image->getRealPath() : $image;
        $tempPaths  = [];
        $merged     = null;

        try {
            $tempPaths = $this->preprocessor->prepareAll($sourcePath, $strategies);

            foreach ($tempPaths as $strategy => $tempPath) {
                foreach ($psmModes as $psm) {
                    try {
                        $text = $this->driver->extractText($tempPath, ['psm' => $psm]);
                    } catch (\Throwable) {
                        continue;
                    }

                    $data   = $this->cccdParser->parse($text, $side);
                    $merged = $merged === null ? $data : $merged->mergeWith($data);
                }
            }
        } finally {
            $this->preprocessor->cleanup(...array_values($tempPaths));
        }

        return $merged ?? $this->cccdParser->parse('', $side);
    }

    /**
     * Đọc CCCD dùng region-based OCR (crop từng trường + PSM 7).
     *
     * Ưu điểm so với readCccd(): tránh nhiễu hologram vì mỗi vùng crop được OCR riêng lẻ.
     * TSV word-level bounding boxes được dùng để xác định vùng value trước khi crop.
     */
    public function readCccdByRegion(
        string|UploadedFile $image,
        DocumentType        $side = DocumentType::CCCD_FRONT,
    ): CccdData {
        $sourcePath = $image instanceof UploadedFile ? $image->getRealPath() : $image;
        $tempPath   = $this->preprocessor->prepare($sourcePath);

        try {
            return app(CccdRegionExtractor::class)->extract($tempPath, $side);
        } finally {
            $this->preprocessor->cleanup($tempPath);
        }
    }

    // ── Scoring ───────────────────────────────────────────────────────────────

    /**
     * Chấm điểm chất lượng text OCR: ưu tiên nhiều chữ/số, ít ký tự rác.
     */
    private function scoreText(string $text): float
    {
        if (empty(trim($text))) return 0.0;

        $totalChars  = mb_strlen($text);
        $alphaDigit  = mb_strlen(preg_replace('/[^\p{L}\p{N}]/u', '', $text));
        $noiseChars  = mb_strlen(preg_replace('/[\p{L}\p{N}\s,.\/\-:]/u', '', $text));

        $alphaRatio  = $totalChars ? $alphaDigit / $totalChars : 0;
        $noisePenalty = $totalChars ? $noiseChars / $totalChars : 0;

        return round(($alphaRatio - $noisePenalty * 0.5) * $alphaDigit, 2);
    }
}
