<?php

namespace App\Shared\Ocr\Region;

use App\Shared\Ocr\Contracts\OcrDriverContract;
use App\Shared\Ocr\Data\CccdData;
use App\Shared\Ocr\Data\OcrWord;
use App\Shared\Ocr\Enums\DocumentType;
use App\Shared\Ocr\Utils\VietnameseNameTokenizer;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Region-based OCR extractor cho CCCD Việt Nam.
 *
 * Quy trình:
 *  1. Chạy TSV OCR (PSM 6) với tessconf tùy chỉnh (tắt dictionary penalties)
 *  2. Nhóm các từ thành dòng (±30px Y-center tolerance)
 *  3. Tìm dòng label → lấy value words bên phải hoặc dòng kế tiếp
 *  4. Fallback 1: crop vùng → Imagick adaptive threshold → PSM 7
 *     (dùng cho vùng hologram: gender, nationality, expiry)
 *  5. Fallback 2: vùng Y cố định (expiry)
 */
class CccdRegionExtractor
{
    private const Y_TOLERANCE    = 30;   // px — tolerance gộp từ vào cùng dòng
    private const CROP_PADDING   = 20;   // px — padding xung quanh khi crop
    private const CROP_SCALE     = 4;    // × — phóng to crop trước PSM 7
    private const CROP_CONTRAST  = 75;
    private const CROP_SHARPEN   = 5;
    private const NAME_MIN_CONF  = 0.75; // Confidence tối thiểu để chấp nhận tên

    private ?string $tessconfTsv;
    private ?string $tessconf;

    public function __construct(private readonly OcrDriverContract $driver)
    {
        $tsvConf = storage_path('app/tessconf/cccd_tsv.conf');
        $txtConf = storage_path('app/tessconf/cccd.conf');
        $this->tessconfTsv = file_exists($tsvConf) ? $tsvConf : null;
        $this->tessconf    = file_exists($txtConf) ? $txtConf : null;
    }

    /**
     * Trích xuất thông tin CCCD từ ảnh đã tiền xử lý.
     *
     * @param  string       $preprocessedPath  Đường dẫn ảnh đã qua ImagePreprocessor::prepare()
     * @param  DocumentType $side
     */
    public function extract(string $preprocessedPath, DocumentType $side): CccdData
    {
        $tsv6 = $this->driver->extractWords($preprocessedPath, $this->tsvOverrides(['psm' => 6]));

        $words6 = $this->parseTsv($tsv6);

        if (empty($words6)) {
            return $this->emptyData();
        }

        $lines6 = $this->groupIntoLines($words6);

        $confidence = [];

        $id          = $this->extractId($lines6, $preprocessedPath, $confidence);
        $fullName    = $this->extractFullName($lines6, $preprocessedPath, $confidence);
        $dateOfBirth = $this->extractDateOfBirth($lines6, $words6, $preprocessedPath, $confidence);
        $gender      = $this->extractGender($lines6, $preprocessedPath, $confidence);
        $nationality = $this->extractNationality($lines6, $preprocessedPath, $confidence);
        $hometown    = $this->extractHometown($lines6, $preprocessedPath, $confidence);
        $address     = $this->extractAddress($lines6, $preprocessedPath, $confidence);
        $expiryDate  = $this->extractExpiryDate($lines6, $words6, $preprocessedPath, $confidence);

        return new CccdData(
            id:          $id,
            fullName:    $fullName,
            dateOfBirth: $dateOfBirth,
            gender:      $gender,
            nationality: $nationality,
            hometown:    $hometown,
            address:     $address,
            expiryDate:  $expiryDate,
            rawText:     '',
            confidence:  $confidence,
        );
    }

    // ── TSV Parsing ───────────────────────────────────────────────────────────

    /**
     * Parse TSV output từ Tesseract thành danh sách OcrWord.
     * TSV format: level page_num block_num par_num line_num word_num left top width height conf text
     */
    private function parseTsv(string $tsv): array
    {
        $words = [];
        $lines = explode("\n", $tsv);

        foreach (array_slice($lines, 1) as $line) {
            $cols = explode("\t", $line);
            if (count($cols) < 12) continue;

            [$level, , $blockNum, , $lineNum, ,
             $left, $top, $width, $height, $conf, $text] = $cols;

            if ((int)$level !== 5) continue;
            if (trim($text) === '') continue;
            if ((float)$conf < 0) continue;

            $words[] = new OcrWord(
                text:       trim($text),
                left:       (int)$left,
                top:        (int)$top,
                width:      (int)$width,
                height:     (int)$height,
                confidence: (float)$conf / 100.0,
                lineNum:    (int)$lineNum,
                blockNum:   (int)$blockNum,
            );
        }

        return $words;
    }

    // ── Line Grouping ─────────────────────────────────────────────────────────

