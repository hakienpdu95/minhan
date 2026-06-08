# Tenant Organization ID — Hướng dẫn chuẩn hóa module

> **Mục đích:** Đảm bảo mọi module đều có `organization_id` đúng chuẩn để  
> `TenantAwareModel` + `BelongsToOrganization` scope dữ liệu theo tenant.  
> **Reference implementation:** `Modules/Branch` — đây là module gold standard cho pattern này.

---

## Mục lục

1. [Kiến trúc tenant](#1-kiến-trúc-tenant)
2. [Chẩn đoán nhanh](#2-chẩn-đoán-nhanh)
3. [Trạng thái hiện tại từng module](#3-trạng-thái-hiện-tại)
4. [Checklist 5 lớp](#4-checklist-5-lớp)
5. [Code template từng lớp](#5-code-template)
6. [Logic phân quyền tổ chức (mở rộng DN)](#6-logic-phân-quyền-tổ-chức)
7. [Thứ tự thực hiện khuyến nghị](#7-thứ-tự-thực-hiện)

---

## 1. Kiến trúc tenant

```
TenantAwareModel (app/Foundation/Models/TenantAwareModel.php)
    └── uses BelongsToOrganization (app/Shared/Tenancy/Traits/)
            ├── bootBelongsToOrganization()
            │     ├── addGlobalScope(OrganizationScope)   ← tự filter WHERE organization_id = ?
            │     └── creating: auto-set organization_id từ TenantContext nếu chưa có
            ├── organization(): BelongsTo Organization
            └── scopeWithoutTenant() / scopeForOrganization()
```

**Quy tắc:** Bất kỳ model nào lưu dữ liệu theo tenant **phải** extend `TenantAwareModel`.  
Model extends `TenantAwareModel` mà bảng thiếu cột `organization_id` → **query sẽ fail**.

---

## 2. Chẩn đoán nhanh

Chạy lệnh sau để kiểm tra tất cả module cùng lúc:

```bash
# Kiểm tra model có TenantAwareModel và organization_id không
for mod in Modules/*/app/Models/*.php; do
  has_tenant=$(grep -c "TenantAwareModel" "$mod" 2>/dev/null || echo 0)
  has_org=$(grep -c "organization_id" "$mod" 2>/dev/null || echo 0)
  [ "$has_tenant" -gt 0 ] && [ "$has_org" -eq 0 ] && echo "⚠️  THIẾU org_id trong fillable: $mod"
  [ "$has_tenant" -eq 0 ] && echo "❌ CHƯA tenant-aware: $mod"
done

# Kiểm tra migration có organization_id không
for mod in Modules/*/database/migrations/; do
  create_mig=$(find "$mod" -name "*create_*table*" 2>/dev/null | head -1)
  [ -n "$create_mig" ] && ! grep -q "organization_id" "$create_mig" && echo "⚠️  Migration thiếu org_id: $create_mig"
done
```

---

## 3. Trạng thái hiện tại

> Cập nhật: 2026-06-08. Scan tự động từ codebase.

| Module | Model TenantAware | org_id migration | org_id fillable | org_id StoreBranchData | Store Action explicit | Views có field |
|---|:---:|:---:|:---:|:---:|:---:|:---:|
| **Branch** ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Department | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| Employee | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| JobPosting | ❌ | ⚠️ partial | ❌ | ❌ | ⚠️ partial | ❌ |
| JobTitle | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| KcCategory | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| KcItem | ❌ | ⚠️ partial | ❌ | ✅ | ❌ | ❌ |
| KpiGoal | ✅ | ✅ | ✅ | ⚠️ partial | ❌ | ❌ |
| Lead | ❌ | ⚠️ partial | ✅ | ❌ | ✅ | ❌ |
| LeadPipelineStage | ❌ | ✅ | ✅ | ❌ | ✅ | ❌ |
| LeadSource | ❌ | ✅ | ✅ | ❌ | ✅ | ❌ |
| Leave | ✅ | ✅ | ✅ | ⚠️ partial | ❌ | ❌ |
| PerformanceReview | ✅ | ✅ | ✅ | ⚠️ partial | ❌ | ❌ |
| Project | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| Recruitment | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Sop | ✅ | ⚠️ partial | ✅ | ✅ | ⚠️ partial | ❌ |
| Survey | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

**Phân nhóm theo độ ưu tiên:**

- 🔴 **Cần làm đầy đủ** (thiếu nhiều lớp): `Recruitment`, `Survey`, `JobPosting`, `KcItem`
- 🟠 **Cần fix model + data + views**: `Lead`, `LeadPipelineStage`, `LeadSource`
- 🟡 **Chỉ cần store action + views**: `Department`, `JobTitle`, `KcCategory`, `KpiGoal`, `Leave`, `PerformanceReview`, `Project`, `Employee`
- ✅ **Done**: `Branch`

---

## 4. Checklist 5 lớp

Mỗi module cần hoàn thành **5 lớp** theo thứ tự sau:

### Lớp 1 — Migration

- [ ] Bảng `[entity]s` có cột `organization_id` (unsignedBigInteger, NOT NULL, FK → organizations.id)
- [ ] Có unique constraint kết hợp `(organization_id, [code_field])` nếu code phải unique trong org
- [ ] Có index `(organization_id, status)` nếu list thường filter theo status

```sql
-- Cột cần có trong create migration
$table->foreignId('organization_id')->constrained()->restrictOnDelete();
-- Hoặc nếu bảng đã tồn tại, tạo migration riêng:
$table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete()->after('id');
```

### Lớp 2 — Model

- [ ] Extends `TenantAwareModel` (không phải `Model` thường)
- [ ] `organization_id` có trong `$fillable`
- [ ] **Không cần** khai báo thêm `organization()` relationship — đã có trong `BelongsToOrganization` trait

```php
// ✅ Đúng
class Department extends TenantAwareModel { ... }

// ❌ Sai
class Department extends Model { ... }
```

### Lớp 3 — Data/Request (Spatie Laravel Data)

- [ ] `StoreFooData` có field `public readonly int $organization_id`
- [ ] Rules có `'organization_id' => ['required', 'integer', 'exists:organizations,id']`
- [ ] `UpdateFooData` **không cần** `organization_id` (org không thay đổi sau khi tạo)

### Lớp 4 — Action

- [ ] `StoreFooAction::handle()` truyền `'organization_id' => $data->organization_id` vào `create([])`
- [ ] `UpdateFooAction::handle()` **không** include `organization_id` trong `update([])`

> **Lưu ý:** `BelongsToOrganization` trait auto-set `organization_id` từ `TenantContext` khi `creating`,  
> nên nếu model extend đúng thì sẽ tự được set. Tuy nhiên **vẫn nên truyền explicit** từ Data  
> để rõ ràng và tránh lỗi khi TenantContext chưa được hydrate (job queue, API không có middleware).

### Lớp 5 — Controller + Views

**Controller:**
- [ ] `create()` và `edit()` truyền `$organizations` và `$defaultOrgId` xuống view
- [ ] Logic phân quyền: DN user → chỉ org của họ; Admin → tất cả org

**Views:**
- [ ] Field "Tổ chức" là **field đầu tiên** trong tab Thông tin cơ bản (hoặc đầu form nếu flat form)
- [ ] Dùng TomSelect chuẩn (`id="ts-organization"`, class `ts-init`) khi admin
- [ ] Dùng readonly input + hidden field khi DN user (1 org)
- [ ] `tabFields.basic` trong Alpine x-data phải include `'organization_id'`

---

## 5. Code template

### 5.1 Migration (thêm vào bảng đã tồn tại)

```php
// database/migrations/YYYY_MM_DD_XXXXXX_alter_[table]_add_organization_id.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('[table]', function (Blueprint $table) {
            // Kiểm tra trước khi add để migration idempotent
            if (!Schema::hasColumn('[table]', 'organization_id')) {
                $table->foreignId('organization_id')
                      ->nullable()
                      ->constrained()
                      ->nullOnDelete()
                      ->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('[table]', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
};
```

### 5.2 Model

```php
use App\Foundation\Models\TenantAwareModel;

class [Entity] extends TenantAwareModel
{
    protected $fillable = [
        'organization_id',  // ← bắt buộc
        // ... các field khác
    ];
}
```

### 5.3 Store Data

```php
use Spatie\LaravelData\Data;

class Store[Entity]Data extends Data
{
    public function __construct(
        #[Required]
        public readonly int $organization_id,  // ← field đầu tiên

        // ... các field khác
    ) {}

    public static function rules(): array
    {
        return [
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
            // ... các rule khác
        ];
    }
}
```

### 5.4 Store Action

```php
class Store[Entity]Action
{
    use AsAction;

    public function handle(Store[Entity]Data $data): [Entity]
    {
        return [Entity]::create([
            'organization_id' => $data->organization_id,  // ← explicit, không dựa vào TenantContext
            // ... các field khác
        ]);
    }
}
```

### 5.5 Controller — create() và edit()

```php
use App\Shared\Tenancy\Models\Organization;

public function create()
{
    [$organizations, $defaultOrgId] = $this->_resolveOrganizations();

    // ... các data khác

    return view('[module]::create', compact(
        'organizations', 'defaultOrgId',
        // ... compact các var khác
    ));
}

public function edit([Entity] $entity)
{
    [$organizations] = $this->_resolveOrganizations();

    // ... các data khác

    return view('[module]::edit', compact(
        'entity', 'organizations',
        // ... compact các var khác
    ));
}

/**
 * DN user chỉ thấy org của họ; admin thấy tất cả.
 * Return: [$organizations, $defaultOrgId]
 */
private function _resolveOrganizations(): array
{
    $userOrgId = auth()->user()->organization_id;

    if ($userOrgId) {
        return [
            Organization::where('id', $userOrgId)->get(['id', 'name']),
            $userOrgId,
        ];
    }

    return [
        Organization::orderBy('name')->get(['id', 'name']),
        null,
    ];
}
```

### 5.6 View — Blade (Tab form)

Thêm vào `tabFields.basic` trong Alpine x-data:

```blade
tabFields: {
    basic: ['organization_id', 'name', 'code', ...],  {{-- organization_id phải có --}}
    ...
},
```

Field HTML — đặt **đầu tiên** trong grid của tab Thông tin cơ bản:

```blade
{{-- Tab: Thông tin cơ bản --}}
<div x-show="tab === 'basic'" data-tab-label="Thông tin cơ bản" class="space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        {{-- ── Tổ chức: LUÔN là field đầu tiên ─────────────────────── --}}
        <div class="form-control sm:col-span-2">
            <label class="label py-0 pb-1.5">
                <span class="label-text font-medium">Tổ chức <span class="text-error">*</span></span>
            </label>
            @if($organizations->count() === 1)
                {{-- DN user: locked, không thể thay đổi --}}
                <input type="hidden" name="organization_id" value="{{ $organizations->first()->id }}">
                <input type="text" value="{{ $organizations->first()->name }}" readonly
                       class="input input-bordered input-sm w-full bg-base-200 cursor-not-allowed">
                <p class="mt-1 text-xs text-base-content/40">Xác định từ tài khoản của bạn.</p>
            @else
                {{-- Admin: chọn tự do --}}
                <select id="ts-organization" name="organization_id"
                        class="select select-bordered select-sm w-full ts-init
                               @error('organization_id') select-error @enderror"
                        data-ts-placeholder="— Chọn tổ chức —"
                        data-req="Vui lòng chọn tổ chức">
                    <option value="">— Chọn tổ chức —</option>
                    @foreach($organizations as $org)
                    <option value="{{ $org->id }}"
                        {{-- Create: --}} {{ old('organization_id', $defaultOrgId ?? '') == $org->id ? 'selected' : '' }}
                        {{-- Edit: thay $defaultOrgId bằng $model->organization_id --}}
                    >{{ $org->name }}</option>
                    @endforeach
                </select>
                @error('organization_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
            @endif
        </div>

        {{-- ── Tên [entity]: field thứ hai ─────────────────────────── --}}
        <div class="form-control sm:col-span-2">
            <label class="label py-0 pb-1.5">
                <span class="label-text font-medium">Tên ... <span class="text-error">*</span></span>
            </label>
            <input type="text" name="name" value="{{ old('name') }}"
                   data-req="Vui lòng nhập tên ..."
                   class="input input-bordered input-sm w-full @error('name') input-error @enderror"
                   placeholder="VD: ...">
            @error('name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
        </div>

        {{-- ... các field còn lại ... --}}

    </div>
</div>
```

**Edit form** — chỉ khác 1 chỗ trong option selected:

```blade
{{ old('organization_id', $[entity]->organization_id) == $org->id ? 'selected' : '' }}
```

### 5.7 View — Blade (Flat form)

Với flat form (≤ 10 trường), đặt field "Tổ chức" là field đầu tiên trong card:

```blade
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            {{-- Tổ chức luôn đầu tiên --}}
            <div class="form-control sm:col-span-2">
                {{-- copy template 5.6 ở trên --}}
            </div>

            {{-- ... các field khác --}}
        </div>
    </div>
</div>
```

---

## 6. Logic phân quyền tổ chức

### Nguyên tắc hiện tại (Admin only)

- Admin (`user.organization_id = null`) → thấy **tất cả** org, chọn tự do
- DN user (`user.organization_id != null`) → chỉ thấy **1 org** của họ, field bị locked

### Mở rộng cho tài khoản DN

Khi một tài khoản DN được kích hoạt, hệ thống tự động hoạt động đúng vì logic đã được handle trong `_resolveOrganizations()`:

```
user.organization_id = null  →  Admin  →  select all orgs, chọn tự do
user.organization_id = X     →  DN     →  chỉ thấy org X, field locked (readonly + hidden input)
```

**Không cần thay đổi gì thêm** khi DN account được activate — chỉ cần đảm bảo `user.organization_id` được set đúng khi onboard DN.

### Nếu cần role-based (tương lai)

```php
private function _resolveOrganizations(): array
{
    $user = auth()->user();

    // System Admin không có org → thấy tất cả
    if ($user->hasRole('System_Admin') || !$user->organization_id) {
        return [Organization::orderBy('name')->get(['id', 'name']), null];
    }

    // Mọi user khác → locked vào org của họ
    return [
        Organization::where('id', $user->organization_id)->get(['id', 'name']),
        $user->organization_id,
    ];
}
```

---

## 7. Thứ tự thực hiện khuyến nghị

### Nhóm ưu tiên 1 — Chỉ cần store action + views (model/migration đã đủ)

Các module này đã có `TenantAwareModel`, `organization_id` trong migration và fillable/data.  
Chỉ cần: **Store Action explicit** + **Views**.

| Module | Việc cần làm |
|---|---|
| Department | Store action + create/edit views |
| JobTitle | Store action + create/edit views |
| KcCategory | Store action + create/edit views |
| Leave | Store action (check partial) + views nếu có |
| PerformanceReview | Store action (check partial) + create/edit views |
| Project | Store action + create/edit views |
| Employee | Views (store action đã có) |
| KpiGoal | Data class (check partial) + store action + views nếu có |

**Ước lượng:** ~15 phút/module × 8 = ~2 giờ

### Nhóm ưu tiên 2 — Cần thêm TenantAwareModel

Các module này có `organization_id` trong model/migration nhưng **chưa extend TenantAwareModel**.  
Cần: **Model extend** + **Data** + **Store Action** + **Views** (nếu có).

| Module | Việc cần làm |
|---|---|
| Lead | Extend TenantAwareModel, StoreLead Data/Action, views nếu có |
| LeadPipelineStage | Extend TenantAwareModel, Data/Action fix |
| LeadSource | Extend TenantAwareModel, Data/Action fix |

> ⚠️ **Thận trọng với Lead:** model có nhiều relationships và queries. Kiểm tra kỹ `withoutTenant()` calls sau khi thêm global scope.

### Nhóm ưu tiên 3 — Cần làm đầy đủ từ đầu

| Module | Việc cần làm |
|---|---|
| Recruitment | Migration add org_id + Model extend + Data + Action + Views |
| Survey | Migration create + Model extend + Data + Action + Views |
| JobPosting | Model extend + Data fix + Action fix + Views |
| KcItem | Model extend + Data check + Action + Views |

---

## Phụ lục — Câu hỏi thường gặp

**Q: TenantContext chưa được set trong queue job thì sao?**  
A: Dùng `TenantAwareJob` (app/Foundation/TenantAwareJob.php) làm base class cho job. Nó restore `TenantContext` từ `organization_id` đã serialize trong job payload.

**Q: API endpoint không có tenant middleware thì sao?**  
A: Truyền `organization_id` explicit từ `$data->organization_id` trong Store Action thay vì dựa vào TenantContext auto-set. Đây là lý do template yêu cầu explicit assignment.

**Q: Có cần `organization()` relationship trong mỗi model không?**  
A: Không — `BelongsToOrganization` trait đã cung cấp relationship này. Chỉ khai báo thêm trong model nếu cần eager loading với tên khác hoặc constraints đặc biệt.

**Q: `withoutTenant()` scope dùng khi nào?**  
A: Khi cần query cross-tenant, ví dụ admin dashboard thống kê toàn hệ thống, hoặc khi lấy parent options (như Branch lấy `parentOptions` của chính org đó bằng `where('organization_id', $orgId)`).
