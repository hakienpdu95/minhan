<?php

namespace App\Shared\Ocr\Parsers;

use App\Shared\Ocr\Data\CccdData;
use App\Shared\Ocr\Enums\DocumentType;
use App\Shared\Ocr\Utils\VietnameseNameTokenizer;

/**
 * Parser trích xuất thông tin có cấu trúc từ raw OCR text của CCCD Việt Nam.
 *
 * Mỗi trường dùng chiến lược nhiều pattern:
 *  1. Pattern chính (label → value)
 *  2. Pattern dự phòng (cấu trúc/vị trí)
 *  3. Pattern MRZ (từ mặt sau, cho CCCD số và ngày)
 *
 * OCR accuracy của Tesseract trên hologram CCCD ~75–85%, nên mọi pattern
 * đều viết để chịu được mất dấu, ký tự lạ, và bố cục dòng không ổn định.
 */
class CccdParser
{
    public function __construct(private readonly MrzParser $mrzParser = new MrzParser()) {}

    public function parse(string $rawText, DocumentType $side): CccdData
    {
        return match ($side) {
            DocumentType::CCCD_FRONT => $this->parseFront($rawText),
            DocumentType::CCCD_BACK  => $this->parseBack($rawText),
            default => new CccdData(
                id: null, fullName: null, dateOfBirth: null, gender: null,
                nationality: null, hometown: null, address: null,
                expiryDate: null, rawText: $rawText,
            ),
        };
    }

    // ── Front (mặt trước) ────────────────────────────────────────────────────

    private function parseFront(string $text): CccdData
    {
        $id       = $this->extractId($text);
        $name     = $this->extractFullName($text);
        $dob      = $this->extractDateOfBirth($text);
        $gender   = $this->extractGender($text);
        $nation   = $this->extractNationality($text);
        $hometown = $this->extractHometown($text);
        $address  = $this->extractAddress($text);
        $expiry   = $this->extractExpiryDate($text);

        $confidence = [
            'id'          => $id       ? $this->idConfidence($id)     : 0.0,
            'fullName'    => $name     ? $this->nameConfidence($name)  : 0.0,
            'dateOfBirth' => $dob      ? 0.95 : 0.0,
            'gender'      => $gender   ? 0.8  : 0.0,
            'nationality' => $nation   ? $this->shortFieldConfidence($nation) : 0.0,
            'hometown'    => $hometown ? 0.7  : 0.0,
            'address'     => $address  ? 0.6  : 0.0,
            'expiryDate'  => $expiry   ? 0.8  : 0.0,
        ];

        return new CccdData(
            id:          $id,
            fullName:    $name,
            dateOfBirth: $dob,
            gender:      $gender,
            nationality: $nation,
            hometown:    $hometown,
            address:     $address,
            expiryDate:  $expiry,
            rawText:     $text,
            confidence:  $confidence,
        );
    }

    // ── Back (mặt sau) ───────────────────────────────────────────────────────

    private function parseBack(string $text): CccdData
    {
        $issueDate  = $this->extractIssueDate($text);
        $mrz        = $this->mrzParser->extract($text);

        // Lấy số CCCD từ MRZ nếu có (dùng làm fallback cho mặt trước)
        $mrzId      = $mrz['doc_number'] ?? null;

        // Nếu ngày cấp không tìm được qua label, tính từ ngày hết hạn MRZ − 10 năm
        if (!$issueDate && !empty($mrz['expiry'])) {
            $issueDate = $this->calcIssueDateFromExpiry($mrz['expiry']);
        }

        $confidence = [
            'issueDate' => $issueDate ? ($this->isLabeledDate($text) ? 0.95 : 0.6) : 0.0,
            'id'        => $mrzId ? (float)($mrz['confidence'] ?? 0.5) : 0.0,
        ];

        return new CccdData(
            id:          $mrzId,
            fullName:    null,
            dateOfBirth: $mrz['dob'] ?? null,
            gender:      $mrz['sex'] ?? null,
            nationality: null,
            hometown:    null,
            address:     null,
            expiryDate:  $mrz['expiry'] ?? null,
            rawText:     $text,
            issueDate:   $issueDate,
            confidence:  $confidence,
        );
    }