    /**
     * Nhóm các OcrWord thành dòng dựa vào Y-center ±30px tolerance.
     * Mỗi dòng được sắp xếp từ trái sang phải theo left.
     *
     * @return array<int, OcrWord[]>
     */
    private function groupIntoLines(array $words): array
    {
        $lines = [];

        foreach ($words as $word) {
            $cy       = $word->centerY();
            $assigned = false;

            foreach ($lines as &$line) {
                $lineCy = (int)(array_sum(array_map(fn($w) => $w->centerY(), $line)) / count($line));
                if (abs($cy - $lineCy) <= self::Y_TOLERANCE) {
                    $line[]   = $word;
                    $assigned = true;
                    break;
                }
            }
            unset($line);

            if (!$assigned) {
                $lines[] = [$word];
            }
        }

        foreach ($lines as &$line) {
            usort($line, fn($a, $b) => $a->left <=> $b->left);
        }
        unset($line);

        usort($lines, function ($a, $b) {
            $aY = (int)(array_sum(array_map(fn($w) => $w->centerY(), $a)) / count($a));
            $bY = (int)(array_sum(array_map(fn($w) => $w->centerY(), $b)) / count($b));
            return $aY <=> $bY;
        });

        return $lines;
    }

    // ── Field Extractors ──────────────────────────────────────────────────────

    /**
     * Trích số CCCD: label trên cùng dòng (PSM6), value ở bên phải.
     *
     * Pass A: label-based — tìm label Số/No/CCCD + value word riêng lẻ
     * Pass B: scan tất cả words tìm word có đúng 12 digit
     */
    private function extractId(
        array $lines6, string $imagePath, array &$confidence
    ): ?string {
        $idCandidates12 = [];
        $idCandidates9  = [];

        foreach ($lines6 as $line) {
            $labelIdx = $this->findLabelWord($line, '/\bNo[\.\:\/]|sino[\.\:]|CCCD|\bS[oố][\.\:\/]/iu');
            if ($labelIdx === null) continue;

            $valueWords = array_slice($line, $labelIdx + 1);
            if (empty($valueWords)) continue;

            foreach ($valueWords as $vw) {
                if ($vw->confidence < 0.3) continue;
                $fixed = $this->fixOcrDigits($vw->text);
                if (preg_match('/^(\d{12})$/', $fixed, $m)) {
                    $idCandidates12[] = ['val' => $m[1], 'conf' => $vw->confidence];
                } elseif (preg_match('/^(\d{9})$/', $fixed, $m) && !$this->looksLikeDate($m[1])) {
                    $idCandidates9[]  = ['val' => $m[1], 'conf' => $vw->confidence];
                }
                $fixedSlash = preg_replace('/(\d)\/(\d)/', '${1}7${2}', $fixed);
                $fixedSlash = preg_replace('/\D/', '', $fixedSlash);
                if (preg_match('/^(\d{12})$/', $fixedSlash, $m)) {
                    $idCandidates12[] = ['val' => $m[1], 'conf' => $vw->confidence];
                }
            }

            // Crop+PSM7 chỉ khi TSV chưa có 12-digit candidate
            if (empty($idCandidates12)) {
                foreach ($valueWords as $vw) {
                    if ($vw->confidence < 0.2 || mb_strlen($vw->text) < 6) continue;

                    $region  = ['left' => $vw->left, 'top' => $vw->top, 'right' => $vw->right(), 'bottom' => $vw->bottom()];
                    $cropped = $this->cropRegion($imagePath, $region);
                    if ($cropped !== null) {
                        try {
                            $text  = $this->driver->extractText($cropped['path'], $this->txtOverrides(['psm' => 7, 'allowlist' => '0123456789/']));
                            $fixed = $this->fixOcrDigits($text);
                            if (preg_match('/^(\d{12})$/', $fixed, $m)) {
                                $idCandidates12[] = ['val' => $m[1], 'conf' => 0.85];
                                break;
                            } elseif (preg_match('/(\d{12})/', $fixed, $m)) {
                                $idCandidates12[] = ['val' => $m[1], 'conf' => 0.7];
                                break;
                            } elseif (preg_match('/^(\d{9})$/', $fixed, $m) && !$this->looksLikeDate($m[1])) {
                                $idCandidates9[] = ['val' => $m[1], 'conf' => 0.8];
                            }
                        } catch (\Throwable) {}
                        $this->cleanupTemp($cropped['path']);
                    }
                }
            }
        }

        if (!empty($idCandidates12)) {
            usort($idCandidates12, fn($a, $b) => $b['conf'] <=> $a['conf']);
            $confidence['id'] = 0.9;
            return $idCandidates12[0]['val'];
        }
        if (!empty($idCandidates9)) {
            usort($idCandidates9, fn($a, $b) => $b['conf'] <=> $a['conf']);
            $confidence['id'] = 0.85;
            return $idCandidates9[0]['val'];
        }

        // Pass B: scan tất cả words tìm word riêng lẻ có đúng 12 digit
        foreach ($lines6 as $line) {
            foreach ($line as $word) {
                $fixed = $this->fixOcrDigits($word->text);
                if (preg_match('/^(\d{12})$/', $fixed, $m)) {
                    $confidence['id'] = 0.85;
                    return $m[1];
                }
            }
        }

        return null;
    }

