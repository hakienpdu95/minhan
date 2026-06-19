<?php

namespace App\Shared\Ocr\PostProcessors;

use App\Shared\Ocr\Data\CccdData;

/**
 * Post-processing thông minh theo cấu trúc CCCD Việt Nam.
 *
 * Áp dụng sau khi đã có kết quả OCR merge từ multi-pass:
 *  1. Xác thực và sửa số CCCD theo cấu trúc AAABCCDDDDDD
 *  2. Chuẩn hoá định dạng ngày tháng
 *  3. Làm sạch noise từ trường ngắn (quốc tịch, giới tính)
 *  4. Cross-validate: kiểm tra tính nhất quán giữa các trường
 */
class CccdPostProcessor
{
    // Mã tỉnh/thành hợp lệ (001–096) theo danh mục Bộ Công An
    private const MAX_PROVINCE_CODE = 96;

    public function process(CccdData $data): CccdData
    {
        $id          = $this->fixId($data->id, $data->dateOfBirth, $data->gender);
        $fullName    = $this->fixName($data->fullName);
        $dateOfBirth = $this->validateDate($data->dateOfBirth, 1920, 2010);
        $expiryDate  = $this->validateDate($data->expiryDate, 2013, 2050);
        $gender      = $this->normalizeGender($data->gender);
        $nationality = $this->cleanShortField($data->nationality);
        $hometown    = $this->cleanLongField($data->hometown);
        $address     = $this->cleanLongField($data->address);

        return new CccdData(
            id:          $id          ?? $data->id,
            fullName:    $fullName    ?? $data->fullName,
            dateOfBirth: $dateOfBirth ?? $data->dateOfBirth,
            gender:      $gender      ?? $data->gender,
            nationality: $nationality ?? $data->nationality,
            hometown:    $hometown    ?? $data->hometown,
            address:     $address     ?? $data->address,
            expiryDate:  $expiryDate  ?? $data->expiryDate,
            rawText:     $data->rawText,
            issueDate:   $data->issueDate,
            confidence:  $data->confidence,
        );
    }

    // ── ID ────────────────────────────────────────────────────────────────────

    /**
     * Xác thực và sửa số CCCD:
     *  - Kiểm tra mã tỉnh (001–096)
     *  - Nếu có DOB + gender: kiểm tra chữ số giới tính-thế kỷ (digit 4)
     *  - Nếu có DOB: kiểm tra 2 chữ số năm sinh (digits 5-6)
     *  - Nếu sai 1 chữ số: tự sửa (confidence cross-validate)
     */
    private function fixId(?string $id, ?string $dob, ?string $gender): ?string
    {
        if (!$id || !preg_match('/^\d{12}$/', $id)) return $id;

        $province = (int)substr($id, 0, 3);
        if ($province < 1 || $province > self::MAX_PROVINCE_CODE) {
            // Thử sửa: đôi khi OCR đọc "0" thành "O" (đã fix), hoặc "6" thành "8"
            // Không sửa tự động nếu không có thêm context
            return $id;
        }

        if (!$dob || !preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dob, $m)) {
            return $id;
        }

        $year     = $m[3];
        $century  = (int)substr($year, 0, 2); // 19 hoặc 20
        $year2    = substr($year, 2, 2);        // 2 chữ số cuối năm sinh

        $centuryDigit = (int)substr($id, 3, 1);
        $idYear2      = substr($id, 4, 2);
        $isFemaleDob  = $gender === 'Nữ';

        // Giá trị đúng của digit 4:
        // Male/1900s = 0, Male/2000s = 1, Female/1900s = 2, Female/2000s = 3
        $expectedCenturyBase = $isFemaleDob ? 2 : 0;
        $expectedCentury     = $expectedCenturyBase + ($century === 20 ? 1 : 0);

