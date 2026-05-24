# BÁO CÁO PHÂN TÍCH KỸ THUẬT CHUYÊN SÂU
## So sánh Đặc tả PDF với Cài đặt Thực tế trong Codebase

**Hệ thống:** Scoring Module — Laravel Survey (Modules/Survey)  
**Đặc tả tham chiếu:** "Hệ thống và Phương pháp Xử lý Dữ liệu Đánh giá Năng lực Người dùng" (5 trang)  
**Sơ đồ tham chiếu:** `docs/scoring/hinhquytrinh.png`  
**Ngày phân tích:** 2026-05-24

---

## I. TỔNG QUAN HỆ THỐNG THEO ĐẶC TẢ

### 1.1 Kiến trúc tổng thể (Hệ thống 100)

PDF định nghĩa một hệ thống xử lý dữ liệu gồm **7 module** liên kết nhau:

| Module | Tên | Loại |
|--------|-----|------|
| **110** | Thu thập dữ liệu | Bắt buộc |
| **120** | Xử lý dữ liệu | Bắt buộc |
| **130** | Chấm điểm | Bắt buộc |
| **140** | Phân loại | Bắt buộc |
| **150** | Gợi ý | Bắt buộc |
| **160** | Cơ sở dữ liệu | Bắt buộc |
| **170** | Học dữ liệu | Tùy chọn nhưng là điểm khác biệt cốt lõi |

### 1.2 Công thức nền tảng

```
Score = Σ (Wi × Fi),  với i = 1 → n

Trong đó:
  Wi = Trọng số của tiêu chí thứ i
  Fi = Giá trị đặc trưng trích xuất từ dữ liệu người dùng
  Wi được CẬP NHẬT ĐỘNG bởi Module 170 dựa trên dữ liệu lịch sử
```

### 1.3 Quy trình 6 bước lặp

```
Bước 1: Thu thập dữ liệu đa nguồn (hành vi + tương tác + lịch sử)
Bước 2: Chuẩn hóa → trích xuất Fi = {f1, f2, …, fn}
Bước 3: Tính Score = Σ(Wi × Fi)
Bước 4: Chuẩn hóa điểm về thang xác định
Bước 5: Phân loại theo T1, T2 (có thể điều chỉnh động)
Bước 6: Cập nhật Wi dựa trên phản hồi (sai lệch predicted vs actual)
         ↑─────────────────────────────────────────────────────┘
         (Bước 3, 4, 6 lặp lại theo chu kỳ)
```

> **Điểm mấu chốt của đặc tả:** "Các bước (iii), (iv) và (vi) được thực hiện lặp lại theo chu kỳ xử lý dữ liệu nhằm điều chỉnh động kết quả đánh giá." — Đây là tính năng phân biệt hệ thống này với các hệ thống khác.

### 1.4 Bốn nguồn dữ liệu đầu vào (theo sơ đồ)

| # | Nguồn | Mô tả |
|---|-------|-------|
| 1 | Bài kiểm tra / đánh giá | Câu trả lời khảo sát có cấu trúc |
| 2 | Hành vi người dùng | Thao tác, pattern tương tác, thời gian |
| 3 | Lịch sử tương tác | Dữ liệu lịch sử hệ thống |
| 4 | Dữ liệu khác | Các nguồn bổ sung tùy cấu hình |

### 1.5 Bốn đầu ra hệ thống (theo sơ đồ)

| # | Đầu ra |
|---|--------|
| 1 | Hồ sơ năng lực |
| 2 | Báo cáo đánh giá năng lực |
| 3 | Lộ trình đào tạo / phát triển |
| 4 | Vị trí công việc phù hợp |

---

## II. PHÂN TÍCH CHI TIẾT TỪNG MODULE

---

### MODULE 110 — THU THẬP DỮ LIỆU

#### Đặc tả yêu cầu

PDF (trang 4): *"Module thu thập dữ liệu (110) nhận dữ liệu đầu vào bao gồm dữ liệu tương tác của người dùng với hệ thống và dữ liệu hành vi được ghi nhận theo thời gian."*

Sơ đồ liệt kê 4 nguồn đầu vào:
1. Bài kiểm tra / đánh giá
2. **Hành vi người dùng** (behavior data)
3. **Lịch sử tương tác** (interaction history)
4. Dữ liệu khác

#### Thực tế cài đặt

