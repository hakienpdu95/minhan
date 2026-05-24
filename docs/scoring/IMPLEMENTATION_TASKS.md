# IMPLEMENTATION TASKS — Survey Scoring Module
## Danh sách công việc hoàn thiện theo Tài liệu Kỹ thuật

**Tài liệu tham chiếu:** `docs/scoring/phan-tich-ky-thuat-chuyen-sau.md`  
**Spec gốc:** `docs/scoring/HỆ THỐNG VÀ PHƯƠNG PHÁP XỬ LÝ DỮ LIỆU ĐÁNH GIÁ NĂNG LỰC NGƯỜI DÙNG-1.pdf`  
**Sơ đồ:** `docs/scoring/hinhquytrinh.png`  
**Cập nhật:** 2026-05-24  
**Ghi chú:** T2 (Email notification) đã được loại bỏ theo yêu cầu.

---

## NGUYÊN TẮC THỰC THI

- Làm xong **từng task**, hỏi xác nhận trước khi sang task tiếp theo
- Mỗi task có **Dependencies** — không làm task con khi task cha chưa xong
- **Không xóa code hiện tại** khi chưa chắc chắn — refactor từng bước
- Sau mỗi task chạy `php artisan test` để xác nhận không có regression
- Mọi migration mới phải **không phá vỡ** dữ liệu đang có

---

## TỔNG QUAN TASK

| ID | Module | Tên Task | Độ ưu tiên | Dependencies |
|----|--------|----------|------------|--------------|
| T0 | BUG FIX | Fix ScoringConfig::hasScoring() logic | 🔴 Critical | — |
| T1 | 150A | Trang kết quả HTML public cho respondent | 🔴 High | — |
| T2 | 150C | Vị trí công việc phù hợp (Job Position) | 🟠 High | — |
| T3 | 110 | Thu thập behavior data từ frontend | 🟠 High | — |
| T4 | 120 | Tích hợp behavior Fi vào FeatureExtractor | 🟡 Medium | T3 |
| T5 | 170A | Auto-seed ScoringFeedback trong ResultPersister | 🔴 Critical | — |
| T6 | 170B | UI admin xác nhận actual_band + FeedbackController | 🟠 High | T5 |
| T7 | 170C | WeightTuningService — tính Δ, cập nhật Wi | 🟠 High | T5, T6 |
| T8 | 170D | RunTuningCycleJob + Artisan command + Scheduler | 🟠 High | T7 |
| T9 | 140 | Dynamic T1/T2 — tự điều chỉnh ngưỡng phân loại | 🟡 Medium | T7, T8 |
| T10 | 120 | Data cleaning trong FeatureExtractor | 🟡 Medium | — |

---

## CHI TIẾT TỪNG TASK

---

## T0 — BUG FIX: ScoringConfig::hasScoring() logic sai

### Trạng thái hiện tại

File: `Modules/Survey/app/Scoring/ScoringConfig.php` dòng 41–44

```php
public function hasScoring(): bool
{
    return $this->assessment === null || $this->assessment->has_scoring;
}
```

**Bug:** Trả về `true` khi `$this->assessment === null` — tức là khi không tìm thấy assessment nào, hàm vẫn báo "có scoring". Điều này khiến pipeline tiếp tục chạy rồi throw exception ở bước load config (InvalidScoringConfigException), thay vì dừng sớm với null result.

### Việc cần sửa

**File:** `Modules/Survey/app/Scoring/ScoringConfig.php`

Đổi logic:
```php
// SAI (hiện tại):
return $this->assessment === null || $this->assessment->has_scoring;

// ĐÚNG (sửa thành):
return $this->assessment !== null && $this->assessment->has_scoring;
```

### Ảnh hưởng sau sửa

- `ScoringEngineService::calculate()` dòng 54: guard `if (!$config->hasScoring())` sẽ hoạt động đúng
- `ScoringAdminController::dryRun()` — workaround explicit check `Assessment::where(...)` ở đó có thể giữ nguyên hoặc đơn giản hóa sau khi bug được fix

### Kiểm tra

```bash
php artisan test --filter=ScoringTest
```

---

## T1 — Module 150A: Trang kết quả HTML public cho respondent

### Trạng thái hiện tại

- `GET /v1/surveys/{slug}/result` → trả **JSON** (chỉ cho API consumer)
- Respondent không có trang HTML để xem kết quả
- Không có public route nào trả HTML

### Việc cần làm

#### 1. Thêm route web public

**File:** `Modules/Survey/routes/web.php` — thêm ngoài middleware `auth`:

