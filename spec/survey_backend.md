# Survey Backend Management Specification

**Module**: `Modules/Survey`
**Phạm vi**: Phần QUẢN LÝ survey trong backend CRM (sidebar admin). Module nhận data + API public ĐÃ HOÀN THÀNH ở giai đoạn trước.
**Tech stack**: Laravel + Blade + Alpine.js + PRedis · spatie/laravel-data · lorisleiva/laravel-actions · spatie/laravel-permission · spatie/laravel-activitylog · rap2hpoutre/fast-excel

> **Lưu ý**: 6 bảng definition/data ĐÃ TỒN TẠI (surveys, survey_sections, survey_fields, survey_field_options, survey_responses, survey_answers). Phần này đã thêm 1 bảng mới (`survey_tokens`) và build CRUD + UI + reporting. Claude chỉ tạo/sửa file trong `Modules/Survey/`.

---

## 0. Mục tiêu phần này

Cho phép admin trong backend CRM:
1. Tạo/quản lý survey (thông tin chung, status, version)
2. Build cấu trúc động: sections → fields → options
3. Cấp/thu hồi API token riêng cho mỗi survey (frontend public dùng để gọi /schema và /submit)
4. Xem responses đã nộp + xem chi tiết từng response
5. Thống kê data per field + export Excel

---

## 1. Key Rules (Claude PHẢI tuân thủ nghiêm ngặt)

- KHÔNG sửa schema 6 bảng có sẵn. Đã có bảng `survey_tokens`.
- `field_key` và `option_value` là machine name BẤT BIẾN sau khi survey `status = active`.
- Survey đã `active` và CÓ responses → KHÔNG cho sửa/xóa field/option đã có answer. Chỉ cho thêm mới hoặc `is_active = 0` (deactivate).
- Slug auto-generate từ title, unique. KHÔNG cho sửa slug sau khi `active`.
- Mọi thay đổi definition phải ghi activity log (spatie/laravel-activitylog).
- Mọi thao tác quản lý phải qua permission (spatie/laravel-permission): `survey.view`, `survey.create`, `survey.update`, `survey.delete`, `survey.manage_tokens`, `survey.view_responses`, `survey.export`.
- Reorder (sort_order) tách thành endpoint riêng, KHÔNG sửa trong form chính.
- CRUD ghi data đặt trong Actions (lorisleiva/laravel-actions). Controller chỉ điều phối.
- Query đọc/thống kê đặt trong Service, index-backed, KHÔNG full table scan.

---

## 2. Bảng mới: survey_tokens

| Column        | Type                  | Note                              |
|---------------|-----------------------|-----------------------------------|
| id            | BIGINT UNSIGNED PK    |                                   |
| survey_id     | FK -> surveys         | CASCADE DELETE                    |
| name          | VARCHAR(150)          | VD: "Website chính"               |
| token         | VARCHAR(80) UNIQUE    | lưu hash, hiển thị plaintext 1 lần |
| is_active     | TINYINT(1) DEFAULT 1  |                                   |
| last_used_at  | TIMESTAMP NULL        | cập nhật mỗi lần token được dùng  |
| expires_at    | TIMESTAMP NULL        | null = không hết hạn              |
| created_at, updated_at |              |                                   |

INDEX (survey_id, is_active) · UNIQUE (token)

Lý do token per survey: mỗi survey embed nhiều domain, revoke độc lập, track nguồn gọi qua last_used_at.

---

## 3. Cấu trúc thư mục bổ sung & đúng chuẩn Directory Structure (AVSA)

```
Modules/Survey/
├── Actions/
│   ├── CreateSurveyAction.php
│   ├── UpdateSurveyAction.php
│   ├── ActivateSurveyAction.php
│   ├── DuplicateSurveyAction.php
│   ├── CreateSectionAction.php / UpdateSectionAction.php
│   ├── CreateFieldAction.php / UpdateFieldAction.php / DeactivateFieldAction.php
│   ├── CreateOptionAction.php / UpdateOptionAction.php
│   ├── ReorderAction.php              # dùng chung cho section/field/option
│   ├── GenerateSurveyTokenAction.php
│   └── RevokeSurveyTokenAction.php
├── Data/
│   ├── SurveyFormData.php
│   ├── SectionFormData.php
│   ├── FieldFormData.php
│   ├── OptionFormData.php
│   └── TokenFormData.php
├── Models/
│   └── SurveyToken.php                # + 6 model có sẵn
├── Services/
│   ├── SurveyStatsService.php         # đã có, mở rộng thêm
│   └── ResponseViewerService.php      # dựng lại 1 response thành dạng đọc được
├── Http/
│   ├── Controllers/Admin/
│   │   ├── SurveyController.php
│   │   ├── SectionController.php
│   │   ├── FieldController.php
│   │   ├── OptionController.php
│   │   ├── TokenController.php
│   │   ├── ResponseController.php
│   │   └── StatsController.php
│   ├── Requests/                      # form request cho từng resource
│   └── Middleware/
│       └── ValidateSurveyToken.php    # verify token cho API public
├── Database/Migrations/
│   └── xxxx_create_survey_tokens_table.php
└── Resources/views/            # Blade + Alpine.js
    ├── surveys/ (index, create, edit)
    ├── builder/ (section, field, option partials)
    ├── tokens/
    ├── responses/ (index, show)
    └── stats/
```