**`AnswerReader.php` (dòng 23–37):** Chỉ đọc duy nhất bảng `survey_answers`:

```php
$rows = DB::table('survey_answers as sa')
    ->join('survey_fields as sf', ...)
    ->leftJoin('survey_field_options as sfo', ...)
    ->where('sa.response_id', $responseId)
    ->where('sf.survey_id', $surveyId)
    ->select([...])
    ->get();
```

**`SubmissionBehaviorLog.php`** — Model tồn tại (bảng `submission_behavior_log` có: `event_type`, `event_value`, `sequence_no`, `occurred_at`), nhưng:
- Không có code ghi dữ liệu hành vi vào bảng này trong `SubmitSurveyAction.php`
- Không có code đọc bảng này trong `AnswerReader.php`
- `FeatureExtractor.php` không nhận behavioral data

**`FeedbackSourcesConfig.php`** — Model tồn tại nhưng không có controller, không có UI cấu hình.

#### Kết luận Module 110

| Nguồn dữ liệu | Đặc tả | Thực tế |
|---|---|---|
| Bài kiểm tra / khảo sát | ✅ Yêu cầu | ✅ Cài đặt đầy đủ |
| Hành vi người dùng | ✅ Yêu cầu | ❌ Model tồn tại, không có data collection |
| Lịch sử tương tác | ✅ Yêu cầu | ❌ Không có bảng, không có collector |
| Dữ liệu khác | ✅ Yêu cầu | ❌ Không có cơ chế nào |

**Mức độ đáp ứng: 25% — Nghiêm trọng**

---

### MODULE 120 — XỬ LÝ DỮ LIỆU

#### Đặc tả yêu cầu

PDF: *"Module xử lý dữ liệu (120) thực hiện chuẩn hóa và trích xuất các đặc trưng dữ liệu Fi = {f1, f2, …, fn} từ dữ liệu đầu vào, trong đó mỗi đặc trưng Fi biểu thị một chỉ số hành vi hoặc tương tác của người dùng."*

Yêu cầu:
- Chuẩn hóa dữ liệu đa nguồn
- Làm sạch dữ liệu
- Lưu trữ có cấu trúc
- **Fi phải biểu thị "chỉ số hành vi hoặc tương tác"** — không chỉ là điểm câu hỏi

#### Thực tế cài đặt

**`FeatureExtractor.php`** — Cài đặt tốt cho phần survey:
- Boolean scoring: `score_if_true / score_if_false`
- Choice scoring: tổng điểm các option chọn, clamp min/max
- Numeric range scoring: so sánh value với ranges
- Signal flags extraction: đúng theo tinh thần đặc tả
- Bucket resolution (domain/section): đúng

**Vấn đề cốt lõi:** Fi trong code là **điểm câu hỏi khảo sát**, không phải "chỉ số hành vi hoặc tương tác". PDF yêu cầu Fi được trích xuất từ **dữ liệu hành vi thực tế** (thời gian làm bài, số lần đổi đáp án, pattern tương tác...).

**`ScoreNormalizer.php`** — Tồn tại, xử lý chuẩn hóa điểm về thang đã định.

#### Kết luận Module 120

| Yêu cầu | Thực tế |
|---|---|
| Chuẩn hóa dữ liệu survey | ✅ Tốt |
| Trích xuất Fi từ behavior data | ❌ Không có |
| Làm sạch / lọc dữ liệu nhiễu | ❌ Không có |
| Fi = chỉ số hành vi / tương tác | ❌ Fi chỉ là điểm câu hỏi survey |

**Mức độ đáp ứng: 40% — Đáp ứng một phần**

---

### MODULE 130 — CHẤM ĐIỂM

#### Đặc tả yêu cầu

PDF: *"Module chấm điểm (130) tính toán điểm năng lực theo công thức Score = Σ(Wi × Fi), trong đó Wi là trọng số tương ứng với từng đặc trưng Fi. Các trọng số Wi được cập nhật bởi module phản hồi (170) dựa trên dữ liệu lịch sử."*

Yêu cầu:
1. Tính Score = Σ(Wi × Fi)
2. Wi phải là **giá trị động**, được cập nhật theo lịch sử
3. Bước 4 của quy trình: **chuẩn hóa điểm về thang xác định**

#### Thực tế cài đặt