```php
// ── Public result page (no auth required) ─────────────────────────────────
Route::get('/surveys/{slug}/result', [SurveyResultController::class, 'publicResult'])
     ->name('surveys.result.public')
     ->middleware(ValidateSurveyToken::class);
```

#### 2. Thêm method vào SurveyResultController

**File:** `Modules/Survey/app/Http/Controllers/SurveyResultController.php`

Thêm method `publicResult()`:
- Nhận `$slug` + `?ref=` (respondent_ref từ query string)
- Load survey (must be active + hasScoring)
- Load SurveyResponse gần nhất theo respondent_ref
- Load SurveyResult với các relations: domainScores, painPoints, recommendations, roadmapPhases, classification
- Load labels: recommendation labels, domain labels, job positions (sau khi T2 xong)
- Trả view `survey::results.public` (xem bên dưới)
- Nếu result chưa sẵn sàng → view "đang xử lý" với polling JS

#### 3. Tạo view public

**File mới:** `Modules/Survey/resources/views/results/public.blade.php`

Layout: standalone (không dùng `layouts.backend`), responsive, có thể dùng layout mới `layouts.public` hoặc layout minimal.

Nội dung view (theo thứ tự từ trên xuống):
1. **Header**: tên survey, tên respondent (ẩn bớt nếu cần), ngày nộp bài
2. **Hero card — Overall Score**: điểm tổng to (font lớn), band label (màu sắc theo level), ngày tính điểm
3. **Domain Scores**: progress bars cho từng domain, label + normalized score
4. **Hồ sơ năng lực**: mô tả band/maturity level (lấy từ `score_bands.description` nếu có)
5. **Pain Points**: danh sách các điểm yếu phát hiện được (từ `pain_point_rules.label`)
6. **Đề xuất cải thiện**: danh sách recommendations có label, description, priority
7. **Lộ trình đào tạo**: roadmap phases + milestones dạng timeline
8. **Vị trí công việc phù hợp**: (render sau khi T2 xong)
9. **Footer**: "Powered by..." + link nộp lại khảo sát

#### 4. Cập nhật API endpoint (tùy chọn)

`SurveyApiController::result()` — thêm header `Accept: text/html` → redirect sang trang HTML.

### Files cần tạo mới

| File | Mô tả |
|------|-------|
| `resources/views/results/public.blade.php` | View HTML công khai |

### Files cần chỉnh sửa

| File | Thay đổi |
|------|---------|
| `routes/web.php` | Thêm 1 route public |
| `app/Http/Controllers/SurveyResultController.php` | Thêm method `publicResult()` |

### Dependencies

Không có (độc lập).

---

## T2 — Module 150C: Vị trí công việc phù hợp (Job Position Matching)

### Trạng thái hiện tại

- Không có bảng, không có model, không có logic nào
- Là 1 trong 4 đầu ra bắt buộc theo sơ đồ spec

### Việc cần làm

#### 1. Migration — Bảng job_positions

**File mới:** migration tạo bảng `job_positions`

```
job_positions:
  - id
  - assessment_code (FK liên kết với assessments.assessment_code)
  - position_code (unique per assessment_code)
  - title
  - description (nullable text)
  - min_overall_score (float, nullable — điểm tổng tối thiểu)
  - requirements (JSON — array domain_code: min_score, ví dụ {"leadership": 60, "technical": 70})
  - sort_order (integer default 0)
  - is_active (boolean default true)
  - timestamps
```

#### 2. Migration — Bảng result_job_positions

**File mới:** migration tạo bảng `result_job_positions`

```
result_job_positions:
  - id
  - result_id (FK → survey_results.id, cascade delete)
  - position_code (string)
  - match_score (float — % khớp so với yêu cầu)
  - timestamps
```

#### 3. Model JobPosition

**File mới:** `Modules/Survey/app/Models/JobPosition.php`

- Fillable: assessment_code, position_code, title, description, min_overall_score, requirements, sort_order, is_active
- Cast: requirements → array, min_overall_score → float, is_active → boolean
- Scope: `forAssessment()`, `active()`, `ordered()`

#### 4. Model ResultJobPosition

**File mới:** `Modules/Survey/app/Models/ResultJobPosition.php`

- Fillable: result_id, position_code, match_score
- Relationship: `result()` → BelongsTo SurveyResult

#### 5. Service JobPositionMatcher

**File mới:** `Modules/Survey/app/Scoring/JobPositionMatcher.php`

Thuật toán match:
```
Với mỗi JobPosition (active, cùng assessment_code):
  1. Kiểm tra min_overall_score: nếu overall_score < min_overall_score → skip
  2. Với mỗi requirement domain trong requirements JSON:
     - So sánh domain_scores[domain_code].normalizedScore với yêu cầu
  3. match_score = (số domain đạt yêu cầu / tổng domain yêu cầu) * 100
  4. Chỉ giữ position có match_score >= 50%
  5. Sắp xếp theo match_score descending
```

