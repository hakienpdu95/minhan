# Survey Module — Hardening & Audit Specification

**Module**: `Modules/Survey`
**Mục tiêu**: Kiểm tra, bổ sung, hoàn thiện module Survey đã build. Tập trung vào tính đúng đắn của data, bảo mật, hiệu suất và độ bền dài hạn.
**Phạm vi**: Chỉ đọc/sửa file trong `Modules/Survey/`. KHÔNG sửa schema DB, KHÔNG sửa migration đã chạy.

> Claude phải ĐỌC FILE THỰC TẾ trước khi làm bất kỳ task nào. Không được giả định code đã đúng hay đã có. Mỗi task bắt đầu bằng việc đọc file liên quan, sau đó báo cáo hiện trạng, rồi mới sửa/bổ sung.

---

## Thứ tự thực thi: 6 nhóm, làm tuần tự, dừng cuối mỗi nhóm để review

---

## Hãy scan toàn bộ Modules/Survey để hiểu cấu trúc trước khi làm nhé

## NHÓM 1 — Validate Submit (ưu tiên cao nhất)

**Đọc trước**: `Actions/SubmitSurveyAction.php`, `Http/Requests/SubmitSurveyRequest.php`

### Task 1.1 — Kiểm tra đủ 5 lớp validate

Báo cáo từng lớp: đã có / chưa có / có nhưng sai logic.

| Lớp | Yêu cầu | Vị trí đúng |
|-----|---------|-------------|
| 1 | `field_key` tồn tại trong survey này VÀ `is_active = 1` | SubmitSurveyAction |
| 2 | `field_type` khớp kiểu value gửi lên (checkbox nhận array, number nhận numeric, date hợp lệ) | SubmitSurveyAction |
| 3 | `option_value` tồn tại trong `survey_field_options` của đúng `field_id` đó | SubmitSurveyAction |
| 4 | `is_required = 1` mà thiếu hoặc rỗng → reject, message rõ field nào | SubmitSurveyAction |
| 5 | `rule_min` / `rule_max` (number, độ dài text) và `rule_max_select` (số option multi-choice) | SubmitSurveyAction |

Nếu thiếu lớp nào → bổ sung vào đúng vị trí. Lỗi trả về `422` với key = `field_key`.

### Task 1.2 — Kiểm tra survey status trước khi nhận submit

`SubmitSurveyAction` phải check `survey->status = active` (status = 1) trước khi xử lý. Nếu `draft` hoặc `closed` → throw `SurveyNotActiveException` → trả `403`.

### Task 1.3 — Kiểm tra Transaction

Toàn bộ flow submit phải nằm trong **một** `DB::transaction()`:
- Tạo `survey_responses`
- Insert toàn bộ `survey_answers`

Nếu bất kỳ insert nào fail → rollback toàn bộ. Không được để `survey_responses` tồn tại mà không có `survey_answers`.

### Task 1.4 — Kiểm tra multi-choice lưu đúng

Với `field_type = checkbox`: mỗi option được chọn phải là **một row riêng** trong `survey_answers` (cùng `response_id` + `field_id`, khác `option_id`). KHÔNG lưu dạng JSON array hay comma-separated string trong một row.

Viết query kiểm tra: lấy một response có multi-choice, đếm số row trong `survey_answers` theo `field_id` đó, phải bằng số option đã chọn.

### Task 1.5 — Kiểm tra option `is_other`

Option có `is_other = 1`: khi user chọn và điền text → row answer phải có cả `option_id` (trỏ option "Khác") + `value_string` (nội dung điền). Nếu `is_other = 1` mà `value_string` rỗng → quyết định: reject nếu field `is_required`, cho qua nếu không required.

**→ DỪNG, báo cáo kết quả Nhóm 1 trước khi sang Nhóm 2.**

---

## NHÓM 2 — Data Integrity

**Đọc trước**: `Actions/UpdateFieldAction.php`, `Actions/DeactivateFieldAction.php`, `Actions/UpdateSurveyAction.php`, `Actions/ActivateSurveyAction.php`

### Task 2.1 — Guard rule: chặn sửa/xóa field khi survey active có response

Khi `survey->status = active` VÀ đã có ít nhất 1 `survey_response` với `status = complete`:
- KHÔNG cho UPDATE `survey_fields` (field_key, label, type, is_required)
- KHÔNG cho DELETE `survey_fields` hoặc `survey_field_options`
- CHỈ cho: thêm field mới, set `is_active = 0` (deactivate), sửa label/placeholder (không ảnh hưởng data)