**Bước 3 — Tính điểm:**
`WeightedDomainAggregation.php` tính `Score = Σ(Wi × domainScore_i)` — **đúng về mặt toán học**.

**`WeightRepository.php` (loadActive):** Logic tải Wi:

```php
// Thử tải dynamic weights từ feature_weights table
$featureWeights = FeatureWeight::forAssessment($assessmentCode)
    ->domainLevel()->get();

if ($featureWeights->isNotEmpty()) {
    // Dùng Wi động từ DB
    return ['weights' => $weights, 'version' => $featureWeights->max('version')];
}

// Fallback: Wi tĩnh từ assessment_domains.weight (luôn xảy ra trong thực tế)
foreach ($config->domains as $domain) {
    $weights[$domain->domain_code] = (float) $domain->getAttribute('weight');
}
return ['weights' => $weights, 'version' => 1];
```

**Vấn đề:** `feature_weights` table **luôn rỗng** vì không có cơ chế nào ghi dữ liệu vào đó. Không có service, không có job, không có command nào cập nhật bảng này. Do đó, hệ thống **luôn chạy fallback** — Wi là giá trị tĩnh do admin cấu hình thủ công.

**Bước 4 — Chuẩn hóa điểm:**
`ScoreNormalizer.php` — có tồn tại và được gọi trong pipeline, **đáp ứng bước 4**.

**Ba chiến lược aggregation:**

| Chiến lược | File | Công thức | Áp dụng Wi |
|---|---|---|---|
| `weighted_domain` | `WeightedDomainAggregation.php` | Σ(Wi × Fi) | ✅ |
| `flat_sum` | `FlatSumAggregation.php` | Σ(Fi) | ❌ |
| `sectioned` | `SectionedAggregation.php` | Theo section | ❌ |

Khi admin chọn `flat_sum` hoặc `sectioned`, công thức Σ(Wi × Fi) không áp dụng.

#### Kết luận Module 130

| Yêu cầu | Thực tế |
|---|---|
| Tính Score = Σ(Wi × Fi) | ✅ Khi dùng weighted_domain |
| Wi động (cập nhật từ lịch sử) | ❌ Wi tĩnh — feature_weights luôn rỗng |
| Chuẩn hóa điểm | ✅ ScoreNormalizer |
| Phân loại aggregation linh hoạt | ✅ 3 chiến lược |

**Mức độ đáp ứng: 60% — Đáp ứng cơ bản nhưng thiếu tính năng quan trọng nhất (Wi động)**

---

### MODULE 140 — PHÂN LOẠI

#### Đặc tả yêu cầu

PDF: *"Module phân loại (140) xác định mức độ năng lực của người dùng dựa trên giá trị Score và các ngưỡng phân loại T1, T2, trong đó các ngưỡng này có thể được điều chỉnh động dựa trên dữ liệu phản hồi."*

Logic IF-THEN theo đặc tả:

```
IF Score ≥ T1             → Nhóm năng lực cao
IF T2 ≤ Score < T1        → Nhóm năng lực trung bình
IF Score < T2             → Nhóm năng lực thấp

T1, T2 được điều chỉnh ĐỘNG bởi module phản hồi
```

#### Thực tế cài đặt

**`ScoreBandClassification.php`:** Phân loại bằng cách so score với `score_bands`:

```php
foreach ($config->scoreBands as $band) {
    if ($band->contains($score)) {
        return ClassificationResult::scoreBand($band->band_code, $band->label);
    }
}
```

Về mặt logic IF-THEN, đây là **tương đương đúng** — `score_bands` chính là T1, T2 được mã hóa thành `min_score/max_score`.

**Tuy nhiên:**
- T1 và T2 là giá trị **hoàn toàn tĩnh**, do admin nhập thủ công qua UI
- Không có code điều chỉnh `score_bands.min_score` hoặc `max_score` theo thời gian
- PDF yêu cầu: *"các ngưỡng T1, T2 xác định trước hoặc được điều chỉnh động bởi module phản hồi"* — phần "điều chỉnh động" không tồn tại

**Ba chiến lược phân loại trong codebase:**

| Chiến lược | File | Có trong đặc tả |
|---|---|---|
| `score_band` | `ScoreBandClassification.php` | ✅ Đúng với spec chính |
| `pass_fail` | `PassFailClassification.php` | ❌ Không có trong spec (nhưng hợp lý) |
| `persona_match` | `PersonaMatchClassification.php` | ❌ Không có trong spec (nhưng hợp lý) |