#### 6. Tích hợp vào ScoringEngineService

**File:** `Modules/Survey/app/Scoring/ScoringEngineService.php`

Thêm `JobPositionMatcher` vào constructor và gọi sau bước 8 (recommendations):
```php
// Bước 8.5 — Job positions (Module 150)
$jobPositions = $this->jobPositionMatcher->match($config, $aggregated->domainScores, $aggregated->overallScore);
```

#### 7. Cập nhật ScoringResult

**File:** `Modules/Survey/app/Scoring/ScoringResult.php`

Thêm property `public readonly array $jobPositions`.

#### 8. Cập nhật ResultPersister

**File:** `Modules/Survey/app/Scoring/ResultPersister.php`

Persist job positions vào `result_job_positions`.

#### 9. Cập nhật SurveyResult model

**File:** `Modules/Survey/app/Models/SurveyResult.php`

Thêm relationship: `jobPositions()` → HasMany ResultJobPosition.

#### 10. Cập nhật views để hiển thị

- `resources/views/results/public.blade.php` (T1) — section "Vị trí công việc phù hợp" (tích hợp sau T2)
- `resources/views/results/show.blade.php` (admin) — thêm job positions panel
- `resources/views/results/summary.blade.php` — thống kê top job positions

#### 11. Cập nhật ScoringAdminController — CRUD job positions

**File:** `Modules/Survey/app/Http/Controllers/ScoringAdminController.php`

Thêm methods để admin quản lý job positions trong Scoring config UI:
- `getJobPositions()` — GET /scoring/job-positions
- `saveJobPositions()` — PUT /scoring/job-positions

#### 12. Cập nhật Scoring Config UI (Tab mới)

**File:** `Modules/Survey/resources/views/scoring/index.blade.php`

Thêm Tab mới "⑧ Vị trí" hiển thị danh sách job positions, cho phép thêm/sửa/xóa.

### Files cần tạo mới

| File | Mô tả |
|------|-------|
| Migration `create_job_positions_table` | Bảng cấu hình vị trí công việc |
| Migration `create_result_job_positions_table` | Bảng kết quả match |
| `app/Models/JobPosition.php` | Model |
| `app/Models/ResultJobPosition.php` | Model |
| `app/Scoring/JobPositionMatcher.php` | Matching logic |

### Files cần chỉnh sửa

| File | Thay đổi |
|------|---------|
| `app/Scoring/ScoringEngineService.php` | Inject + gọi JobPositionMatcher |
| `app/Scoring/ScoringResult.php` | Thêm property jobPositions |
| `app/Scoring/ResultPersister.php` | Persist job positions |
| `app/Models/SurveyResult.php` | Thêm relationship |
| `app/Http/Controllers/ScoringAdminController.php` | CRUD job positions |
| `resources/views/scoring/index.blade.php` | Tab mới |
| `resources/views/results/show.blade.php` | Hiển thị |
| `resources/views/results/summary.blade.php` | Thống kê |
| `routes/web.php` | Thêm 2 routes scoring/job-positions |

### Dependencies

Không có dependency bắt buộc (có thể làm song song với T1).

---

## T3 — Module 110: Thu thập behavior data từ frontend

### Trạng thái hiện tại

- `SubmissionBehaviorLog` model tồn tại (bảng `submission_behavior_log`)
- **Không có code nào ghi dữ liệu vào bảng này**
- `AnswerReader.php` không đọc bảng này
- Bảng `submission_behavior_log` có: `response_id`, `question_code`, `event_type`, `event_value`, `sequence_no`, `occurred_at`

### Việc cần làm

#### 1. Backend API endpoint nhận behavior events

**File:** `Modules/Survey/app/Http/Controllers/Api/SurveyApiController.php`

Thêm method `behavior()`:
- `POST /v1/surveys/{slug}/behavior`
- Middleware: `ValidateSurveyToken` (đã có)
- Body: `{ response_id, events: [{question_code, event_type, event_value, occurred_at}] }`
- Batch insert vào `submission_behavior_log`
- Idempotent: nếu trùng (response_id + question_code + event_type + occurred_at) → skip

**Event types cần hỗ trợ:**
```
question_focus       — người dùng focus vào câu hỏi
question_blur        — rời khỏi câu hỏi  
answer_changed       — thay đổi câu trả lời
answer_cleared       — xóa câu trả lời
section_entered      — vào section mới
time_spent           — tổng thời gian ở câu hỏi (giây, gửi khi blur)
```

