Tóm tắt Task 5:

  SurveyAnswerData — DTO một câu trả lời:
  - field_key (string), value (mixed — scalar hoặc int[] cho checkbox), other_text (?string)
  
  SurveyResponseData — DTO toàn bộ submit payload:
  - answers (DataCollection<SurveyAnswerData>), respondent_ref (?string), respondent_ip (?string binary)

  SubmitSurveyRequest — static validation (HTTP layer):

  ┌────────────────────────────────────────────────┬──────────────────────────────────────────────────────┐
  │                      Rule                      │                       Mục đích                       │
  ├────────────────────────────────────────────────┼──────────────────────────────────────────────────────┤
  │ answers required, array, min:1                 │ Phải có ít nhất 1 câu trả lời                        │
  ├────────────────────────────────────────────────┼──────────────────────────────────────────────────────┤
  │ answers.*.field_key required, string, max:100  │ Cấu trúc cơ bản                                      │
  ├────────────────────────────────────────────────┼──────────────────────────────────────────────────────┤
  │ answers.*.value required                       │ Phải có giá trị (kiểu cụ thể validate động ở Task 6) │
  ├────────────────────────────────────────────────┼──────────────────────────────────────────────────────┤
  │ answers.*.other_text nullable, string, max:500 │ Text cho option "Khác"                               │
  └────────────────────────────────────────────────┴──────────────────────────────────────────────────────┘

  Có thêm respondentIpBinary() — chuyển IPv4/IPv6 thành binary 16 bytes để lưu vào respondent_ip (VARBINARY(16)), và toResponseData() — bridge sang Action.

  Ranh giới rõ: static = cấu trúc payload đúng format → dynamic (Task 6) = field_key tồn tại, option hợp lệ, required, rule_min/max.


Tóm tắt Task 6 — SubmitSurveyAction:

  ---
  Flow tổng thể:
  loadFieldMap (1 query) → runValidation (5 lớp, collect ALL errors) → DB::transaction (insert)
  
  5 lớp validation — thứ tự và logic:

  ┌─────┬───────────────────────────────────────────────────────────────────────────────────┬─────────────────────────────────────────┐
  │ Lớp │                                     Mục đích                                      │          Dừng field nếu fail?           │
  ├─────┼───────────────────────────────────────────────────────────────────────────────────┼─────────────────────────────────────────┤
  │ 1   │ field_key tồn tại + is_active = 1                                                 │ Có — layers 2–5 không thể chạy          │
  ├─────┼───────────────────────────────────────────────────────────────────────────────────┼─────────────────────────────────────────┤
  │ 2   │ Kiểu dữ liệu khớp field_type (checkbox=array, number=numeric, date=Y-m-d...)      │ Không throw, nhưng skip layer 3 nếu lỗi │
  ├─────┼───────────────────────────────────────────────────────────────────────────────────┼─────────────────────────────────────────┤
  │ 3   │ option_value phải tồn tại trong survey_field_options của đúng field               │ Skip nếu layer 2 lỗi                    │
  ├─────┼───────────────────────────────────────────────────────────────────────────────────┼─────────────────────────────────────────┤
  │ 4   │ Required fields phải có mặt và không rỗng — chạy sau khi scan xong tất cả answers │ —                                       │
  ├─────┼───────────────────────────────────────────────────────────────────────────────────┼─────────────────────────────────────────┤
  │ 5   │ rule_min/rule_max (number: giá trị, text: độ dài), rule_max_select (checkbox)     │ Skip nếu có lỗi trước                   │
  └─────┴───────────────────────────────────────────────────────────────────────────────────┴─────────────────────────────────────────┘

  Quyết định thiết kế quan trọng:
  - Thu thập ALL errors trước khi throw — user thấy tất cả field cần sửa cùng lúc
  - Duplicate field_key trong cùng submit → reject ngay 
  - fieldMap load 1 lần với eager options — không có N+1 dù survey có 100 fields
  - SurveyAnswer::insert($rows) bulk insert — không gọi create() từng row
  - buildAnswerRows() tự tìm otherOptionId trong submitted options — đúng spec "is_other + text → row có cả option_id + value_string"
  - Lỗi trả về keyed by field_key — đúng contract { errors: { field_key: [...] } }

