<?php

namespace App\Shared\Ocr\Preprocessors;

use Intervention\Image\Laravel\Facades\Image;

/**
 * Tiền xử lý ảnh CCCD trước khi đưa vào OCR.
 *
 * Chiến lược:
 *  - 'standard'      : grayscale → scale 2× → contrast 60 (mặc định, cân bằng)
 *  - 'high_contrast' : grayscale → scale 2.5× → contrast 80 → sharpen 10
 *                      (tốt cho hologram mờ hoặc ảnh chụp nghiêng)
 *  - 'mrz'           : grayscale → scale 3× → contrast 90 → sharpen 15
 *                      (aggressive contrast để đọc MRZ — hy sinh chất lượng chữ in thường)
 *
 * Mỗi chiến lược trả về path file .png tạm thời.
 * Gọi cleanup() sau khi dùng xong để xoá file tạm.
 */
class ImagePreprocessor
{
    public function __construct(private readonly array $config = []) {}

    /**
     * Chuẩn bị ảnh với chiến lược mặc định từ config.
     */
    public function prepare(string $sourcePath): string
    {
        $strategy = $this->config['strategy'] ?? 'standard';
        return $this->prepareWithStrategy($sourcePath, $strategy);
    }

    /**
     * Chuẩn bị ảnh với chiến lược chỉ định.
     *
     * @param  string $strategy  'standard' | 'high_contrast' | 'mrz'
     */
    public function prepareWithStrategy(string $sourcePath, string $strategy): string
    {
        $params = $this->strategyParams($strategy);
        return $this->process($sourcePath, $params);
    }

    /**
     * Chuẩn bị nhiều biến thể cùng lúc (multi-pass OCR).
     * Trả về map strategy → path.
     *
     * @param  string[] $strategies
     * @return array<string, string>
     */
    public function prepareAll(string $sourcePath, array $strategies = ['standard', 'high_contrast']): array
    {
        $paths = [];
        foreach ($strategies as $strategy) {
            $paths[$strategy] = $this->prepareWithStrategy($sourcePath, $strategy);
        }
        return $paths;
    }

    public function cleanup(string ...$paths): void
    {
        foreach ($paths as $path) {
            if ($path && file_exists($path) && str_starts_with($path, sys_get_temp_dir())) {
                @unlink($path);
            }
        }
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function process(string $sourcePath, array $params): string
    {
        // Imagick-based strategies (adaptive threshold, unsharp mask)
        if ($params['imagick'] ?? false) {
            return $this->processWithImagick($sourcePath, $params);
        }

        $tempPath = sys_get_temp_dir() . '/ocr_' . uniqid() . '.png';

        $image = Image::decode($sourcePath);

        // Ảnh portrait (cao > rộng) = CCCD chụp đứng → xoay 90° CW
        if ($image->height() > $image->width()) {
            $image->rotate(-90);
        }

        // Scale (phóng to trước để tăng pixel density → OCR chính xác hơn)
        if (($scale = $params['scale'] ?? 1) > 1) {
            $image->scale((int)($image->width() * $scale));
        }

        if ($params['greyscale'] ?? true) {
            $image->grayscale();
        }

        if ($brightness = $params['brightness'] ?? 0) {
            $image->brightness($brightness);
        }

        if ($contrast = $params['contrast'] ?? 60) {
            $image->contrast($contrast);
        }

        if ($sharpen = $params['sharpen'] ?? 0) {
            $image->sharpen($sharpen);
        }

        $image->save($tempPath);

        return $tempPath;
    }

    /**
     * Xử lý ảnh dùng Imagick (ImageMagick).
     *
     * Ưu điểm so với Intervention Image:
     *  - adaptiveThresholdImage: ngưỡng hóa cục bộ → xuyên vùng hologram
     *  - contrastStretchImage: chuẩn hoá histogram dựa vào phân phối pixel thực tế
     *  - unsharpMaskImage: làm sắc nét cạnh text mà không khuếch đại noise
     *
     * Tham số adaptive_block, adaptive_offset được chỉnh theo thực nghiệm trên CCCD.
     */
    private function processWithImagick(string $sourcePath, array $params): string
    {
        $tempPath = sys_get_temp_dir() . '/ocr_' . uniqid() . '.png';

        $im = new \Imagick($sourcePath);

        // Xoay ảnh portrait
        if ($im->getImageHeight() > $im->getImageWidth()) {
            $im->rotateImage(new \ImagickPixel('none'), -90);
        }

        // Scale 2×
        if (($scale = $params['scale'] ?? 2) > 1) {
            $im->scaleImage((int)($im->getImageWidth() * $scale), 0);
        }

        // Grayscale
        $im->transformImageColorspace(\Imagick::COLORSPACE_GRAY);

        // Adaptive threshold: tính ngưỡng cục bộ trong từng block
        // block_size: kích thước block (px); offset: bias âm = ưu tiên text tối
        if ($params['adaptive_threshold'] ?? false) {
            $w         = $im->getImageWidth();
            $blockSize = $params['adaptive_block'] ?? max(21, (int)($w * 0.006) | 1);
            $blockSize = ($blockSize % 2 === 0) ? $blockSize + 1 : $blockSize;
            $q         = \Imagick::getQuantumRange()['quantumRangeLong'];
            $offset    = (int)(($params['adaptive_offset'] ?? -0.08) * $q);
            $im->adaptiveThresholdImage($blockSize, $blockSize, $offset);
        }

        // ContrastStretch: chuẩn hoá histogram (black/white point = 1% pixel)
        if ($params['contrast_stretch'] ?? false) {
            $pixels = $im->getImageWidth() * $im->getImageHeight();
            $im->contrastStretchImage($pixels * 0.01, $pixels * 0.01);
        }

        // Unsharp mask: làm sắc nét cạnh text
        if ($params['unsharp'] ?? true) {
            $im->unsharpMaskImage(0, 1.0, 1.5, 0.02);
        }

        $im->writeImage($tempPath);
        $im->destroy();

        return $tempPath;
    }

    private function strategyParams(string $strategy): array
    {
        return match ($strategy) {
            'imagick_cccd' => [
                'imagick'            => true,
                'scale'              => 2,
                'adaptive_threshold' => true,
                'adaptive_block'     => 61,
                'adaptive_offset'    => -0.08,
                'contrast_stretch'   => true,
                'unsharp'            => true,
            ],
            'medium_contrast' => [
                'greyscale'  => true,
                'scale'      => 2,
                'brightness' => 10,
                'contrast'   => 75,
                'sharpen'    => 0,
            ],
            'high_contrast' => [
                'greyscale'  => true,
                'scale'      => 2.5,
                'brightness' => 5,
                'contrast'   => 80,
                'sharpen'    => 10,
            ],
            'mrz' => [
                'greyscale'  => true,
                'scale'      => 3.0,
                'brightness' => 0,
                'contrast'   => 90,
                'sharpen'    => 15,
            ],
            default => [  // 'standard'
                'greyscale'  => $this->config['greyscale']  ?? true,
                'scale'      => $this->config['scale']      ?? 2,
                'brightness' => $this->config['brightness'] ?? 10,
                'contrast'   => $this->config['contrast']   ?? 60,
                'sharpen'    => $this->config['sharpen']    ?? 5,
            ],
        };
    }
}