#### Kết luận Module 140

| Yêu cầu | Thực tế |
|---|---|
| Phân loại theo T1, T2 | ✅ Đúng bằng score_bands |
| T1, T2 tĩnh ban đầu (do admin cấu hình) | ✅ |
| T1, T2 điều chỉnh động theo feedback | ❌ Không có cơ chế |
| Tích hợp với module phản hồi | ❌ Không có |

**Mức độ đáp ứng: 65% — Đáp ứng phần tĩnh, thiếu tính năng động**

---

### MODULE 150 — GỢI Ý (OUTPUT)

#### Đặc tả yêu cầu

PDF: *"Module gợi ý (150) tạo ra các tập hợp kết quả xử lý dữ liệu đầu ra tương ứng với từng mức độ năng lực, trong đó các tập hợp kết quả đầu ra được xác định dựa trên các đặc trưng dữ liệu đã được xử lý."*

Sơ đồ hiển thị 4 đầu ra:
1. Hồ sơ năng lực
2. Báo cáo đánh giá năng lực
3. Lộ trình đào tạo / phát triển
4. Vị trí công việc phù hợp

#### Thực tế cài đặt

**Pipeline output (đã cài đặt):**

| Component | File | Trạng thái |
|---|---|---|
| Pain points | `PainPointDetector.php` | ✅ Hoàn chỉnh |
| Recommendations | `RecommendationEngine.php` | ✅ Hoàn chỉnh |
| Roadmap phases + milestones | `RoadmapLoader.php` | ✅ Hoàn chỉnh |
| Lưu kết quả | `ResultPersister.php` | ✅ Hoàn chỉnh (1 transaction) |

**Đầu ra cho Admin:**
- `SurveyResultController::show()` → view `survey::results.show` — chi tiết một response
- `SurveyResultController::summary()` → view `survey::results.summary` — tổng hợp toàn survey

**Đầu ra cho người dùng (respondent):**
- `SurveyApiController::result()` — trả JSON qua Bearer token + `?ref=email`
- Dữ liệu trả về: `overall_score`, `maturity_level`, `domain_scores`, `pain_points`, `recommendations`, `roadmap`

**Vấn đề còn tồn tại:**
- "Hồ sơ năng lực" và "Báo cáo đánh giá năng lực" chưa có trang HTML đẹp cho respondent — chỉ trả JSON thô
- "Vị trí công việc phù hợp" — không có bảng, không có logic, không có UI
- Không có email / notification khi kết quả sẵn sàng
- Gợi ý trong đặc tả: *"được xác định dựa trên các đặc trưng dữ liệu đã được xử lý"* — hiện tại chỉ dựa trên `domain_scores`, không dùng behavioral features

#### Kết luận Module 150

| Đầu ra theo đặc tả | Thực tế |
|---|---|
| Hồ sơ năng lực (chi tiết) | ⚠️ Có dữ liệu, chưa có trang view đẹp cho respondent |
| Báo cáo đánh giá | ⚠️ Có data, chỉ có JSON API |
| Lộ trình đào tạo | ✅ Roadmap phases + milestones |
| Vị trí công việc phù hợp | ❌ Không tồn tại |
| Pain points | ✅ Đầy đủ |
| Recommendations | ✅ Đầy đủ |

**Mức độ đáp ứng: 55%**

---

### MODULE 160 — CƠ SỞ DỮ LIỆU

#### Đặc tả yêu cầu

PDF: *"(160) Cơ sở dữ liệu, được cấu hình để lưu trữ dữ liệu."*

#### Thực tế cài đặt

**`ResultPersister.php`** — Lưu toàn bộ kết quả trong 1 DB transaction:

| Bảng | Nội dung |
|---|---|
| `survey_results` | Overall score, maturity level, assessment_code, weight_version |
| `result_domain_scores` | Điểm từng domain (raw + normalized) |
| `result_signal_flags` | Các signal flags |
| `result_pain_points` | Pain points phát hiện được |
| `result_recommendations` | Recommendations theo priority |
| `result_question_scores` | Điểm từng câu hỏi (audit trail) |
| `result_classifications` | Loại phân loại, band_code, passed |
| `result_roadmap_phases` | Các phase roadmap gắn với result |

**Config storage (đã có schema):**