Tóm tắt Task 7:

  ---
  Kiến trúc cốt lõi — Batch query pattern:
  
  Thay vì N queries (1 per field), service chạy tối đa 5 queries — một per stat type, tất cả index-backed:

  forSurvey()
    ├── countCompleteResponses()   → INDEX (survey_id, status, submitted_at)
    ├── batchChoiceStats()         → INDEX (field_id, option_id)
    ├── batchNumberStats()         → INDEX (field_id, value_number)
    ├── batchBooleanStats()        → INDEX (field_id, value_bool)
    ├── batchTextStats()           → INDEX (field_id, value_string)
    └── batchTextareaStats()       → field_id prefix của INDEX (field_id, option_id)

  Output shape per field_type:

  ┌───────────────────────────┬────────────────────────────────────────────────────────────────────────────────────────────┐
  │           Type            │                                        Stats trả về                                        │
  ├───────────────────────────┼────────────────────────────────────────────────────────────────────────────────────────────┤
  │ select / radio / checkbox │ distribution[] → {option_value, label, count, percent} — percent tính trên total_responses │
  ├───────────────────────────┼────────────────────────────────────────────────────────────────────────────────────────────┤
  │ number / rating           │ {count, avg, min, max} — avg round 2 chữ số thập phân                                      │
  ├───────────────────────────┼────────────────────────────────────────────────────────────────────────────────────────────┤
  │ boolean                   │ {yes_count, no_count, total} — cast TINYINT 1/0 khi so sánh                                │
  ├───────────────────────────┼────────────────────────────────────────────────────────────────────────────────────────────┤
  │ text / textarea           │ {count} — không aggregate nội dung per spec                                                │
  └───────────────────────────┴────────────────────────────────────────────────────────────────────────────────────────────┘

  Quyết định quan trọng:
  - options cho choice fields được eager load 1 lần cùng fieldMap — không join SQL, không N+1
  - batchBooleanStats cast value_bool sang int khi so sánh vì MySQL trả về TINYINT string 
  - textarea không có index riêng trên value_text — dùng COUNT(*) WHERE field_id IN (...) thông qua prefix của index (field_id, option_id) — đúng spec "index-backed, không full table
  scan"
  - Service là pure read — không có side effect, không write DB



Giai đoạn 1 hoàn thành. Tóm tắt những gì đã implement:

  ┌──────┬───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
  │ Task │                                                                                  Kết quả                                                                                  │
  ├──────┼───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
  │ 1.1  │ SurveyToken model — relationship survey(), scopes active()/forSurvey(), helpers isExpired()/isValid(). Quan hệ tokens() thêm vào Survey model.                            │
  ├──────┼───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
  │ 1.2  │ SurveyPermissionSeeder — tạo 7 permissions survey.* trong DB, gán cho system_admin, ceo, marketing, ops. Chạy thành công.                                                 │
  ├──────┼───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
  │ 1.3  │ CreateSurveyAction + UpdateSurveyAction (slug lock khi active, unique check) + SurveyFormData DTO + SurveyRequest (validate title/slug/version) + Admin\SurveyController  │
  │      │ (index/create/store/edit/update/destroy + activity log).                                                                                                                  │
  ├──────┼───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
  │ 1.4  │ ActivateSurveyAction — validate ≥1 active field, đổi status → Active, lock slug.                                                                                          │
  ├──────┼───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
  │ 1.5  │ Sidebar thêm menu "Khảo sát" với @can('survey.view') guard, submenu Danh sách + Tạo mới.                                                                                  │
  ├──────┼───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
  │ 1.6  │ admin/surveys/index.blade.php — table với filter (q + status), status badge, responses count, actions (edit/delete), empty state.                                         │
  ├──────┼───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
  │ 1.7  │ admin/surveys/create.blade.php — form tạo mới + Alpine.js auto-generate slug từ title. admin/surveys/edit.blade.php — 2 cột: form thông tin + builder placeholder + stats │
  │      │  card + nút activate.                                                                                                                                                     │
  └──────┴───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

  7 routes backend.surveys.* đã đăng ký. 17/17 unit tests pass.