    /**
     * Trích họ tên: label (PSM6) → dòng kế tiếp là value.
     */
    private function extractFullName(
        array $lines6, string $imagePath, array &$confidence
    ): ?string {
        $labelLineY   = null;
        $textAreaX    = 0;
        $textAreaMaxX = 0;
        foreach ($lines6 as $line) {
            $labelIdx = $this->findLabelWord($line, '/t[eê]n|name/iu');
            if ($labelIdx !== null) {
                $labelLineY  = (int)(array_sum(array_map(fn($w) => $w->centerY(), $line)) / count($line));
                $textAreaX   = max(0, min(array_map(fn($w) => $w->left, $line)) - 50);
                $highConfWords = array_filter($line, fn($w) => $w->confidence >= 0.5);
                if (empty($highConfWords)) $highConfWords = $line;
                $labelRightX  = max(array_map(fn($w) => $w->right(), $highConfWords));
                $labelWidth   = max(1, $labelRightX - $textAreaX);
                $textAreaMaxX = $textAreaX + (int)($labelWidth * 1.5);
                break;
            }
        }

        if ($labelLineY === null) return null;

        foreach ($lines6 as $line) {
            $lineY = (int)(array_sum(array_map(fn($w) => $w->centerY(), $line)) / count($line));
            if ($lineY <= $labelLineY + 50 || $lineY >= $labelLineY + 350) continue;

            $validWords = array_filter($line, fn($w) =>
                $w->confidence >= 0.35
                && $w->left >= $textAreaX
                && $w->left <= $textAreaMaxX
            );
            if (empty($validWords)) continue;

            $tsvText   = $this->concatWords(array_values($validWords));
            $tokenized = VietnameseNameTokenizer::tokenize(trim($tsvText));
            if ($this->isKnownHeader($tokenized)) continue;

            $conf = $this->nameConfidence($tokenized);
            if ($conf >= self::NAME_MIN_CONF) {
                $confidence['fullName'] = $conf;
                return $tokenized;
            }
        }

        // Crop+PSM7
        $nameRegionPath = $this->cropNameRegionBelowLabel(
            $imagePath, $labelLineY, $textAreaX, $textAreaMaxX
        );
        if ($nameRegionPath !== null) {
            try {
                $text      = $this->driver->extractText($nameRegionPath, $this->txtOverrides(['psm' => 7]));
                $tokenized = VietnameseNameTokenizer::tokenize(trim($text));
                if (!$this->isKnownHeader($tokenized)) {
                    $conf = $this->nameConfidence($tokenized);
                    if ($conf >= self::NAME_MIN_CONF) {
                        $confidence['fullName'] = $conf;
                        $this->cleanupTemp($nameRegionPath);
                        return $tokenized;
                    }
                }
            } catch (\Throwable) {}
            $this->cleanupTemp($nameRegionPath);
        }

        return null;
    }

    /**
     * Trích ngày sinh: label (PSM6) cùng dòng với value bên phải.
     *
     * Lý do cần flat-word scan:
     * groupIntoLines() dùng Y-tolerance ±30px. Nếu có noise word ở Y gần ngày sinh
     * (e.g., "_" ở Y=2434 khi "30/03/1995" ở Y_center=2414), date word có thể bị
     * nhóm vào group của noise word thay vì group của label "sinh". Flat-word scan
     * bypass vấn đề này bằng cách tìm trực tiếp word có date pattern trong Y range.
     */
    private function extractDateOfBirth(
        array $lines6, array $allWords, string $imagePath, array &$confidence
    ): ?string {
        foreach ($lines6 as $line) {
            $labelIdx = $this->findLabelWord($line, '/sinh|birth/iu');
            if ($labelIdx === null) continue;

            $labelLineY = (int)(array_sum(array_map(fn($w) => $w->centerY(), $line)) / count($line));

            // Pass 1: value words on same grouped line
            $valueWords = array_slice($line, $labelIdx + 1);
            if (!empty($valueWords)) {
                $tsvText = $this->concatWords($valueWords);
                $date    = $this->normalizeDate($tsvText);
                if ($date) {
                    $confidence['dateOfBirth'] = 0.95;
                    return $date;
                }
            }

            // Pass 2: flat-word scan — bỏ qua giới hạn group, tìm date word trong Y range
            // Giải quyết trường hợp noise word làm tách date khỏi label group
            foreach ($allWords as $word) {
                if (abs($word->centerY() - $labelLineY) > 120) continue;
                if ($word->confidence < 0.25) continue;
                $date = $this->normalizeDate($word->text);
                if ($date) {
                    $confidence['dateOfBirth'] = 0.92;
                    return $date;
                }
            }

            // Pass 3: crop fallback (chỉ khi có valueWords để định vị crop region)
            if (empty($valueWords)) continue;
            $region  = $this->wordsRegion($valueWords);
            $cropped = $this->cropRegion($imagePath, $region);
            if ($cropped === null) continue;

            try {
                $text = $this->driver->extractText($cropped['path'], $this->txtOverrides(['psm' => 7, 'allowlist' => '0123456789/.-']));
                $date = $this->normalizeDate($text);
                if ($date) {
                    $confidence['dateOfBirth'] = 0.9;
                    $this->cleanupTemp($cropped['path']);
                    return $date;
                }
            } catch (\Throwable) {}
            $this->cleanupTemp($cropped['path']);
        }

        return null;
    }

