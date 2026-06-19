<?php

namespace App\Shared\Ocr\Drivers;

use App\Shared\Ocr\Contracts\OcrDriverContract;
use thiagoalessio\TesseractOCR\TesseractOCR;

class TesseractDriver implements OcrDriverContract
{
    public function __construct(private readonly array $config) {}

    /**
     * @param  array $overrides  Ghi đè config tạm thời cho lần chạy này (psm, oem, whitelist, dpi…)
     */
    public function extractText(string $imagePath, array $overrides = []): string
    {
        $cfg = array_merge($this->config, $overrides);

        $ocr = new TesseractOCR($imagePath);

        $langs = $cfg['langs'] ?? ['vie', 'eng'];
        $ocr->lang(...$langs);

        if (isset($cfg['psm'])) { $ocr->psm($cfg['psm']); }
        if (isset($cfg['oem'])) { $ocr->oem($cfg['oem']); }
        if (isset($cfg['dpi'])) { $ocr->dpi($cfg['dpi']); }

        if (isset($cfg['allowlist']))      { $ocr->allowlist($cfg['allowlist']); }
        elseif (isset($cfg['whitelist']))  { $ocr->allowlist($cfg['whitelist']); }

        foreach ($cfg['tessdata_configs'] ?? [] as $key => $value) {
            $ocr->config($key, $value);
        }

        // tessconf: absolute path to a Tesseract config file.
        // Apply settings as individual -c flags (parse the file line by line).
        // NOT via configFile() — that changes the output format type detection in the library.
        if (!empty($cfg['tessconf'])) {
            $this->applyTessconfSettings($ocr, $cfg['tessconf'], skip: ['tessedit_create_tsv', 'tessedit_create_pdf', 'tessedit_create_hocr']);
        }

        return trim($ocr->run());
    }

    /**
     * Chạy OCR ở chế độ TSV — trả về raw TSV với word-level bounding boxes.
     *
     * Dùng configFile('tsv') để thư viện biết dùng extension .tsv khi đọc output.
     * Các cấu hình dictionary/penalty từ tessconf_tsv được áp dụng qua -c flags riêng lẻ.
     *
     * @param  array $overrides  Ghi đè config tạm thời (psm, oem, tessconf_tsv…)
     */
    public function extractWords(string $imagePath, array $overrides = []): string
    {
        $cfg = array_merge($this->config, $overrides);

        $ocr = new TesseractOCR($imagePath);

        $langs = $cfg['langs'] ?? ['vie', 'eng'];
        $ocr->lang(...$langs);

        if (isset($cfg['psm'])) { $ocr->psm($cfg['psm']); }
        if (isset($cfg['oem'])) { $ocr->oem($cfg['oem']); }

        // PHẢI dùng configFile('tsv') để thư viện dùng đúng extension .tsv khi đọc output.
        // Không dùng full path vì thư viện nhận diện extension qua tên config (tsv/hocr/pdf).
        $ocr->configFile('tsv');

        // tessconf_tsv: áp dụng các cài đặt bổ sung qua -c flags.
        // Bỏ qua tessedit_create_tsv (đã xử lý bởi configFile('tsv') ở trên).
        if (!empty($cfg['tessconf_tsv'])) {
            $this->applyTessconfSettings($ocr, $cfg['tessconf_tsv'], skip: ['tessedit_create_tsv']);
        }

        return trim($ocr->run());
    }

    /**
     * Parse file tessconf và áp dụng từng cài đặt dạng -c key=value.
     *
     * Format file: mỗi dòng là "key value" (key và value cách nhau bởi khoảng trắng).
     * Comment: dòng bắt đầu bằng "#". Dòng trống: bỏ qua.
     *
     * @param  string[] $skip  Danh sách key KHÔNG áp dụng (vì đã xử lý ở nơi khác)
     */
    private function applyTessconfSettings(TesseractOCR $ocr, string $confPath, array $skip = []): void
    {
        if (!file_exists($confPath)) return;

        foreach (file($confPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if (str_starts_with($line, '#') || $line === '') continue;

            $parts = preg_split('/\s+/', $line, 2);
            if (count($parts) < 2) continue;

            [$key, $value] = $parts;
            if (in_array($key, $skip, true)) continue;

            $ocr->config($key, $value);
        }
    }
}
