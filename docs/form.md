# Dynamic Survey Form — Integration Guide

Hướng dẫn đầy đủ để tích hợp form khảo sát động từ CRM Survey API vào bất kỳ website nào.  
Kết quả submit được lưu trực tiếp vào CRM và có thể tra cứu trong dashboard.

---

## Mục lục

1. [Kiến trúc tổng quan](#1-kiến-trúc-tổng-quan)
2. [Xác thực — Bearer Token](#2-xác-thực--bearer-token)
3. [API Endpoints](#3-api-endpoints)
4. [Schema Response — Cấu trúc JSON](#4-schema-response--cấu-trúc-json)
5. [Field Types — Bảng tra cứu](#5-field-types--bảng-tra-cứu)
6. [Submit Payload — Cách đóng gói câu trả lời](#6-submit-payload--cách-đóng-gói-câu-trả-lời)
7. [Xử lý lỗi từ API](#7-xử-lý-lỗi-từ-api)
8. [Frontend Implementation — Alpine.js + DaisyUI](#8-frontend-implementation--alpinejs--daisyui)
9. [UX Patterns chuẩn](#9-ux-patterns-chuẩn)
10. [Checklist tích hợp](#10-checklist-tích-hợp)

---

## 1. Kiến trúc tổng quan

```
┌──────────────────────────┐         HTTPS          ┌──────────────────────┐
│  Web ngoài (bất kỳ)      │  ──── GET /schema ───▶  │   CRM Survey API     │
│  HTML / React / Vue /    │  ◀── JSON schema ────   │  /api/v1/surveys/    │
│  Vanilla JS / ...        │                          │  {slug}/schema       │
│                          │  ──── POST /submit ──▶  │  {slug}/submit       │
│                          │  ◀── { response_id } ─  │                      │
└──────────────────────────┘                         └──────────┬───────────┘
                                                                │
                                              ┌─────────────────▼────────────┐
                                              │   Database CRM               │
                                              │   survey_responses           │
                                              │   survey_answers             │
                                              │   → Hiển thị trong Dashboard │
                                              └──────────────────────────────┘
```

**Luồng cơ bản:**
1. Client gọi `GET /schema` → nhận định nghĩa form (sections, fields, options)
2. Client render form động dựa trên schema
3. Người dùng điền form, client validate phía trước
4. Client gọi `POST /submit` → nhận `response_id`
5. Dữ liệu tự động xuất hiện trong CRM Dashboard > Khảo sát > Phản hồi

---

## 2. Xác thực — Bearer Token

**Tất cả endpoint** đều yêu cầu Bearer Token trong header:

```http
Authorization: Bearer <token>
```

Token được tạo trong CRM Dashboard → Khảo sát → [Tên khảo sát] → **Quản lý Token**.  
Mỗi token được gắn với **một survey cụ thể** — token của survey A không dùng được cho survey B.

**Ví dụ header đầy đủ:**
```http
GET /api/v1/surveys/ai-readiness-workflow/schema HTTP/1.1
Host: your-crm.com
Authorization: Bearer E55XrTsfYqXkgnAOaSEnFyub3BJsIYHV8HK6a4pQXliAWAOTF9xcWUYRvTPLrlA7
Accept: application/json
```

**Lỗi xác thực:**
| HTTP | Body | Nguyên nhân |
|------|------|-------------|
| 401  | `{"error": "API token is required."}` | Thiếu header Authorization |
| 401  | `{"error": "Invalid API token."}` | Token sai hoặc đã bị xóa |
| 401  | `{"error": "Token is inactive or has expired."}` | Token bị vô hiệu hóa |
| 403  | `{"error": "Token is not authorized for this survey."}` | Token đúng nhưng sai survey |

---

## 3. API Endpoints

### 3.1 Lấy Schema Form

```
GET /api/v1/surveys/{slug}/schema
```

Trả về toàn bộ cấu trúc form: sections, fields, options.  
Schema được cache 30 phút — không cần lo về rate limit khi gọi lần đầu.

```http
GET /api/v1/surveys/ai-readiness-workflow/schema
Authorization: Bearer <token>
```

**Response 200:**
```json
{
  "id": 195,
  "title": "Bộ Khảo Sát AI Readiness & Workflow",
  "slug": "ai-readiness-workflow",
  "version": 1,
  "sections": [ ... ]
}
```

---

### 3.2 Submit Form

```
POST /api/v1/surveys/{slug}/submit
Content-Type: application/json
Authorization: Bearer <token>
```

**Request body:**
```json
{
  "respondent_ref": "nguyen-van-a@company.com",
  "answers": [
    { "field_key": "company_name", "value": "Công ty ABC" },
    { "field_key": "industry",     "value": 12 },
    { "field_key": "ai_tools_used","value": [14, 15, 16] },
    { "field_key": "ai_concerns",  "value": [22], "other_text": "Lo ngại về bảo mật" }
  ]
}
```

> `respondent_ref` — định danh người dùng (email, UUID, ID CRM, v.v.). Nullable.  
> `answers` — mảng câu trả lời, xem chi tiết ở [Mục 6](#6-submit-payload--cách-đóng-gói-câu-trả-lời).

**Response 201 — Thành công:**
```json
{ "response_id": 42 }
```

**Response 422 — Validation lỗi:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "company_name": ["Trường này là bắt buộc."],
    "contact_email": ["Đây là trường bắt buộc."]
  }
}
```

**Response 403 — Survey không active:**
```json
{ "message": "Survey is not accepting responses." }
```

> **Rate limit:** 60 requests/phút per token trên endpoint submit.

---

### 3.3 Các endpoint khác (tham khảo)

| Method | Path | Mô tả |
|--------|------|--------|
| `GET` | `/api/v1/surveys/{slug}/stats` | Thống kê tổng quan (tổng phản hồi, theo ngày) |
| `GET` | `/api/v1/surveys/{slug}/responses` | Danh sách phản hồi đã submit |

---

## 4. Schema Response — Cấu trúc JSON

### 4.1 Cấu trúc đầy đủ

```json
{
  "id": 195,
  "title": "Bộ Khảo Sát AI Readiness & Workflow",
  "slug": "ai-readiness-workflow",
  "version": 1,
  "sections": [
    {
      "id": 2,
      "title": "Thông tin doanh nghiệp",
      "icon": "🏢",
      "sort_order": 1,
      "fields": [
        {
          "id": 644,
          "parent_field_id": null,
          "field_key": "company_name",
          "label": "1.1 Tên doanh nghiệp",
          "field_type": 1,
          "value_kind": 1,
          "is_required": true,
          "sort_order": 1,
          "rule_min": null,
          "rule_max": null,
          "rule_max_select": null,
          "placeholder": "Nhập tên doanh nghiệp",
          "options": []
        },
        {
          "id": 645,
          "parent_field_id": null,
          "field_key": "industry",
          "label": "1.2 Ngành nghề",
          "field_type": 4,
          "value_kind": 6,
          "is_required": true,
          "sort_order": 2,
          "rule_min": null,
          "rule_max": null,
          "rule_max_select": null,
          "placeholder": null,
          "options": [
            { "id": 1, "option_value": "agriculture_food", "label": "Nông nghiệp / Thực phẩm", "sort_order": 1, "is_other": false },
            { "id": 2, "option_value": "retail_ecommerce",  "label": "Bán lẻ / TMĐT",           "sort_order": 2, "is_other": false },
            { "id": 7, "option_value": "other",             "label": "Khác",                     "sort_order": 7, "is_other": true  }
          ]
        },
        {
          "id": 650,
          "parent_field_id": null,
          "field_key": "ai_tools_used",
          "label": "6.1 Đã từng sử dụng AI ở công cụ nào?",
          "field_type": 6,
          "value_kind": 6,
          "is_required": false,
          "sort_order": 1,
          "rule_min": null,
          "rule_max": null,
          "rule_max_select": null,
          "placeholder": null,
          "options": [
            { "id": 80, "option_value": "chatgpt",  "label": "ChatGPT",  "sort_order": 1, "is_other": false },
            { "id": 81, "option_value": "gemini",   "label": "Gemini",   "sort_order": 2, "is_other": false }
          ]
        },
        {
          "id": 701,
          "parent_field_id": null,
          "field_key": "system_satisfaction",
          "label": "5.7 Hệ thống hiện đáp ứng nhu cầu ở mức độ nào?",
          "field_type": 7,
          "value_kind": 3,
          "is_required": false,
          "sort_order": 7,
          "rule_min": 1,
          "rule_max": 5,
          "rule_max_select": null,
          "placeholder": null,
          "options": []
        }
      ]
    }
  ]
}
```

### 4.2 Giải thích từng trường

| Trường | Kiểu | Mô tả |
|--------|------|--------|
| `field_key` | string | **Key duy nhất** — dùng làm key trong `answers[]` khi submit |
| `field_type` | int | Loại input cần render — xem bảng ở Mục 5 |
| `value_kind` | int | Kiểu dữ liệu khi lưu — xem bảng ở Mục 5 |
| `is_required` | bool | Bắt buộc điền hay không |
| `rule_min` | int\|null | Giá trị tối thiểu (cho Number, Rating) hoặc độ dài tối thiểu (cho Text) |
| `rule_max` | int\|null | Giá trị tối đa (cho Number, Rating) hoặc độ dài tối đa (cho Text) |
| `rule_max_select` | int\|null | Số lựa chọn tối đa cho Checkbox |
| `parent_field_id` | int\|null | ID field cha — field con chỉ hiển thị khi field cha có giá trị cụ thể |
| `options[].id` | int | **ID option** — đây là `value` cần gửi khi submit choice fields |
| `options[].option_value` | string | Machine key (không gửi khi submit, chỉ dùng UI logic nếu cần) |
| `options[].is_other` | bool | Option "Khác" — cho phép kèm `other_text` khi chọn |

> **Quan trọng:** Với choice fields (Select, Radio, Checkbox), giá trị cần gửi khi submit là **`option.id`** (integer), **không phải** `option_value` (string).

---

## 5. Field Types — Bảng tra cứu

### 5.1 field_type enum

| `field_type` | Tên | Render | `value` khi submit |
|-------------|-----|--------|-------------------|
| `1` | Text | `<input type="text">` | `"chuỗi văn bản"` |
| `2` | Textarea | `<textarea>` | `"văn bản dài..."` |
| `3` | Number | `<input type="number">` | `42` (number) |
| `4` | Select | `<select>` dropdown | `12` (option id) |
| `5` | Radio | Radio buttons | `12` (option id) |
| `6` | Checkbox | Checkboxes (chọn nhiều) | `[12, 13, 15]` (mảng option id) |
| `7` | Rating | Star rating / 1-5 scale | `4` (number) |
| `8` | Date | `<input type="date">` | `"2025-12-31"` (YYYY-MM-DD) |
| `9` | Boolean | Toggle / Yes-No | `true` hoặc `false` |

### 5.2 value_kind enum

| `value_kind` | Kiểu dữ liệu | Ghi chú |
|-------------|-------------|---------|
| `1` | String | Text, Textarea ngắn |
| `2` | Text | Textarea dài |
| `3` | Number | Number, Rating |
| `4` | Date | Ngày tháng |
| `5` | Bool | Boolean |
| `6` | Option | Choice — value là option.id (int hoặc int[]) |

### 5.3 Quy tắc submit theo field_type

```
field_type 1 (Text)      → value: "string"
field_type 2 (Textarea)  → value: "string dài"
field_type 3 (Number)    → value: 123
field_type 4 (Select)    → value: <option.id>          // 1 số nguyên
field_type 5 (Radio)     → value: <option.id>          // 1 số nguyên
field_type 6 (Checkbox)  → value: [<id1>, <id2>, ...]  // mảng số nguyên
field_type 7 (Rating)    → value: 4                    // số từ rule_min đến rule_max
field_type 8 (Date)      → value: "2025-12-31"         // format YYYY-MM-DD
field_type 9 (Boolean)   → value: true | false
```

**Field bỏ trống (không bắt buộc):** Không đưa vào `answers[]` hoặc gửi `value: null`.  
**Option "Khác" (is_other: true):** Gửi `id` của option đó + thêm `"other_text": "nội dung nhập tay"`.

---

## 6. Submit Payload — Cách đóng gói câu trả lời

### 6.1 Cấu trúc tổng quát

```json
{
  "respondent_ref": "email-hoac-uuid-cua-nguoi-dung",
  "answers": [
    {
      "field_key":  "field_key_trong_schema",
      "value":       <giá trị phụ thuộc field_type>,
      "other_text": "chỉ dùng khi chọn option is_other=true"
    }
  ]
}
```

### 6.2 Ví dụ từng loại field

```json
{
  "respondent_ref": "nguyen-van-a@company.com",
  "answers": [

    // field_type 1 — Text
    { "field_key": "company_name", "value": "Công ty TNHH ABC" },

    // field_type 2 — Textarea
    { "field_key": "sales_process", "value": "Tìm kiếm lead → Gọi điện → Demo → Chốt hợp đồng" },

    // field_type 4 — Select (gửi option.id)
    { "field_key": "industry", "value": 2 },

    // field_type 5 — Radio (gửi option.id)
    { "field_key": "workflow_mode", "value": 5 },

    // field_type 6 — Checkbox (gửi mảng option.id)
    { "field_key": "ai_tools_used", "value": [80, 81, 82] },

    // field_type 6 — Checkbox với option "Khác"
    { "field_key": "customer_sources", "value": [101, 108], "other_text": "Hội thảo offline" },

    // field_type 7 — Rating
    { "field_key": "system_satisfaction", "value": 3 },

    // field_type 3 — Number
    { "field_key": "some_number_field", "value": 42 },

    // field_type 8 — Date
    { "field_key": "some_date_field", "value": "2025-06-15" },

    // field_type 9 — Boolean
    { "field_key": "contact_consent", "value": true }
  ]
}
```

### 6.3 Quy tắc validation phía server

Server validate **sau** khi nhận submit:
- `is_required: true` → `value` không được null/empty/array rỗng
- `rule_min / rule_max` → áp dụng cho Number, Rating, độ dài Text
- `rule_max_select` → giới hạn số phần tử trong mảng Checkbox
- `field_key` phải tồn tại trong survey (fields không thuộc survey bị bỏ qua)
- Option `id` phải thuộc field đó (option của field khác bị reject)

---

## 7. Xử lý lỗi từ API

```javascript
async function submitSurvey(payload) {
  try {
    const res = await fetch(`${BASE_URL}/api/v1/surveys/${SLUG}/submit`, {
      method: 'POST',
      headers: {
        'Content-Type':  'application/json',
        'Authorization': `Bearer ${BEARER_TOKEN}`,
        'Accept':        'application/json',
      },
      body: JSON.stringify(payload),
    });

    if (res.status === 201) {
      const data = await res.json();
      return { ok: true, responseId: data.response_id };
    }

    if (res.status === 422) {
      const data = await res.json();
      // data.errors = { field_key: ["message"], ... }
      return { ok: false, errors: data.errors };
    }

    if (res.status === 403) {
      return { ok: false, message: 'Khảo sát đã đóng.' };
    }

    if (res.status === 401) {
      return { ok: false, message: 'Token không hợp lệ.' };
    }

    return { ok: false, message: `Lỗi không xác định: ${res.status}` };

  } catch (e) {
    return { ok: false, message: 'Lỗi kết nối. Vui lòng thử lại.' };
  }
}
```

---

## 8. Frontend Implementation — Alpine.js + DaisyUI

### 8.1 Cấu trúc HTML tổng quan

```html
<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Khảo sát</title>
  <!-- DaisyUI + Tailwind CSS -->
  <link href="https://cdn.jsdelivr.net/npm/daisyui@5/dist/full.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Alpine.js -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
</head>
<body class="bg-base-200 min-h-screen">
  <div x-data="surveyForm" x-init="init()" class="max-w-3xl mx-auto py-10 px-4">

    <!-- Loading skeleton -->
    <div x-show="loading" class="text-center py-20">
      <span class="loading loading-spinner loading-lg text-primary"></span>
      <p class="mt-4 text-base-content/60">Đang tải khảo sát...</p>
    </div>

    <!-- Error state -->
    <div x-show="loadError" class="alert alert-error">
      <span x-text="loadError"></span>
    </div>

    <!-- Survey form -->
    <template x-if="schema && !loading && !submitted">
      <div>
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-black text-base-content" x-text="schema.title"></h1>

          <!-- Progress bar -->
          <div class="mt-4">
            <div class="flex justify-between text-sm text-base-content/60 mb-1">
              <span>Phần <span x-text="currentStep + 1"></span> / <span x-text="schema.sections.length"></span></span>
              <span x-text="Math.round(progress) + '%'"></span>
            </div>
            <progress class="progress progress-primary w-full" :value="progress" max="100"></progress>
          </div>
        </div>

        <!-- Section tabs (sidebar dạng steps) -->
        <ul class="steps steps-vertical lg:steps-horizontal w-full mb-8 hidden lg:flex">
          <template x-for="(sec, i) in schema.sections" :key="sec.id">
            <li class="step" :class="i <= currentStep ? 'step-primary' : ''"
                @click="goToStep(i)" style="cursor:pointer">
              <span x-text="sec.icon || (i+1)"></span>
            </li>
          </template>
        </ul>

        <!-- Current section -->
        <template x-if="currentSection">
          <div class="card bg-base-100 shadow-lg">
            <div class="card-body">

              <!-- Section title -->
              <h2 class="card-title text-xl mb-6">
                <span x-text="currentSection.icon"></span>
                <span x-text="currentSection.title"></span>
              </h2>

              <!-- Fields -->
              <div class="space-y-6">
                <template x-for="field in currentSection.fields" :key="field.id">
                  <div x-show="isFieldVisible(field)">
                    <label class="block mb-2 font-semibold text-sm">
                      <span x-text="field.label"></span>
                      <span x-show="field.is_required" class="text-error ml-1">*</span>
                    </label>

                    <!-- field_type 1: Text -->
                    <template x-if="field.field_type === 1">
                      <input type="text" class="input input-bordered w-full"
                             :class="errors[field.field_key] ? 'input-error' : ''"
                             :placeholder="field.placeholder || ''"
                             x-model="answers[field.field_key]">
                    </template>

                    <!-- field_type 2: Textarea -->
                    <template x-if="field.field_type === 2">
                      <textarea class="textarea textarea-bordered w-full min-h-[100px]"
                                :class="errors[field.field_key] ? 'textarea-error' : ''"
                                :placeholder="field.placeholder || ''"
                                x-model="answers[field.field_key]"></textarea>
                    </template>

                    <!-- field_type 3: Number -->
                    <template x-if="field.field_type === 3">
                      <input type="number" class="input input-bordered w-full"
                             :min="field.rule_min" :max="field.rule_max"
                             :class="errors[field.field_key] ? 'input-error' : ''"
                             x-model.number="answers[field.field_key]">
                    </template>

                    <!-- field_type 4: Select dropdown -->
                    <template x-if="field.field_type === 4">
                      <select class="select select-bordered w-full"
                              :class="errors[field.field_key] ? 'select-error' : ''"
                              x-model.number="answers[field.field_key]">
                        <option :value="null">-- Chọn --</option>
                        <template x-for="opt in field.options" :key="opt.id">
                          <option :value="opt.id" x-text="opt.label"></option>
                        </template>
                      </select>
                    </template>

                    <!-- field_type 5: Radio -->
                    <template x-if="field.field_type === 5">
                      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <template x-for="opt in field.options" :key="opt.id">
                          <label class="label cursor-pointer gap-3 border border-base-200 rounded-lg px-4 py-2 hover:bg-base-100"
                                 :class="answers[field.field_key] === opt.id ? 'border-primary bg-primary/5' : ''">
                            <input type="radio" class="radio radio-primary"
                                   :name="field.field_key"
                                   :value="opt.id"
                                   :checked="answers[field.field_key] === opt.id"
                                   @change="answers[field.field_key] = opt.id">
                            <span class="label-text flex-1" x-text="opt.label"></span>
                          </label>
                        </template>
                      </div>
                    </template>

                    <!-- field_type 6: Checkbox (multi-select) -->
                    <template x-if="field.field_type === 6">
                      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <template x-for="opt in field.options" :key="opt.id">
                          <label class="label cursor-pointer gap-3 border border-base-200 rounded-lg px-4 py-2 hover:bg-base-100"
                                 :class="isChecked(field.field_key, opt.id) ? 'border-primary bg-primary/5' : ''">
                            <input type="checkbox" class="checkbox checkbox-primary"
                                   :checked="isChecked(field.field_key, opt.id)"
                                   @change="toggleCheck(field, opt)">
                            <span class="label-text flex-1" x-text="opt.label"></span>
                          </label>
                        </template>
                        <!-- Sub-input cho option "Khác" -->
                        <template x-if="hasOtherSelected(field)">
                          <div class="col-span-full">
                            <input type="text" class="input input-bordered input-sm w-full"
                                   placeholder="Vui lòng mô tả thêm..."
                                   x-model="otherTexts[field.field_key]">
                          </div>
                        </template>
                      </div>
                      <!-- Hiển thị giới hạn chọn nếu có -->
                      <p x-show="field.rule_max_select" class="text-xs text-base-content/50 mt-1">
                        Chọn tối đa <span x-text="field.rule_max_select"></span> mục
                      </p>
                    </template>

                    <!-- field_type 7: Rating -->
                    <template x-if="field.field_type === 7">
                      <div class="flex gap-2 items-center flex-wrap">
                        <template x-for="n in ratingRange(field)" :key="n">
                          <button type="button"
                                  class="btn btn-sm"
                                  :class="answers[field.field_key] === n ? 'btn-primary' : 'btn-outline'"
                                  @click="answers[field.field_key] = n"
                                  x-text="n">
                          </button>
                        </template>
                        <span class="text-xs text-base-content/50 ml-2"
                              x-text="ratingLabel(field)"></span>
                      </div>
                    </template>

                    <!-- field_type 8: Date -->
                    <template x-if="field.field_type === 8">
                      <input type="date" class="input input-bordered w-full"
                             :class="errors[field.field_key] ? 'input-error' : ''"
                             x-model="answers[field.field_key]">
                    </template>

                    <!-- field_type 9: Boolean -->
                    <template x-if="field.field_type === 9">
                      <div class="flex gap-4">
                        <label class="label cursor-pointer gap-2">
                          <input type="radio" class="radio radio-success"
                                 :name="field.field_key"
                                 :checked="answers[field.field_key] === true"
                                 @change="answers[field.field_key] = true">
                          <span class="label-text">Có</span>
                        </label>
                        <label class="label cursor-pointer gap-2">
                          <input type="radio" class="radio radio-error"
                                 :name="field.field_key"
                                 :checked="answers[field.field_key] === false"
                                 @change="answers[field.field_key] = false">
                          <span class="label-text">Không</span>
                        </label>
                      </div>
                    </template>

                    <!-- Error message -->
                    <p x-show="errors[field.field_key]" class="text-error text-xs mt-1"
                       x-text="errors[field.field_key]?.[0]"></p>
                  </div>
                </template>
              </div>

              <!-- Navigation buttons -->
              <div class="card-actions justify-between mt-8">
                <button type="button" class="btn btn-ghost"
                        x-show="currentStep > 0"
                        @click="prevStep()">
                  ← Quay lại
                </button>
                <div class="flex-1"></div>
                <button type="button" class="btn btn-primary"
                        x-show="currentStep < schema.sections.length - 1"
                        @click="nextStep()">
                  Tiếp theo →
                </button>
                <button type="button" class="btn btn-success"
                        x-show="currentStep === schema.sections.length - 1"
                        :disabled="submitting"
                        @click="submitForm()">
                  <span x-show="!submitting">🚀 Gửi khảo sát</span>
                  <span x-show="submitting" class="loading loading-spinner loading-sm"></span>
                </button>
              </div>

            </div>
          </div>
        </template>

      </div>
    </template>

    <!-- Success state -->
    <template x-if="submitted">
      <div class="card bg-base-100 shadow-lg text-center">
        <div class="card-body py-16">
          <div class="text-6xl mb-4">🎉</div>
          <h2 class="text-2xl font-bold text-success">Cảm ơn bạn!</h2>
          <p class="text-base-content/60 mt-2">Khảo sát đã được gửi thành công.</p>
          <p class="text-xs text-base-content/40 mt-1">Mã phản hồi: #<span x-text="responseId"></span></p>
        </div>
      </div>
    </template>

  </div>

  <script>
  // ── Cấu hình ─────────────────────────────────────────────────────────────────
  const SURVEY_CONFIG = {
    baseUrl:      'https://your-crm.com',
    slug:         'ai-readiness-workflow',
    bearerToken:  'YOUR_BEARER_TOKEN_HERE',
    respondentRef: null,   // Set nếu biết email/id người dùng (optional)
  };

  // ── Alpine.js Component ───────────────────────────────────────────────────────
  document.addEventListener('alpine:init', () => {
    Alpine.data('surveyForm', () => ({
      // State
      loading:     true,
      loadError:   null,
      submitted:   false,
      submitting:  false,
      responseId:  null,
      schema:      null,
      currentStep: 0,

      // Form data
      answers:    {},   // { field_key: value }
      otherTexts: {},   // { field_key: "text khi chọn option is_other" }
      errors:     {},   // { field_key: ["error message"] }

      // ── Computed ──────────────────────────────────────────────────────────
      get currentSection() {
        return this.schema?.sections?.[this.currentStep] ?? null;
      },

      get progress() {
        if (!this.schema) return 0;
        return ((this.currentStep + 1) / this.schema.sections.length) * 100;
      },

      // ── Lifecycle ─────────────────────────────────────────────────────────
      async init() {
        await this.loadSchema();
      },

      // ── API calls ─────────────────────────────────────────────────────────
      async loadSchema() {
        this.loading   = true;
        this.loadError = null;
        try {
          const res = await fetch(
            `${SURVEY_CONFIG.baseUrl}/api/v1/surveys/${SURVEY_CONFIG.slug}/schema`,
            {
              headers: {
                'Authorization': `Bearer ${SURVEY_CONFIG.bearerToken}`,
                'Accept':        'application/json',
              },
            }
          );
          if (!res.ok) throw new Error(`HTTP ${res.status}`);
          this.schema = await res.json();
          this.initAnswers();
        } catch (e) {
          this.loadError = 'Không thể tải khảo sát. Vui lòng thử lại sau.';
          console.error('[survey] loadSchema error:', e);
        } finally {
          this.loading = false;
        }
      },

      // Khởi tạo giá trị mặc định cho từng field
      initAnswers() {
        for (const section of this.schema.sections) {
          for (const field of section.fields) {
            if (field.field_type === 6) {
              // Checkbox → mảng rỗng
              this.answers[field.field_key] = [];
            } else {
              this.answers[field.field_key] = null;
            }
          }
        }
      },

      async submitForm() {
        this.errors = {};

        // Client-side validate section cuối trước khi submit
        if (!this.validateSection(this.currentSection)) return;

        this.submitting = true;
        try {
          const payload = this.buildPayload();
          const res = await fetch(
            `${SURVEY_CONFIG.baseUrl}/api/v1/surveys/${SURVEY_CONFIG.slug}/submit`,
            {
              method:  'POST',
              headers: {
                'Content-Type':  'application/json',
                'Authorization': `Bearer ${SURVEY_CONFIG.bearerToken}`,
                'Accept':        'application/json',
              },
              body: JSON.stringify(payload),
            }
          );

          if (res.status === 201) {
            const data  = await res.json();
            this.responseId = data.response_id;
            this.submitted  = true;
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
          }

          if (res.status === 422) {
            const data = await res.json();
            this.errors = data.errors ?? {};
            // Scroll đến field lỗi đầu tiên
            const firstErrKey = Object.keys(this.errors)[0];
            if (firstErrKey) {
              this.$nextTick(() => {
                const el = document.querySelector(`[data-field="${firstErrKey}"]`);
                el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
              });
            }
            return;
          }

          if (res.status === 403) {
            this.loadError = 'Khảo sát đã đóng, không nhận thêm phản hồi.';
            return;
          }

          throw new Error(`Unexpected status: ${res.status}`);
        } catch (e) {
          this.loadError = 'Lỗi kết nối. Vui lòng kiểm tra mạng và thử lại.';
          console.error('[survey] submit error:', e);
        } finally {
          this.submitting = false;
        }
      },

      // ── Navigation ────────────────────────────────────────────────────────
      nextStep() {
        if (!this.validateSection(this.currentSection)) return;
        this.errors = {};
        if (this.currentStep < this.schema.sections.length - 1) {
          this.currentStep++;
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      },

      prevStep() {
        this.errors = {};
        if (this.currentStep > 0) {
          this.currentStep--;
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      },

      goToStep(i) {
        if (i <= this.currentStep) {
          this.errors = {};
          this.currentStep = i;
        }
      },

      // ── Validation ────────────────────────────────────────────────────────
      validateSection(section) {
        if (!section) return true;
        let valid = true;

        for (const field of section.fields) {
          if (!this.isFieldVisible(field)) continue;
          const val = this.answers[field.field_key];
          const key = field.field_key;

          // Required check
          if (field.is_required) {
            const isEmpty = val === null || val === '' || val === undefined
              || (Array.isArray(val) && val.length === 0);
            if (isEmpty) {
              this.errors[key] = ['Trường này là bắt buộc.'];
              valid = false;
              continue;
            }
          }

          // rule_min / rule_max cho Text / Number
          if (val !== null && val !== '' && val !== undefined) {
            if ([1, 2].includes(field.field_type) && typeof val === 'string') {
              if (field.rule_min && val.length < field.rule_min)
                this.errors[key] = [`Nhập tối thiểu ${field.rule_min} ký tự.`], valid = false;
              if (field.rule_max && val.length > field.rule_max)
                this.errors[key] = [`Nhập tối đa ${field.rule_max} ký tự.`], valid = false;
            }
            if ([3, 7].includes(field.field_type) && typeof val === 'number') {
              if (field.rule_min !== null && val < field.rule_min)
                this.errors[key] = [`Giá trị tối thiểu là ${field.rule_min}.`], valid = false;
              if (field.rule_max !== null && val > field.rule_max)
                this.errors[key] = [`Giá trị tối đa là ${field.rule_max}.`], valid = false;
            }
          }

          // rule_max_select cho Checkbox
          if (field.field_type === 6 && field.rule_max_select && Array.isArray(val)) {
            if (val.length > field.rule_max_select)
              this.errors[key] = [`Chọn tối đa ${field.rule_max_select} mục.`], valid = false;
          }
        }
        return valid;
      },

      // ── Helpers ───────────────────────────────────────────────────────────

      // Field con chỉ hiện khi field cha có giá trị
      isFieldVisible(field) {
        if (!field.parent_field_id) return true;
        // Tìm trong toàn bộ schema
        for (const sec of this.schema.sections) {
          const parent = sec.fields.find(f => f.id === field.parent_field_id);
          if (parent) {
            const val = this.answers[parent.field_key];
            return val !== null && val !== '' && val !== undefined
              && !(Array.isArray(val) && val.length === 0);
          }
        }
        return true;
      },

      // Checkbox helpers
      isChecked(fieldKey, optionId) {
        const arr = this.answers[fieldKey];
        return Array.isArray(arr) && arr.includes(optionId);
      },

      toggleCheck(field, opt) {
        if (!Array.isArray(this.answers[field.field_key])) {
          this.answers[field.field_key] = [];
        }
        const arr = this.answers[field.field_key];
        const idx = arr.indexOf(opt.id);
        if (idx > -1) {
          arr.splice(idx, 1);
          // Xóa other_text nếu bỏ chọn option is_other
          if (opt.is_other) delete this.otherTexts[field.field_key];
        } else {
          // Kiểm tra rule_max_select
          if (field.rule_max_select && arr.length >= field.rule_max_select) return;
          arr.push(opt.id);
        }
      },

      hasOtherSelected(field) {
        const arr = this.answers[field.field_key];
        if (!Array.isArray(arr)) return false;
        return field.options.some(o => o.is_other && arr.includes(o.id));
      },

      // Rating helpers
      ratingRange(field) {
        const min = field.rule_min ?? 1;
        const max = field.rule_max ?? 5;
        return Array.from({ length: max - min + 1 }, (_, i) => min + i);
      },

      ratingLabel(field) {
        const min = field.rule_min ?? 1;
        const max = field.rule_max ?? 5;
        return `${min} = Rất kém  •  ${max} = Rất tốt`;
      },

      // Build payload để submit
      buildPayload() {
        const answersArr = [];

        for (const [key, val] of Object.entries(this.answers)) {
          // Bỏ qua field bỏ trống (không bắt buộc)
          if (val === null || val === '' || val === undefined) continue;
          if (Array.isArray(val) && val.length === 0) continue;

          const entry = { field_key: key, value: val };

          // Đính kèm other_text nếu có
          if (this.otherTexts[key]) {
            entry.other_text = this.otherTexts[key];
          }

          answersArr.push(entry);
        }

        return {
          respondent_ref: SURVEY_CONFIG.respondentRef,
          answers:        answersArr,
        };
      },
    }));
  });
  </script>
</body>
</html>
```

---

## 9. UX Patterns chuẩn

### 9.1 Multi-step navigation

- Mỗi **section** = 1 bước — không cuộn dài, không overwhelm người dùng
- Validate section hiện tại **trước khi** chuyển sang section tiếp theo
- Cho phép quay lại các bước đã hoàn thành (click step trên progress bar)
- Auto scroll lên đầu khi chuyển bước

### 9.2 Hiển thị lỗi

- Highlight input bằng class `input-error` / `select-error` / `textarea-error` của DaisyUI
- Text lỗi màu đỏ ngay dưới field
- Scroll đến field lỗi đầu tiên sau submit

### 9.3 Field cha-con (`parent_field_id`)

```javascript
// Hiện field con chỉ khi field cha có giá trị
isFieldVisible(field) {
  if (!field.parent_field_id) return true;
  // Tìm giá trị của field cha, return false nếu rỗng
}
```

Ví dụ: Field "Tên CRM đang dùng" chỉ hiện khi "Đang dùng CRM chưa?" = "Có".

### 9.4 Option "Khác" (`is_other: true`)

Khi người dùng chọn option có `is_other: true`:
- Hiển thị thêm `<input type="text">` để nhập nội dung tùy chỉnh
- Khi submit, đính kèm `"other_text": "nội dung"` vào answer đó

### 9.5 Loading states

| Trạng thái | UI |
|-----------|----|
| Đang tải schema | Spinner + text "Đang tải khảo sát..." |
| Đang submit | Button disabled + spinner nhỏ |
| Submit thành công | Ẩn form, hiện card cảm ơn + `response_id` |
| Lỗi tải schema | Alert error với thông báo |
| Lỗi submit 422 | Highlight field lỗi + text message |

### 9.6 Respondent Ref — Liên kết vào CRM

`respondent_ref` là trường tùy chọn nhưng **quan trọng** để liên kết phản hồi với khách hàng trong CRM:

```javascript
// Lấy từ URL param nếu gửi link cá nhân hóa
const urlParams = new URLSearchParams(window.location.search);
SURVEY_CONFIG.respondentRef = urlParams.get('ref') || urlParams.get('email') || null;
```

Ví dụ link cá nhân hóa:
```
https://yoursite.com/khao-sat?ref=nguyen-van-a@company.com
```

---

## 10. Checklist tích hợp

### Thiết lập

- [ ] Tạo token trong CRM: Dashboard → Khảo sát → [Survey] → Quản lý Token
- [ ] Điền `SURVEY_CONFIG.baseUrl`, `slug`, `bearerToken` vào file HTML
- [ ] Verify gọi `/schema` trả về đúng survey (check `title`, `slug`)

### Render form

- [ ] Render đúng field theo `field_type` (1-9)
- [ ] Với choice fields, dùng `option.id` (int) làm value — **không phải** `option_value` (string)
- [ ] Checkbox: khởi tạo `answers[key] = []` (mảng), không phải `null`
- [ ] Rating: dùng `rule_min` và `rule_max` để tạo range nút bấm
- [ ] Option `is_other: true`: hiển thị input text phụ, đính kèm `other_text` khi submit
- [ ] Field con (`parent_field_id`): ẩn/hiện theo giá trị field cha

### Validation

- [ ] Client-side validate `is_required` trước khi next step
- [ ] Validate `rule_min / rule_max` cho Text (độ dài) và Number/Rating (giá trị)
- [ ] Validate `rule_max_select` cho Checkbox
- [ ] Hiển thị lỗi server 422 đúng field (`errors[field_key]`)

### Submit

- [ ] `answers[].value` đúng kiểu dữ liệu theo field_type
- [ ] Bỏ qua field bỏ trống (không đưa vào `answers[]`)
- [ ] Xử lý response 201 (success), 422 (validation), 403 (closed), 401 (auth)
- [ ] Hiện trạng thái loading khi đang submit (disable button)
- [ ] Hiện màn hình cảm ơn + `response_id` sau submit thành công

### UX

- [ ] Progress bar cập nhật theo step hiện tại
- [ ] Validate trước khi next step (không cho qua nếu còn lỗi)
- [ ] Cho phép quay lại step trước
- [ ] Auto scroll lên đầu khi chuyển step / sau submit
- [ ] Responsive mobile (grid 1 cột trên mobile, 2 cột trên desktop)

---

## Ghi chú kỹ thuật

- **Cache schema:** Schema được cache 30 phút phía server. Khi admin sửa field/option, cache tự purge.
- **Idempotency:** Cùng `respondent_ref` có thể submit nhiều lần (server log warning nếu trong 5 phút). Không có dedup tự động.
- **Turnstile CAPTCHA:** Endpoint submit có middleware Turnstile. Nếu server yêu cầu, thêm header `cf-turnstile-response` từ Cloudflare widget.
- **CORS:** API cần được cấu hình CORS cho phép domain của website ngoài. Liên hệ admin CRM để whitelist domain.
- **Rate limit:** 60 req/phút per token cho submit. Schema không có rate limit.
