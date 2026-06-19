<?php

namespace App\Shared\Ocr\Data;

use Spatie\LaravelData\Data;

class CccdData extends Data
{
    public function __construct(
        public readonly ?string $id,           // Số CCCD (12 chữ số) hoặc CMND (9 chữ số)
        public readonly ?string $fullName,     // Họ và tên (đã tokenize)
        public readonly ?string $dateOfBirth,  // Ngày sinh (DD/MM/YYYY)
        public readonly ?string $gender,       // Giới tính (Nam / Nữ)
        public readonly ?string $nationality,  // Quốc tịch
        public readonly ?string $hometown,     // Quê quán
        public readonly ?string $address,      // Nơi thường trú
        public readonly ?string $expiryDate,   // Ngày hết hạn (từ mặt trước)
        public readonly string  $rawText,      // Text thô từ OCR
        public readonly ?string $issueDate  = null, // Ngày cấp (từ mặt sau)
        public readonly array   $confidence = [], // Độ tin cậy từng trường (0.0–1.0)
    ) {}

    public function isComplete(): bool
    {
        return $this->id && $this->fullName && $this->dateOfBirth;
    }

    /**
     * Tính điểm confidence tổng từ map field.
     * Dùng để so sánh kết quả multi-pass OCR.
     */
    public function overallConfidence(): float
    {
        if (empty($this->confidence)) {
            // Estimate từ số trường có giá trị
            $filled  = count(array_filter([$this->id, $this->fullName, $this->dateOfBirth, $this->gender]));
            return round($filled / 4, 2);
        }
        return count($this->confidence) ? array_sum($this->confidence) / count($this->confidence) : 0.0;
    }

    /**
     * Merge kết quả từ 2 lần đọc: field-by-field, ưu tiên giá trị có confidence cao hơn.
     *
     * Confidence được merge theo max (không dùng array_merge thông thường vì nó overwrite
     * confidence cao bằng 0.0 từ pass không tìm được trường đó).
     */
    public function mergeWith(self $other): self
    {
        // Max-merge confidence để không bị overwrite bởi pass 0.0
        $mergedConf = [];
        foreach (array_keys($this->confidence + $other->confidence) as $key) {
            $mergedConf[$key] = max($this->confidence[$key] ?? 0.0, $other->confidence[$key] ?? 0.0);
        }

        $pick = function (?string $mine, ?string $theirs, string $field) use ($other, $mergedConf): ?string {
            if (!$mine)   return $theirs;
            if (!$theirs) return $mine;
            // So sánh confidence của từng nguồn (không dùng mergedConf để tránh circular)
            $myConf    = $this->confidence[$field]  ?? 0.5;
            $theirConf = $other->confidence[$field] ?? 0.5;
            return $theirConf > $myConf ? $theirs : $mine;
        };

        return new self(
            id:          $pick($this->id,          $other->id,          'id'),
            fullName:    $pick($this->fullName,    $other->fullName,    'fullName'),
            dateOfBirth: $pick($this->dateOfBirth, $other->dateOfBirth, 'dateOfBirth'),
            gender:      $pick($this->gender,      $other->gender,      'gender'),
            nationality: $pick($this->nationality, $other->nationality,  'nationality'),
            hometown:    $pick($this->hometown,    $other->hometown,    'hometown'),
            address:     $pick($this->address,     $other->address,     'address'),
            expiryDate:  $pick($this->expiryDate,  $other->expiryDate,  'expiryDate'),
            issueDate:   $pick($this->issueDate,   $other->issueDate,   'issueDate'),
            rawText:     $this->rawText,
            confidence:  $mergedConf,
        );
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'full_name'     => $this->fullName,
            'date_of_birth' => $this->dateOfBirth,
            'gender'        => $this->gender,
            'nationality'   => $this->nationality,
            'hometown'      => $this->hometown,
            'address'       => $this->address,
            'expiry_date'   => $this->expiryDate,
            'issue_date'    => $this->issueDate,
        ];
    }
}