    // ── ID (Số CCCD / CMND) ──────────────────────────────────────────────────

    private function extractId(string $text): ?string
    {
        $twelveDigit = null;
        $nineDigit   = null;

        // 1. Tất cả label matches — collect candidates, ưu tiên 12-digit
        // Tesseract thường đọc "7" → "/", "O/Q" → "0", "Số" → "sino"
        $labelPatterns = [
            '/(?:S[oố]|sino|:No\b|No\.?)[:\s.\/]+([QqOoI0-9][QqOoI0-9\s\.\/]{7,16})/iu',
            '/(?:S[oố]\s*[\/\\\\]\s*No|ID\s*No|CCCD)[:\s.]+([QqOoI0-9][QqOoI0-9\s\.\/]{7,16})/iu',
        ];
        foreach ($labelPatterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                foreach ($matches[1] as $raw) {
                    $id = $this->fixOcrDigits($raw);
                    if (strlen($id) === 12 && !$this->looksLikeDate($id)) {
                        $twelveDigit = $id;
                        break 2;  // 12-digit từ label là kết quả tốt nhất
                    }
                    if (strlen($id) === 9 && !$nineDigit) {
                        $nineDigit = $id;
                    }
                }
            }
        }
        if ($twelveDigit) return $twelveDigit;

        // 2. 12 chữ số liên tiếp (theo nhóm 3-3-3-3 hoặc liền nhau)
        if (preg_match('/\b(\d{3})\s?(\d{3})\s?(\d{3})\s?(\d{3})\b/', $text, $m)) {
            $id = $m[1] . $m[2] . $m[3] . $m[4];
            if (strlen($id) === 12 && !$this->looksLikeDate($id)) return $id;
        }