---

## 4. THỨ TỰ TASK ĐẦY ĐỦ (thực thi đúng trình tự). Làm tuân thủ cấu trúc Advanced Vertical Slice (AVSA) + CQRS-lite 

### GIAI ĐOẠN 1 — Survey CRUD + UI cơ bản

| Task | Nội dung | Output |
|------|----------|--------|
| 1.1 | Model `SurveyToken` | bảng survey_tokens, relationship survey ↔ tokens |
| 1.2 | Permission seeder | 7 permission survey.* + gán vào role admin/manager |
| 1.3 | `SurveyController` (index, create, store, edit, update, destroy) + Actions + Requests | CRUD survey |
| 1.4 | `ActivateSurveyAction` | đổi status draft→active, khóa slug, validate có ít nhất 1 field |
| 1.5 | UI: thêm menu "Khảo sát" vào sidebar CRM | sidebar link |
| 1.6 | UI: trang danh sách surveys (table + status badge + actions) | views/surveys/index |
| 1.7 | UI: trang tạo/sửa survey (form thông tin chung) | views/surveys/create+edit |

**→ DỪNG, review trước khi sang Giai đoạn 2.**

### GIAI ĐOẠN 2 — Survey Builder (Definition động)

| Task | Nội dung | Output |
|------|----------|--------|
| 2.1 | `SectionController` + `CreateSectionAction`/`UpdateSectionAction` | CRUD section |
| 2.2 | `FieldController` + Field Actions (create/update/deactivate) | CRUD field, hỗ trợ parent_field_id |
| 2.3 | `OptionController` + Option Actions | CRUD option per choice field |
| 2.4 | `ReorderAction` + endpoint `PATCH .../reorder` | sort_order cho section/field/option |
| 2.5 | Guard rule: chặn sửa/xóa field+option khi survey active & có response | logic trong Action, throw nếu vi phạm |
| 2.6 | UI Survey Builder (Alpine.js): sections collapsible → fields sortable → inline options | views/builder/* |
| 2.7 | UI: chọn field_type → render đúng config (text/number/rating/choice...) | dynamic form Alpine |

**→ DỪNG, review trước khi sang Giai đoạn 3.**

### GIAI ĐOẠN 3 — API Token Management

| Task | Nội dung | Output |
|------|----------|--------|
| 3.1 | `GenerateSurveyTokenAction` (tạo token, hash lưu DB, trả plaintext 1 lần) | token tạo |
| 3.2 | `RevokeSurveyTokenAction` + `TokenController` (list/generate/revoke) | quản lý token |
| 3.3 | `ValidateSurveyToken` middleware → gắn vào route API public /schema, /submit | verify + cập nhật last_used_at |
| 3.4 | Rate limit theo token cho endpoint submit | throttle |
| 3.5 | UI: trang Tokens per survey (list + nút generate + revoke + copy plaintext) | views/tokens |

**→ DỪNG, review trước khi sang Giai đoạn 4.**

### GIAI ĐOẠN 4 — Responses & Statistics

| Task | Nội dung | Output |
|------|----------|--------|
| 4.1 | `ResponseController` index: danh sách response per survey + filter (date range, status, respondent_ref) | views/responses/index |
| 4.2 | `ResponseViewerService` + show: dựng lại 1 response đầy đủ theo field/option | views/responses/show |
| 4.3 | Soft delete response | nút xóa |
| 4.4 | Mở rộng `SurveyStatsService`: distribution choice, avg/min/max number, count boolean, total theo ngày | service methods |
| 4.5 | `StatsController` + UI dashboard per survey (cards + Apache ECharts) | views/stats |
| 4.6 | Gắn `ExportSurveyResponsesAction` (đã có) vào route export?format=xlsx | export Excel |

**→ HOÀN THÀNH module quản lý.**

---

## 5. Nguyên tắc UI (Blade + Alpine.js)

- SSR Blade render khung + data sẵn. Alpine chỉ điều khiển UI state (collapse, modal, sortable, dynamic field config).
- Survey Builder dùng Alpine: thêm field/option KHÔNG reload, sort drag/drop hoặc up/down, submit qua fetch tới endpoint CRUD.
- Chart dùng Chart.js (CDN), data inject từ StatsController dưới dạng JSON trong Blade.
- KHÔNG fetch data hiển thị qua Alpine khi load trang (giữ SSR). Chỉ fetch cho thao tác ghi (add/sort/delete trong builder).

---

## 6. API Public (đã có — chỉ bổ sung middleware token)

| Endpoint | Thay đổi |
|----------|----------|
| GET /api/v1/surveys/{slug}/schema | + middleware ValidateSurveyToken |
| POST /api/v1/surveys/{slug}/submit | + ValidateSurveyToken + rate limit + Turnstile verify |

---

## 7. Lệnh khởi động cho Claude

> Đọc file này trước. Thực thi theo đúng thứ tự Giai đoạn 1 → 4, mỗi cuối giai đoạn DỪNG để tôi review. Bắt đầu Giai đoạn 1 bằng Task 1.1 (model SurveyToken) và Task 1.2 (permission seeder). Chỉ tạo/sửa file trong `Modules/Survey/`. KHÔNG động vào schema 6 bảng có sẵn.