#### 2. Thêm route

**File:** `Modules/Survey/routes/api.php`

```php
Route::post('surveys/{slug}/behavior', [SurveyApiController::class, 'behavior'])->name('behavior');
```

Đặt trong middleware block `ValidateSurveyToken` hiện có.

#### 3. Frontend JS tracking

Thêm JS snippet vào trang khảo sát (trong `SurveyApiController::schema()` response hoặc frontend survey form).

Script cần:
- Track `focus/blur` events trên mỗi câu hỏi
- Track `change` events khi user đổi answer
- Tính `time_spent` = blur_time - focus_time
- Buffer events vào array, flush định kỳ (mỗi 30s hoặc khi submit)
- POST batch lên `/v1/surveys/{slug}/behavior`
- Gắn `response_id` sau khi submit thành công

#### 4. Xử lý response_id

Behavior events gửi **trước khi submit** (khi đang làm bài) không có `response_id` → cần cơ chế:

**Option A (đơn giản):** Chỉ gửi behavior events **sau** khi submit thành công (dùng `response_id` trả về từ submit API). Flush toàn bộ events đã buffer.

**Option B (đầy đủ):** Dùng `session_token` tạm thời trước submit, sau submit link về `response_id`.

→ **Dùng Option A** cho đơn giản.

### Files cần tạo mới

Không có file model mới (model đã tồn tại).

### Files cần chỉnh sửa

| File | Thay đổi |
|------|---------|
| `app/Http/Controllers/Api/SurveyApiController.php` | Thêm method `behavior()` |
| `routes/api.php` | Thêm route POST behavior |
| Frontend survey JS | Thêm tracking snippet |

### Dependencies

Không có.

---

## T4 — Module 120: Tích hợp behavior Fi vào FeatureExtractor

### Trạng thái hiện tại

- `FeatureExtractor::extract()` chỉ nhận `$answers` từ `survey_answers`
- Fi = điểm câu hỏi, không phải behavioral features
- Tài liệu yêu cầu: *"mỗi đặc trưng Fi biểu thị một chỉ số hành vi hoặc tương tác"*

### Việc cần làm

#### 1. Cập nhật AnswerReader để đọc behavior data

**File:** `Modules/Survey/app/Scoring/AnswerReader.php`

Thêm method `readBehavior(int $responseId): array`:

```php
// Trả về: ['field_key' => BehaviorPayload]
// BehaviorPayload: [
//   'time_spent_seconds' => int,
//   'change_count'       => int,
//   'was_cleared'        => bool,
//   'hesitation_index'   => float (change_count / time_spent nếu time > 0),
// ]
```

Query từ `submission_behavior_log` nhóm theo `question_code`.

#### 2. Thêm BehaviorFeature type vào FeatureExtractor

**File:** `Modules/Survey/app/Scoring/FeatureExtractor.php`

Thêm behavioral feature extraction vào method `extract()`:

Sau khi tính survey-based scores, thêm behavioral bonuses/penalties:
- `high_hesitation_penalty`: nếu `hesitation_index > threshold` → giảm điểm domain tương ứng
- `fast_answer_bonus`: nếu `time_spent < threshold` với `change_count = 0` → câu trả lời tự tin
- Các behavioral features này được cấu hình trong `score_rules` với `scoring_type = 'behavior'`

#### 3. Thêm scoring_type 'behavior' vào ScoreRule

**File:** `Modules/Survey/app/Models/ScoreRule.php`

Thêm support cho `scoring_type = 'behavior'`:
- `behavior_metric`: loại metric (`time_spent`, `change_count`, `hesitation_index`)
- `threshold_value`: ngưỡng so sánh
- `operator`: `<`, `>`, `<=`, `>=`
- `score_adjustment`: điểm cộng/trừ

#### 4. Cập nhật ScoringEngineService

**File:** `Modules/Survey/app/Scoring/ScoringEngineService.php`

Bước 2 (load answers): cũng load behavior data:
```php
$answers = $this->answerReader->read($responseId, $response->survey_id);
$behaviorData = $this->answerReader->readBehavior($responseId);
```

Truyền `$behaviorData` vào `$this->featureExtractor->extract($config, $answers, $behaviorData)`.

### Files cần chỉnh sửa

| File | Thay đổi |
|------|---------|
| `app/Scoring/AnswerReader.php` | Thêm `readBehavior()` |
| `app/Scoring/FeatureExtractor.php` | Nhận + xử lý behavioral features |
| `app/Scoring/ScoringEngineService.php` | Truyền behavior data vào pipeline |
| `app/Models/ScoreRule.php` | Thêm scoring_type 'behavior' |