    /**
     * Trích giới tính.
     *
     * Vùng gender nằm ngay cạnh hologram → TSV thường thiếu value words.
     * Fallback: tính toán crop region từ vị trí label → Imagick adaptive threshold → PSM 7.
     */
    private function extractGender(
        array $lines6, string $imagePath, array &$confidence
    ): ?string {
        foreach ($lines6 as $line) {
            $labelIdx = $this->findLabelWord($line, '/t[iíy]nh|\bSex\b/iu');
            if ($labelIdx === null) continue;

            $valueWords = array_slice($line, $labelIdx + 1);
            $tsvText    = $this->concatWords($valueWords);
            $gender     = $this->parseGender($tsvText);
            if ($gender) {
                $confidence['gender'] = 0.85;
                return $gender;
            }

            // Toàn bộ dòng
            $allText = $this->concatWords($line);
            $gender  = $this->parseGender($allText);
            if ($gender) {
                $confidence['gender'] = 0.8;
                return $gender;
            }

            // Imagick hologram crop: lấy vùng bên phải label word (ngay cả khi valueWords rỗng)
            // Dùng 800px width để bao phủ vùng "Nam/Nữ" nằm giữa "Sex." và "Quốc tịch"
            $labelWord  = $line[$labelIdx];
            $holoRegion = [
                'left'   => $labelWord->right() + 10,
                'top'    => max(0, $labelWord->top - 15),
                'right'  => $labelWord->right() + 800,
                'bottom' => $labelWord->bottom() + 15,
            ];
            $holoPath = $this->cropAndEnhanceForHologram($imagePath, $holoRegion);
            if ($holoPath) {
                try {
                    // Thử PSM 7 (single line) trước, fallback PSM 8 (single word)
                    foreach ([7, 8] as $psm) {
                        $text   = $this->driver->extractText($holoPath, $this->txtOverrides(['psm' => $psm]));
                        $gender = $this->parseGender($text);
                        if ($gender) {
                            $confidence['gender'] = 0.75;
                            $this->cleanupTemp($holoPath);
                            return $gender;
                        }
                    }
                } catch (\Throwable) {}
                $this->cleanupTemp($holoPath);
            }

            // Standard crop fallback nếu có valueWords
            if (!empty($valueWords)) {
                $region  = $this->wordsRegion($valueWords);
                $cropped = $this->cropRegion($imagePath, $region);
                if ($cropped) {
                    try {
                        $text   = $this->driver->extractText($cropped['path'], $this->txtOverrides(['psm' => 7]));
                        $gender = $this->parseGender($text);
                        if ($gender) {
                            $confidence['gender'] = 0.8;
                            $this->cleanupTemp($cropped['path']);
                            return $gender;
                        }
                    } catch (\Throwable) {}
                    $this->cleanupTemp($cropped['path']);
                }
            }
        }

        return null;
    }

    /**
     * Trích quốc tịch.
     *
     * Vùng quốc tịch bị hologram che → dùng Imagick adaptive threshold crop cho độ chính xác cao hơn.
     * Sau khi có text, normalize "VIỆT/Việt" → "Việt Nam" (99%+ CCCD là Việt Nam).
     */
    private function extractNationality(
        array $lines6, string $imagePath, array &$confidence
    ): ?string {
        foreach ($lines6 as $line) {
            $labelIdx = $this->findLabelWord($line, '/t[iị]ch|Nationality/iu');
            if ($labelIdx === null) continue;

            $valueWords = array_slice($line, $labelIdx + 1);

            // Quick check: có chứa "việt" hoặc biến thể → normalize ngay
            $allText = $this->concatWords($valueWords) . ' ' . $this->concatWords($line);
            if (preg_match('/vi[eêệ]\w*\s*n\w*m/iu', $allText)) {
                $confidence['nationality'] = 0.8;
                return 'Việt Nam';
            }

            // Lọc words chất lượng cao
            $cleanWords = array_filter($valueWords, function ($w) {
                if ($w->confidence < 0.4) return false;
                if (preg_match('/^Nationality/iu', $w->text)) return false;
                return (bool)preg_match('/[a-zA-ZÀ-ỹĐđ]/u', $w->text);
            });

            if (!empty($cleanWords)) {
                $tsvText = trim($this->concatWords(array_values($cleanWords)));
                $tsvText = preg_replace('/[\.]{2,}|[^\p{L}\p{N}\s]/u', ' ', $tsvText);
                $tsvText = trim(preg_replace('/\s{2,}/', ' ', $tsvText));
                if (mb_strlen($tsvText) >= 3) {
                    $confidence['nationality'] = 0.75;
                    return $tsvText;
                }
            }

            // Imagick hologram crop (kể cả khi valueWords rỗng)
            $labelWord  = $line[$labelIdx];
            $holoRegion = [
                'left'   => $labelWord->right() + 10,
                'top'    => max(0, $labelWord->top - 15),
                'right'  => $labelWord->right() + 600,
                'bottom' => $labelWord->bottom() + 15,
            ];
            $holoPath = $this->cropAndEnhanceForHologram($imagePath, $holoRegion);
            if ($holoPath) {
                try {
                    $text = trim($this->driver->extractText($holoPath, $this->txtOverrides(['psm' => 7])));
                    $text = preg_replace('/[^a-zA-ZÀ-ỹĐđ\s]/u', '', $text);
                    $text = trim(preg_replace('/\s{2,}/', ' ', $text));
                    if (mb_strlen($text) >= 3) {
                        if (preg_match('/vi[eêệ]/iu', $text)) {
                            $confidence['nationality'] = 0.78;
                            $this->cleanupTemp($holoPath);
                            return 'Việt Nam';
                        }
                        $confidence['nationality'] = 0.7;
                        $this->cleanupTemp($holoPath);
                        return $text;
                    }
                } catch (\Throwable) {}
                $this->cleanupTemp($holoPath);
            }

            // Standard crop fallback
            if (!empty($valueWords)) {
                $cleanForCrop = array_filter($valueWords, fn($w) => $w->confidence >= 0.3);
                $cropWords    = !empty($cleanForCrop) ? array_values($cleanForCrop) : $valueWords;
                $region       = $this->wordsRegion($cropWords);
                $cropped      = $this->cropRegion($imagePath, $region);
                if ($cropped) {
                    try {
                        $text = trim($this->driver->extractText($cropped['path'], $this->txtOverrides(['psm' => 7])));
                        $text = preg_replace('/[^a-zA-ZÀ-ỹĐđ\s]/u', '', $text);
                        $text = trim(preg_replace('/\s{2,}/', ' ', $text));
                        if (mb_strlen($text) >= 3) {
                            $confidence['nationality'] = 0.7;
                            $this->cleanupTemp($cropped['path']);
                            return $text;
                        }
                    } catch (\Throwable) {}
                    $this->cleanupTemp($cropped['path']);
                }
            }
        }

        return null;
    }