        // Sửa digit 4 nếu sai (và year2 đúng)
        if ($centuryDigit !== $expectedCentury && $idYear2 === $year2) {
            $fixed = substr($id, 0, 3) . $expectedCentury . substr($id, 4);
            return $fixed;
        }

        // Sửa digits 5-6 nếu sai (và digit 4 đúng)
        if ($centuryDigit === $expectedCentury && $idYear2 !== $year2) {
            $fixed = substr($id, 0, 4) . $year2 . substr($id, 6);
            return $fixed;
        }

        return $id;
    }

    // ── Name ──────────────────────────────────────────────────────────────────

    /**
     * Làm sạch tên: bỏ ký tự noise cuối/đầu, chuẩn hoá khoảng trắng.
     */
    private function fixName(?string $name): ?string
    {
        if (!$name) return null;
        $clean = trim(preg_replace('/[^a-zA-ZÀ-ỹĐđ\s]/u', ' ', $name));
        $clean = trim(preg_replace('/\s{2,}/', ' ', $clean));
        return mb_strlen($clean) >= 3 ? $clean : null;
    }

    // ── Dates ─────────────────────────────────────────────────────────────────

    /**
     * Kiểm tra ngày có hợp lệ trong khoảng năm cho trước.
     * Trả về null nếu ngoài khoảng (để caller dùng giá trị cũ).
     */
    private function validateDate(?string $date, int $minYear, int $maxYear): ?string
    {
        if (!$date || !preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $m)) {
            return null; // không sửa được
        }
        $year = (int)$m[3];
        if ($year < $minYear || $year > $maxYear) return null; // ngoài khoảng → discard
        return $date; // hợp lệ
    }

    // ── Short fields ──────────────────────────────────────────────────────────

    /**
     * Chuẩn hoá giới tính về "Nam" / "Nữ".
     */
    private function normalizeGender(?string $gender): ?string
    {
        if (!$gender) return null;
        $lower = mb_strtolower(trim($gender));
        return match (true) {
            in_array($lower, ['nam', 'male', 'm'])        => 'Nam',
            in_array($lower, ['nữ', 'nu', 'female', 'f']) => 'Nữ',
            default                                        => null, // không nhận dạng được → discard
        };
    }

    /**
     * Làm sạch và chuẩn hoá quốc tịch.
     *
     * CCCD VN: quốc tịch gần như chắc chắn là "Việt Nam".
     * Nếu OCR đọc được fragment "Việt" hoặc "VIỆT" → normalize ngay.
     * Nếu text quá ngắn/dài/noise → discard (caller dùng giá trị cũ).
     */
    private function cleanShortField(?string $value): ?string
    {
        if (!$value) return null;

        // Normalize "Việt Nam" fragments (hologram thường làm mất "Nam")
        // VIỆC → VIỆT bị đọc sai; VIỆT là prefix của "Việt Nam"
        if (preg_match('/vi[eêệ][tc]/iu', $value) || preg_match('/vi[eêệ]t/iu', $value)) {
            return 'Việt Nam';
        }

        // Bỏ ký tự không phải chữ/dấu cách
        $clean = preg_replace('/[^a-zA-ZÀ-ỹĐđ\s]/u', ' ', $value);
        $clean = trim(preg_replace('/\s{2,}/', ' ', $clean));
        // Quá ngắn hoặc quá dài = noise
        $len = mb_strlen($clean);
        if ($len < 3 || $len > 40) return null;
        return $clean;
    }

    /**
     * Làm sạch trường dài (địa chỉ, quê quán).
     */
    private function cleanLongField(?string $value): ?string
    {
        if (!$value) return null;
        $clean = preg_replace('/[^a-zA-ZÀ-ỹĐđ0-9\s,\.\-\/]/u', ' ', $value);
        $clean = trim(preg_replace('/\s{2,}/', ' ', $clean));
        $len = mb_strlen($clean);
        if ($len < 5 || $len > 200) return null;
        return $clean;
    }
}