### Dependencies

**T3 phải hoàn thành trước** (phải có data trong `submission_behavior_log`).

---

## T5 — Module 170A: Auto-seed ScoringFeedback trong ResultPersister

### Trạng thái hiện tại

- `scoring_feedback` table tồn tại (model `ScoringFeedback` với đủ fields)
- **`ResultPersister::persist()` không ghi gì vào `scoring_feedback`**
- Bảng luôn rỗng → Module 170 (tuning) không bao giờ có data để hoạt động
- Đây là **điểm gãy** của vòng lặp adaptive

### Việc cần làm

#### Cập nhật ResultPersister

**File:** `Modules/Survey/app/Scoring/ResultPersister.php`

Trong method `persist()`, sau khi tạo `$surveyResult`, thêm:

```php
// Auto-seed ScoringFeedback (Module 170 — seed predicted_band/score)
ScoringFeedback::create([
    'result_id'        => $resultId,
    'assessment_code'  => $result->assessmentCode,
    'predicted_band'   => $classification->bandCode ?? $classification->personaCode,
    'predicted_score'  => $result->overallScore,
    'actual_band'      => null,      // Admin xác nhận sau (T7)
    'actual_score'     => null,      // Admin xác nhận sau (T7)
    'feedback_source'  => 'system',
    'is_processed'     => false,
]);
```

**Lưu ý:** `actual_band` và `actual_score` để `null` — admin sẽ điền sau (Task T6).

### Files cần chỉnh sửa

| File | Thay đổi |
|------|---------|
| `app/Scoring/ResultPersister.php` | Thêm ScoringFeedback::create() trong transaction |

### Dependencies

Không có (độc lập, nên làm sớm nhất).

---

## T6 — Module 170B: UI admin xác nhận actual_band + FeedbackController

### Trạng thái hiện tại

- Admin không có giao diện để xác nhận "kết quả thực tế" của respondent
- `scoring_feedback.actual_band` mãi là `null` dù T5 đã seed `predicted_band`
- Vòng lặp tuning vẫn không hoạt động vì không có actual_band để so sánh

### Việc cần làm

#### 1. Thêm Feedback section vào results/show.blade.php

**File:** `Modules/Survey/resources/views/results/show.blade.php`

Thêm card "Phản hồi thực tế" (chỉ hiện với `@can('survey.update')`):

```
┌─ Phản hồi thực tế (Module 170) ─────────────────┐
│ Predicted band: [INTERMEDIATE] (auto)             │
│ Actual band:    [Chưa xác nhận]                   │
│                                                   │
│ Band thực tế: [dropdown chọn band] [Xác nhận]    │
│                                                   │
│ [trạng thái: Chưa xử lý / Đã xử lý bởi tuning]  │
└──────────────────────────────────────────────────┘
```

Dropdown liệt kê các bands từ `score_bands` của assessment.
Sau khi submit → PATCH request → flash success.

#### 2. Thêm controller method

**File:** `Modules/Survey/app/Http/Controllers/SurveyResultController.php`

Thêm method `submitFeedback(Survey $survey, SurveyResponse $response, Request $request)`:
- Validate: `actual_band` required, phải là band_code hợp lệ của assessment
- Update `scoring_feedback` record: set `actual_band`, `actual_score` (optional), `is_processed = false`
- Return JSON response

#### 3. Thêm route

**File:** `Modules/Survey/routes/web.php`

```php
Route::patch('/{response}/feedback', [SurveyResultController::class, 'submitFeedback'])
     ->name('result.feedback');
```

Đặt trong block `/{survey}/responses/` hiện có.

### Files cần chỉnh sửa

| File | Thay đổi |
|------|---------|
| `resources/views/results/show.blade.php` | Thêm feedback panel |
| `app/Http/Controllers/SurveyResultController.php` | Thêm `submitFeedback()` |
| `routes/web.php` | Thêm PATCH route |

### Dependencies

**T5 phải hoàn thành trước** (cần có record trong scoring_feedback để update).

---

## T7 — Module 170C: WeightTuningService — tính Δ và cập nhật Wi

### Trạng thái hiện tại

- `feature_weights` table tồn tại nhưng luôn rỗng
- `WeightRepository::loadActive()` có logic đọc `feature_weights` nhưng luôn fallback về static
- Không có service nào tính toán Wi mới

### Việc cần làm

#### 1. Tạo WeightTuningService

**File mới:** `Modules/Survey/app/Services/WeightTuningService.php`

**Constructor nhận:** `TuningScheduleConfig`, `FeatureWeight` (model), `FeatureWeightHistory` (model)

**Method chính:** `tune(string $assessmentCode): ?TuningCycle`

