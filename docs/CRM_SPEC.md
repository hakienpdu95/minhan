# Customer Module Specification
> **Module:** `Modules/Customer` · **Feature flag:** `module.crm` · **Plans:** Growth, Professional, Enterprise
> **Status:** Design — ready to implement · **Date:** June 2026

---

## 1. Tổng quan

Module **Customer** quản lý tập trung tất cả khách hàng đã chuyển đổi của một tổ chức (`organization_id`), bất kể đó là cá nhân hay doanh nghiệp. Không phân tầng Contact / Company — chỉ có một entity duy nhất: **Khách hàng**.

### So sánh với Lead module

| Khía cạnh | Lead | Customer |
|-----------|------|----------|
| Mục đích | Pipeline bán hàng — prospecting | Hồ sơ khách hàng đã mua / quan hệ lâu dài |
| Entity | `Lead` (1 opportunity) | `Customer` (1 khách hàng) |
| Trạng thái | Active → Converted → Archived | Prospect → Active → VIP → Inactive |
| Contact info | LeadContact (dedup, nhúng trong Lead) | Trực tiếp trên Customer |
| Công ty | `contact_company` string field | `customer_type = business` + `company_name` |
| Activities | LeadActivity (gắn vào Lead) | CustomerActivity (gắn vào Customer) |

### Luồng chuyển đổi Lead → Customer

```
Lead (status → Converted)
    └─► ConvertLeadToCustomerAction
            ├─ Tìm Customer theo dedup_hash (email / phone)
            ├─ Nếu chưa có → tạo mới từ Lead data
            ├─ Gán Lead.customer_id
            └─ Set Customer.lifecycle_stage = Active
```

---

## 2. Domain Model

### 2.1 Customer

```
customers
  id                    bigint PK
  uuid                  char(36) unique
  organization_id       bigint FK → organizations   [TenantAware]

  -- Loại hình
  customer_type         tinyint   enum: Individual=1, Business=2

  -- Thông tin hiển thị chung
  display_name          varchar(255)  required      ← tên người / tên công ty
  primary_email         varchar(255)  nullable  idx
  primary_phone         varchar(30)   nullable  idx
  province_code         varchar(10)   nullable
  full_address          varchar(500)  nullable
  website               varchar(500)  nullable
  description           text          nullable
  avatar_url            varchar(500)  nullable

  -- Phân loại & quản lý
  lifecycle_stage       tinyint   enum: Prospect=1, Active=2, VIP=3, Inactive=4, Churned=5
  source_id             bigint FK → lead_sources nullable   ← reuse catalog có sẵn
  assigned_to           bigint FK → users nullable
  last_activity_at      datetime  nullable
  activity_count        int       default 0

  -- Chỉ Individual (customer_type = 1)
  first_name            varchar(100) nullable
  last_name             varchar(100) nullable
  gender                tinyint      nullable   enum: Male=1, Female=2, Other=3
  date_of_birth         date         nullable

  -- Chỉ Business (customer_type = 2)
  company_name          varchar(255) nullable   ← tên pháp lý (display_name = tên giao dịch)
  tax_code              varchar(50)  nullable
  industry              varchar(100) nullable
  company_size          tinyint      nullable   enum: Micro=1, Small=2, Medium=3, Large=4, Enterprise=5
  representative_name   varchar(255) nullable   ← người đại diện liên hệ
  representative_title  varchar(150) nullable

  -- Truy vết
  dedup_hash            char(32)     nullable   ← MD5(normalize(email|phone))
  converted_from_lead_id bigint FK → leads nullable
  created_by            bigint FK → users nullable
  updated_by            bigint FK → users nullable
  created_at, updated_at, deleted_at

Indexes:
  uq_customer_org_dedup   (organization_id, dedup_hash)
  idx_customer_email      (organization_id, primary_email)
  idx_customer_phone      (organization_id, primary_phone)
  idx_customer_name       (organization_id, display_name)        -- prefix 100
  idx_customer_list       (organization_id, lifecycle_stage, customer_type, assigned_to)
  idx_customer_activity   (organization_id, last_activity_at)
  idx_customer_source     (organization_id, source_id)
  idx_customer_province   (organization_id, province_code)
```