Nếu vi phạm → throw `FieldImmutableException` với message rõ lý do.

### Task 2.2 — Kiểm tra slug lock sau khi active

`UpdateSurveyAction` phải kiểm tra: nếu `survey->status = active` → không cho sửa `slug`. Slug là key public, thay đổi sẽ phá link đã chia sẻ.

### Task 2.3 — Kiểm tra ActivateSurveyAction

`ActivateSurveyAction` phải validate đủ điều kiện trước khi đổi status:
- Phải có ít nhất 1 `survey_section`
- Phải có ít nhất 1 `survey_field` với `is_active = 1`
- Tất cả field `is_required = 1` phải có đầy đủ config (không thiếu options với choice field)

Nếu không đủ → throw exception với message cụ thể.

### Task 2.4 — Kiểm tra cascade khi xóa Survey

Khi soft-delete `Survey`:
- `survey_tokens` phải được set `is_active = 0` (token không còn dùng được)
- `survey_responses` và `survey_answers` giữ nguyên (không xóa data lịch sử)

Kiểm tra trong `Survey` model có observer hoặc boot method xử lý không. Nếu chưa → thêm `SurveyObserver`.

### Task 2.5 — Kiểm tra `/schema` filter field inactive

`BuildSurveySchemaAction` phải chỉ trả field có `is_active = 1` và option còn hoạt động. Field `is_active = 0` không được xuất hiện trong response schema — frontend không được render field đã deactivate.

### Task 2.6 — Chính sách duplicate submit

Quyết định rõ và implement:
- **Allow**: cùng `respondent_ref` submit nhiều lần → tạo nhiều `survey_responses` → mỗi lần là một response độc lập.
- **Block**: cùng `respondent_ref` chỉ được submit một lần → check trước, nếu đã có → trả `409 Conflict`.

Hiện tại đang theo chính sách nào? Nếu chưa có chính sách → implement **Allow** (mặc định) + log warning nếu cùng ref submit trong 5 phút.

**→ DỪNG, báo cáo kết quả Nhóm 2 trước khi sang Nhóm 3.**

---

## NHÓM 3 — Security

**Đọc trước**: `Http/Middleware/ValidateSurveyToken.php`, `Routes/api.php`, `Http/Controllers/Admin/*.php`

### Task 3.1 — Rate limiting endpoint submit

Kiểm tra route `POST /api/v1/surveys/{slug}/submit` có rate limit không.

Yêu cầu: `throttle:10,1` (10 lần/phút per IP) hoặc custom `RateLimiter` trong `AppServiceProvider`. Nếu chưa có → thêm vào `Routes/api.php` hoặc middleware stack của route submit.

### Task 3.2 — Turnstile verify

Kiểm tra `SubmitSurveyAction` hoặc middleware có verify Cloudflare Turnstile token không (dùng `ryangjchandler/laravel-cloudflare-turnstile`). Turnstile token phải được frontend gửi kèm trong request body (`cf-turnstile-response`). Backend verify trước khi xử lý bất kỳ logic nào.

Nếu chưa có → thêm Turnstile verify là bước đầu tiên trong `SubmitSurveyAction`, trước cả validate 5 lớp.

### Task 3.3 — API Token middleware đầy đủ

Kiểm tra `ValidateSurveyToken` middleware xử lý đủ các case:
- Token không tồn tại → `401`
- Token `is_active = 0` → `401`
- Token `expires_at` đã qua → `401`
- Token thuộc survey khác (không khớp slug) → `403`
- Token hợp lệ → cập nhật `last_used_at = now()` (dùng `updateQuietly` để không trigger event)

### Task 3.4 — Permission check trong Admin Controllers

Mỗi method trong Admin Controllers phải có `$this->authorize()` hoặc `Gate::authorize()` với đúng permission:

```
survey.view         → index, show
survey.create       → create, store
survey.update       → edit, update, activate, reorder
survey.delete       → destroy
survey.manage_tokens → TokenController toàn bộ
survey.view_responses → ResponseController index, show
survey.export       → export
```

Nếu dùng Policy → kiểm tra `SurveyPolicy` đã đăng ký trong `AuthServiceProvider` chưa.

### Task 3.5 — Mass assignment protection