**Thuật toán chi tiết:**

```
Bước 1 — Kiểm tra điều kiện
  - Load TuningScheduleConfig cho assessmentCode
  - Nếu is_auto_tuning_enabled = false → return null
  - Kiểm tra max_cooldown_days: nếu last_cycle_at + cooldown > now → return null
  - Load unprocessed feedbacks (is_processed = false, actual_band IS NOT NULL)
  - Nếu count(feedbacks) < min_feedback_to_trigger → return null

Bước 2 — Tạo TuningCycle
  - cycle_number = last cycle_number + 1 (hoặc 1 nếu chưa có)
  - status = 'running', started_at = now()
  - method = 'simple_average'

Bước 3 — Tính error trước khi tune (error_before)
  - error_before = count(feedbacks where predicted_band ≠ actual_band) / count(feedbacks)

Bước 4 — Tính điều chỉnh Wi theo từng feedback
  - Load current weights (từ feature_weights version mới nhất, hoặc fallback assessment_domains)
  - Với mỗi feedback:
    * Nếu predicted_band = actual_band → bỏ qua (không có sai lệch)
    * Nếu predicted score > actual (overestimate):
      → các domain có score cao → giảm Wi nhẹ
    * Nếu predicted score < actual (underestimate):
      → các domain có score thấp nhưng actual cao → tăng Wi
  - Tích lũy delta_Wi cho từng domain
  - Lấy average delta sau tất cả feedbacks

Bước 5 — Áp dụng learning rate và clamp
  - new_Wi = old_Wi + learning_rate * avg_delta_Wi
  - Clamp: new_Wi = max(weight_min, min(weight_max, new_Wi))
  - Normalize: new_Wi /= Σ(new_Wi) (để tổng = 1.0)

Bước 6 — Persist Wi mới
  - Với mỗi domain:
    * Tạo hoặc update FeatureWeight record (version = max_version + 1)
    * Tạo FeatureWeightHistory record (old_weight, new_weight, delta, reason='tuning', cycle_id)

Bước 7 — Đánh dấu feedback đã xử lý
  - Update is_processed = true cho tất cả feedbacks đã dùng

Bước 8 — Cập nhật TuningCycle
  - Tính error_after (tương tự error_before nhưng với Wi mới — dùng pending feedbacks mới nếu có, hoặc mock)
  - status = 'completed', completed_at = now()
  - Cập nhật TuningScheduleConfig.last_cycle_at = now()

Bước 9 — Return TuningCycle
```

#### 2. Tích hợp error_before / error_after đúng nghĩa

Để tính `error_after` có ý nghĩa: dùng validation set (subset feedbacks giữ lại, không dùng để tune).

**Simple approach:** Split feedbacks 80/20: 80% để tune, 20% để validate, tính error_after trên 20%.

### Files cần tạo mới

| File | Mô tả |
|------|-------|
| `app/Services/WeightTuningService.php` | Service chính |

### Files cần chỉnh sửa

Không có (WeightRepository đã sẵn sàng đọc Wi mới khi bảng được ghi).

### Dependencies

**T5 và T6** phải hoàn thành trước (cần có `scoring_feedback` records với `actual_band` không null).

---

## T8 — Module 170D: RunTuningCycleJob + Artisan Command + Scheduler

### Trạng thái hiện tại

- Không có Job, không có Artisan command, không có scheduler
- `Console` directory trong module không tồn tại
- `TuningScheduleConfig` model tồn tại nhưng không có code nào đọc lịch từ đó

### Việc cần làm

#### 1. Tạo Job

**File mới:** `Modules/Survey/app/Jobs/RunTuningCycleJob.php`

```php
class RunTuningCycleJob implements ShouldQueue
{
    public int $tries = 1;  // Không retry — tuning cần idempotent check

    public function __construct(public readonly string $assessmentCode) {}

    public function handle(WeightTuningService $service): void
    {
        $service->tune($this->assessmentCode);
    }
}
```

#### 2. Tạo Artisan Command

**File mới:** `Modules/Survey/app/Console/Commands/RunSurveyTuning.php`

```
php artisan survey:tuning [--assessment=CODE] [--force]
```

- Không có `--assessment` → chạy cho tất cả assessments có `TuningScheduleConfig.is_auto_tuning_enabled = true`
- `--force` → bỏ qua cooldown check và min_feedback check
- Output: table với từng assessment code, trạng thái, cycle_number, error_before/after

#### 3. Đăng ký Command trong module ServiceProvider

**File:** `Modules/Survey/app/Providers/SurveyServiceProvider.php`

Thêm command vào `$commands` array (hoặc register trong `boot()`).

