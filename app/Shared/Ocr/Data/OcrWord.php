<?php

namespace App\Shared\Ocr\Data;

readonly class OcrWord
{
    public function __construct(
        public string $text,
        public int    $left,
        public int    $top,
        public int    $width,
        public int    $height,
        public float  $confidence,
        public int    $lineNum,
        public int    $blockNum,
    ) {}

    public function right(): int   { return $this->left + $this->width; }
    public function bottom(): int  { return $this->top + $this->height; }
    public function centerY(): int { return $this->top + intdiv($this->height, 2); }
    public function centerX(): int { return $this->left + intdiv($this->width, 2); }
}