    /**
     * Trích quê quán: label (PSM6) → dòng kế tiếp là value.
     */
    private function extractHometown(
        array $lines6, string $imagePath, array &$confidence
    ): ?string {
        foreach ($lines6 as $lineIdx => $line) {
            $labelIdx = $this->findLabelWord($line, '/qu[aá]n|origin/iu');
            if ($labelIdx === null) continue;

            $labelLineY = (int)(array_sum(array_map(fn($w) => $w->centerY(), $line)) / count($line));

            // Tìm dòng kế có Y trong range 100-450px dưới label
            foreach ($lines6 as $line2) {
                $lineY2 = (int)(array_sum(array_map(fn($w) => $w->centerY(), $line2)) / count($line2));
                if ($lineY2 < $labelLineY + 80 || $lineY2 > $labelLineY + 450) continue;

                $highConf = array_filter($line2, function ($w) {
                    if ($w->confidence < 0.7) return false;
                    if (preg_match('/^(?:Place|of|origin|Qu[eê]|qu[aá]n)/iu', $w->text)) return false;
                    return (bool)preg_match('/[a-zA-ZÀ-ỹĐđ]/u', $w->text);
                });

                if (!empty($highConf)) {
                    $tsvText = trim($this->concatWords(array_values($highConf)));
                    $tsvText = trim(preg_replace('/\s{2,}/', ' ', $tsvText));
                    if (mb_strlen($tsvText) >= 5) {
                        $confidence['hometown'] = 0.7;
                        return $tsvText;
                    }
                }
            }

            // PSM6 fallback: dòng kế
            $nextWords  = $lines6[$lineIdx + 1] ?? [];
            $cleanWords = array_filter($nextWords, function ($w) {
                return $w->confidence >= 0.5 && preg_match('/[a-zA-ZÀ-ỹĐđ]/u', $w->text);
            });

            if (!empty($cleanWords)) {
                $tsvText = trim($this->concatWords(array_values($cleanWords)));
                if (mb_strlen($tsvText) >= 5) {
                    $confidence['hometown'] = 0.65;
                    return $tsvText;
                }
            }

            // Imagick hologram crop: crop vùng kế tiếp dưới label
            $labelWord  = $line[$labelIdx];
            $holoRegion = [
                'left'   => max(0, $labelWord->left - 20),
                'top'    => $labelLineY + 60,
                'right'  => $labelWord->right() + 1000,
                'bottom' => $labelLineY + 200,
            ];
            $holoPath = $this->cropAndEnhanceForHologram($imagePath, $holoRegion);
            if ($holoPath) {
                try {
                    $text = trim($this->driver->extractText($holoPath, $this->txtOverrides(['psm' => 7])));
                    if (mb_strlen($text) >= 5) {
                        $confidence['hometown'] = 0.6;
                        $this->cleanupTemp($holoPath);
                        return $text;
                    }
                } catch (\Throwable) {}
                $this->cleanupTemp($holoPath);
            }

            // Standard crop fallback
            if (!empty($nextWords)) {
                $highConfCrop = array_filter($nextWords, fn($w) => $w->confidence >= 0.5);
                $cropWords    = !empty($highConfCrop) ? array_values($highConfCrop) : $nextWords;
                if (!empty($cropWords)) {
                    $region  = $this->wordsRegion($cropWords);
                    $cropped = $this->cropRegion($imagePath, $region);
                    if ($cropped) {
                        try {
                            $text = trim($this->driver->extractText($cropped['path'], $this->txtOverrides(['psm' => 7])));
                            if (mb_strlen($text) >= 5) {
                                $confidence['hometown'] = 0.6;
                                $this->cleanupTemp($cropped['path']);
                                return $text;
                            }
                        } catch (\Throwable) {}
                        $this->cleanupTemp($cropped['path']);
                    }
                }
            }
        }

        return null;
    }