#### 4. Đăng ký Scheduler

**File:** `routes/console.php` (trong thư mục gốc dự án)

```php
use Modules\Survey\Jobs\RunTuningCycleJob;
use Modules\Survey\Models\TuningScheduleConfig;

Schedule::call(function () {
    TuningScheduleConfig::where('is_auto_tuning_enabled', true)->each(function ($config) {
        RunTuningCycleJob::dispatch($config->assessment_code)->onQueue('default');
    });
})->weekly()->sundays()->at('02:00')->name('survey-tuning')->withoutOverlapping();
```

### Files cần tạo mới

| File | Mô tả |
|------|-------|
| `app/Jobs/RunTuningCycleJob.php` | Queue job |
| `app/Console/Commands/RunSurveyTuning.php` | Artisan command |
| `app/Console/` | Thư mục Console |

### Files cần chỉnh sửa

| File | Thay đổi |
|------|---------|
| `app/Providers/SurveyServiceProvider.php` | Đăng ký command |
| `routes/console.php` (gốc dự án) | Đăng ký scheduler |

### Dependencies

**T7 phải hoàn thành trước.**

---

## T9 — Module 140: Dynamic T1/T2 — tự điều chỉnh ngưỡng phân loại

### Trạng thái hiện tại

- `score_bands` (T1, T2) là static — admin nhập tay, không bao giờ thay đổi
- Tài liệu: *"các ngưỡng T1, T2 có thể được điều chỉnh động dựa trên dữ liệu phản hồi"*
- Không có logic nào đọc phân bố thực tế để điều chỉnh

### Việc cần làm

#### 1. Thêm method adjustThresholds vào WeightTuningService

**File:** `Modules/Survey/app/Services/WeightTuningService.php`

Thêm method `adjustThresholds(string $assessmentCode, TuningCycle $cycle): void`

**Thuật toán:**
```
Điều kiện kích hoạt:
  - Chỉ chạy khi số responses >= 100 (min_responses_for_threshold)
  - Chỉ chạy mỗi 5 tuning cycles (không điều chỉnh threshold mỗi tuần)

Thuật toán percentile:
  - Load tất cả overall_score từ survey_results của assessment này
  - Tính phân bố score
  - Tính percentile 33 và 66 của distribution
  - T2 = percentile 33 (ngưỡng thấp/trung bình)
  - T1 = percentile 66 (ngưỡng trung bình/cao)
  
  Constraint: không thay đổi quá max_weight_change_pct % so với giá trị hiện tại trong 1 lần điều chỉnh
  
  Nếu new T1/T2 khác old quá ngưỡng cho phép → apply
  Log thay đổi vào tuning_cycles notes
```

#### 2. Gọi adjustThresholds sau mỗi tuning cycle

**File:** `Modules/Survey/app/Services/WeightTuningService.php`

Trong method `tune()` ở Bước 8, sau khi update TuningCycle:
```php
$this->adjustThresholds($assessmentCode, $cycle);
```

### Files cần chỉnh sửa

| File | Thay đổi |
|------|---------|
| `app/Services/WeightTuningService.php` | Thêm `adjustThresholds()` |

### Dependencies

**T7 và T8 phải hoàn thành trước.**

---

## T10 — Module 120: Data cleaning trong FeatureExtractor

### Trạng thái hiện tại

- `FeatureExtractor::extract()` xử lý tất cả answers mà không có bước lọc/làm sạch
- Tài liệu: *"Module xử lý dữ liệu chuẩn hóa, làm sạch và lưu trữ dữ liệu"*
- Không có xử lý cho: missing values, outlier scores, conflicting answers

### Việc cần làm

#### 1. Thêm bước data cleaning trước khi extract

**File:** `Modules/Survey/app/Scoring/FeatureExtractor.php`

Thêm private method `cleanAnswers(array $answers, ScoringConfig $config): array`:

**Các bước làm sạch:**

```
1. Loại bỏ answers của field không active
   → AnswerReader đã join với is_active=true, nhưng nên double-check

2. Xử lý missing values (field có rule nhưng không có answer)
   → Hiện tại: $answer = $answers[$fieldKey] ?? null → trả về score 0 (OK)
   → Thêm: ghi signal_flag 'missing_answers_count' nếu quá nhiều field bị missing

3. Clamp outlier scores
   → Nếu domain raw score > max_score * 1.2 → clamp về max_score (phòng config sai)
   → Ghi warning log

4. Kiểm tra tính nhất quán (consistency check)
   → Nếu có 2 fields với signal_flag mâu thuẫn nhau (do admin cấu hình sai) → log warning
```

#### 2. Thêm missing_data_ratio vào signal flags

