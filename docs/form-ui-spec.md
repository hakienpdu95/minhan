# Form UI/UX Specification — Backend SaaS

> **Version:** 4.0  
> **Stack:** Laravel 13 · DaisyUI 5 · Tailwind CSS 4 · Alpine.js 3 · TomSelect · SCSS (sass) · Vite 8  
> **Gold Standard:** `Modules/Organization/resources/views/`  
> **Build:** `vite.config.backend.js` — **một build duy nhất** cho toàn backend

---

## Mục lục

1. [Triết lý thiết kế](#1-triết-lý-thiết-kế)
2. [Kiến trúc tổng thể](#2-kiến-trúc-tổng-thể)
3. [SCSS — Shared partials & Module](#3-scss)
4. [JS — Shared utils & Module page controllers](#4-js)
5. [Vite build — Cách đăng ký module mới](#5-vite-build)
6. [Blade — Cách load asset](#6-blade)
7. [Page Shell](#7-page-shell)
8. [Form Wrapper](#8-form-wrapper)
9. [Card Section](#9-card-section)
10. [Grid & bố cục cột](#10-grid)
11. [Form Control — cấu trúc bắt buộc](#11-form-control)
12. [Input types](#12-input-types)
13. [Validation](#13-validation)
14. [Interactive states & Loading](#14-interactive-states)
15. [Submit Actions Bar](#15-submit-bar)
16. [Wizard Multi-step](#16-wizard)
17. [TomSelect](#17-tomselect)
18. [Ngôn ngữ](#18-ngôn-ngữ)
19. [Class reference](#19-class-reference)
20. [Anti-patterns](#20-anti-patterns)
21. [Checklist trước khi merge](#21-checklist)

---

## 1. Triết lý thiết kế

| Nguyên tắc | Biểu hiện cụ thể |
|---|---|
| **Clarity first** | Label rõ, placeholder có ví dụ, hint đúng chỗ |
| **Progressive disclosure** | Form ≥3 nhóm không liên quan → wizard |
| **Immediate feedback** | Lỗi hiện ngay tại field sau blur |
| **Spatial consistency** | Cùng loại element → cùng size, spacing, màu trên mọi module |
| **Mobile first** | 1 cột mobile, 2 cột desktop |

**Hệ thống kích thước — không tùy biến theo module:**
```
Input height:    input-sm  = 2.25rem (36px)
Form max-width:  max-w-3xl = 48rem  (768px)
Card gap:        space-y-4 = 1rem   (16px)
Field gap:       gap-4     = 1rem   (16px)
Label→Input:     pb-1.5    = 6px
```

---

## 2. Kiến trúc tổng thể

### 2.1 Sơ đồ cây file

```
minhan/
├── vite.config.backend.js          ← Build duy nhất cho toàn backend
│
├── resources/
│   ├── css/app.css                 ← [CORE] Tailwind 4 + DaisyUI 5 + admin shell
│   │
│   ├── scss/                       ← [SHARED] Partials dùng bởi mọi module
│   │   ├── _tokens.scss            ←   DaisyUI CSS vars → SCSS vars
│   │   ├── _mixins.scss            ←   Mixins: input-base, card-base, md()...
│   │   ├── _form-patterns.scss     ←   Classes: .color-picker-combo, .wizard-step-dot...
│   │   └── _tom-select.scss        ←   TomSelect dark/light theme override
│   │
│   └── js/
│       ├── app.js                  ← [CORE] jQuery, Alpine, Iconify, form-validation
│       ├── modules/                ← [WIDGET LIBS] lazy per-page
│       │   ├── tom-select.js       ←   initTomSelect, initOrgAddress (global)
│       │   ├── jodit.js            ←   initJoditAll (global)
│       │   ├── tabulator.js        ←   initTabulator (global)
│       │   └── ...
│       └── shared/                 ← [SHARED] Utilities dùng bởi module page controllers
│           ├── form-controller.js  ←   makeFormController()
│           ├── wizard-controller.js←   makeWizardController()
│           └── tom-select-factory.js← createTs(), createTsRemote(), createTsAssignee()
│
└── Modules/
    └── [Name]/
        └── resources/
            ├── assets/
            │   ├── sass/           ← Module SCSS (dùng sass/, không tạo scss/)
            │   │   ├── [name].scss ←   Entry point (đăng ký trong vite.config.backend.js)
            │   │   └── _[name]-components.scss ← CSS đặc thù module
            │   └── js/
            │       ├── [name].js   ←   Entry point (đăng ký trong vite.config.backend.js)
            │       └── pages/
            │           ├── [entity]-form.js    ← Alpine controller form
            │           └── [entity]-index.js   ← Alpine controller listing
            └── views/
```

### 2.2 Build output (`public/build/backend/`)

```
assets/
├── app.[hash].css          ← Core CSS (Tailwind + DaisyUI + shell)
├── app.[hash].js           ← Core JS (jQuery + Alpine + Iconify)
├── shared-utils.[hash].js  ← Shared JS utilities (auto chunk)
├── vendor-*.js/css         ← Vendor chunks (browser cache độc lập)
├── tom-select.[hash].js    ← Widget lib lazy
├── jodit.[hash].js
├── tabulator.[hash].js
├── ...
└── modules/
    ├── lead.[hash].css     ← Module Lead CSS (từ lead.scss)
    ├── lead.[hash].js      ← Module Lead JS  (từ lead.js)
    ├── user.[hash].css
    └── user.[hash].js
```

### 2.3 Nguyên tắc phân tầng

```
Tầng 0 — Core (tải TOÀN BỘ trang, không lazy)
    app.css + app.js
    → Tailwind utilities, DaisyUI components, admin shell layout
    → jQuery, Alpine, Iconify, initFormValidation (globals)

Tầng 1 — Shared SCSS (resources/scss/_*.scss)
    KHÔNG tự build thành file riêng
    → @use bởi module SCSS → merge vào module CSS khi build
    → Chứa token, mixin, pattern chung — không có class module-specific

Tầng 2 — Shared JS (resources/js/shared/)
    Build thành shared-utils.[hash].js (1 chunk, tự động dedup)
    → Import qua alias @shared từ module page controllers
    → Tree-shaken: chỉ code được import mới vào bundle

Tầng 3 — Widget libs (resources/js/modules/)
    Lazy per-page: chỉ tải khi @vite(...) trong blade
    → TomSelect, Jodit, Tabulator, Flatpickr, FilePond...
    → Expose globals (window.TomSelect, initJoditAll, ...) cho inline script

Tầng 4 — Module assets (Modules/[Name]/resources/assets/)
    Lazy per-page: chỉ tải khi @vite(...) trong blade trang đó
    → Module CSS: custom components, overrides
    → Module JS: Alpine component, page controller
```

---

## 3. SCSS

### 3.1 Shared partials (`resources/scss/`)

| File | Nội dung | Ai import |
|---|---|---|
| `_tokens.scss` | `$primary`, `$border`, `$text-muted`, `$input-h`, `$radius-*`... | Tất cả partials khác |
| `_mixins.scss` | `input-base`, `focus-ring`, `card-base`, `md()`, `skeleton`... | `_form-patterns`, module SCSS |
| `_form-patterns.scss` | `.color-picker-combo`, `.field-readonly`, `.tag-checkbox-group`, `.wizard-step-dot`, `.form-submit-bar`, `.empty-state`... | Module SCSS |
| `_tom-select.scss` | Override toàn bộ TomSelect — tự follow DaisyUI dark/light | Module SCSS có TomSelect |

**Nguyên tắc:**
- Không có class đặc thù bất kỳ module nào
- Không có `@import "tailwindcss"` hay `@plugin "daisyui"` — chỉ ở `app.css`
- Dùng `$token` thay vì màu cứng (`$primary` không phải `#6366f1`)

### 3.2 Module SCSS entry `[name].scss`

```scss
// Modules/Lead/resources/assets/sass/lead.scss
// ─────────────────────────────────────────────
// @use tên không có underscore, không có đường dẫn → loadPaths resolve
// Mọi module có TomSelect → @use 'tom-select'
// Mọi module có form     → @use 'form-patterns'

@use 'form-patterns';       // shared: .color-picker-combo, .field-readonly...
@use 'tom-select';          // shared: TomSelect dark/light theme
@use 'lead-components';     // module-specific: kanban, pipeline badge...
```

Khi module không cần style riêng (chỉ dùng DaisyUI + shared):
```scss
// Modules/Organization/resources/assets/sass/organization.scss
@use 'form-patterns';
@use 'tom-select';
// Không có module-specific partial → file chỉ 2 dòng
```

### 3.3 Module SCSS partial `_[name]-components.scss`

```scss
// Modules/Lead/resources/assets/sass/_lead-components.scss
@use 'tokens' as t;    // $primary, $border, $text-muted, $radius-lg...
@use 'mixins'  as m;   // card-base, truncate, md()...

// CSS đặc thù Lead: kanban, pipeline badge, stats card...
.lead-kanban-card {
    @include m.card-base;
    // ...
}

.lead-stage-badge { ... }
```

### 3.4 Quy tắc đặt tên file

| File | Dùng cho |
|---|---|
| `[name].scss` | Entry point — đăng ký trong `vite.config.backend.js` |
| `_[name]-components.scss` | CSS đặc thù module (có underscore = không phải entry) |
| Không tạo `_overrides.scss` thừa | Chỉ tạo khi module cần override token global |

---

## 4. JS

### 4.1 Globals từ core (không cần import)

Các hàm/object sau đã expose qua `window` từ core bundle `app.js` và widget libs:

| Global | Nguồn | Mô tả |
|---|---|---|
| `window.Alpine` | `app.js` | Alpine.js 3 |
| `window.$`, `window.jQuery` | `app.js` | jQuery 4 |
| `window.initFormValidation` | `app.js` | Validate form bằng data-attr |
| `window.TomSelect` | `tom-select.js` | TomSelect class |
| `window.initTomSelect` | `tom-select.js` | Factory helper |
| `window.initOrgAddress` | `tom-select.js` | Province/Ward cascade |
| `window.initJoditAll` | `jodit.js` | Khởi tạo rich text |

### 4.2 Shared utils (`resources/js/shared/`)

Import từ module page controllers qua alias `@shared`:

| File | Export | Dùng khi |
|---|---|---|
| `form-controller.js` | `makeFormController(serverData, opts)` | Alpine form cần validation |
| `wizard-controller.js` | `makeWizardController(opts)` | Form multi-step wizard |
| `tom-select-factory.js` | `createTs()`, `createTsRemote()`, `createTsAssignee()` | TomSelect có cấu hình phức tạp |

### 4.3 Module entry `[name].js`

```js
// Modules/Lead/resources/assets/js/lead.js
import './pages/lead-form.js';
import './pages/lead-index.js';
```

### 4.4 Page controller `pages/[entity]-form.js`

```js
// Modules/[Name]/resources/assets/js/pages/[entity]-form.js

import { makeFormController }   from '@shared/form-controller.js';
import { makeWizardController } from '@shared/wizard-controller.js';  // nếu wizard
import { createTs }             from '@shared/tom-select-factory.js'; // nếu có TomSelect

// Validation rules
const RULES = {
    name: v => !String(v).trim() ? 'Tên là bắt buộc' : null,
};
const REQUIRED = ['name', 'status'];

// Đăng ký Alpine component
document.addEventListener('alpine:init', () => {
    Alpine.data('[entity]Form', (serverData = {}) => ({

        ...makeFormController(serverData, { rules: RULES, requiredFields: REQUIRED }),

        // State riêng module
        name:   serverData.name   ?? '',
        status: serverData.status ?? 'active',

        init() {
            this.$nextTick(() => _initWidgets());
        },
    }));
});

// Widget init tách riêng khỏi Alpine
function _initWidgets() {
    createTs('#ts-status', { placeholder: '— Chọn trạng thái —' });
}
```

### 4.5 Blade mount Alpine

```blade
<form x-data="[entity]Form({{ Js::from($initData ?? []) }})"
      @submit="handleSubmit($event)"
      class="max-w-3xl space-y-4" novalidate>
```

**Không** viết `Alpine.data(...)` hay logic JS > 5 dòng trong `<script>` của blade.

---

## 5. Vite build

### 5.1 Đăng ký module mới — 3 bước

**Bước 1 — Tạo file assets:**
```
Modules/[Name]/resources/assets/sass/[name].scss
Modules/[Name]/resources/assets/sass/_[name]-components.scss  ← nếu cần CSS riêng
Modules/[Name]/resources/assets/js/[name].js
Modules/[Name]/resources/assets/js/pages/[entity]-form.js
```

**Bước 2 — Thêm vào `vite.config.backend.js`:**
```js
// Phần MODULE_ENTRIES
const MODULE_ENTRIES = [
  'Modules/Lead/resources/assets/sass/lead.scss',
  'Modules/Lead/resources/assets/js/lead.js',
  'Modules/User/resources/assets/sass/user.scss',
  'Modules/User/resources/assets/js/user.js',
  // Thêm module mới tại đây:
  'Modules/[Name]/resources/assets/sass/[name].scss',
  'Modules/[Name]/resources/assets/js/[name].js',
];

// Phần JS_OUTPUT — thêm key JS entry:
'[name]': 'assets/modules/[name].[hash].js',

// Phần CSS_OUTPUT — thêm key CSS compiled:
'[name].css': 'assets/modules/[name].[hash].css',
```

**Bước 3 — Load trong blade:**
```blade
@push('styles')
    @vite(['Modules/[Name]/resources/assets/sass/[name].scss'], 'build/backend')
@endpush
@push('scripts')
    @vite(['Modules/[Name]/resources/assets/js/[name].js'], 'build/backend')
@endpush
```

### 5.2 Tại sao KHÔNG dùng per-module `vite.config.js`

Mỗi module NWIDART scaffold tạo sẵn `Modules/[Name]/vite.config.js` — **bỏ qua những file này**.

| Per-module vite config | vite.config.backend.js duy nhất |
|---|---|
| N build process | 1 build process |
| N manifest.json | 1 manifest.json |
| N output directory | 1 output directory |
| Không dedup vendor chunk | vendor-*.js cache chung |
| Không có `@shared` alias | Alias dùng được mọi nơi |
| `npm run build` phức tạp | `npm run build` là xong |

### 5.3 Alias có sẵn trong build

| Alias | Trỏ tới | Dùng trong |
|---|---|---|
| `@` | `resources/` | Import JS, CSS |
| `@js` | `resources/js/` | Import JS modules |
| `@modules` | `resources/js/modules/` | Import widget libs |
| `@shared` | `resources/js/shared/` | Import shared utils |
| `@css` | `resources/css/` | Import CSS |

### 5.4 SCSS `loadPaths`

```scss
// Trong bất kỳ module SCSS nào:
@use 'tokens';         // → resources/scss/_tokens.scss
@use 'mixins';         // → resources/scss/_mixins.scss
@use 'form-patterns';  // → resources/scss/_form-patterns.scss
@use 'tom-select';     // → resources/scss/_tom-select.scss

// Không cần đường dẫn tương đối như:
// @use '../../../../../resources/scss/tokens' as t;  ← KHÔNG làm thế này
```

---

## 6. Blade

### 6.1 Pattern load asset chuẩn

```blade
{{-- Layout tải core TỰ ĐỘNG cho mọi trang (backend.blade.php) --}}
@vite(['resources/css/app.css', 'resources/js/app.js'], 'build/backend')

{{-- Widget lib — chỉ trang cần --}}
@push('scripts')
    @vite(['resources/js/modules/tom-select.js'], 'build/backend')
@endpush

{{-- Module asset — chỉ trang thuộc module đó --}}
@push('styles')
    @vite(['Modules/Lead/resources/assets/sass/lead.scss'], 'build/backend')
@endpush
@push('scripts')
    @vite(['Modules/Lead/resources/assets/js/lead.js'], 'build/backend')
@endpush

{{-- Kết hợp widget lib + module JS trong cùng @push --}}
@push('scripts')
    @vite([
        'resources/js/modules/tom-select.js',
        'Modules/Lead/resources/assets/js/lead.js',
    ], 'build/backend')
@endpush
```

### 6.2 Truyền server data vào Alpine

Alpine component được đăng ký trong file JS, nhưng **server data** (old values, model, errors) phải truyền từ blade qua tham số:

```blade
{{-- Truyền data qua Js::from() — tự escape an toàn --}}
<form x-data="leadWizard({{ Js::from([
    'hasErrors'   => $errors->any(),
    'errorStep'   => $errors->has('contact_name') ? 1 : ($errors->has('stage_id') ? 2 : 1),
    'contactName' => old('contact_name', ''),
    'stageId'     => old('stage_id', ''),
]) }})" ...>
```

```blade
{{-- Edit form — truyền model values làm fallback --}}
<div x-data="orgForm({{ Js::from([
    'name'   => old('name',   $organization->name),
    'status' => old('status', $organization->status->value),
]) }})">
```

**Dùng `Js::from()` thay vì `@json()`** — `Js::from()` encode đúng ký tự đặc biệt trong attribute HTML.

### 6.3 Toast / Flash message

Layout `backend.blade.php` đã tự handle session flash. Không cần viết lại trong từng trang:

```blade
{{-- Đã có sẵn trong layout — blade chỉ cần session('success'/'error') --}}
return redirect()->route('...index')->with('success', 'Tạo thành công');
return redirect()->back()->with('error', 'Có lỗi xảy ra');
```

Gọi toast thủ công từ JS (khi cần):
```js
// window.Toast đã expose từ toastify.js (phải load trước)
window.Toast?.success('Đã lưu thành công');
window.Toast?.error('Có lỗi xảy ra');
window.Toast?.info('Thông báo');
```

---

## 7. Page Shell

```blade
@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.[module].index') }}">Tên module</a>
    <span class="sep">›</span>
    <span class="current">Thêm mới</span>
</nav>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-content">Tiêu đề trang</h1>
    <a href="{{ route('...index') }}" class="btn btn-ghost btn-sm">← Quay lại</a>
</div>
```

---

## 8. Form Wrapper

### Create (không Alpine)

```blade
<form method="POST" action="{{ route('...store') }}"
      class="max-w-3xl space-y-4" novalidate
      data-[entity]-form>
    @csrf
    {{-- fields --}}
</form>
```

`data-[entity]-form` là attribute selector để `initFormValidation` hook vào. Đặt tên theo entity: `data-org-form`, `data-lead-form`, `data-user-form`...

### Create (có Alpine)

```blade
<form method="POST" action="{{ route('...store') }}"
      class="max-w-3xl space-y-4" novalidate
      x-data="[entity]Form({{ Js::from($initData ?? []) }})"
      @submit="handleSubmit($event)">
    @csrf
</form>
```

### Edit

```blade
<form method="POST" action="{{ route('...update', $model) }}"
      class="max-w-3xl space-y-4" novalidate
      data-[entity]-form>
    @csrf
    @method('PUT')
</form>
```

### `old()` — create vs edit

```blade
{{-- Create: old() không có fallback --}}
<input value="{{ old('name') }}">

{{-- Edit: old() fallback về giá trị model — quan trọng sau validation fail redirect --}}
<input value="{{ old('name', $model->name) }}">
<select>
    <option value="active" {{ old('status', $model->status->value) === 'active' ? 'selected' : '' }}>
</select>
```

**Quy tắc:** mọi field trong edit form phải dùng `old('field', $model->field)` để giữ giá trị khi server trả về lỗi validation.

---

## 9. Card Section

```blade
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <h2 class="card-title text-base mb-2">Tên nhóm</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- fields --}}
        </div>
    </div>
</div>
```

Divider sub-section:
```blade
<div class="divider my-0 text-xs text-base-content/40">Địa chỉ</div>
```

---

## 10. Grid

| Loại field | Class |
|---|---|
| Tên, tiêu đề, URL, website | `form-control md:col-span-2` |
| Email, phone, slug, ngày, số, mã | `form-control` |
| Textarea, rich text | Ngoài grid hoặc `md:col-span-2` |

```blade
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="form-control md:col-span-2">  {{-- tên —full width --}}
    <div class="form-control">               {{-- slug --}}
    <div class="form-control">               {{-- trạng thái --}}
</div>
<div class="form-control mt-2">              {{-- textarea —ngoài grid --}}
```

---

## 11. Form Control

```blade
<div class="form-control">
    <label class="label py-0 pb-1.5">
        <span class="label-text font-medium">Tên field <span class="text-error">*</span></span>
        <span class="label-text-alt text-base-content/40">Gợi ý</span>
    </label>
    <input type="text" name="field" value="{{ old('field') }}"
           class="input input-bordered input-sm @error('field') input-error @enderror"
           placeholder="VD: ...">
    <p class="mt-1 text-xs text-base-content/40">Mô tả thêm nếu cần.</p>
    @error('field')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
</div>
```

---

## 12. Input types

### Text / Email / URL / Number / Date

```blade
<input type="text" name="name" value="{{ old('name') }}"
       class="input input-bordered input-sm @error('name') input-error @enderror"
       placeholder="VD: Nguyễn Văn A">

<input type="email" name="email" value="{{ old('email') }}"
       data-val-email="Email không đúng định dạng"
       class="input input-bordered input-sm @error('email') input-error @enderror"
       placeholder="contact@company.com">

<input type="url" name="website" value="{{ old('website') }}"
       data-val-url="URL phải bắt đầu bằng https://"
       class="input input-bordered input-sm @error('website') input-error @enderror"
       placeholder="https://company.com">
```

### Select

```blade
<select name="status"
        class="select select-bordered select-sm @error('status') select-error @enderror">
    <option value="">— Chọn trạng thái —</option>
    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Hoạt động</option>
</select>
```

### Textarea

```blade
{{-- Thuần --}}
<textarea name="note" rows="4"
          class="textarea textarea-bordered textarea-sm w-full"
          placeholder="Ghi chú...">{{ old('note') }}</textarea>

{{-- Jodit —thêm class + data-preset, gọi initJoditAll trong blade script --}}
<textarea name="description"
          class="jodit-editor textarea textarea-bordered textarea-sm w-full"
          data-jodit-preset="compact">{{ old('description') }}</textarea>
```

Blade khi dùng Jodit:
```blade
@push('scripts')
    @vite(['resources/js/modules/jodit.js'], 'build/backend')
    <script>
    document.addEventListener('DOMContentLoaded', () => initJoditAll('.jodit-editor'));
    </script>
@endpush
```

### Checkbox standalone

```blade
<label class="flex items-start gap-2.5 cursor-pointer select-none group">
    <input type="checkbox" name="is_active" value="1"
           class="checkbox checkbox-sm checkbox-primary mt-0.5 shrink-0"
           {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
    <div>
        <span class="text-sm font-medium group-hover:text-primary transition-colors">
            Kích hoạt ngay
        </span>
        <p class="text-xs text-base-content/50 mt-0.5">Mô tả phụ</p>
    </div>
</label>
```

### Join (Input + Select)

```blade
<div class="join w-full">
    <input type="number" name="expected_value"
           class="input input-bordered input-sm join-item flex-1" placeholder="0">
    <select name="currency" class="select select-bordered select-sm join-item w-24">
        <option value="VND" {{ old('currency', 'VND') === 'VND' ? 'selected' : '' }}>VND</option>
        <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
    </select>
</div>
```

### Color picker

```blade
<div class="color-picker-combo">  {{-- class từ _form-patterns.scss --}}
    <input type="color" id="colorPicker" value="{{ old('color', '#6b7280') }}">
    <input type="text" name="color" id="colorText"
           value="{{ old('color', '#6b7280') }}" maxlength="7"
           class="input input-bordered input-sm flex-1 font-mono"
           placeholder="#6b7280">
</div>
```

JS sync (trong page controller):
```js
const picker = document.getElementById('colorPicker');
const text   = document.getElementById('colorText');
picker?.addEventListener('input', () => { text.value = picker.value; });
text?.addEventListener('input',   () => {
    if (/^#[0-9a-fA-F]{6}$/.test(text.value)) picker.value = text.value;
});
```

### Readonly field

```blade
<input type="text" value="{{ $model->code }}"
       class="input input-bordered input-sm field-readonly" readonly>
{{-- class .field-readonly từ _form-patterns.scss --}}
```

### Radio button — color swatch (visual)

Dùng khi chọn một giá trị trong tập nhỏ có thể hiển thị trực quan (màu sắc, icon):

```blade
<div class="form-control">
    <label class="label py-0 pb-1.5">
        <span class="label-text font-medium">Màu sắc</span>
    </label>

    <div class="flex flex-wrap gap-2"
         x-data="{ selected: '{{ old('color', $tag->color ?? '#6b7280') }}' }">

        @foreach($colors as $hex => $label)
        <label class="cursor-pointer" title="{{ $label }}">
            <input type="radio" name="color" value="{{ $hex }}" class="sr-only"
                   {{ old('color', $tag->color ?? '#6b7280') === $hex ? 'checked' : '' }}
                   x-on:change="selected = '{{ $hex }}'">
            <span class="block w-7 h-7 rounded-full border-2 transition-all"
                  style="background: {{ $hex }}"
                  :class="selected === '{{ $hex }}'
                      ? 'border-base-content scale-110'
                      : 'border-transparent'">
            </span>
        </label>
        @endforeach

        {{-- Preview badge --}}
        <span class="badge badge-sm text-white text-xs font-medium ml-2 self-center"
              x-bind:style="'background:' + selected">
            Preview
        </span>
    </div>

    @error('color')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
</div>
```

Radio button dạng text thông thường (chọn loại, chế độ):

```blade
<div class="form-control">
    <label class="label py-0 pb-2">
        <span class="label-text font-medium">Loại hiển thị</span>
    </label>
    <div class="flex flex-wrap gap-4">
        @foreach(['list' => 'Danh sách', 'grid' => 'Lưới', 'kanban' => 'Kanban'] as $val => $lbl)
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" name="view_mode" value="{{ $val }}"
                   class="radio radio-sm radio-primary"
                   {{ old('view_mode', 'list') === $val ? 'checked' : '' }}>
            <span class="text-sm">{{ $lbl }}</span>
        </label>
        @endforeach
    </div>
    @error('view_mode')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
</div>
```

### Date picker (Flatpickr)

```blade
{{-- Trong form-control --}}
<input type="text" name="close_date" id="fp-close-date"
       value="{{ old('close_date', $model->close_date?->format('d/m/Y') ?? '') }}"
       class="input input-bordered input-sm @error('close_date') input-error @enderror"
       placeholder="DD/MM/YYYY" readonly>
```

Khởi tạo trong blade script (sau @vite flatpickr):
```blade
@push('scripts')
    @vite(['resources/js/modules/flatpickr.js'], 'build/backend')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        initDatePicker('#fp-close-date');
        // Hoặc date+time:
        // initDateTimePicker('#fp-created-at');
        // Range picker:
        // initDateRangePicker('#fp-date-range');
    });
    </script>
@endpush
```

Globals từ `flatpickr.js`: `initDatePicker`, `initDateTimePicker`, `initDateRangePicker`, `initTimePicker`.  
Format mặc định: `d/m/Y` (DD/MM/YYYY). Submit lên server vẫn là string — controller parse bằng `Carbon::createFromFormat('d/m/Y', $value)`.

### Address picker (Blade component dùng chung)

Dùng cho mọi form cần địa chỉ tỉnh/thành + phường/xã:

```blade
<x-address-picker
    :province-value="old('province_code', $model->province_code ?? '')"
    :ward-value="old('ward_code', $model->ward_code ?? '')"
    instance-id="[unique-id]"
    :required="true"
/>
```

- `instance-id` phải **unique trên trang** — dùng để tạo ID DOM không trùng.
- Component tự load TomSelect và tự init Province/Ward cascade.
- Hidden input `province_name` + `ward_name` được tự động submit kèm.

### Tag checkbox group

```blade
<div class="tag-checkbox-group">  {{-- class từ _form-patterns.scss --}}
    @foreach($tags as $tag)
    <label class="tag-item">
        <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}"
               data-color="{{ $tag->color ?? '#6b7280' }}"
               {{ in_array($tag->id, old('tag_ids', [])) ? 'checked' : '' }}>
        <span class="tag-badge" data-color="{{ $tag->color ?? '#6b7280' }}">
            {{ $tag->name }}
        </span>
    </label>
    @endforeach
</div>
```

---

## 13. Validation

### Khi nào dùng phương án nào

| Phương án | Dùng khi |
|---|---|
| `data-[entity]-form` + `initFormValidation` | Form đơn giản, không Alpine, validation basic |
| `makeFormController` (Alpine) | Form phức tạp, cross-field validation, real-time feedback |
| Kết hợp cả hai | Cho phép — `initFormValidation` bắt HTML5 types, Alpine bắt logic phức tạp |

### Server-side

```blade
<input class="input input-bordered input-sm @error('field') input-error @enderror">
@error('field')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
```

### Client-side — data attributes (đơn giản)

```blade
data-req="Vui lòng nhập tên"
data-val-email="Email không đúng định dạng"
data-val-url="URL phải bắt đầu bằng https://"
data-val-maxlength="20"
data-val-minlength="3"
data-val-pattern="[a-z0-9_]+"
data-val-pattern-msg="Chỉ dùng chữ thường, số và _"
```

Kích hoạt (không cần import — `initFormValidation` là global từ core):
```blade
{{-- Blade script — chỉ dùng cho form không Alpine --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    initFormValidation('[data-[entity]-form]');
});
</script>
@endpush
```

Hoặc trong page controller JS khi dùng cùng module bundle:
```js
// pages/[entity]-form.js
document.addEventListener('DOMContentLoaded', () => {
    // initFormValidation là global từ app.js core — không cần import
    initFormValidation('[data-[entity]-form]');
});
```

### Client-side — Alpine (form phức tạp)

```blade
<input :class="fieldCls('name')" @blur="touch('name')"
       class="input input-bordered input-sm w-full transition-colors">
<p x-show="showErr('name')" x-text="errMsg('name')"
   class="mt-1 text-xs text-error" x-transition></p>
```

`fieldCls`, `showErr`, `errMsg`, `touch` từ `makeFormController`.

---

## 14. Interactive states

### `x-cloak` — chống flash Alpine

Dùng khi element Alpine sẽ bị ẩn ban đầu (`x-show="false"`, menu dropdown...) — tránh flash hiện rồi ẩn trước khi Alpine init xong.

```blade
{{-- CSS đã có trong app.css: [x-cloak] { display: none !important; } --}}

{{-- Thêm x-cloak vào element cần ẩn trước khi Alpine init --}}
<div x-data="{ open: false }" x-cloak>
    <div x-show="open">Nội dung dropdown</div>
</div>

{{-- Không cần x-cloak khi element mặc định hiển thị (x-show="true") --}}
```

**Quy tắc:** chỉ thêm `x-cloak` khi thực sự thấy flash khi reload trang. Không thêm tràn lan.

### Submit loading

```blade
<button type="submit" class="btn btn-primary btn-sm gap-2"
        :disabled="submitting"
        :class="{ 'btn-disabled': submitting }">
    <span x-show="submitting" class="loading loading-spinner loading-xs"></span>
    <span x-text="submitting ? 'Đang xử lý...' : 'Tạo mới'"></span>
</button>
```

### Skeleton (fetch async)

```blade
<div x-show="loading" class="space-y-3">
    <div class="skeleton h-4 w-full rounded"></div>
    <div class="skeleton h-4 w-3/4 rounded"></div>
</div>
```

---

## 15. Submit bar

### Cơ bản

```blade
<div class="flex gap-3 pt-2">
    <button type="submit" class="btn btn-primary btn-sm">Tạo [entity]</button>
    <a href="{{ route('...index') }}" class="btn btn-ghost btn-sm">Hủy</a>
</div>
```

### Sticky (form dài)

```blade
<div class="form-submit-bar form-submit-bar--sticky">
    {{-- class từ _form-patterns.scss --}}
    <div x-show="_attempted && !isValid" x-transition
         class="flex items-center gap-2 text-sm text-error">
        <svg class="w-4 h-4 shrink-0" .../>
        Vui lòng kiểm tra lại các trường bắt buộc
    </div>
    <div class="submit-actions">
        <a href="{{ route('...index') }}" class="btn btn-ghost btn-sm">Hủy</a>
        <button type="submit" class="btn btn-sm gap-1.5 transition-all"
                :class="_attempted && !isValid ? 'btn-error' : 'btn-primary'"
                :disabled="submitting">
            <span x-show="submitting" class="loading loading-spinner loading-xs"></span>
            Tạo [entity]
        </button>
    </div>
</div>
```

---

## 16. Wizard

### Step indicator

```blade
<div class="flex items-center gap-0 mb-8 max-w-2xl mx-auto">
    <template x-for="(label, idx) in steps" :key="idx">
        <div class="flex items-center flex-1 last:flex-none">
            <div class="flex flex-col items-center gap-1">
                <div class="wizard-step-dot" :class="stepDotClass(idx)">
                    {{-- icon check khi done, số khi pending/active --}}
                    <template x-if="currentStep > idx + 1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </template>
                    <template x-if="currentStep <= idx + 1">
                        <span x-text="idx + 1"></span>
                    </template>
                </div>
                <span class="text-xs whitespace-nowrap transition-colors"
                      :class="currentStep === idx + 1 ? 'text-primary font-semibold' : 'text-base-content/40'"
                      x-text="label"></span>
            </div>
            <template x-if="idx < steps.length - 1">
                <div class="wizard-step-line" :class="stepLineClass(idx)"></div>
            </template>
        </div>
    </template>
</div>
```

Classes `.wizard-step-dot`, `.wizard-step-dot--active/done/pending`, `.wizard-step-line` từ `_form-patterns.scss`.  
Methods `stepDotClass()`, `stepLineClass()`, `isFirstStep()`, `isLastStep()` từ `makeWizardController`.

### Navigation

```blade
<div class="flex justify-between items-center mt-6 max-w-3xl mx-auto">
    <button type="button" x-show="!isFirstStep()" @click="prevStep()"
            class="btn btn-ghost btn-sm">← Quay lại</button>
    <div x-show="isFirstStep()"></div>
    <div class="flex gap-2">
        <button type="button" x-show="!isLastStep()" @click="nextStep()"
                class="btn btn-primary btn-sm">Tiếp theo →</button>
        <button type="submit" x-show="isLastStep()"
                class="btn btn-primary btn-sm">Tạo [entity]</button>
    </div>
</div>
```

---

## 17. TomSelect

### HTML

```blade
<select id="ts-[field]" name="[field]"
        class="select select-bordered select-sm @error('[field]') select-error @enderror">
    <option value=""></option>
    @foreach($options as $opt)
        <option value="{{ $opt->id }}" {{ old('[field]') == $opt->id ? 'selected' : '' }}>
            {{ $opt->label }}
        </option>
    @endforeach
</select>
```

### Khởi tạo trong page controller

```js
import { createTs, createTsRemote, createTsAssignee } from '@shared/tom-select-factory.js';

createTs('#ts-status',  { placeholder: '— Chọn trạng thái —' });
createTsRemote('#ts-org', { url: '/api/organizations', onChange: v => ctx.orgId = v });
createTsAssignee('#ts-assigned', '/api/users/assignable');
```

### CSS

TomSelect theme nằm trong `resources/scss/_tom-select.scss` — được `@use` từ module SCSS. **Không** đặt inline trong blade.

---

## 18. Ngôn ngữ

### Label

| ❌ | ✅ |
|---|---|
| `Assessment Code` | `Mã đánh giá` |
| `Aggregation Model` | `Phương thức tính điểm` |
| `Is Active` | `Kích hoạt` |
| `Sort Order` | `Thứ tự hiển thị` |
| `Probability` | `Xác suất chốt (%)` |

### Placeholder — `VD:` hoặc ví dụ cụ thể

```
Text:   "VD: Công ty TNHH ABC"
Email:  "contact@company.com"
URL:    "https://company.com"
```

### Nút bấm

| Hành động | Label |
|---|---|
| Tạo mới | `Tạo [tên thực thể]` |
| Lưu chỉnh sửa | `Lưu thay đổi` |
| Hủy / Quay lại | `Hủy` / `← Quay lại` |
| Bước kế tiếp | `Tiếp theo →` |

---

## 19. Class reference

| Thành phần | Class |
|---|---|
| Form wrapper | `max-w-3xl space-y-4` |
| Card | `card bg-base-100 shadow-sm border border-base-200` |
| Card body | `card-body` |
| Card title | `card-title text-base mb-2` |
| Grid | `grid grid-cols-1 md:grid-cols-2 gap-4` |
| Field full-width | `form-control md:col-span-2` |
| Field half | `form-control` |
| Label | `label py-0 pb-1.5` |
| Label text | `label-text font-medium` |
| Label hint | `label-text-alt text-base-content/40` |
| Dấu bắt buộc | `<span class="text-error">*</span>` |
| Input | `input input-bordered input-sm w-full` |
| Input error | thêm `input-error` |
| Select | `select select-bordered select-sm w-full` |
| Select error | thêm `select-error` |
| Textarea | `textarea textarea-bordered textarea-sm w-full` |
| Checkbox | `checkbox checkbox-sm checkbox-primary` |
| Error msg | `mt-1 text-xs text-error` |
| Hint dưới field | `mt-1 text-xs text-base-content/40` |
| Readonly | `field-readonly` *(từ _form-patterns.scss)* |
| Color picker | `.color-picker-combo` *(từ _form-patterns.scss)* |
| Tag checkboxes | `.tag-checkbox-group .tag-item .tag-badge` *(từ _form-patterns.scss)* |
| Join | `join w-full` + thêm `join-item` |
| Divider | `divider my-0 text-xs text-base-content/40` |
| Wizard dot | `.wizard-step-dot` + modifier *(từ _form-patterns.scss)* |
| Wizard line | `.wizard-step-line` + modifier *(từ _form-patterns.scss)* |
| Submit bar | `.form-submit-bar` *(từ _form-patterns.scss)* |
| Sticky submit | thêm `.form-submit-bar--sticky` |
| Submit primary | `btn btn-primary btn-sm` |
| Submit cancel | `btn btn-ghost btn-sm` |
| Loading | `loading loading-spinner loading-xs` |
| Radio text | `radio radio-sm radio-primary` |
| Radio visual | `sr-only` trên input + span style inline |

---

## 20. Anti-patterns

### Build & Asset

| ❌ Sai | ✅ Đúng |
|---|---|
| Dùng per-module `vite.config.js` | Đăng ký vào `MODULE_ENTRIES` trong `vite.config.backend.js` |
| Đặt tên entry SCSS là `app.scss` | Đặt tên là `[module].scss` (tránh chunk name conflict) |
| Package không có trong `package.json` | Kiểm tra trước, không tham chiếu package chưa install |
| `@push('styles')` với CSS block inline | Viết vào `_[module]-components.scss` |
| `@push('scripts')` với logic JS > 5 dòng | Chuyển vào page controller `.js` |
| Dùng màu cứng `#6366f1` trong SCSS | Dùng `t.$primary` từ `_tokens.scss` |

### Layout

| ❌ Sai | ✅ Đúng |
|---|---|
| `grid-cols-2` không `md:` | `grid-cols-1 md:grid-cols-2` |
| `input-md` hoặc không size | `input-sm` |
| Field không `form-control` wrapper | Luôn bọc `<div class="form-control">` |
| Textarea thiếu `w-full` | Thêm `w-full` |
| Label tiếng Anh kỹ thuật | Label tiếng Việt rõ nghĩa |

### JS

| ❌ Sai | ✅ Đúng |
|---|---|
| `Alpine.data(...)` trong `<script>` blade | Đăng ký trong JS file, event `alpine:init` |
| Copy-paste validation logic | `makeFormController` dùng chung |
| `new TomSelect(...)` config khác nhau mỗi trang | `createTs()` từ factory |

---

## 21. Checklist trước khi merge

### Build

- [ ] Module entry files đặt tên `[name].scss` / `[name].js` (không phải `app.scss`)
- [ ] Đã thêm vào `MODULE_ENTRIES` trong `vite.config.backend.js`
- [ ] Đã thêm vào `JS_OUTPUT['[name]']` và `CSS_OUTPUT['[name].css']`
- [ ] `npm run build` chạy thành công, không có lỗi

### SCSS

- [ ] Module `[name].scss` có `@use 'form-patterns'`
- [ ] Module `[name].scss` có `@use 'tom-select'` nếu trang dùng TomSelect
- [ ] Không dùng màu cứng — dùng token từ `_tokens.scss`
- [ ] CSS đặc thù module nằm trong `_[name]-components.scss`
- [ ] Không có CSS block inline trong blade

### JS

- [ ] Alpine component đăng ký trong JS file (event `alpine:init`), không inline blade
- [ ] Blade `@push('scripts')` chỉ có `@vite(...)` và ≤5 dòng bootstrap
- [ ] Import `@shared/*` đúng alias, không dùng đường dẫn tương đối

### Form

- [ ] Form: `max-w-3xl space-y-4 novalidate`
- [ ] Form có `data-[entity]-form` (nếu dùng `initFormValidation`) hoặc `x-data` (nếu Alpine)
- [ ] Edit form: tất cả value dùng `old('field', $model->field)`, không phải chỉ `old('field')`
- [ ] Grid: `grid-cols-1 md:grid-cols-2 gap-4`
- [ ] Label: `label py-0 pb-1.5` + `label-text font-medium`
- [ ] Input: `input-sm` / Select: `select-sm` / Textarea: `w-full`
- [ ] `@error('field') input-error @enderror` trên mọi input
- [ ] Select có option placeholder
- [ ] Date picker dùng Flatpickr (`initDatePicker`), không dùng `type="date"` native
- [ ] Address form dùng `<x-address-picker>` (không tự viết cascade)
- [ ] `x-cloak` chỉ thêm khi thực sự thấy flash khi reload

### Ngôn ngữ & UX

- [ ] Mọi label tiếng Việt
- [ ] Placeholder có `VD:` hoặc ví dụ cụ thể
- [ ] Test mobile (1 cột) và dark mode
- [ ] Submit button có loading state nếu form async