| Nhóm | Bảng |
|---|---|
| Cấu hình chính | `assessments`, `assessment_domains`, `score_bands` |
| Score rules | `score_rules`, `score_rule_options`, `score_rule_numeric_ranges` |
| Output rules | `pain_point_rules`, `recommendation_rules` |
| Roadmap | `roadmap_phases`, `roadmap_milestones` |
| Dynamic weights | `feature_weights`, `feature_weight_history` |
| Tuning (Module 170) | `tuning_cycles`, `tuning_schedule_config`, `scoring_feedback` |

#### Kết luận Module 160

**Mức độ đáp ứng: 95% — Xuất sắc.**

Schema đầy đủ, thiết kế đúng hướng, đã có sẵn bảng cho Module 170 dù chưa implement logic. Đây là nền tảng vững chắc nhất trong toàn hệ thống.

---

### MODULE 170 — HỌC DỮ LIỆU (FEEDBACK LOOP)

#### Đặc tả yêu cầu

PDF: *"Module học dữ liệu (170) thu thập dữ liệu tương tác tiếp theo của người dùng và cập nhật các trọng số Wi cũng như các tham số đánh giá nhằm giảm sai lệch giữa kết quả dự đoán và kết quả thực tế. Quy trình trên được lặp lại theo chu kỳ xử lý dữ liệu, qua đó cải thiện độ chính xác của hệ thống theo thời gian."*

PDF còn nhấn mạnh: *"Trong một phương án nâng cao, việc cập nhật trọng số Wi được thực hiện thông qua mô hình học máy sử dụng dữ liệu lịch sử để tối ưu hóa độ chính xác của hệ thống."*

Module 170 phải thực hiện:
1. **Thu thập phản hồi**: so sánh predicted band vs actual band
2. **Tính sai lệch**: Δ = actual - predicted
3. **Cập nhật Wi**: điều chỉnh để giảm Δ (gradient descent đơn giản hoặc ML)
4. **Lưu lịch sử**: track từng lần điều chỉnh Wi
5. **Điều chỉnh T1, T2**: cập nhật ngưỡng phân loại theo phân bố thực tế
6. **Chạy theo chu kỳ**: không phải realtime, nhưng phải có scheduler

#### Thực tế cài đặt — Kiểm tra toàn bộ codebase

**Models tồn tại (infrastructure DB đã sẵn sàng):**

`ScoringFeedback.php` — Thu thập feedback:
```php
protected $fillable = [
    'result_id', 'assessment_code',
    'predicted_band', 'actual_band',      // So sánh predicted vs actual ✓
    'predicted_score', 'actual_score',
    'feedback_source', 'is_processed',    // Track trạng thái xử lý ✓
];
// scopeUnprocessed() — query feedback chưa xử lý ✓
```

`TuningCycle.php` — Ghi lại từng chu kỳ tuning:
```php
protected $fillable = [
    'assessment_code', 'cycle_number', 'method',
    'feedback_count',
    'error_before', 'error_after',        // Track cải thiện ✓
    'status', 'started_at', 'completed_at',
];
// wasImproved() — so sánh error_before vs error_after ✓
```

`TuningScheduleConfig.php` — Cấu hình lịch chạy tuning

`FeatureWeight.php` — Lưu Wi với version tracking:
```php
protected $fillable = [
    'weight', 'default_weight',
    'weight_min', 'weight_max',           // Constraints ✓
    'version',                            // Version tracking ✓
    'updated_by',
];
```

`FeatureWeightHistory.php` — Lịch sử thay đổi Wi qua từng version

**Không tồn tại (zero implementation):**

| Cần có | Thực tế |
|---|---|
| `TuningService.php` (tính Δ, cập nhật Wi) | ❌ Không tồn tại |
| `FeedbackCollectionJob.php` | ❌ Không tồn tại |
| `WeightUpdateJob.php` | ❌ Không tồn tại |
| UI để admin xác nhận `actual_band` | ❌ Không tồn tại |
| Scheduler trong console routes | ❌ Không tồn tại |
| Thuật toán cập nhật (gradient, average) | ❌ Không tồn tại |
| Logic điều chỉnh T1, T2 | ❌ Không tồn tại |

**Luồng dữ liệu phản hồi — Đặc tả vs Thực tế:**