Kiểm tra `$fillable` trong các model:
- `SurveyField`: KHÔNG được fillable `is_active` từ request ngoài (chỉ set qua `DeactivateFieldAction`)
- `SurveyResponse`: KHÔNG được fillable `status` từ request (chỉ set trong `SubmitSurveyAction`)
- `SurveyToken`: KHÔNG được fillable `token` trực tiếp (chỉ set qua `GenerateSurveyTokenAction`)

**→ DỪNG, báo cáo kết quả Nhóm 3 trước khi sang Nhóm 4.**

---

## NHÓM 4 — Performance

**Đọc trước**: `Actions/BuildSurveySchemaAction.php`, `Services/SurveyStatsService.php`, `Services/ResponseViewerService.php`

### Task 4.1 — Kiểm tra N+1 trong BuildSurveySchemaAction

Phải dùng eager load:
```php
Survey::with([
    'sections' => fn($q) => $q->orderBy('sort_order'),
    'sections.fields' => fn($q) => $q->where('is_active', 1)->orderBy('sort_order'),
    'sections.fields.options' => fn($q) => $q->orderBy('sort_order'),
])->where('slug', $slug)->firstOrFail()
```

Chạy `DB::enableQueryLog()` → gọi action → `DB::getQueryLog()` → đếm số query. Phải là **3 query** (surveys + sections+fields + options), không phải N+1.

### Task 4.2 — Kiểm tra N+1 trong ResponseViewerService

Dựng lại một response cần: `answers` → `field` → `option`. Phải eager load:
```php
SurveyResponse::with([
    'answers.field',
    'answers.option',
])->findOrFail($id)
```

Đếm query log, phải là 3 query tối đa.

### Task 4.3 — Kiểm tra Stats query có đi đúng index

Chạy `EXPLAIN` trên 3 query chính của `SurveyStatsService`:

```sql
-- Đếm lựa chọn
EXPLAIN SELECT option_id, COUNT(*) FROM survey_answers
WHERE field_id = ? AND option_id IS NOT NULL GROUP BY option_id;
-- Phải dùng index (field_id, option_id)

-- Avg number/rating
EXPLAIN SELECT AVG(value_number) FROM survey_answers
WHERE field_id = ? AND value_number IS NOT NULL;
-- Phải dùng index (field_id, value_number)

-- Count boolean
EXPLAIN SELECT value_bool, COUNT(*) FROM survey_answers
WHERE field_id = ? AND value_bool IS NOT NULL GROUP BY value_bool;
-- Phải dùng index (field_id, value_bool)
```

Nếu `type = ALL` (full scan) → báo cáo, kiểm tra index có tồn tại trong DB thực tế chưa (`SHOW INDEX FROM survey_answers`).

### Task 4.4 — Cache schema trong Repository/Action

`BuildSurveySchemaAction` phải cache kết quả vào Redis:
```
key:   survey:schema:{slug}
TTL:   3600 giây (1 giờ)
purge: khi admin sửa field/option/section thuộc survey đó
```

Kiểm tra cache đã có chưa. Nếu chưa → thêm cache wrapper. Purge cache phải được gọi trong `UpdateFieldAction`, `DeactivateFieldAction`, `CreateOptionAction`, `UpdateOptionAction`.

### Task 4.5 — Kiểm tra Export pivot multi-choice

`ExportSurveyResponsesAction` với field `checkbox` (multi-choice): nhiều row cùng `response_id` + `field_id` phải được **gộp thành một ô** trong Excel (join bằng ", "). Không được để mỗi option thành một dòng Excel riêng (vỡ cấu trúc bảng).

Viết test thủ công: tạo response có multi-choice 3 option → export → kiểm tra file Excel có đúng 1 dòng cho response đó không.

**→ DỪNG, báo cáo kết quả Nhóm 4 trước khi sang Nhóm 5.**

---

## NHÓM 5 — Edge Cases

**Đọc trước**: toàn bộ Actions, `Http/Controllers/Admin/SurveyController.php`

### Task 5.1 — Submit khi survey không tồn tại hoặc đã closed

```
GET  /api/v1/surveys/slug-khong-ton-tai/schema → 404
POST /api/v1/surveys/slug-da-closed/submit     → 403 với message "Survey đã đóng"
POST /api/v1/surveys/slug-draft/submit         → 403 với message "Survey chưa active"
```

Kiểm tra từng case, bổ sung nếu thiếu.

### Task 5.2 — Section rỗng không field

`BuildSurveySchemaAction`: section không có field `is_active = 1` nào → không trả section đó trong response (lọc bỏ). Frontend không nhận section rỗng.