**Relations:**
```php
activities()   HasMany CustomerActivity  (ordered by created_at desc)
notes()        HasMany CustomerNote      (ordered by is_pinned desc, created_at desc)
tags()         BelongsToMany CustomerTag via customer_tag_map
source()       BelongsTo LeadSource      (cross-module reuse)
assignee()     BelongsTo User
leads()        HasMany Lead              (via leads.customer_id, cross-module)
createdBy()    BelongsTo User
```

**Enum: CustomerType** (int)
```php
Individual = 1  → "Cá nhân"
Business   = 2  → "Doanh nghiệp"
```

**Enum: CustomerLifecycleStage** (int)
```php
Prospect = 1  → "Tiềm năng"
Active   = 2  → "Đang hoạt động"
VIP      = 3  → "VIP"
Inactive = 4  → "Không hoạt động"
Churned  = 5  → "Đã rời bỏ"
```

**Enum: CompanySize** (int)
```php
Micro      = 1  → "Siêu nhỏ (< 10)"
Small      = 2  → "Nhỏ (10–50)"
Medium     = 3  → "Vừa (50–250)"
Large      = 4  → "Lớn (250–1000)"
Enterprise = 5  → "Tập đoàn (1000+)"
```

---

### 2.2 CustomerActivity

```
customer_activities
  id              bigint PK
  organization_id bigint
  customer_id     bigint FK → customers
  lead_id         bigint FK → leads nullable   ← optional cross-ref
  type            tinyint   enum: Call=1, Email=2, Meeting=3, Note=4, Task=5, Other=6
  title           varchar(255)
  description     text nullable
  outcome         varchar(500) nullable
  scheduled_at    datetime nullable
  completed_at    datetime nullable
  duration_minutes int nullable
  actor_id        bigint nullable
  actor_name      varchar(255) nullable   ← snapshot
  created_at      datetime               ← manual, không dùng timestamps()

Indexes:
  idx_ca_customer  (customer_id, created_at)
  idx_ca_org_type  (organization_id, type, created_at)
```

---

### 2.3 CustomerNote

```
customer_notes
  id, organization_id
  customer_id   bigint FK → customers
  content       text
  is_pinned     boolean default false
  author_id     bigint nullable
  author_name   varchar(255) nullable   ← snapshot
  created_at, updated_at, deleted_at

Index: (customer_id, is_pinned, created_at)
```

---

### 2.4 CustomerTag

```
customer_tags
  id, organization_id, name varchar(100), color varchar(20)
  unique: (organization_id, name)

customer_tag_map
  customer_id, tag_id
  PK: (customer_id, tag_id)
```

---

## 3. Cross-Module Integration (Lead → Customer)

### 3.1 Thêm `customer_id` vào leads

```php
// Migration: add_customer_id_to_leads
Schema::table('leads', function (Blueprint $table) {
    $table->unsignedBigInteger('customer_id')->nullable()->after('contact_id');
    $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
    $table->index(['organization_id', 'customer_id']);
});
```

**Lead model** — thêm relation:
```php
public function customer(): BelongsTo
{
    return $this->belongsTo(\Modules\Customer\Models\Customer::class);
}
```

### 3.2 ConvertLeadToCustomerAction