Signal flag tự động: `missing_data_ratio` = số field bỏ trống / tổng field có rule.
Nếu ratio > 0.5 → set flag `high_missing_rate = true`.

### Files cần chỉnh sửa

| File | Thay đổi |
|------|---------|
| `app/Scoring/FeatureExtractor.php` | Thêm `cleanAnswers()` + missing data flag |

### Dependencies

Không có (độc lập).

---

## BẢNG CHECKLIST HOÀN THÀNH

| Task | Mô tả | Status |
|------|-------|--------|
| T0 | Fix ScoringConfig::hasScoring() bug | ⬜ TODO |
| T1 | Trang kết quả HTML public | ⬜ TODO |
| T2 | Job Position matching | ⬜ TODO |
| T3 | Thu thập behavior data (backend API) | ⬜ TODO |
| T4 | FeatureExtractor đọc behavior Fi | ⬜ TODO |
| T5 | Auto-seed ScoringFeedback trong ResultPersister | ⬜ TODO |
| T6 | UI admin xác nhận actual_band | ⬜ TODO |
| T7 | WeightTuningService — tính Δ, cập nhật Wi | ⬜ TODO |
| T8 | RunTuningCycleJob + Artisan + Scheduler | ⬜ TODO |
| T9 | Dynamic T1/T2 threshold adjustment | ⬜ TODO |
| T10 | Data cleaning trong FeatureExtractor | ⬜ TODO |

---

## PHỤ LỤC — DEPENDENCY GRAPH

```
T0 (Bug Fix)       ← Không phụ thuộc ai. Làm trước nhất.
T5 (Seed Feedback) ← Không phụ thuộc. Làm sớm nhất cùng T0.

T1 (HTML view)     ← Không phụ thuộc.
T2 (Job Position)  ← Không phụ thuộc (nhưng tích hợp tốt hơn sau T1).

T3 (Behavior API)  ← Không phụ thuộc.
T4 (Behavior Fi)   ← Phụ thuộc T3.

T6 (Feedback UI)   ← Phụ thuộc T5.
T7 (Tuning Svc)    ← Phụ thuộc T5 + T6.
T8 (Job+Scheduler) ← Phụ thuộc T7.
T9 (Dynamic T1T2)  ← Phụ thuộc T7 + T8.

T10 (Data clean)   ← Không phụ thuộc (độc lập).
```

**Thứ tự thực thi đề xuất:**

```
Wave 1 (critical, không phụ thuộc):  T0 → T5 → T10
Wave 2 (output cho user):             T1 → T2
Wave 3 (behavior data):               T3 → T4
Wave 4 (feedback loop):               T6 → T7 → T8 → T9
```

---

## PHỤ LỤC — FILES CẦN TẠO MỚI (TỔNG HỢP)

| File | Task |
|------|------|
| `resources/views/results/public.blade.php` | T1 |
| Migration `create_job_positions_table` | T2 |
| Migration `create_result_job_positions_table` | T2 |
| `app/Models/JobPosition.php` | T2 |
| `app/Models/ResultJobPosition.php` | T2 |
| `app/Scoring/JobPositionMatcher.php` | T2 |
| `app/Services/WeightTuningService.php` | T7 |
| `app/Jobs/RunTuningCycleJob.php` | T8 |
| `app/Console/Commands/RunSurveyTuning.php` | T8 |

---

## PHỤ LỤC — FILES CẦN CHỈNH SỬA (TỔNG HỢP)

| File | Tasks liên quan |
|------|----------------|
| `app/Scoring/ScoringConfig.php` | T0 |
| `app/Scoring/ScoringEngineService.php` | T2, T4 |
| `app/Scoring/FeatureExtractor.php` | T4, T10 |
| `app/Scoring/AnswerReader.php` | T4 |
| `app/Scoring/ResultPersister.php` | T2, T5 |
| `app/Scoring/ScoringResult.php` | T2 |
| `app/Models/SurveyResult.php` | T2 |
| `app/Models/ScoreRule.php` | T4 |
| `app/Http/Controllers/Api/SurveyApiController.php` | T3 |
| `app/Http/Controllers/SurveyResultController.php` | T1, T6 |
| `app/Http/Controllers/ScoringAdminController.php` | T2 |
| `app/Providers/SurveyServiceProvider.php` | T8 |
| `resources/views/results/show.blade.php` | T2, T6 |
| `resources/views/results/summary.blade.php` | T2 |
| `resources/views/scoring/index.blade.php` | T2 |
| `routes/web.php` | T1, T2, T6 |
| `routes/api.php` | T3 |
| `routes/console.php` (gốc dự án) | T8 |
