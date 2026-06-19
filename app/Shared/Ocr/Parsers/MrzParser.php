<?php

namespace App\Shared\Ocr\Parsers;

/**
 * Parser cho vùng MRZ (Machine Readable Zone) trên CCCD / Passport Việt Nam.
 *
 * Hỗ trợ:
 *  - TD1  (3 dòng × 30 ký tự): CCCD chip 2021+
 *  - TD3  (2 dòng × 44 ký tự): Hộ chiếu
 *
 * Vì OCR thường đọc sai các ký tự MRZ do hologram, class này áp dụng bộ
 * sửa lỗi OCR phổ biến (O↔0, I↔1, S↔5...) trước khi giải mã.
 *
 * Kết quả trả về là "best effort" — luôn kiểm tra confidence trước khi dùng.
 */
class MrzParser
{
    // Ký tự OCR thường nhầm trong vùng MRZ (digit context)
    private const ALPHA_TO_DIGIT = [
        'O' => '0', 'I' => '1', 'S' => '5', 'Z' => '2',
        'B' => '8', 'G' => '6', 'Q' => '0', 'D' => '0',
    ];

    /**
     * Trích xuất thông tin từ vùng MRZ trong raw OCR text.
     *
     * @return array{
     *   doc_number: string|null,   // Số CCCD / hộ chiếu (9 hoặc 12 ký tự số)
     *   dob:        string|null,   // Ngày sinh DD/MM/YYYY
     *   expiry:     string|null,   // Ngày hết hạn DD/MM/YYYY
     *   sex:        string|null,   // M / F / <
     *   confidence: float,         // 0.0–1.0 ước lượng độ tin cậy
     * }
     */
    public function extract(string $rawText): array
    {
        $lines = $this->findMrzLines($rawText);

        if (count($lines) < 2) {
            return $this->emptyResult();
        }

        $line1 = $lines[0];
        $line2 = $lines[1];

        $docNumber = $this->extractDocNumber($line1);
        $dob       = $this->extractDob($line2);
        $expiry    = $this->extractExpiry($line2);
        $sex       = $this->extractSex($line2);

        // Tính confidence dựa trên số trường tìm được
        $found      = array_filter(compact('docNumber', 'dob', 'expiry', 'sex'));
        $confidence = count($found) / 4;

        return [
            'doc_number' => $docNumber,
            'dob'        => $dob,
            'expiry'     => $expiry,
            'sex'        => $sex,
            'confidence' => $confidence,
        ];
    }

    // ── Line detection ────────────────────────────────────────────────────────

    /**
     * Tìm dòng có đặc trưng MRZ: toàn UPPERCASE+digits+<, ≥ 20 ký tự hợp lệ.
     * Ưu tiên những dòng bắt đầu bằng "IDVNM" hoặc "VNM".
     *
     * @return string[]
     */
    private function findMrzLines(string $text): array
    {
        $candidates = [];

        foreach (explode("\n", $text) as $rawLine) {
            $clean = preg_replace('/[^A-Z0-9<]/', '', strtoupper($rawLine));
            if (strlen($clean) < 18) continue;

            // Phải có ít nhất 30% ký tự hợp lệ MRZ so với độ dài dòng
            $ratio = strlen($clean) / max(1, strlen(trim($rawLine)));
            if ($ratio < 0.5) continue;

            $priority = str_starts_with($clean, 'IDVNM') || str_contains($clean, 'VNM') ? 1 : 0;
            $candidates[] = ['line' => $clean, 'len' => strlen($clean), 'priority' => $priority];
        }

        if (empty($candidates)) return [];

        // Sắp xếp: ưu tiên dòng có prefix IDVNM, sau đó dài hơn
        usort($candidates, fn($a, $b) =>
            $b['priority'] <=> $a['priority'] ?: $b['len'] <=> $a['len']
        );

        // Lấy 2 dòng MRZ đầu tiên
        return array_map(fn($c) => $c['line'], array_slice($candidates, 0, 2));
    }

    // ── Field extractors ──────────────────────────────────────────────────────