```php
// Modules/Customer/app/Features/Conversion/Actions/ConvertLeadToCustomerAction.php
class ConvertLeadToCustomerAction
{
    use AsAction;

    public function handle(Lead $lead): Customer
    {
        $hash = $this->buildDedupHash($lead->contact_phone, $lead->contact_email ?? '');

        $customer = Customer::firstOrCreate(
            ['organization_id' => $lead->organization_id, 'dedup_hash' => $hash],
            [
                'customer_type'  => $lead->contact_company ? CustomerType::Business : CustomerType::Individual,
                'display_name'   => $lead->contact_company ?: $lead->contact_name,
                'company_name'   => $lead->contact_company ?: null,
                'representative_name' => $lead->contact_company ? $lead->contact_name : null,
                'primary_phone'  => $lead->contact_phone,
                'primary_email'  => $lead->contact_email ?? null,
                'source_id'      => $lead->source_id,
                'assigned_to'    => $lead->assigned_to,
                'lifecycle_stage' => CustomerLifecycleStage::Active,
                'converted_from_lead_id' => $lead->id,
                'created_by'     => $lead->created_by,
            ]
        );

        // Promote stage nếu đang thấp hơn Active
        if ($customer->lifecycle_stage->value < CustomerLifecycleStage::Active->value) {
            $customer->update(['lifecycle_stage' => CustomerLifecycleStage::Active]);
        }

        $lead->update(['customer_id' => $customer->id]);

        return $customer;
    }

    private function buildDedupHash(string $phone, string $email): string
    {
        $key = $phone ?: $email;
        return md5(preg_replace('/\D/', '', $key));
    }
}
```

### 3.3 Trigger trong Lead module

Trong `ChangeLeadStageAction` khi Lead → Converted:
```php
if ($lead->wasChanged('status') && $lead->status === LeadStatus::Converted) {
    ConvertLeadToCustomerAction::dispatch($lead);
}
```

---

## 4. Kiến trúc Module (NWIDART)

```
Modules/Customer/
├── app/
│   ├── Features/
│   │   ├── Customers/
│   │   │   ├── Http/
│   │   │   │   ├── CustomerController.php      (CRUD web)
│   │   │   │   └── CustomerApiController.php   (Tabulator JSON)
│   │   │   ├── Actions/
│   │   │   │   ├── CreateCustomerAction.php
│   │   │   │   ├── UpdateCustomerAction.php
│   │   │   │   └── DeleteCustomerAction.php
│   │   │   ├── Queries/
│   │   │   │   ├── ListCustomersQuery.php
│   │   │   │   ├── ListCustomersHandler.php
│   │   │   │   ├── GetCustomerQuery.php
│   │   │   │   └── GetCustomerHandler.php
│   │   │   ├── Events/
│   │   │   │   ├── CustomerCreated.php
│   │   │   │   └── CustomerUpdated.php
│   │   │   └── Data/
│   │   │       ├── StoreCustomerData.php
│   │   │       └── UpdateCustomerData.php
│   │   ├── Activities/
│   │   │   ├── Http/ActivityController.php
│   │   │   ├── Actions/LogActivityAction.php
│   │   │   └── Queries/ListActivitiesHandler.php
│   │   ├── Notes/
│   │   │   ├── Actions/
│   │   │   │   ├── StoreNoteAction.php
│   │   │   │   ├── UpdateNoteAction.php
│   │   │   │   ├── DestroyNoteAction.php
│   │   │   │   └── TogglePinNoteAction.php
│   │   └── Conversion/
│   │       └── Actions/ConvertLeadToCustomerAction.php
│   ├── Models/
│   │   ├── Customer.php
│   │   ├── CustomerActivity.php
│   │   ├── CustomerNote.php
│   │   └── CustomerTag.php
│   ├── Observers/
│   │   └── CustomerObserver.php
│   ├── Policies/
│   │   └── CustomerPolicy.php
│   └── Providers/
│       ├── CustomerServiceProvider.php
│       └── EventServiceProvider.php
├── config/
│   └── customer.php
├── database/
│   ├── migrations/
│   │   ├── 2026_xx_create_customers_table.php
│   │   ├── 2026_xx_create_customer_activities_table.php
│   │   ├── 2026_xx_create_customer_notes_table.php
│   │   ├── 2026_xx_create_customer_tags_table.php
│   │   └── 2026_xx_add_customer_id_to_leads.php        (Phase 2)
│   └── seeders/CustomerDatabaseSeeder.php
├── resources/views/
│   ├── index.blade.php     (Tabulator list)
│   ├── show.blade.php      (detail + tabs)
│   └── _form.blade.php     (create/edit, dynamic theo customer_type)
└── routes/
    ├── web.php
    └── api.php
```