`ActivateSurveyAction`: không cho activate nếu có section rỗng (cảnh báo hoặc reject).

### Task 5.3 — Field choice không có option

Field `field_type` là `radio/select/checkbox` nhưng không có option nào trong `survey_field_options` → không được xuất hiện trong schema. Log warning trong `BuildSurveySchemaAction`.

### Task 5.4 — Reorder với sort_order conflict

`ReorderAction`: khi nhận mảng `[{id: 3, order: 1}, {id: 1, order: 2}]` phải update trong transaction. Không được để trạng thái giữa chừng (hai field cùng sort_order tạm thời) gây lỗi unique constraint nếu có.

### Task 5.5 — Generate token trùng

`GenerateSurveyTokenAction`: token phải đảm bảo unique. Dùng `Str::random(60)` + kiểm tra DB trước khi lưu. Nếu trùng (xác suất rất thấp nhưng phải xử lý) → regenerate tối đa 3 lần, sau đó throw exception.

**→ DỪNG, báo cáo kết quả Nhóm 5 trước khi sang Nhóm 6.**

---

## NHÓM 6 — Testing tối thiểu

**Tạo mới**: `Tests/Feature/`

### Task 6.1 — Feature test: Submit happy path

```
Setup: tạo survey active + section + fields (text, radio, checkbox) + options
Action: POST /submit với đầy đủ đúng data
Assert:
  - Response 201 với response_id
  - survey_responses có 1 record, status = complete
  - survey_answers có đúng số row (1 per field đơn + N per multi-choice)
  - Đúng value_kind → đúng cột (value_string, value_number, option_id...)
```

### Task 6.2 — Feature test: Reject field_key lạ

```
Action: POST /submit với field_key không tồn tại trong survey
Assert: 422, errors có key = field_key lạ đó
Assert: DB không có record nào được tạo (transaction rollback)
```

### Task 6.3 — Feature test: Reject option_value lạ

```
Action: POST /submit với option_value không tồn tại trong field đó
Assert: 422
Assert: DB sạch
```

### Task 6.4 — Feature test: Reject thiếu required field

```
Setup: field is_required = 1
Action: POST /submit không gửi field đó
Assert: 422 với message rõ field nào bị thiếu
```

### Task 6.5 — Feature test: Submit khi survey closed

```
Setup: survey status = closed (2)
Action: POST /submit
Assert: 403
```

### Task 6.6 — Feature test: Token invalid

```
Action: GET /schema không có token header
Assert: 401

Action: GET /schema với token sai
Assert: 401

Action: GET /schema với token is_active = 0
Assert: 401
```

### Task 6.7 — Unit test: AnswerValueResolver

```
Test từng value_kind → đúng cột:
  value_kind = string  → ['value_string' => 'abc']
  value_kind = number  → ['value_number' => 4.5]
  value_kind = bool    → ['value_bool' => 1]
  value_kind = date    → ['value_date' => '2025-01-15']
  value_kind = option  → ['option_id' => 5]
```

**→ DỪNG, báo cáo kết quả Nhóm 6. Đây là audit hoàn tất.**

---

## Tổng hợp output mong đợi sau audit

Claude phải tạo file `SURVEY_AUDIT_REPORT.md` tổng hợp:

```
## Kết quả audit

### Nhóm 1 — Validate Submit
- Task 1.1: [ĐÃ CÓ / BỔ SUNG / LỖI] — mô tả cụ thể
- Task 1.2: ...

### Nhóm 2 — Data Integrity
...

### Nhóm 3 — Security
...

### Nhóm 4 — Performance
...

### Nhóm 5 — Edge Cases
...

### Nhóm 6 — Testing
...

## Danh sách file đã sửa/tạo mới
- Modules/Survey/Actions/SubmitSurveyAction.php — [lý do sửa]
- ...

## Vấn đề còn tồn đọng (nếu có)
- ...
```

---

## Lệnh khởi động cho Claude

> Đọc file này toàn bộ trước. Bắt đầu Nhóm 1: đọc `Modules/Survey/Actions/SubmitSurveyAction.php` và `Modules/Survey/Http/Requests/SubmitSurveyRequest.php`, báo cáo hiện trạng từng task, sau đó sửa/bổ sung. Sau khi xong Nhóm 1, DỪNG và báo cáo kết quả để tôi review trước khi tiếp tục Nhóm 2. Chỉ sửa file trong `Modules/Survey/`. KHÔNG sửa migration hay schema DB.