    /**
     * Số CCCD từ dòng 1 của MRZ TD1.
     * Vị trí: char 6–14 (9 ký tự) sau prefix "IDVNM".
     * Với CCCD mới 12 chữ số: cả 12 digit xuất hiện liên tiếp sau prefix.
     */
    private function extractDocNumber(string $line): ?string
    {
        // Thử trích prefix + số (CCCD 9 hoặc 12 ký tự, sửa lỗi OCR)
        foreach (['IDVNM', 'DVNM', 'VNM', 'I<VNM'] as $prefix) {
            $pos = strpos($line, $prefix);
            if ($pos === false) continue;

            $after = substr($line, $pos + strlen($prefix));
            $after = $this->fixDigits($after);

            // Lấy chuỗi số liên tiếp đầu tiên (bỏ < hoặc chữ kiểm tra)
            if (preg_match('/^(\d{9,12})/', $after, $m)) {
                $num = $m[1];
                // Ưu tiên 12 digit (CCCD mới), chấp nhận 9 digit (CMND / CCCD cũ)
                return (strlen($num) >= 12) ? substr($num, 0, 12) : substr($num, 0, 9);
            }
        }

        // Fallback: bất kỳ chuỗi 9–12 digit liên tiếp trong dòng MRZ
        if (preg_match('/\b(\d{9,12})\b/', $this->fixDigits($line), $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * Ngày sinh từ dòng 2 MRZ TD1 (positions 1–6, YYMMDD).
     */
    private function extractDob(string $line): ?string
    {
        $fixed = $this->fixDigits($line);

        // Dòng 2 bắt đầu ngay bằng YYMMDD
        if (preg_match('/^(\d{6})/', $fixed, $m)) {
            return $this->decodeMrzDate($m[1], allowFuture: false);
        }

        // Fallback: tìm chuỗi YYMMDD hợp lệ đứng trước M/F/< (ký tự giới tính)
        if (preg_match('/(\d{6})[MF<]/', $fixed, $m)) {
            return $this->decodeMrzDate($m[1], allowFuture: false);
        }

        return null;
    }

    /**
     * Ngày hết hạn từ dòng 2 MRZ TD1 (positions 9–14 sau ký tự giới tính).
     */
    private function extractExpiry(string $line): ?string
    {
        $fixed = $this->fixDigits($line);

        // Ký tự giới tính (M/F/<) sau check digit → ngay sau là 6 digit expiry
        if (preg_match('/[MF<](\d{6})/', $fixed, $m)) {
            return $this->decodeMrzDate($m[1], allowFuture: true);
        }

        return null;
    }

    /**
     * Ký tự giới tính từ dòng 2.
     */
    private function extractSex(string $line): ?string
    {
        // Đứng sau 7 ký tự đầu (YYMMDD + check)
        if (preg_match('/\d{7}([MF<])/', $this->fixDigits($line), $m)) {
            return match ($m[1]) {
                'M' => 'Nam',
                'F' => 'Nữ',
                default => null,
            };
        }
        return null;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Sửa lỗi nhầm chữ-số trong chuỗi MRZ (trong context toàn số).
     */
    private function fixDigits(string $str): string
    {
        return strtr($str, self::ALPHA_TO_DIGIT);
    }

    /**
     * Chuyển YYMMDD → DD/MM/YYYY.
     * Cut-off năm: ≤ 30 → 2000s, > 30 → 1900s.
     */
    private function decodeMrzDate(string $yymmdd, bool $allowFuture = false): ?string
    {
        if (strlen($yymmdd) !== 6 || !ctype_digit($yymmdd)) return null;

        $yy    = (int)substr($yymmdd, 0, 2);
        $month = (int)substr($yymmdd, 2, 2);
        $day   = (int)substr($yymmdd, 4, 2);

        if ($month < 1 || $month > 12 || $day < 1 || $day > 31) return null;

        $year = $yy <= 30 ? 2000 + $yy : 1900 + $yy;

        if (!$allowFuture && $year > (int)date('Y') + 1) return null;

        return sprintf('%02d/%02d/%04d', $day, $month, $year);
    }

    private function emptyResult(): array
    {
        return [
            'doc_number' => null,
            'dob'        => null,
            'expiry'     => null,
            'sex'        => null,
            'confidence' => 0.0,
        ];
    }
}