    /**
     * Trích nơi thường trú: label (PSM6) → 2 dòng kế tiếp là value.
     */
    private function extractAddress(
        array $lines6, string $imagePath, array &$confidence
    ): ?string {
        foreach ($lines6 as $lineIdx => $line) {
            $labelIdx = $this->findLabelWord($line, '/th[uư][oờ]ng\s*tr[uú]|residence/iu');
            if ($labelIdx === null) continue;

            $labelLineY = (int)(array_sum(array_map(fn($w) => $w->centerY(), $line)) / count($line));

            $addrParts = [];
            foreach ($lines6 as $line2) {
                $lineY2 = (int)(array_sum(array_map(fn($w) => $w->centerY(), $line2)) / count($line2));
                if ($lineY2 < $labelLineY + 80 || $lineY2 > $labelLineY + 700) continue;

                $highConf = array_filter($line2, function ($w) {
                    if ($w->confidence < 0.6) return false;
                    if (preg_match('/^(?:Place|of|residence|Nơi|thường|trú)/iu', $w->text)) return false;
                    return (bool)preg_match('/[a-zA-ZÀ-ỹĐđ]/u', $w->text);
                });

                if (!empty($highConf)) {
                    $lineText = trim($this->concatWords(array_values($highConf)));
                    if (mb_strlen($lineText) >= 3) {
                        $addrParts[] = $lineText;
                    }
                }
            }

            if (!empty($addrParts)) {
                $address = implode(', ', array_slice($addrParts, 0, 2));
                if (mb_strlen($address) >= 5) {
                    $confidence['address'] = 0.65;
                    return $address;
                }
            }

            // PSM6 fallback
            $nextLine1  = $lines6[$lineIdx + 1] ?? [];
            $nextLine2  = $lines6[$lineIdx + 2] ?? [];
            $allNext    = array_merge($nextLine1, $nextLine2);
            $cleanWords = array_filter($allNext, fn($w) =>
                $w->confidence >= 0.5 && preg_match('/[a-zA-ZÀ-ỹĐđ]/u', $w->text)
            );

            if (!empty($cleanWords)) {
                $tsvText = trim($this->concatWords(array_values($cleanWords)));
                if (mb_strlen($tsvText) >= 5) {
                    $confidence['address'] = 0.6;
                    return $tsvText;
                }
            }
        }

        return null;
    }

    /**
     * Trích ngày hết hạn.
     *
     * CCCD in "Có giá trị đến / Valid until" ở cuối mặt trước.
     * PSM6 thường đọc sai "đến" (hologram). Các fallback:
     *   1. Tìm label "đến/giá/hạn/valid/until" trên dòng → value bên phải
     *   2. Imagick crop từ vị trí label → PSM 7
     *   3. Quét tất cả words ở vùng Y dưới (bottom 30%) tìm date pattern
     */
    private function extractExpiryDate(
        array $lines6, array $allWords, string $imagePath, array &$confidence
    ): ?string {
        // Pass 1: label-based (pattern mở rộng để bắt các OCR errors)
        $broadLabelPattern = '/[dđ]\w*n|expir|[hH][aạ][nN]|giá\s*tr|valid|until|có\s*giá/iu';

        foreach ($lines6 as $line) {
            $labelIdx = $this->findLabelWord($line, $broadLabelPattern);
            if ($labelIdx === null) continue;

            $valueWords = array_slice($line, $labelIdx + 1);

            // Tìm date trong value words
            $tsvText = $this->concatWords($valueWords ?: $line);
            $date    = $this->normalizeDate($tsvText);
            if ($date) {
                $confidence['expiryDate'] = 0.9;
                return $date;
            }

            // Imagick hologram crop
            $labelWord  = $line[$labelIdx];
            $lineY      = (int)(array_sum(array_map(fn($w) => $w->centerY(), $line)) / count($line));
            $holoRegion = [
                'left'   => $labelWord->right() + 10,
                'top'    => max(0, $lineY - 40),
                'right'  => $labelWord->right() + 700,
                'bottom' => $lineY + 80,
            ];
            $holoPath = $this->cropAndEnhanceForHologram($imagePath, $holoRegion);
            if ($holoPath) {
                try {
                    $text = $this->driver->extractText($holoPath, $this->txtOverrides(['psm' => 7, 'allowlist' => '0123456789/.-']));
                    $date = $this->normalizeDate($text);
                    if ($date) {
                        $confidence['expiryDate'] = 0.85;
                        $this->cleanupTemp($holoPath);
                        return $date;
                    }
                } catch (\Throwable) {}
                $this->cleanupTemp($holoPath);
            }

            // Standard crop fallback (chỉ khi có valueWords)
            if (!empty($valueWords)) {
                $region  = $this->wordsRegion($valueWords);
                $cropped = $this->cropRegion($imagePath, $region);
                if ($cropped) {
                    try {
                        $text = $this->driver->extractText($cropped['path'], $this->txtOverrides(['psm' => 7, 'allowlist' => '0123456789/.-']));
                        $date = $this->normalizeDate($text);
                        if ($date) {
                            $confidence['expiryDate'] = 0.85;
                            $this->cleanupTemp($cropped['path']);
                            return $date;
                        }
                    } catch (\Throwable) {}
                    $this->cleanupTemp($cropped['path']);
                }
            }
        }

        // Pass 2: quét tất cả words ở bottom 30% của ảnh tìm date pattern
        // Lấy chiều cao ảnh từ max Y + height của word
        if (!empty($allWords)) {
            $maxBottom = max(array_map(fn($w) => $w->bottom(), $allWords));
            $threshold = (int)($maxBottom * 0.70); // bottom 30%

            $bottomWords = array_filter($allWords, fn($w) => $w->top >= $threshold && $w->confidence >= 0.4);
            $bottomText  = $this->concatWords(array_values($bottomWords));
            $date        = $this->normalizeDate($bottomText);
            if ($date) {
                $confidence['expiryDate'] = 0.75;
                return $date;
            }

            // Tìm từng word riêng lẻ xem có word nào là date không
            foreach (array_values($bottomWords) as $w) {
                $date = $this->normalizeDate($w->text);
                if ($date) {
                    $confidence['expiryDate'] = 0.7;
                    return $date;
                }
            }
        }

        return null;
    }