---

## 5. Routes

```php
// web.php
Route::middleware(['web', 'auth', 'can:customers.view_all|customers.view_assigned'])
    ->prefix('customers')
    ->name('customers.')
    ->group(function () {
        Route::get('/',                    [CustomerController::class, 'index'])->name('index');
        Route::get('/create',              [CustomerController::class, 'create'])->name('create');
        Route::post('/',                   [CustomerController::class, 'store'])->name('store');
        Route::get('/{customer}',          [CustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/edit',     [CustomerController::class, 'edit'])->name('edit');
        Route::put('/{customer}',          [CustomerController::class, 'update'])->name('update');
        Route::delete('/{customer}',       [CustomerController::class, 'destroy'])->name('destroy');

        // Activities (AJAX)
        Route::post('/{customer}/activities', [ActivityController::class, 'store'])->name('activities.store');
        Route::delete('/activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');

        // Notes (AJAX)
        Route::post('/{customer}/notes',          [NoteController::class, 'store'])->name('notes.store');
        Route::put('/notes/{note}',               [NoteController::class, 'update'])->name('notes.update');
        Route::delete('/notes/{note}',            [NoteController::class, 'destroy'])->name('notes.destroy');
        Route::post('/notes/{note}/toggle-pin',   [NoteController::class, 'togglePin'])->name('notes.toggle-pin');
    });

// api.php — Tabulator + TomSelect search
Route::middleware(['web', 'auth'])
    ->prefix('backend/api/customers')
    ->name('backend.customers.')
    ->group(function () {
        Route::get('/',       [CustomerApiController::class, 'index'])->name('index');
        Route::get('/search', [CustomerApiController::class, 'search'])->name('search'); // TomSelect
    });
```

---

## 6. RBAC

### 6.1 PermissionEnum — thêm mới

```php
// app/Enums/PermissionEnum.php

// ══ CUSTOMERS ══════════════════════════════════════════════════
// CEO=Full | Sales=Assigned | Ops=View+Edit | Marketing=View | Admin=Config
case CUSTOMERS_VIEW_ALL      = 'customers.view_all';
case CUSTOMERS_VIEW_ASSIGNED = 'customers.view_assigned';
case CUSTOMERS_CREATE        = 'customers.create';
case CUSTOMERS_EDIT          = 'customers.edit';
case CUSTOMERS_DELETE        = 'customers.delete';
case CUSTOMERS_EXPORT        = 'customers.export';
case CUSTOMERS_CONFIG        = 'customers.config';    // quản lý tags
```

### 6.2 config/permissions.php

```php
'customers' => [
    'sidebar_label' => 'Khách hàng',
    'feature_gate'  => 'module.crm',
    'roles' => [
        'CEO'       => [P::CUSTOMERS_VIEW_ALL, P::CUSTOMERS_CREATE, P::CUSTOMERS_EDIT,
                        P::CUSTOMERS_DELETE, P::CUSTOMERS_EXPORT],
        'SALES'     => [P::CUSTOMERS_VIEW_ASSIGNED, P::CUSTOMERS_CREATE, P::CUSTOMERS_EDIT],
        'OPS'       => [P::CUSTOMERS_VIEW_ALL, P::CUSTOMERS_CREATE, P::CUSTOMERS_EDIT,
                        P::CUSTOMERS_EXPORT],
        'MARKETING' => [P::CUSTOMERS_VIEW_ALL],
        'ADMIN'     => [P::CUSTOMERS_CONFIG],
    ],
],
```

### 6.3 CustomerPolicy