```
ĐẶC TẢ (vòng lặp đầy đủ):
User nộp bài → Score tính → Kết quả lưu → [sau X ngày]
Admin/hệ thống xác nhận actual_band → ScoringFeedback.is_processed=false
→ TuningJob chạy theo lịch → Tính Δ → Cập nhật Wi → FeatureWeightHistory
→ Score tính lại với Wi mới → Độ chính xác cải thiện theo thời gian

THỰC TẾ (vòng hở):
User nộp bài → Score tính → Kết quả lưu
→ [DỪNG LẠI — không có bước nào tiếp theo]
```

#### Kết luận Module 170

| Yêu cầu | DB Schema | Logic | UI |
|---|---|---|---|
| Thu thập feedback predicted vs actual | ✅ | ❌ | ❌ |
| Tính sai lệch Δ | ✅ (schema) | ❌ | ❌ |
| Cập nhật Wi | ✅ (schema) | ❌ | ❌ |
| Lưu lịch sử Wi qua từng version | ✅ | ❌ | ❌ |
| Scheduler / chu kỳ tuning | ✅ (config) | ❌ | ❌ |
| Điều chỉnh T1, T2 động | ❌ | ❌ | ❌ |
| Cải thiện độ chính xác theo thời gian | — | ❌ | ❌ |

**Mức độ đáp ứng: 10% — Chỉ có DB schema, không có bất kỳ implementation nào.**

---

## III. BẢNG TỔNG HỢP GAP ANALYSIS

| Module | Tên | % Đáp ứng | Mức độ gap | Ghi chú |
|--------|-----|:---------:|-----------|---------|
| **110** | Thu thập dữ liệu | 25% | 🔴 Nghiêm trọng | Chỉ có survey, thiếu behavior/interaction |
| **120** | Xử lý dữ liệu | 40% | 🟠 Đáng kể | Fi chỉ từ survey, không phải multi-source |
| **130** | Chấm điểm | 60% | 🟡 Trung bình | Công thức đúng, Wi tĩnh thay vì động |
| **140** | Phân loại | 65% | 🟡 Trung bình | T1/T2 tĩnh, không điều chỉnh theo feedback |
| **150** | Gợi ý / Output | 55% | 🟠 Đáng kể | Thiếu view cho respondent, thiếu job position |
| **160** | Cơ sở dữ liệu | 95% | 🟢 Tốt | Schema đầy đủ, thiết kế tốt |
| **170** | Học dữ liệu | 10% | 🔴 Nghiêm trọng | Chỉ có DB schema, zero logic |

**Điểm trung bình đáp ứng đặc tả: ~50%**

---

## IV. PHÂN TÍCH NGUYÊN NHÂN GỐC RỄ

### 4.1 Gap cấu trúc: Đặc tả là hệ thống adaptive, codebase là hệ thống rule-based tĩnh

Đặc tả PDF mô tả một hệ thống **adaptive** (tự cải thiện theo thời gian). Codebase hiện tại là một hệ thống **deterministic rule-based** (cố định, mỗi input luôn cho output giống nhau). Đây là gap về mặt **triết lý thiết kế**, không chỉ là thiếu tính năng.

| Khía cạnh | Đặc tả | Codebase |
|---|---|---|
| Bản chất | Adaptive / learning | Static / rule-based |
| Wi | Thay đổi theo thời gian | Cố định do admin nhập |
| T1, T2 | Điều chỉnh theo phân bố thực tế | Cố định do admin nhập |
| Dữ liệu đầu vào | Đa nguồn (behavior + interaction + survey) | Chỉ survey |
| Chu kỳ cải thiện | Lặp lại tự động | Không có |

### 4.2 Infrastructure đã có sẵn nhưng thiếu "engine"

Đội dev đã thiết kế **đúng hướng** — DB schema cho Module 170 đầy đủ, `WeightRepository` đã có logic đọc `feature_weights`. Tuy nhiên thiếu phần "động cơ" khép vòng lặp:

```
ĐÃ CÓ:    [DB schema] ←→ [WeightRepository đọc] ←→ [Pipeline tính điểm]

THIẾU:    [Feedback collector] → [Δ calculator] → [Weight updater] → [DB schema]
                                                        ↑
                                              vòng lặp không được khép kín
```

### 4.3 Module 110 là nguồn gốc của nhiều gap khác

Nếu Module 110 không thu thập behavior/interaction data, thì toàn bộ chuỗi cascade bị ảnh hưởng:

```
Module 110 thiếu behavior data
    → Module 120 không có gì để normalize ngoài survey answers
    → Fi chỉ là điểm câu hỏi, không phải behavioral features
    → Module 170 không có "actual outcome" để so sánh
    → Vòng lặp học không thể khép kín
```

---

## V. ĐÁNH GIÁ NHỮNG GÌ ĐÃ CÀI ĐẶT ĐÚNG

Mặc dù có nhiều gap, những gì đã làm **đúng về mặt kỹ thuật**:

| # | Thành phần | Nhận xét |
|---|---|---|
| 1 | **Pipeline orchestration** (`ScoringEngineService.php`) | 10 bước rõ ràng, đúng thứ tự, idempotent |
| 2 | **Queue-based async scoring** (`CalculateSurveyScoreJob`) | 3 retries, 30s backoff — tốt cho production |
| 3 | **Transaction integrity** (`ResultPersister.php`) | Toàn bộ persist trong 1 DB transaction, cascade delete khi recalculate |
| 4 | **DB schema forward-thinking** | Đã có bảng cho Module 170 dù chưa implement |
| 5 | **Aggregation strategy pattern** | 3 chiến lược tính điểm linh hoạt |
| 6 | **Signal flags** | Cơ chế phong phú, đúng với tinh thần đặc tả |
| 7 | **Per-question scoring** | Lưu điểm từng câu hỏi — cho phép audit trail |
| 8 | **Admin dry-run** | Test scoring config trước khi deploy |
| 9 | **Respondent API** (`result()` endpoint) | Người dùng tự tra kết quả qua Bearer token |
| 10 | **Weight version tracking** | `weight_version` field trong `survey_results` — chuẩn bị cho Wi động |

---

## VI. KHUYẾN NGHỊ THỰC TIỄN THEO MỨC ƯU TIÊN

### Ưu tiên 1 — Hoàn thiện tính năng hiện có (không cần code lớn)

#### A. Module 150 — Trang kết quả HTML cho respondent

Hiện tại respondent nhận JSON thô qua API. Cần thêm:
- View `survey::results.respondent` trả HTML (không chỉ JSON)
- Hiển thị: overall score, band/level, domain radar chart, pain points, recommendations, roadmap
- Email / SMS notification khi kết quả sẵn sàng (queue-based)

**Ước tính:** 1–2 ngày dev

#### B. Module 110 — Thu thập behavior data cơ bản

Ghi vào `submission_behavior_log` những sự kiện đơn giản từ frontend:
- Thời gian bắt đầu / kết thúc mỗi câu
- Số lần thay đổi đáp án
- Thứ tự hoàn thành câu hỏi

Không cần ML phức tạp — chỉ cần ghi raw events để tích lũy dữ liệu lịch sử.

**Ước tính:** 0.5 ngày backend + 1 ngày frontend JS

---

### Ưu tiên 2 — Module 170 bản cơ bản (Simple Average Update)

Không cần ML. Implement phiên bản đơn giản với 3 bước:

**Bước 1 — Thu thập feedback:**
- UI cho admin xác nhận `actual_band` sau khi có kết quả thực tế của người dùng
- Ghi vào `scoring_feedback`: `predicted_band`, `actual_band`, `is_processed=false`

**Bước 2 — Job tuning chạy định kỳ (hàng tuần):**
```
Lấy scoring_feedback chưa xử lý (is_processed = false)
Với mỗi feedback: predicted ≠ actual → domain nào contribute nhiều → tăng/giảm Wi
Cập nhật feature_weights với version mới
Ghi FeatureWeightHistory
Đánh dấu is_processed = true
Tạo TuningCycle record (error_before, error_after)
```

**Bước 3 — WeightRepository đã sẵn sàng đọc Wi mới** (không cần thay đổi pipeline)

**Ước tính:** ~300–400 dòng PHP + 1 Artisan command + 1 scheduled job — khoảng 2–3 ngày dev.

---

### Ưu tiên 3 — Dynamic T1, T2 (Module 140)

Sau khi Module 170 cơ bản hoạt động, thêm logic điều chỉnh `score_bands.min_score/max_score` dựa trên phân bố thực tế của `survey_results.overall_score`.

Ví dụ: nếu 80% users rơi vào band "cao", có thể T1 đang quá thấp — tự động nâng T1 lên percentile 70.