    // ── Hologram-aware crop ───────────────────────────────────────────────────

    /**
     * Crop vùng ảnh và áp dụng Imagick adaptive threshold để xuyên hologram.
     *
     * So với cropRegion() (dùng Intervention Image + contrast tĩnh):
     *  - adaptiveThresholdImage tính ngưỡng cục bộ per-block → không bị ảnh hưởng bởi hologram
     *  - contrastStretchImage chuẩn hoá histogram → text tối trên nền sáng đồng đều hơn
     *  - unsharpMaskImage sắc nét cạnh chữ mà không khuếch đại noise
     *
     * @param  string  $imagePath  Đường dẫn ảnh preprocessed
     * @param  array   $region     ['left', 'top', 'right', 'bottom']
     * @return string|null         Đường dẫn file tạm, null nếu lỗi
     */
    private function cropAndEnhanceForHologram(string $imagePath, array $region): ?string
    {
        try {
            $padding = self::CROP_PADDING;
            $left    = max(0, $region['left'] - $padding);
            $top     = max(0, $region['top'] - $padding);
            $right   = $region['right'] + $padding;
            $bottom  = $region['bottom'] + $padding;
            $w       = $right - $left;
            $h       = $bottom - $top;
            if ($w <= 0 || $h <= 0) return null;

            $tempPath = sys_get_temp_dir() . '/ocr_holo_' . uniqid() . '.png';

            $im = new \Imagick($imagePath);
            $im->cropImage($w, $h, $left, $top);

            // Scale 4× để tăng pixel density cho OCR
            $newW = $w * self::CROP_SCALE;
            $im->scaleImage($newW, 0);

            // Grayscale
            $im->transformImageColorspace(\Imagick::COLORSPACE_GRAY);

            // ContrastStretch: chuẩn hoá histogram trước khi threshold
            $pixels = $im->getImageWidth() * $im->getImageHeight();
            $im->contrastStretchImage((int)($pixels * 0.01), (int)($pixels * 0.01));

            // Adaptive threshold: block ~1.2% chiều rộng, offset -8%
            $scaledW   = $im->getImageWidth();
            $blockSize = max(21, (int)($scaledW * 0.012) | 1);
            $blockSize = ($blockSize % 2 === 0) ? $blockSize + 1 : $blockSize;
            $q         = \Imagick::getQuantumRange()['quantumRangeLong'];
            $offset    = (int)(-0.08 * $q);
            $im->adaptiveThresholdImage($blockSize, $blockSize, $offset);

            // Unsharp mask: làm sắc nét cạnh text
            $im->unsharpMaskImage(0, 1.0, 1.5, 0.02);

            $im->writeImage($tempPath);
            $im->destroy();

            return $tempPath;
        } catch (\Throwable) {
            return null;
        }
    }

    // ── Helpers: Find label / region / crop ──────────────────────────────────

    /**
     * Tìm index của từ label đầu tiên trong một dòng khớp với pattern.
     */
    private function findLabelWord(array $line, string $pattern, float $minConf = 0.0): ?int
    {
        foreach ($line as $idx => $word) {
            if ($minConf > 0.0 && $word->confidence < $minConf) continue;
            if (preg_match($pattern, $word->text)) {
                return $idx;
            }
        }
        return null;
    }

    /**
     * Nối text của các OcrWord thành chuỗi.
     */
    private function concatWords(array $words): string
    {
        return implode(' ', array_map(fn($w) => $w->text, $words));
    }

    /**
     * Tính bounding box bao phủ tất cả các OcrWord.
     */
    private function wordsRegion(array $words): array
    {
        $left   = min(array_map(fn($w) => $w->left, $words));
        $top    = min(array_map(fn($w) => $w->top, $words));
        $right  = max(array_map(fn($w) => $w->right(), $words));
        $bottom = max(array_map(fn($w) => $w->bottom(), $words));

        return compact('left', 'top', 'right', 'bottom');
    }

    /**
     * Crop vùng tên: Y[labelY+80 → labelY+320], X[textAreaX → textAreaMaxX].
     */
    private function cropNameRegionBelowLabel(
        string $imagePath, int $labelLineY, int $textAreaX, int $textAreaMaxX
    ): ?string {
        $region = [
            'left'   => max(0, $textAreaX - self::CROP_PADDING),
            'top'    => max(0, $labelLineY + 80),
            'right'  => $textAreaMaxX + self::CROP_PADDING,
            'bottom' => $labelLineY + 320,
        ];
        $cropped = $this->cropRegion($imagePath, $region);
        return $cropped ? $cropped['path'] : null;
    }