```php
viewAny → CUSTOMERS_VIEW_ALL || CUSTOMERS_VIEW_ASSIGNED
view    → view_all || (view_assigned && assigned_to === auth()->id())
create  → CUSTOMERS_CREATE
update  → CUSTOMERS_EDIT  (SALES: chỉ bản ghi assigned_to === auth()->id())
delete  → CUSTOMERS_DELETE
```

---

## 7. UI Specification

### 7.1 Customer List — `/customers`

```
┌────────────────────────────────────────────────────────────────┐
│ Khách hàng                              [+ Tạo khách hàng]    │
├────────────────────────────────────────────────────────────────┤
│ [Search tên/email/SĐT]  [Loại ▼]  [Giai đoạn ▼]  [Nguồn ▼] │
│                         [Người PT ▼]  [Tỉnh/TP ▼]            │
├────────────────────────────────────────────────────────────────┤
│ Tên         | Loại     | Email | SĐT | Giai đoạn | Người PT  │
│ [link]      | badge    | text  | txt | badge     | avatar     │
│ Pagination 25/50/100                    [Export CSV]          │
└────────────────────────────────────────────────────────────────┘
```

**Tabulator columns:** `display_name`, `customer_type` (badge), `primary_email`, `primary_phone`, `lifecycle_stage` (badge), `assignee.name`, `last_activity_at`, `created_at`

### 7.2 Customer Detail — `/customers/{id}`

```
┌────────────────────────────────────────────────────────────────┐
│ ← Khách hàng       [Sửa]  [Xóa]                              │
│                                                                │
│  [Avatar]  Công ty ABC  ·  badge: Doanh nghiệp                │
│            📧 info@abc.com  ·  📱 0901234567                 │
│            👤 Người phụ trách: Trần B  ·  🏷 VIP             │
│            🏭 MST: 0123456789  ·  👔 Đại diện: Nguyễn Văn A  │
├────────────────────────────────────────────────────────────────┤
│  [Thông tin]  [Hoạt động]  [Leads]  [Ghi chú]                │
│                                                                │
│  Tab Hoạt động:                                               │
│    [+ Ghi nhận]                                               │
│    📞 Gọi điện  10/06 · "Tư vấn gói mới"                    │
│    📧 Email     08/06 · "Gửi báo giá"                        │
│                                                                │
│  Tab Leads:                                                   │
│    Danh sách Leads đã link (stage, value, status)             │
└────────────────────────────────────────────────────────────────┘
```

### 7.3 Customer Form — Create/Edit

Form tự thích ứng theo `customer_type` (Alpine.js `x-show`):

```
[● Cá nhân]  [○ Doanh nghiệp]    ← radio, đổi thì show/hide fields

── Section: Thông tin chung ──────────────────────────────────
  Tên hiển thị*     [text]
  Email chính       [email]
  Số điện thoại     [tel]
  Giai đoạn         [TomSelect: Tiềm năng / Đang HĐ / VIP / ...]
  Nguồn             [TomSelect → lead_sources]
  Người phụ trách   [TomSelect → users]
  Tags              [TomSelect multiple → customer_tags]

── Section: Cá nhân (x-show: type === 'individual') ─────────
  Họ                [text]
  Tên               [text]
  Giới tính         [select]
  Ngày sinh         [date]

── Section: Doanh nghiệp (x-show: type === 'business') ──────
  Tên pháp lý       [text]
  Mã số thuế        [text]
  Ngành nghề        [text]
  Quy mô            [TomSelect]
  Người đại diện    [text]
  Chức vụ đại diện  [text]

── Section: Địa chỉ ──────────────────────────────────────────
  Tỉnh/TP           [TomSelect]
  Địa chỉ           [textarea]
  Website           [url]

── Section: Ghi chú ──────────────────────────────────────────
  Mô tả             [textarea]
```

---

## 8. Config — `customer.php`