        // 3. 9 chữ số liên tiếp bắt đầu bằng 0 (CMND cũ)
        return $nineDigit ?? (preg_match('/\b(0\d{8})\b/', $text, $m) ? $m[1] : null);
    }

    /**
     * Sửa lỗi OCR phổ biến trong chuỗi số CCCD:
     *  Q/O → 0,  I → 1,  S → 5
     *  "/" nằm giữa hai chữ số → "7" (Tesseract hay nhầm "7" thành "/")
     */
    private function fixOcrDigits(string $raw): string
    {
        $clean = strtoupper(trim($raw));
        $clean = strtr($clean, ['Q' => '0', 'O' => '0', 'I' => '1', 'S' => '5']);
        // "/" chỉ replace khi nằm giữa hai digit (không phải separator thực sự)
        $clean = preg_replace('/(\d)\/(\d)/', '${1}7${2}', $clean);
        // Loại bỏ mọi ký tự không phải số
        return preg_replace('/\D/', '', $clean);
    }

    // ── Họ và tên ────────────────────────────────────────────────────────────

    private function extractFullName(string $text): ?string
    {
        $lines = explode("\n", $text);

        // 1. Tìm dòng có label "Full name" / "Họ và tên" rồi check 2 dòng tiếp theo
        foreach ($lines as $idx => $line) {
            if (!preg_match('/(?:Họ\s*v[àa]\s*t[êe]n|Ho\s*v[aà]\s*t[eé]n|Full\s*name)/iu', $line)) {
                continue;
            }

            // Tìm tên trong: phần sau ":" trên dòng label, dòng +1, dòng +2
            $candidates = [];
            // Same line: phần sau dấu ":"
            if (preg_match('/[:\.](.+)$/', $line, $m)) {
                $candidates[] = $m[1];
            }
            // Next 2 lines
            for ($i = 1; $i <= 2; $i++) {
                if (isset($lines[$idx + $i])) {
                    $candidates[] = $lines[$idx + $i];
                }
            }

            // Chọn tên có confidence cao nhất từ tất cả candidates (same line / +1 / +2)
            $bestName  = null;
            $bestScore = 0.0;
            foreach ($candidates as $candidate) {
                $name = $this->pickNameFromLine($candidate);
                if (!$name) continue;
                $score = $this->nameConfidence($name);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestName  = $name;
                }
            }
            if ($bestName) return $bestName;
        }

        // 2. Fallback: chuỗi ALL_CAPS dài nhất chứa ký tự tiếng Việt, không là header
        $best    = null;
        $bestLen = 0;
        foreach ($lines as $line) {
            if (!preg_match_all('/[\p{Lu}\p{M}]+(?:\s+[\p{Lu}\p{M}]+)*/u', $line, $seqs)) continue;
            foreach ($seqs[0] as $seq) {
                $noSpaceLen = mb_strlen(preg_replace('/\s+/', '', $seq));
                if ($noSpaceLen < 5 || $noSpaceLen > 40) continue;
                if ($this->isKnownHeader(mb_strtoupper($seq))) continue;
                // Ưu tiên tên có dấu tiếng Việt (chữ CCCD thật) hơn noise ASCII
                $hasViet = preg_match('/[\x{0300}-\x{036F}\x{1EA0}-\x{1EFF}]/u', $seq);
                $score   = $noSpaceLen + ($hasViet ? 10 : 0);
                if ($score > $bestLen) {
                    $bestLen = $score;
                    $best    = $seq;
                }
            }
        }

        if (!$best) return null;
        $name = VietnameseNameTokenizer::tokenize(trim($best));
        return $this->nameConfidence($name) >= self::NAME_MIN_CONFIDENCE ? $name : null;
    }

    /**
     * Tìm chuỗi ALL_CAPS hợp lệ như một tên người trên 1 dòng OCR.
     * Trả về tên có nameConfidence ≥ NAME_MIN_CONFIDENCE, hoặc null.
     *
     * Tiêu chí: ≥ 4 ký tự thực, ≤ 40, không là header.
     * Ưu tiên chuỗi chứa ký tự tiếng Việt có dấu, sau đó dài hơn.
     */
    private function pickNameFromLine(string $line): ?string
    {
        if (!preg_match_all('/[\p{Lu}\p{M}]+(?:\s+[\p{Lu}\p{M}]+)*/u', $line, $seqs)) return null;

        // Sắp xếp: ưu tiên chuỗi có dấu tiếng Việt, sau đó dài hơn
        usort($seqs[0], function ($a, $b) {
            $aViet = (int)(bool)preg_match('/[\x{0300}-\x{036F}\x{1EA0}-\x{1EFF}]/u', $a);
            $bViet = (int)(bool)preg_match('/[\x{0300}-\x{036F}\x{1EA0}-\x{1EFF}]/u', $b);
            if ($aViet !== $bViet) return $bViet - $aViet;
            return mb_strlen(preg_replace('/\s+/', '', $b)) -
                   mb_strlen(preg_replace('/\s+/', '', $a));
        });

        foreach ($seqs[0] as $seq) {
            $tokenized = VietnameseNameTokenizer::tokenize(trim($seq));
            $len       = mb_strlen(preg_replace('/\s+/', '', $tokenized));
            if ($len < 4 || $len > 40) continue;
            if ($this->isKnownHeader(mb_strtoupper($tokenized))) continue;
            // Từ chối tên có độ tin cậy thấp (ví dụ: "CAN E" từ OCR noise)
            if ($this->nameConfidence($tokenized) < self::NAME_MIN_CONFIDENCE) continue;
            return $tokenized;
        }
        return null;
    }

    // ── Ngày sinh ────────────────────────────────────────────────────────────

    private function extractDateOfBirth(string $text): ?string
    {
        $patterns = [
            // Label rõ ràng
            '/(?:Ng[àa]y\s*sinh|Date\s*of\s*birth|DOB|NGAY\s*SINH)\s*[:\/.]*\s*(\d{2}[\/\-\.]\d{2}[\/\-\.]\d{4})/iu',
            // Bất kỳ ngày DD/MM/YYYY nào gần label
            '/(?:Ng[àa]y\s*sinh|Date\s*of\s*birth)[^\n]*\n?[^\d]*(\d{2}[\/\-]\d{2}[\/\-]\d{4})/iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                return $this->normalizeDate($m[1]);
            }
        }

        return null;
    }

    // ── Giới tính ─────────────────────────────────────────────────────────────

    private function extractGender(string $text): ?string
    {
        $pattern = '/(?:Gi[oớ][í]\s*t[iíy]nh|Gioi\s*tinh|Sex)\s*[:\/.]*\s*(Nam|N[ưữ]|Nu|Male|Female)/iu';
        if (preg_match($pattern, $text, $m)) {
            $raw = mb_strtolower(trim($m[1]));
            return match (true) {
                in_array($raw, ['nam', 'male'])          => 'Nam',
                in_array($raw, ['nữ', 'nu', 'female'])  => 'Nữ',
                default                                   => null,
            };
        }
        return null;
    }

    // ── Quốc tịch ────────────────────────────────────────────────────────────

    private function extractNationality(string $text): ?string
    {
        // Khoá trên label tiếng Việt trước (lấy sau dấu ":")
        if (preg_match(
            '/Qu[oố][cC]\s*t[iị][cC]h?\s*(?:\/\s*[^\n:]+)?:\s*([^\n:\/]{2,25})/iu',
            $text, $m
        )) {
            $val = $this->cleanShortField($m[1]);
            if (mb_strlen($val) >= 3) return $val;
        }

        // Khoá trên label tiếng Anh "Nationality:"
        if (preg_match('/\bNationality\s*:\s*([^\n:\/]{2,25})/i', $text, $m)) {
            $val = $this->cleanShortField($m[1]);
            if (mb_strlen($val) >= 3) return $val;
        }

        return null;
    }

    // ── Quê quán ─────────────────────────────────────────────────────────────

    private function extractHometown(string $text): ?string
    {
        // Khoá trên label tiếng Việt (lấy sau dấu ":")
        if (preg_match(
            '/Qu[êe]\s*qu[aá]n\s*(?:\/\s*[^\n:]+)?:\s*([^\n]{5,80})/iu',
            $text, $m
        )) {
            return $this->cleanLongField($m[1]);
        }

        // Khoá trên label tiếng Anh
        if (preg_match('/\bPlace\s*of\s*origin\s*:\s*([^\n]{5,80})/i', $text, $m)) {
            return $this->cleanLongField($m[1]);
        }

        // Fallback: dòng ngay sau label trên dòng riêng
        if (preg_match('/(?:Qu[êe]\s*qu[aá]n|Place\s*of\s*origin)[^\n]*\n([^\n]{5,80})/iu', $text, $m)) {
            return $this->cleanLongField($m[1]);
        }

        return null;
    }

    // ── Nơi thường trú ───────────────────────────────────────────────────────

    private function extractAddress(string $text): ?string
    {
        // Khoá trên label tiếng Việt (lấy sau dấu ":")
        if (preg_match(
            '/N[oơ]i\s*th[uư][oờ]ng\s*tr[uú]\s*(?:\/\s*[^\n:]+)?:\s*(.+?)(?=(?:Ng[àa]y|Date\s*of\s*expiry|$))/isu',
            $text, $m
        )) {
            return $this->cleanLongField($m[1]);
        }

        // Khoá trên label tiếng Anh
        if (preg_match(
            '/\bPlace\s*of\s*residence\s*:\s*(.+?)(?=(?:Ng[àa]y|$))/isu',
            $text, $m
        )) {
            return $this->cleanLongField($m[1]);
        }

        // Fallback: sau label, đến cuối block text
        if (preg_match(
            '/(?:N[oơ]i\s*th[uư][oờ]ng\s*tr[uú]|Place\s*of\s*residence)[^\n]*\n([^\n]{10,}(?:\n[^\n]{5,})?)/iu',
            $text, $m
        )) {
            return $this->cleanLongField($m[1]);
        }

        return null;
    }

    // ── Ngày hết hạn (mặt trước) ─────────────────────────────────────────────

    private function extractExpiryDate(string $text): ?string
    {
        // Label rõ ràng
        $pattern = '/(?:Ng[àa]y\s*h[eế]t\s*h[aạ]n|Date\s*of\s*expiry|C[oó]\s*gi[aá]\s*tr[ịi]\s*[dđ][eế]n)[:\s\/.-]*(\d{2}[\/\-]\d{2}[\/\-]\d{4})/iu';
        if (preg_match($pattern, $text, $m)) {
            return $this->normalizeDate($m[1]);
        }

        // Fallback: sau "giá trị đến" / "có giá trị" — OCR hay đọc "đến" thành "den"
        if (preg_match('/gi[aá]\s*tr[ịi]\s*[dđ][eế]n\s*[:\s.]*([^\n]{0,30})/iu', $text, $m)) {
            // Tìm trong đoạn text ngắn sau label — có thể bị noise
            $chunk = $this->fixOcrDigits($m[1]);
            if (preg_match('/(\d{8})/', $chunk, $d)) {
                // Thử parse DDMMYYYY liền nhau
                $raw = $d[1];
                $candidate = substr($raw, 0, 2) . '/' . substr($raw, 2, 2) . '/' . substr($raw, 4, 4);
                $result = $this->normalizeDate($candidate);
                if ($result) return $result;
            }
        }

        return null;
    }

    // ── Ngày cấp (mặt sau) ────────────────────────────────────────────────────

    /**
     * Trích ngày cấp từ mặt sau CCCD.
     * Mặt sau: phần chữ ký / đóng dấu của Cục Cảnh Sát có ghi "Ngày cấp" hoặc
     * "Ngày, tháng, năm" kèm theo ngày.
     */
    private function extractIssueDate(string $text): ?string
    {
        $patterns = [
            // Label "Ngày cấp" / "Date of issue"
            '/(?:Ng[àa]y\s*c[aấ]p|Date\s*of\s*issue)[:\s\/.-]*(\d{2}[\/\-\.]\d{2}[\/\-\.]\d{4})/iu',
            // Label ký tên "Ngày, tháng, năm / Date, month, year"
            '/(?:Ng[àa]y[,.]?\s*th[aá]ng[,.]?\s*n[aă]m|Date[,.]?\s*month[,.]?\s*year)[:\s\/.-]*(\d{2}[\/\-]\d{2}[\/\-]\d{4})/iu',
            // Bất kỳ DD/MM/YYYY nào trên mặt sau (thường chỉ có 1 ngày = ngày cấp)
            '/\b(\d{2}[\/]\d{2}[\/]\d{4})\b/',
            '/\b(\d{2}[\-]\d{2}[\-]\d{4})\b/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $date = $this->normalizeDate($m[1]);
                if ($date && $this->isReasonableIssueDate($date)) return $date;
            }
        }

        return null;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    // ── Confidence helpers ─────────────────────────────────────────────────────

    /**
     * Confidence cho CCCD ID: 12 digit cao hơn 9 digit.
     */
    private function idConfidence(string $id): float
    {
        return strlen($id) === 12 ? 0.9 : 0.7;
    }

    /**
     * Confidence cho tên người.
     *
     * Thưởng: dài + dấu tiếng Việt + nhiều âm tiết
     * Trừ:   âm tiết 1-ký-tự (ví dụ "E", "Ỉ" — thường là OCR noise, không phải tên thật)
     *
     * Ví dụ:
     *   "ĐỖ HÀ KIÊN"  (8 chars, Viet, 0 short-syllable) → 0.95 ✓
     *   "NGUYEN THI HOA" (12, no-Viet, 0 short)         → 0.80 ✓
     *   "E IE Ỉ NAM"  (7, Viet, 2 short-syllable)       → 0.68 ✗
     *   "CAN E"        (4, no-Viet, 1 short-syllable)   → 0.51 ✗
     */
    private function nameConfidence(string $name): float
    {
        $len     = mb_strlen(preg_replace('/\s+/', '', $name));
        $hasViet = (bool)preg_match('/[\x{0300}-\x{036F}\x{1EA0}-\x{1EFF}]/u', $name);

        $syllableList   = array_values(array_filter(explode(' ', trim($name))));
        $syllableCount  = count($syllableList);
        $shortSyllables = count(array_filter($syllableList, fn($s) => mb_strlen($s) <= 1));

        $score = 0.4
            + min(0.3, $len * 0.04)               // 0–0.3 từ độ dài
            + ($hasViet     ? 0.2 : 0.0)           // +0.2 có dấu Việt
            + ($syllableCount >= 2 ? 0.1 : 0.0)   // +0.1 có ≥ 2 âm tiết
            - ($shortSyllables * 0.15);            // −0.15 mỗi âm tiết 1-ký-tự (OCR noise)

        return min(0.95, max(0.0, $score));
    }

    /**
     * Confidence cho trường ngắn (nationality, gender…): trừ điểm nếu có noise.
     */
    private function shortFieldConfidence(string $value): float
    {
        $hasNoise = (bool)preg_match('/[^a-zA-ZÀ-ỹĐđ\s]/u', $value);
        return $hasNoise ? 0.5 : 0.75;
    }

    private function normalizeDate(string $raw): ?string
    {
        // Chuẩn hoá về DD/MM/YYYY
        $clean = preg_replace('/[.\-]/', '/', trim($raw));
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $clean, $m)) {
            [$_, $d, $mo, $y] = $m;
            if ((int)$mo >= 1 && (int)$mo <= 12 && (int)$d >= 1 && (int)$d <= 31) {
                return "{$d}/{$mo}/{$y}";
            }
        }
        return null;
    }

    private function cleanShortField(string $text): string
    {
        $clean = trim(preg_replace('/\s{2,}/', ' ', $text));
        // Bỏ noise cuối: ký tự đặc biệt, dấu gạch đơn lẻ, dấu chấm cuối
        $clean = preg_replace('/[\!\@\#\$\%\^\&\*\(\)\[\]\{\}\\\\\/\|<>\-\.\s]+$/', '', $clean);
        // Cắt đoạn bắt đầu bằng ký tự rác nếu value bắt đầu sai
        $clean = preg_replace('/^[^a-zA-ZÀ-ỹĐđ\p{L}]+/u', '', $clean);
        return trim($clean);
    }

    private function cleanLongField(string $text): string
    {
        // Collapse newline → space, bỏ noise đặc biệt
        $clean = preg_replace('/\s+/', ' ', $text);
        $clean = preg_replace('/[^a-zA-ZÀ-ỹĐđ0-9\s,\.\-\/]/u', '', $clean);
        return trim($clean);
    }

    /**
     * Tính ngày cấp = ngày hết hạn − 10 năm (quy định CCCD Việt Nam).
     */
    private function calcIssueDateFromExpiry(string $expiryDMY): ?string
    {
        if (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $expiryDMY, $m)) return null;
        [, $d, $mo, $y] = $m;
        $issueYear = (int)$y - 10;
        if ($issueYear < 2010 || $issueYear > (int)date('Y')) return null;
        return "{$d}/{$mo}/{$issueYear}";
    }

    private function isReasonableIssueDate(string $dmyDate): bool
    {
        if (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dmyDate, $m)) return false;
        $year = (int)$m[3];
        return $year >= 2010 && $year <= (int)date('Y');
    }

    private function isLabeledDate(string $text): bool
    {
        return (bool)preg_match(
            '/(?:Ng[àa]y\s*c[aấ]p|Date\s*of\s*issue|Ng[àa]y[,.]?\s*th[aá]ng)/i',
            $text
        );
    }

    private function looksLikeDate(string $digits): bool
    {
        // Heuristic: 8 ký tự có thể là DDMMYYYY hoặc YYYYMMDD
        if (strlen($digits) !== 8) return false;
        $month = (int)substr($digits, 2, 2);
        return $month >= 1 && $month <= 12;
    }

    private const NAME_MIN_CONFIDENCE = 0.75;

    /** Các header/tiêu đề thường xuất hiện in HOA trên CCCD, không phải tên người. */
    private function isKnownHeader(string $upper): bool
    {
        // Chuẩn hoá: bỏ dấu tiếng Việt để so sánh dễ hơn
        $stripped = $this->stripAccents($upper);

        $keywords = [
            // English headers
            'SOCIALIST', 'REPUBLIC', 'INDEPENDENCE', 'FREEDOM', 'HAPPINESS',
            'CITIZEN', 'IDENTITY', 'CARD', 'POLICE', 'DEPARTMENT',
            // Vietnamese without diacritics (after strip)
            'CONG HOA', 'CHU NGHIA', 'VIET NAM', 'NHAN DAN',
            'DOC LAP', 'TU DO', 'HANH PHUC', 'NGHIA',
            // Field labels
            'NATIONALITY', 'RESIDENCE', 'ORIGIN', 'BIRTH',
        ];

        foreach ($keywords as $kw) {
            if (str_contains($stripped, $kw)) return true;
        }
        return false;
    }

    /** Bỏ dấu tiếng Việt (đơn giản, chỉ dùng cho so sánh header). */
    private function stripAccents(string $text): string
    {
        static $map = null;
        if ($map === null) {
            $map = [
                'À','Á','Â','Ã','È','É','Ê','Ì','Í','Ò','Ó','Ô','Õ','Ù','Ú','Ý',
                'à','á','â','ã','è','é','ê','ì','í','ò','ó','ô','õ','ù','ú','ý',
                'Ă','ă','Ắ','ắ','Ằ','ằ','Ặ','ặ','Ẳ','ẳ','Ẵ','ẵ',
                'Ơ','ơ','Ớ','ớ','Ờ','ờ','Ợ','ợ','Ở','ở','Ỡ','ỡ',
                'Ư','ư','Ứ','ứ','Ừ','ừ','Ự','ự','Ử','ử','Ữ','ữ',
                'Ấ','ấ','Ầ','ầ','Ậ','ậ','Ẩ','ẩ','Ẫ','ẫ',
                'Ế','ế','Ề','ề','Ệ','ệ','Ể','ể','Ễ','ễ',
                'Ố','ố','Ồ','ồ','Ộ','ộ','Ổ','ổ','Ỗ','ỗ',
                'Ỉ','ỉ','Ị','ị','Ũ','ũ','Ụ','ụ','Ủ','ủ',
                'Ỳ','ỳ','Ỵ','ỵ','Ỷ','ỷ','Ỹ','ỹ',
                'Đ','đ',
            ];
            $replace = [
                'A','A','A','A','E','E','E','I','I','O','O','O','O','U','U','Y',
                'a','a','a','a','e','e','e','i','i','o','o','o','o','u','u','y',
                'A','a','A','a','A','a','A','a','A','a','A','a',
                'O','o','O','o','O','o','O','o','O','o','O','o',
                'U','u','U','u','U','u','U','u','U','u','U','u',
                'A','a','A','a','A','a','A','a','A','a',
                'E','e','E','e','E','e','E','e','E','e',
                'O','o','O','o','O','o','O','o','O','o',
                'I','i','I','i','U','u','U','u','U','u',
                'Y','y','Y','y','Y','y','Y','y',
                'D','d',
            ];
            $map = [$map, $replace];
        }
        return strtoupper(str_replace($map[0], $map[1], $text));
    }
}