    /**
     * Crop vùng từ ảnh preprocessed, scale thêm 4×, enhance contrast, lưu ra file tạm.
     *
     * @param  string      $imagePath   Đường dẫn ảnh preprocessed
     * @param  array       $region      ['left', 'top', 'right', 'bottom']
     * @return array|null  ['path' => string] hoặc null nếu lỗi
     */
    private function cropRegion(string $imagePath, array $region): ?array
    {
        try {
            $padding = self::CROP_PADDING;
            $left    = max(0, $region['left']   - $padding);
            $top     = max(0, $region['top']    - $padding);
            $right   = $region['right']  + $padding;
            $bottom  = $region['bottom'] + $padding;
            $w       = $right - $left;
            $h       = $bottom - $top;

            if ($w <= 0 || $h <= 0) return null;

            $tempPath = sys_get_temp_dir() . '/ocr_crop_' . uniqid() . '.png';

            $cropped = Image::decode($imagePath)
                ->crop($w, $h, $left, $top);

            $newW = (int)($w * self::CROP_SCALE);
            $cropped->scale($newW);
            $cropped->grayscale();
            $cropped->contrast(self::CROP_CONTRAST);
            $cropped->sharpen(self::CROP_SHARPEN);
            $cropped->save($tempPath);

            return ['path' => $tempPath];
        } catch (\Throwable) {
            return null;
        }
    }

    private function cleanupTemp(?string $path): void
    {
        if ($path && file_exists($path) && str_starts_with($path, sys_get_temp_dir())) {
            @unlink($path);
        }
    }

    /**
     * Merge tessconf_tsv key vào TSV override array.
     */
    private function tsvOverrides(array $overrides = []): array
    {
        if ($this->tessconfTsv) {
            $overrides['tessconf_tsv'] = $this->tessconfTsv;
        }
        return $overrides;
    }

    /**
     * Merge tessconf key vào text override array.
     */
    private function txtOverrides(array $overrides = []): array
    {
        if ($this->tessconf) {
            $overrides['tessconf'] = $this->tessconf;
        }
        return $overrides;
    }

    // ── Field-specific parsers ────────────────────────────────────────────────

    /**
     * Heuristic: 8-9 ký tự số có thể là DDMMYYYY — không phải CCCD.
     */
    private function looksLikeDate(string $digits): bool
    {
        if (strlen($digits) < 8) return false;
        $month = (int)substr($digits, 2, 2);
        return $month >= 1 && $month <= 12;
    }

    /**
     * Sửa lỗi OCR phổ biến trong chuỗi số CCCD.
     */
    private function fixOcrDigits(string $raw): string
    {
        $clean = strtoupper(trim($raw));
        $clean = strtr($clean, ['Q' => '0', 'O' => '0', 'I' => '1', 'S' => '5']);
        $clean = preg_replace('/(\d)\/(\d)/', '${1}7${2}', $clean);
        return preg_replace('/\D/', '', $clean);
    }

    /**
     * Normalize date string về DD/MM/YYYY.
     */
    private function normalizeDate(string $raw): ?string
    {
        if (!preg_match('/(\d{2})[\/\.\-](\d{2})[\/\.\-](\d{4})/', $raw, $m)) {
            return null;
        }
        [$_, $d, $mo, $y] = $m;
        if ((int)$mo >= 1 && (int)$mo <= 12 && (int)$d >= 1 && (int)$d <= 31) {
            return "{$d}/{$mo}/{$y}";
        }
        return null;
    }

    /**
     * Parse giới tính từ OCR text.
     */
    private function parseGender(string $text): ?string
    {
        $lower = mb_strtolower(trim($text));
        return match (true) {
            (bool)preg_match('/nữ|nu\b/iu', $lower) || str_contains($lower, 'female') => 'Nữ',
            (bool)preg_match('/\bnam\b/iu', $lower)  || str_contains($lower, 'male')   => 'Nam',
            default                                                                       => null,
        };
    }

    /**
     * Tính độ tin cậy của chuỗi tên người.
     */
    private function nameConfidence(string $name): float
    {
        $len     = mb_strlen(preg_replace('/\s+/', '', $name));
        $hasViet = (bool)preg_match('/[\x{0300}-\x{036F}\x{1EA0}-\x{1EFF}]/u', $name);

        $syllableList   = array_values(array_filter(explode(' ', trim($name))));
        $syllableCount  = count($syllableList);
        $shortSyllables = count(array_filter($syllableList, fn($s) => mb_strlen($s) <= 1));

        $score = 0.4
            + min(0.3, $len * 0.04)
            + ($hasViet     ? 0.2 : 0.0)
            + ($syllableCount >= 2 ? 0.1 : 0.0)
            - ($shortSyllables * 0.15);

        return min(0.95, max(0.0, $score));
    }

    /**
     * Kiểm tra xem chuỗi có phải header thông thường trên CCCD không.
     */
    private function isKnownHeader(string $text): bool
    {
        static $patterns = [
            '/c[oôộồ]ng\s*h[oòóôộ]/iu',
            '/vi[eêệ]t\s*nam/iu',
            '/x[aãà]\s*h[oộ]i/iu',
            '/socialist|republic/iu',
            '/\bcitizen\b|\bidentity\b|\bcard\b/iu',
            '/\bpolice\b|\bdepartment\b/iu',
            '/nationality|independence|freedom|happiness/iu',
        ];
        foreach ($patterns as $p) {
            if (preg_match($p, $text)) return true;
        }
        return false;
    }

    private function emptyData(): CccdData
    {
        return new CccdData(
            id:          null,
            fullName:    null,
            dateOfBirth: null,
            gender:      null,
            nationality: null,
            hometown:    null,
            address:     null,
            expiryDate:  null,
            rawText:     '',
            confidence:  [],
        );
    }
}