```php
return [
    'queue' => env('CUSTOMER_QUEUE', 'default'),

    'dedup' => [
        'strategy' => 'phone_then_email',
    ],

    'conversion' => [
        'auto_convert_on_lead_won' => true,
    ],

    'pagination' => [
        'default_per_page' => 25,
    ],

    'cache_ttl' => [
        'tags' => 600,
    ],

    'activity_types' => [
        1 => ['label' => 'Gọi điện',  'icon' => 'phone'],
        2 => ['label' => 'Email',     'icon' => 'envelope'],
        3 => ['label' => 'Họp',       'icon' => 'calendar'],
        4 => ['label' => 'Ghi chú',   'icon' => 'note'],
        5 => ['label' => 'Tác vụ',    'icon' => 'check'],
        6 => ['label' => 'Khác',      'icon' => 'ellipsis'],
    ],
];
```

---

## 9. Kế hoạch triển khai

### Phase 1 — Customer CRUD *(~3–4 ngày)*

1. `php artisan module:make Customer`
2. Migration: `customers`, `customer_tags`, `customer_tag_map`
3. Models: `Customer`, `CustomerTag` (TenantAware + SoftDeletes + LogsActivity)
4. Enums: `CustomerType`, `CustomerLifecycleStage`, `CompanySize`
5. `ListCustomersQuery/Handler` — filter: type, stage, source, assigned, province, tag, search, date range
6. `GetCustomerQuery/Handler` — eager load tags, recent activities, notes
7. `StoreCustomerData`, `UpdateCustomerData` (Spatie Data với validation)
8. `CreateCustomerAction` (dedup check), `UpdateCustomerAction`, `DeleteCustomerAction`
9. `CustomerController`, `CustomerApiController`
10. Views: `index.blade.php`, `show.blade.php` (tabs), `_form.blade.php` (Alpine type-switch)
11. Routes (web + api)
12. `CustomerPolicy`
13. PermissionEnum cases + config/permissions.php
14. `CustomerServiceProvider` với feature gate `module.crm`
15. Sidebar entry

### Phase 2 — Lead Integration *(~1–2 ngày)*

1. Migration: `add_customer_id_to_leads`
2. `ConvertLeadToCustomerAction`
3. Hook trong Lead module: Lead.status → Converted → dispatch ConvertLeadToCustomerAction
4. Lead detail: thêm card "Khách hàng" với link → Customer profile
5. Customer detail tab "Leads": danh sách Leads liên kết

### Phase 3 — Activities & Notes *(~1–2 ngày)*

1. Migrations: `customer_activities`, `customer_notes`
2. Models: `CustomerActivity`, `CustomerNote`
3. `LogActivityAction`, `StoreNoteAction`, etc.
4. AJAX endpoints (ActivityController, NoteController)
5. Timeline + Notes tab trong Customer detail

### Phase 4 — Data Migration từ LeadContact *(~1 ngày)*

```bash
php artisan customer:migrate-from-leads
```

Promote `LeadContact` → `Customer`:
- Lấy tất cả `lead_contacts` của org
- `firstOrCreate` theo dedup_hash → `customers`
- Map: `contact_company` có → `customer_type = business`, không có → `individual`
- Backfill `leads.customer_id`

---

## 10. Checklist triển khai

- [ ] `php artisan module:make Customer`
- [ ] Migrations + `php artisan migrate`
- [ ] Models với TenantAware + SoftDeletes
- [ ] Enums (CustomerType, CustomerLifecycleStage, CompanySize)
- [ ] CQRS queries với covering indexes
- [ ] Actions + Events
- [ ] Controller + Policy
- [ ] Views theo `docs/form-ui-spec.md`
- [ ] Routes registered
- [ ] PermissionEnum + config/permissions.php
- [ ] Sidebar: `@canFeature('module.crm')`
- [ ] Feature gate trong ServiceProvider
- [ ] Test: CRUD, tenant isolation, feature gate block, Lead conversion
