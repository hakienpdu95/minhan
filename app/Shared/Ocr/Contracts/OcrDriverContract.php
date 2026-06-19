<?php

namespace App\Shared\Ocr\Contracts;

interface OcrDriverContract
{
    /**
     * @param  string $imagePath  Đường dẫn file ảnh đã tiền xử lý
     * @param  array  $overrides  Ghi đè config cho lần chạy này (psm, dpi, whitelist…)
     */
    public function extractText(string $imagePath, array $overrides = []): string;

    /**
     * Chạy OCR ở chế độ TSV (word-level bounding boxes).
     *
     * @param  string $imagePath  Đường dẫn file ảnh đã tiền xử lý
     * @param  array  $overrides  Ghi đè config tạm thời (psm, oem…)
     * @return string  TSV output từ Tesseract
     */
    public function extractWords(string $imagePath, array $overrides = []): string;
}
