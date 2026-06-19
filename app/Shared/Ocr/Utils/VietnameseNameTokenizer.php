<?php

namespace App\Shared\Ocr\Utils;

/**
 * Tách tên tiếng Việt bị OCR viết liền thành các tiếng có khoảng trắng.
 *
 * Ví dụ: "ĐỖHÀKIÊN" → "ĐỖ HÀ KIÊN"
 *
 * Cách hoạt động:
 *  Dùng syllable-regex khớp từng tiếng hoàn chỉnh theo cấu trúc âm tiết tiếng Việt:
 *    [phụ âm đầu]? + [vần 1-3 ký tự] + [phụ âm cuối]?
 *  Sau đó join lại bằng dấu cách.
 *
 *  Ưu điểm: xử lý đúng các digraph cuối (NG, NH, CH) và đầu (TH, TR, PH, KH, CH…)
 *  Giới hạn:  tên có phụ âm đơn lẻ (không có nguyên âm) sẽ bị bỏ qua — không xảy ra
 *             trong tên thật tiếng Việt.
 */
class VietnameseNameTokenizer
{
    // Tất cả nguyên âm HOA tiếng Việt (có và không có dấu thanh / dấu phụ)
    private const VOWELS =
        'AĂÂEÊIOÔƠUƯY'       // base, không dấu thanh
        . 'ÀÁẠẢÃ'            // A tones
        . 'ẰẮẶẲẴ'            // Ă tones
        . 'ẦẤẬẨẪ'            // Â tones
        . 'ÈÉẸẺẼ'            // E tones
        . 'ỀẾỆỂỄ'            // Ê tones
        . 'ÌÍỊỈĨ'            // I tones
        . 'ÒÓỌỎÕ'            // O tones
        . 'ỒỐỘỔỖ'            // Ô tones
        . 'ỜỚỢỞỠ'            // Ơ tones
        . 'ÙÚỤỦŨ'            // U tones
        . 'ỪỨỰỬỮ'            // Ư tones
        . 'ỲÝỴỶỸ';           // Y tones

    /**
     * Tách tên viết liền → các tiếng riêng biệt cách nhau bởi dấu cách.
     * Nếu đầu vào đã có khoảng trắng hợp lệ, kết quả giữ nguyên.
     */
    public static function tokenize(string $name): string
    {
        $v = self::VOWELS;

        // Cấu trúc 1 âm tiết tiếng Việt (uppercase):
        //   ┌─ phụ âm đầu (dài nhất trước, tránh partial match) ─────────────────────┐
        //   │  ┌─ vần (1-3 nguyên âm) ──────────────────┐  ┌─ phụ âm cuối ─────────┐ │
        $pattern = "/(?:NGH|GH|GI|QU|NG|NH|TH|TR|PH|KH|CH|[BCDĐGHKLMNPQRSTVX])?[{$v}]{1,3}(?:NG|NH|CH|[MNPCT])?/u";

        if (!preg_match_all($pattern, mb_strtoupper($name), $m) || empty($m[0])) {
            return $name;
        }

        $syllables = array_values(array_filter($m[0], fn($s) => mb_strlen($s) > 0));

        return implode(' ', $syllables);
    }
}