**Ước tính:** 1 ngày dev + cần ít nhất 100 responses để phân bố có ý nghĩa thống kê.

---

### Ưu tiên 4 — Multi-source data (Module 110 đầy đủ)

Tích hợp dữ liệu từ các hệ thống ngoài (LMS, CRM) vào pipeline scoring. Đây là tính năng phức tạp nhất, nên để sau khi các module khác ổn định.

---

## VII. KẾT LUẬN

**Hệ thống hiện tại là một scoring engine rule-based hoạt động tốt, nhưng chưa phải là hệ thống đánh giá năng lực thích nghi (adaptive) như đặc tả PDF mô tả.**

Khoảng cách lớn nhất không phải là bug hay lỗi kỹ thuật — mà là **vòng lặp phản hồi (feedback loop) chưa được implement**. Không có vòng lặp này, hệ thống không thể tự học, Wi sẽ mãi là giá trị tĩnh do admin đặt tay, và độ chính xác không cải thiện theo thời gian — trái ngược hoàn toàn với cam kết của đặc tả.

**Điểm tích cực:** DB schema đã được thiết kế đúng hướng, pipeline code có cấu trúc tốt, và `WeightRepository` đã có logic đọc Wi động — nền tảng để implement Module 170 đã sẵn sàng. Chi phí để khép kín vòng lặp ước tính 2–3 ngày dev (phiên bản cơ bản), thấp hơn rất nhiều so với việc xây lại từ đầu.

---

## PHỤ LỤC — FILE MAP

### Files đã cài đặt (trong Modules/Survey)

| File | Module liên quan | Trạng thái |
|---|---|---|
| `Scoring/ScoringEngineService.php` | 130, 140, 150, 160 | ✅ Hoàn chỉnh |
| `Scoring/AnswerReader.php` | 110 | ⚠️ Chỉ đọc survey_answers |
| `Scoring/FeatureExtractor.php` | 120 | ⚠️ Chỉ xử lý survey data |
| `Scoring/WeightRepository.php` | 130 | ⚠️ Đọc Wi động nhưng bảng luôn rỗng |
| `Scoring/ScoreNormalizer.php` | 130 | ✅ Hoàn chỉnh |
| `Scoring/AggregationFactory.php` | 130 | ✅ Hoàn chỉnh |
| `Scoring/Aggregation/WeightedDomainAggregation.php` | 130 | ✅ Hoàn chỉnh |
| `Scoring/ClassificationFactory.php` | 140 | ✅ Hoàn chỉnh |
| `Scoring/Classification/ScoreBandClassification.php` | 140 | ⚠️ T1/T2 tĩnh |
| `Scoring/PainPointDetector.php` | 150 | ✅ Hoàn chỉnh |
| `Scoring/RecommendationEngine.php` | 150 | ✅ Hoàn chỉnh |
| `Scoring/RoadmapLoader.php` | 150 | ✅ Hoàn chỉnh |
| `Scoring/ResultPersister.php` | 160 | ✅ Hoàn chỉnh |
| `Jobs/CalculateSurveyScoreJob.php` | 130 | ✅ Queue + retry |
| `Models/ScoringFeedback.php` | 170 | ⚠️ Schema only |
| `Models/TuningCycle.php` | 170 | ⚠️ Schema only |
| `Models/FeatureWeight.php` | 170 | ⚠️ Schema only, không được ghi |
| `Models/FeatureWeightHistory.php` | 170 | ⚠️ Schema only |
| `Models/SubmissionBehaviorLog.php` | 110 | ⚠️ Schema only, không được ghi |

### Files cần tạo mới (theo thứ tự ưu tiên)

| File cần tạo | Module | Mục đích |
|---|---|---|
| `resources/views/results/respondent.blade.php` | 150 | Trang kết quả HTML cho người dùng |
| `Jobs/CollectBehaviorDataJob.php` | 110 | Thu thập behavior events |
| `Services/FeedbackCollectionService.php` | 170 | Lưu predicted vs actual |
| `Services/WeightTuningService.php` | 170 | Tính Δ và cập nhật Wi |
| `Jobs/RunTuningCycleJob.php` | 170 | Job định kỳ chạy tuning |
| `Http/Controllers/FeedbackController.php` | 170 | UI admin xác nhận actual_band |
| `Console/Commands/RunTuning.php` | 170 | Artisan command thủ công |