Tổng kết GIAI ĐOẠN 3 đã implement:

  ---
  Task 3.1 — GenerateSurveyTokenAction
  
  - Sinh Str::random(64) plaintext → lưu hash('sha256', $plain) vào DB
  - Trả ['token' => $model, 'plain' => $plain] — plaintext chỉ xuất hiện 1 lần duy nhất
  - Ghi activity log token.created

  Task 3.2 — RevokeSurveyTokenAction + TokenController

  - 3 routes: GET /tokens (list), POST /tokens (generate JSON), DELETE /tokens/{token} (revoke JSON)
  - TokenController check quyền survey.manage_tokens, validate input

  Task 3.3 — ValidateSurveyToken middleware

  - Đọc Authorization: Bearer <plain> → hash SHA-256 → lookup DB
  - Kiểm tra is_active, expires_at, đúng survey (theo slug)
  - updateQuietly(['last_used_at' => now()]) — không trigger events
  - Gắn vào /schema và /submit routes

  Task 3.4 — Rate limit

  - Named limiter survey-submit: 60 req/min per token hash (fallback IP)
  - Đăng ký trong RouteServiceProvider::boot(), áp dụng qua throttle:survey-submit trên /submit

  Task 3.5 — UI views/tokens/index.blade.php

  - List tất cả tokens: name, status badge (Active/Revoked/Hết hạn), last_used_at, expires_at
  - Modal tạo token: name + optional expires_at
  - Modal reveal plaintext: hiển thị token 1 lần với copy button, cảnh báo "lưu ngay"
  - Thu hồi token với confirm dialog
  - Fix migration: unique(['survey_id', 'is_active']) → index (MySQL-safe, tạo index thay thế trước khi drop)

  Index migration fix đúng
  Migration trước set UNIQUE(survey_id, is_active) — sai spec và giới hạn 1 active/1 inactive per survey. Migration fix đổi về non-unique composite index. Comment giải thích rõ lý do.

┌───────────┬
  ❯ làm thế nào để file job có thể chạy được nhỉ. bạn hướng dẫn nhanh giúp tôi nhé                                                                                                        
Bảng jobs đã tồn tại sẵn. Mọi thứ đã sẵn sàng, chỉ cần chạy worker.

  ---
  Hướng dẫn nhanh
  
  .env đang dùng QUEUE_CONNECTION=database — job sẽ lưu vào bảng jobs trong MySQL. Bảng jobs, failed_jobs, job_batches đều đã có.

  Chạy worker (trong terminal riêng, song song với php artisan serve):

  php artisan queue:work

  Hoặc nếu muốn auto-detect file mới khi dev:

  php artisan queue:listen

  ┌───────────┬───────────────────────┬─────────────────────────────┐
  │           │      queue:work       │        queue:listen         │
  ├───────────┼───────────────────────┼─────────────────────────────┤
  │ Hiệu suất │ Cao hơn (giữ process) │ Thấp hơn (restart mỗi job)  │
  ├───────────┼───────────────────────┼─────────────────────────────┤
  │ Dùng khi  │ Production            │ Dev (pick up code thay đổi) │
  └───────────┴───────────────────────┴─────────────────────────────┘

  Test nhanh: Gọi một API có Bearer token hợp lệ → xem last_used_at trong DB được cập nhật sau vài giây.

  Nếu job lỗi, xem trong bảng failed_jobs:
  php artisan queue:failed

  Retry:
  php artisan queue:retry all

  ---
  Tóm lại: chỉ cần chạy php artisan queue:listen là job UpdateTokenLastUsedJob sẽ tự xử lý. Không cần config thêm gì.

  ┌───────────┬

  Phân quyền tương lai: Hiện tại cả 3 dùng survey.manage_tokens. Sau này chỉ cần thêm permission mới (VD: survey.view_token_secret) và thay vào $this->authorize(...) trong reveal() —
  không cần đụng vào logic nào khác.