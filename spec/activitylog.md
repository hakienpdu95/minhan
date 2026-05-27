# ActivityLog Module — Specification (Package-Optimized)

> **Stack đầy đủ**: Laravel 13 · PHP 8.4 · MySQL 8+ (production) / SQLite (dev — xem ghi chú tương thích §2.1) · Redis (`predis/predis`)
> **Packages tận dụng**:
> - `spatie/laravel-activitylog ^5.0` — engine lõi ghi log Model
> - `spatie/laravel-data ^4.23` — typed DTOs thay readonly class
> - `spatie/laravel-permission ^7.4` — phân quyền admin UI
> - `lorisleiva/laravel-actions ^2.10` — Action as Job/Command/Controller
> - `rap2hpoutre/fast-excel ^5.7` — export không tốn RAM
> - `predis/predis ^3.0` — Redis queue + cache
>
> **Scale mục tiêu**: <10k log/ngày · 1 server VPS · MySQL 8+ · chỉ có Redis
>
> **Triết lý**: tận dụng tối đa những gì package đã làm tốt, chỉ tự build
> những gì package không có (level, typed context, HTTP tracking, alert).
>
> **Cập nhật**: 2026-05-26

---

## Mục lục

0. [Chiến lược chuyển đổi từ code hiện tại](#0-chiến-lược-chuyển-đổi-từ-code-hiện-tại)
1. [Chiến lược tích hợp Spatie ActivityLog](#1-chiến-lược-tích-hợp-spatie-activitylog)
2. [Database Schema — mở rộng trên Spatie](#2-database-schema)
3. [DTOs với spatie/laravel-data](#3-dtos-với-spatielaravel-data)
4. [Core Engine](#4-core-engine)
5. [Actions với lorisleiva/laravel-actions](#5-actions-với-lorisleivallaravel-actions)
6. [Model Observers — dùng LogsActivity của Spatie](#6-model-observers--dùng-logsactivity-của-spatie)
7. [Event Listeners](#7-event-listeners)
8. [Middleware & Request Tracking](#8-middleware--request-tracking)
9. [Routes & Controllers](#9-routes--controllers)
10. [Views — Admin UI](#10-views--admin-ui)
11. [Alerting](#11-alerting)
12. [Export với FastExcel](#12-export-với-fastexcel)
13. [Retention & Cleanup](#13-retention--cleanup)
14. [Permissions với spatie/laravel-permission](#14-permissions-với-spatielaravel-permission)
15. [Tích hợp module khác](#15-tích-hợp-module-khác)
16. [Config](#16-config)
17. [Migrations hoàn chỉnh](#17-migrations-hoàn-chỉnh)
18. [Seeders](#18-seeders)
19. [Thứ tự triển khai](#19-thứ-tự-triển-khai)

---

## 0. Chiến lược chuyển đổi từ code hiện tại

### 0.1 Tình trạng hiện tại

Hệ thống đang dùng `App\Shared\Support\ActivityLogger` (wrapper Spatie) với API cũ,
song song với các call site dùng thẳng `activity()` builder của Spatie:

```php
// API cũ — đang dùng trong >20 call site
ActivityLogger::log('created', $model);
ActivityLogger::logEvent('organization.registered', [...]);
ActivityLogger::on('auth')->event('login')->log('login');

// Raw Spatie builder — dùng trong Organization Listeners, Survey Actions
activity()->causedBy(Auth::user())->performedOn($model)->withProperties([...])->log('event');
```

Call sites phân bố:
- `Modules/Auth/app/Actions/RegisterOrganizationAction.php`
- `Modules/Auth/app/Listeners/LogSuccessfulLogin.php`
- `Modules/Organization/app/Listeners/LogOrganizationCreated.php`
- `Modules/Organization/app/Listeners/LogOrganizationUpdated.php`
- `Modules/Survey/app/Actions/*` — hơn 10 file
- `Modules/Survey/app/Http/Controllers/SurveyController.php`

### 0.2 Chiến lược migration — song song, không breaking

Giữ `App\Shared\Support\ActivityLogger` cũ hoạt động trong quá trình chuyển đổi.
Module `ActivityLog` mới dùng class `Modules\ActivityLog\app\Core\ActivityLogger` song song.
Migrate từng module theo 4 phase:

| Phase | Hạng mục | Mô tả |
|-------|----------|-------|
| 1 | Infrastructure | Migrations, models, core engine — **không đụng code cũ** |
| 2 | Survey module | Chuyển toàn bộ call site Survey sang `ActivityLogger::*` mới |
| 3 | Auth + Organization | Chuyển Listeners, Actions |
| 4 | Cleanup | Xóa `App\Shared\Support\ActivityLogger` cũ |

### 0.3 Namespace conflict trong Phase 1-3

Dùng **alias** trong các file đang migrate để tránh nhầm lẫn:

```php
use Modules\ActivityLog\app\Core\ActivityLogger as ActivityLog;
// hoặc import đầy đủ khi viết code mới trong module Survey
```

### 0.4 Fix namespace `LogsActivity` trong models Survey hiện tại

Spatie v5 đổi namespace trait. Các model Survey đang import sai (Spatie v4 path):

```php
// HIỆN TẠI — SAI với Spatie v5
use Spatie\Activitylog\Models\Concerns\LogsActivity;

// ĐÚNG — Spatie v5
use Spatie\Activitylog\Traits\LogsActivity;
```

Models cần fix khi thực hiện Phase 2:
`Assessment`, `Persona`, `PainPointRule`, `RecommendationRule`, `ScoreBand`, `ScoreRule`.

### 0.5 Xử lý column `attribute_changes` hiện có

Migration tại `database/migrations/vendor/2026_05_13_020054_create_activity_log_table.php`
đã tạo bảng `activity_log` với column `attribute_changes JSON` (custom, không có trong Spatie chuẩn).
Column này không dùng trong spec mới — **giữ nguyên, không drop** (backward compat).

---

## 1. Chiến lược tích hợp Spatie ActivityLog

### 1.1 Spatie làm được gì — dùng trực tiếp

| Tính năng Spatie | Dùng hay không | Lý do |
|-----------------|---------------|-------|
| `LogsActivity` trait trên Model | ✅ Dùng | Tự ghi created/updated/deleted, diff attributes |
| `activity()` log builder | ✅ Dùng | API fluent, causedBy, performedOn, withProperties |
| `Activity` model | ✅ Extend | Thêm columns: level, organization_id, request_id, module, action... |
| `activity_log` table | ✅ Extend | Thêm migrations bổ sung |
| `LogsActivity` on Model | ✅ Dùng | Tự động log CRUD |
| `CleansUpActivityLog` | ✅ Dùng | Tự cleanup theo config |

### 1.2 Spatie KHÔNG có — tự build thêm

| Tính năng cần thêm | Giải pháp |
|--------------------|-----------|
| `level` (info/warning/error/critical) | Thêm column vào `activity_log` |
| `organization_id` — tenant scoping | Thêm column + inject từ `TenantContext` |
| `request_id` để trace 1 request | Thêm column + middleware |
| `module` / `action` thay vì `log_name` | Thêm 2 column; `log_name` = `"{module}.{action}"` (tương thích ngược) |
| `actor_name`, `actor_ip` — snapshot tại thời điểm log | Thêm columns; không join sang `users` về sau |
| `subject_label` — snapshot label của subject | Thêm column; tránh join khi query |
| `session_id` — trace session | Thêm column |
| HTTP context (method, url, status, duration) | Bảng `activity_log_http` riêng |
| Typed context (không JSON) | Bảng `activity_log_contexts` (EAV) |
| Alert rules | Bảng `activity_log_alert_rules` + service |
| Admin UI riêng có filter Tabulator | Custom controllers + views |

### 1.3 Kiến trúc tổng thể

```
Module khác (Survey, Auth, Organization...)
        │
        │  3 cách gọi log:
        │  1. ActivityLogger::info(...)   ← facade tùy chỉnh (wrap Spatie)
        │  2. event(SomeEvent::class)     ← Listener gọi ActivityLogger
        │  3. Model dùng LogsActivity     ← Spatie tự log CRUD
        │
        ▼
  ActivityLogger::log()
        │
        │  dispatch()->onQueue('actlog')
        ▼
  WriteActivityLogAction (asJob)      ← lorisleiva/laravel-actions
        │
        ├── DB::table('activity_log')->insert(...)  [không qua Spatie builder để kiểm soát hoàn toàn]
        ├── INSERT activity_log_contexts  (EAV, typed)
        └── INSERT activity_log_http     (HTTP metadata)
        │
        ▼
  AlertEvaluatorService::evaluate()   ← sau commit
```

---

## 2. Database Schema

### 2.1 Ghi chú tương thích SQLite / MySQL

> **Dev**: SQLite — tất cả migrations dùng `Blueprint` của Laravel (driver-agnostic).
> **Production**: MySQL 8+.
> **Ngoại lệ duy nhất**: Prefix index trên `activity_log_contexts` dùng raw SQL —
> chỉ apply trên MySQL, bỏ qua trên SQLite (kiểm tra `DB::getDriverName()` trong migration).

### 2.2 Mở rộng bảng `activity_log` của Spatie

Bảng `activity_log` đã được tạo tại `database/migrations/vendor/2026_05_13_020054_create_activity_log_table.php`.
Ta **thêm migration bổ sung** các column cần thiết thay vì tạo bảng mới.

**Schema hiện tại trong project** (để tham khảo):
```
id, log_name, description, subject_type, subject_id,
causer_type, causer_id, properties (JSON), event,
attribute_changes (JSON — custom, giữ nguyên),
created_at, updated_at
```

**Thêm vào qua migration bổ sung** (`add_custom_columns_to_activity_log`):
```sql
level          TINYINT UNSIGNED  NOT NULL DEFAULT 2    '1=debug 2=info 3=warning 4=error 5=critical'
organization_id BIGINT UNSIGNED  NULL                  'Tenant — NULL khi CLI/system job'
module         VARCHAR(64)       NULL                  'Survey, Auth, Organization...'
action         VARCHAR(128)      NULL                  'survey_created, login_failed...'
actor_name     VARCHAR(255)      NULL                  'Snapshot tên actor tại thời điểm log'
actor_ip       VARCHAR(45)       NULL                  'IPv4 hoặc IPv6. NULL khi CLI.'
request_id     CHAR(36)          NULL                  'UUID per-request, set bởi middleware'
session_id     VARCHAR(255)      NULL                  'Session ID'
subject_label  VARCHAR(255)      NULL                  'Snapshot label của subject (name/title/email)'
```

> **Tại sao `actor_ip` là VARCHAR(45) thay vì INT UNSIGNED?**
> VARCHAR(45) hỗ trợ cả IPv4 và IPv6 (max 45 chars), driver-agnostic (SQLite không có UNSIGNED),
> và không cần `ip2long()` / `long2ip()` conversion.

**Indexes bổ sung**:
```
idx_org_created   (organization_id, created_at)
idx_level         (level, created_at)
idx_module_action (module, action, created_at)
idx_request       (request_id)
```

**Tại sao không tạo bảng riêng?**
- Spatie `LogsActivity` trait và `activity()` builder ghi thẳng vào `activity_log`.
- Nếu tạo bảng riêng phải fork toàn bộ Spatie internals → mất update package.
- Extend bảng gốc + thêm column → tận dụng 100% Spatie features.

**Tại sao KHÔNG dùng `properties` JSON của Spatie cho context?**
- `properties` là JSON column → không indexable → query chậm.
- Ta để `properties` trống (hoặc để Spatie tự ghi khi dùng `LogsActivity`),
  dùng bảng `activity_log_contexts` riêng cho typed context.

### 2.3 Bảng `activity_log_contexts` — typed key-value

```sql
CREATE TABLE `activity_log_contexts` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `log_id`       BIGINT UNSIGNED NOT NULL,
    `key_name`     VARCHAR(64)   NOT NULL,
    `value_type`   TINYINT UNSIGNED NOT NULL DEFAULT 1
                   COMMENT '1=string 2=integer 3=decimal 4=boolean 5=datetime',
    `val_string`   VARCHAR(500)  NULL,
    `val_integer`  BIGINT        NULL,
    `val_decimal`  DECIMAL(20,6) NULL,
    `val_boolean`  TINYINT(1)    NULL,
    `val_datetime` DATETIME      NULL,
    `created_at`   TIMESTAMP     NULL,

    INDEX `idx_log`         (`log_id`),
    INDEX `idx_key_integer` (`key_name`, `val_integer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Prefix index — MySQL only (xem migration §17)
ALTER TABLE activity_log_contexts
    ADD INDEX idx_key_string (key_name, val_string(64));
```

### 2.4 Bảng `activity_log_http`

```sql
CREATE TABLE `activity_log_http` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `log_id`       BIGINT UNSIGNED NOT NULL,
    `http_method`  TINYINT UNSIGNED NOT NULL
                   COMMENT '1=GET 2=POST 3=PUT 4=PATCH 5=DELETE 6=HEAD 7=OPTIONS',
    `url`          VARCHAR(2000) NOT NULL,
    `route_name`   VARCHAR(191)  NULL,
    `status_code`  SMALLINT UNSIGNED NULL,
    `duration_ms`  SMALLINT UNSIGNED NULL,
    `user_agent`   VARCHAR(500)  NULL,
    `created_at`   TIMESTAMP NULL,

    UNIQUE KEY `uq_log` (`log_id`),
    INDEX `idx_route`  (`route_name`),
    INDEX `idx_status` (`status_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2.5 Bảng `activity_log_alert_rules`

```sql
CREATE TABLE `activity_log_alert_rules` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`             VARCHAR(191) NOT NULL,
    `module`           VARCHAR(64)  NULL COMMENT 'NULL = tất cả module',
    `action`           VARCHAR(128) NULL COMMENT 'NULL = tất cả action',
    `level_min`        TINYINT UNSIGNED NULL,
    `condition_type`   TINYINT UNSIGNED NOT NULL
                       COMMENT '1=first_occurrence 2=count_threshold',
    `threshold_count`  SMALLINT UNSIGNED NULL,
    `window_minutes`   SMALLINT UNSIGNED NULL,
    `notify_channel`   TINYINT UNSIGNED NOT NULL
                       COMMENT '1=email 2=database',
    `notify_target`    VARCHAR(500) NOT NULL,
    `cooldown_minutes` SMALLINT UNSIGNED NOT NULL DEFAULT 60,
    `last_triggered_at` DATETIME NULL,
    `is_active`        TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`       TIMESTAMP NULL,
    `updated_at`       TIMESTAMP NULL,

    INDEX `idx_active` (`is_active`, `module`, `action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 3. DTOs với spatie/laravel-data

Dùng `Spatie\LaravelData\Data` thay vì `readonly class` thuần — tận dụng casting tự động,
`from()` factory, và tích hợp tốt với hệ sinh thái Spatie.

```php
// Modules/ActivityLog/app/Data/HttpSnapshotData.php
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Max;

class HttpSnapshotData extends Data
{
    public function __construct(
        public readonly HttpMethod $method,
        #[Max(2000)]
        public readonly string    $url,
        public readonly ?string   $routeName,
        public readonly ?int      $statusCode,
        public readonly ?int      $durationMs,
        public readonly ?string   $userAgent,
    ) {}

    public static function fromRequest(\Illuminate\Http\Request $req): self
    {
        return new self(
            method:     HttpMethod::fromString($req->method()),
            url:        substr($req->fullUrl(), 0, 2000),
            routeName:  $req->route()?->getName(),
            statusCode: null,   // filled after response by middleware
            durationMs: null,
            userAgent:  $req->userAgent() ? substr($req->userAgent(), 0, 500) : null,
        );
    }
}

// Modules/ActivityLog/app/Data/LogEntryData.php
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;

class LogEntryData extends Data
{
    public function __construct(
        // Tenant
        public readonly ?int          $organizationId,   // ← inject từ TenantContext

        // Actor
        public readonly ?int          $actorId,
        #[WithCast(EnumCast::class)]
        public readonly ActorType     $actorType,
        public readonly string        $actorName,        // snapshot tại thời điểm log
        public readonly ?string       $actorIp,          // IPv4 hoặc IPv6 string

        // Action
        public readonly string        $module,
        public readonly string        $action,
        #[WithCast(EnumCast::class)]
        public readonly LogLevel      $level,

        // Subject
        public readonly ?string       $subjectType,      // FQCN đầy đủ của Model
        public readonly ?int          $subjectId,
        public readonly ?string       $subjectLabel,     // snapshot label

        // Misc
        public readonly ?string       $description,
        public readonly string        $requestId,
        public readonly ?string       $sessionId,

        // Typed context
        public readonly array         $context,

        // HTTP — nullable vì CLI/Job không có
        public readonly ?HttpSnapshotData $http,

        public readonly \DateTimeImmutable $loggedAt,
    ) {}
}
```

---

## 4. Core Engine

### 4.1 Enums (PHP 8.4 backed enum)

```php
// Modules/ActivityLog/app/Enums/LogLevel.php
enum LogLevel: int
{
    case Debug    = 1;
    case Info     = 2;
    case Warning  = 3;
    case Error    = 4;
    case Critical = 5;

    public function label(): string
    {
        return match($this) {
            self::Debug    => 'Debug',
            self::Info     => 'Info',
            self::Warning  => 'Cảnh báo',
            self::Error    => 'Lỗi',
            self::Critical => 'Nghiêm trọng',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Debug    => 'badge-gray',
            self::Info     => 'badge-teal',
            self::Warning  => 'badge-amber',
            self::Error    => 'badge-red',
            self::Critical => 'badge-crimson',
        };
    }
}

// Modules/ActivityLog/app/Enums/ActorType.php
enum ActorType: int
{
    case User     = 1;
    case System   = 2;
    case ApiToken = 3;
    case Job      = 4;
    case Guest    = 5;
}

// Modules/ActivityLog/app/Enums/HttpMethod.php
enum HttpMethod: int
{
    case GET = 1; case POST = 2; case PUT    = 3;
    case PATCH = 4; case DELETE = 5; case HEAD = 6; case OPTIONS = 7;

    public static function fromString(string $m): self
    {
        return match(strtoupper($m)) {
            'GET'    => self::GET,    'POST'   => self::POST,
            'PUT'    => self::PUT,    'PATCH'  => self::PATCH,
            'DELETE' => self::DELETE, 'HEAD'   => self::HEAD,
            default  => self::OPTIONS,
        };
    }
}
```

### 4.2 `ActivityLog` Model — extend Spatie

```php
// Modules/ActivityLog/app/Models/ActivityLog.php
use Spatie\Activitylog\Models\Activity;

class ActivityLog extends Activity
{
    protected $table = 'activity_log';

    protected $casts = [
        'level'      => LogLevel::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────

    public function contexts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ActivityLogContext::class, 'log_id');
    }

    public function http(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ActivityLogHttp::class, 'log_id');
    }

    // ── Accessors ─────────────────────────────────────────────────

    /**
     * Trả về tất cả context dưới dạng key => typed_value
     */
    public function getContextMapAttribute(): array
    {
        return $this->contexts
            ->mapWithKeys(fn($c) => [$c->key_name => $c->typedValue()])
            ->all();
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeForOrganization(\Illuminate\Database\Eloquent\Builder $q, int $orgId): \Illuminate\Database\Eloquent\Builder
    {
        return $q->where('organization_id', $orgId);
    }

    public function scopeModule(\Illuminate\Database\Eloquent\Builder $q, string $module): \Illuminate\Database\Eloquent\Builder
    {
        return $q->where('module', $module);
    }

    public function scopeAction(\Illuminate\Database\Eloquent\Builder $q, string $action): \Illuminate\Database\Eloquent\Builder
    {
        return $q->where('action', $action);
    }

    public function scopeLevel(\Illuminate\Database\Eloquent\Builder $q, int $min): \Illuminate\Database\Eloquent\Builder
    {
        return $q->where('level', '>=', $min);
    }

    /**
     * Lọc theo subject — dùng FQCN (get_class) để khớp với giá trị lưu trong DB.
     * KHÔNG dùng class_basename() vì WriteActivityLogAction lưu FQCN đầy đủ.
     */
    public function scopeForSubject(\Illuminate\Database\Eloquent\Builder $q, \Illuminate\Database\Eloquent\Model $model): \Illuminate\Database\Eloquent\Builder
    {
        return $q->where('subject_type', get_class($model))
                 ->where('subject_id', $model->getKey());
    }
}
```

### 4.3 `ActivityLogContext` Model

```php
// Modules/ActivityLog/app/Models/ActivityLogContext.php
class ActivityLogContext extends \Illuminate\Database\Eloquent\Model
{
    public $timestamps = false;

    protected $fillable = [
        'log_id', 'key_name', 'value_type',
        'val_string', 'val_integer', 'val_decimal', 'val_boolean', 'val_datetime',
    ];

    protected $casts = [
        'val_boolean'  => 'boolean',
        'val_datetime' => 'datetime',
    ];

    /**
     * Trả về giá trị đúng kiểu dựa trên value_type
     */
    public function typedValue(): mixed
    {
        return match ($this->value_type) {
            1 => $this->val_string,
            2 => $this->val_integer,
            3 => $this->val_decimal ? (float) $this->val_decimal : null,
            4 => $this->val_boolean,
            5 => $this->val_datetime,
            default => $this->val_string,
        };
    }
}
```

### 4.4 `ActivityLogHttp` Model

```php
// Modules/ActivityLog/app/Models/ActivityLogHttp.php
class ActivityLogHttp extends \Illuminate\Database\Eloquent\Model
{
    public $timestamps = false;

    protected $fillable = [
        'log_id', 'http_method', 'url', 'route_name',
        'status_code', 'duration_ms', 'user_agent', 'created_at',
    ];

    protected $casts = [
        'http_method' => HttpMethod::class,
        'created_at'  => 'datetime',
    ];

    public function log(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ActivityLog::class, 'log_id');
    }

    public function getHttpMethodLabelAttribute(): string
    {
        return $this->http_method?->name ?? '';
    }
}
```

### 4.5 `ActivityLogAlertRule` Model

```php
// Modules/ActivityLog/app/Models/ActivityLogAlertRule.php
class ActivityLogAlertRule extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = [
        'name', 'module', 'action', 'level_min',
        'condition_type', 'threshold_count', 'window_minutes',
        'notify_channel', 'notify_target',
        'cooldown_minutes', 'last_triggered_at', 'is_active',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    /**
     * condition_type:
     *   1 = first_occurrence — trigger ngay lần đầu match
     *   2 = count_threshold  — trigger khi đạt threshold_count trong window_minutes
     */
    public function isCountThreshold(): bool
    {
        return $this->condition_type === 2;
    }
}
```

### 4.6 `LogEntryBuilder` — tái sử dụng giữa các call context

```php
// Modules/ActivityLog/app/Core/LogEntryBuilder.php
use App\Shared\Tenancy\TenantContext;

final class LogEntryBuilder
{
    private const SENSITIVE = [
        'password', 'token', 'secret', 'token_encrypted',
        'api_key', 'credit_card', 'cvv', 'respondent_ip',
    ];

    public function build(
        string   $module,
        string   $action,
        mixed    $subject,
        array    $context,
        LogLevel $level,
        ?string  $description,
    ): LogEntryData {
        return new LogEntryData(
            organizationId: $this->organizationId(),
            actorId:        $this->actorId(),
            actorType:      $this->actorType(),
            actorName:      $this->actorName(),
            actorIp:        $this->actorIp(),
            module:         $module,
            action:         $action,
            level:          $level,
            subjectType:    $this->subjectType($subject),
            subjectId:      $this->subjectId($subject),
            subjectLabel:   $this->subjectLabel($subject),
            description:    $description ? substr($description, 0, 500) : null,
            requestId:      request()->header('X-Request-Id', (string) \Str::uuid()),
            sessionId:      $this->sessionId(),
            context:        $this->sanitize($context),
            http:           app()->runningInConsole() ? null : HttpSnapshotData::fromRequest(request()),
            loggedAt:       new \DateTimeImmutable(),
        );
    }

    private function organizationId(): ?int
    {
        return TenantContext::isSet() ? TenantContext::getOrganizationId() : null;
    }

    private function actorId(): ?int { return auth()->id(); }

    private function actorType(): ActorType
    {
        if (app()->runningInConsole()) return ActorType::System;
        if (auth()->check())           return ActorType::User;
        if (request()->bearerToken())  return ActorType::ApiToken;
        return ActorType::Guest;
    }

    private function actorName(): string
    {
        if (app()->runningInConsole()) return 'system';
        $u = auth()->user();
        if ($u) return $u->name ?? $u->email ?? "User#{$u->id}";
        return 'guest';
    }

    private function actorIp(): ?string
    {
        if (app()->runningInConsole()) return null;
        return request()->ip() ?: null;
    }

    private function subjectType(mixed $s): ?string
    {
        // Lưu FQCN đầy đủ — nhất quán với scopeForSubject và HasActivityLog trait
        return $s instanceof \Illuminate\Database\Eloquent\Model ? get_class($s) : null;
    }

    private function subjectId(mixed $s): ?int
    {
        return $s instanceof \Illuminate\Database\Eloquent\Model ? (int) $s->getKey() : null;
    }

    private function subjectLabel(mixed $s): ?string
    {
        if (!($s instanceof \Illuminate\Database\Eloquent\Model)) return null;
        if (method_exists($s, 'getActivityLabel')) {
            return substr($s->getActivityLabel(), 0, 255);
        }
        $label = $s->name ?? $s->title ?? $s->email ?? "#{$s->getKey()}";
        return substr((string) $label, 0, 255);
    }

    private function sessionId(): ?string
    {
        try { return session()->getId(); } catch (\Throwable) { return null; }
    }

    private function sanitize(array $ctx): array
    {
        array_walk_recursive($ctx, function (&$v, $k) {
            if (in_array(strtolower((string) $k), self::SENSITIVE, true)) {
                $v = '[REDACTED]';
            }
        });
        return $ctx;
    }
}
```

### 4.7 `ActivityLogger` — Facade wrapper

```php
// Modules/ActivityLog/app/Core/ActivityLogger.php
final class ActivityLogger
{
    public static function log(
        string   $module,
        string   $action,
        mixed    $subject     = null,
        array    $context     = [],
        LogLevel $level       = LogLevel::Info,
        ?string  $description = null,
    ): void {
        if ($level->value < config('activitylog.min_level', LogLevel::Info->value)) {
            return;
        }

        try {
            $entry = app(LogEntryBuilder::class)->build(
                $module, $action, $subject, $context, $level, $description
            );
            WriteActivityLogAction::dispatch($entry)->onQueue(
                config('activitylog.queue', 'actlog')
            );
        } catch (\Throwable $e) {
            logger()->error('[ActivityLog] dispatch failed', [
                'module' => $module, 'action' => $action, 'error' => $e->getMessage(),
            ]);
        }
    }

    public static function info(string $m, string $a, mixed $s = null, array $c = []): void
    {
        self::log($m, $a, $s, $c, LogLevel::Info);
    }

    public static function warning(string $m, string $a, mixed $s = null, array $c = []): void
    {
        self::log($m, $a, $s, $c, LogLevel::Warning);
    }

    public static function error(string $m, string $a, mixed $s = null, array $c = [], string $desc = ''): void
    {
        self::log($m, $a, $s, $c, LogLevel::Error, $desc ?: null);
    }

    public static function critical(string $m, string $a, mixed $s = null, array $c = []): void
    {
        self::log($m, $a, $s, $c, LogLevel::Critical);
    }
}
```

---

## 5. Actions với lorisleiva/laravel-actions

`lorisleiva/laravel-actions` cho phép 1 class vừa là Job, vừa là Command,
vừa là Controller. Dùng `asJob()` cho write async, `asCommand()` cho cleanup.

### 5.1 `WriteActivityLogAction` — core action

```php
// Modules/ActivityLog/app/Actions/WriteActivityLogAction.php
use Lorisleiva\Actions\Concerns\AsAction;

class WriteActivityLogAction
{
    use AsAction;

    // ── Job config ────────────────────────────────────────────────
    public string $jobQueue   = 'actlog';
    public int    $jobTries   = 5;
    public array  $jobBackoff = [3, 10, 30, 60, 120];
    public int    $jobTimeout = 30;

    // ── Handle ───────────────────────────────────────────────────

    public function handle(LogEntryData $entry): void
    {
        \DB::transaction(function () use ($entry) {
            $logId = $this->writeMainLog($entry);
            $this->writeContexts($logId, $entry->context);
            $this->writeHttp($logId, $entry);
        });

        // Alert evaluation ngoài transaction — không delay commit
        app(AlertEvaluatorService::class)->evaluate($entry);
    }

    // ── Job failed handler ────────────────────────────────────────

    public function jobFailed(\Throwable $e, LogEntryData $entry): void
    {
        logger()->error('[ActivityLog] WriteActivityLogAction failed permanently', [
            'module' => $entry->module,
            'action' => $entry->action,
            'error'  => $e->getMessage(),
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────

    private function writeMainLog(LogEntryData $entry): int
    {
        // Dùng DB::table thay vì Spatie builder để kiểm soát custom columns hoàn toàn
        return \DB::table('activity_log')->insertGetId([
            'log_name'        => "{$entry->module}.{$entry->action}",
            'description'     => $entry->description ?? $entry->action,
            'subject_type'    => $entry->subjectType,   // FQCN đầy đủ
            'subject_id'      => $entry->subjectId,
            'causer_type'     => $entry->actorId ? \App\Models\User::class : null,
            'causer_id'       => $entry->actorId,
            'event'           => $entry->action,
            // Custom columns
            'organization_id' => $entry->organizationId,
            'level'           => $entry->level->value,
            'module'          => $entry->module,
            'action'          => $entry->action,
            'actor_name'      => $entry->actorName,
            'actor_ip'        => $entry->actorIp,
            'request_id'      => $entry->requestId,
            'session_id'      => $entry->sessionId,
            'subject_label'   => $entry->subjectLabel,
            'created_at'      => $entry->loggedAt->format('Y-m-d H:i:s'),
            'updated_at'      => now(),
        ]);
    }

    private function writeContexts(int $logId, array $context): void
    {
        if (empty($context)) return;

        $rows = [];
        foreach ($context as $key => $value) {
            $rows[] = $this->buildContextRow($logId, $key, $value);
        }
        \DB::table('activity_log_contexts')->insert($rows);
    }

    private function writeHttp(int $logId, LogEntryData $entry): void
    {
        if ($entry->http === null) return;

        // Enrich status_code + duration từ Cache (set bởi CaptureHttpContext middleware).
        // TTL = 60s. Nếu job chạy sau 60s, status_code/duration_ms sẽ là NULL — chấp nhận được.
        $cached = \Cache::pull("actlog:http_ctx:{$entry->requestId}");

        \DB::table('activity_log_http')->insert([
            'log_id'      => $logId,
            'http_method' => $entry->http->method->value,
            'url'         => $entry->http->url,
            'route_name'  => $entry->http->routeName,
            'status_code' => $cached['status_code'] ?? null,
            'duration_ms' => $cached['duration_ms'] ?? null,
            'user_agent'  => $entry->http->userAgent,
            'created_at'  => now(),
        ]);
    }

    private function buildContextRow(int $logId, string $key, mixed $value): array
    {
        $row = [
            'log_id'       => $logId,
            'key_name'     => substr($key, 0, 64),
            'value_type'   => 1,
            'val_string'   => null, 'val_integer'  => null,
            'val_decimal'  => null, 'val_boolean'  => null, 'val_datetime' => null,
            'created_at'   => now(),
        ];

        if      (is_bool($value))                     { $row['value_type'] = 4; $row['val_boolean']  = (int) $value; }
        elseif  (is_int($value))                      { $row['value_type'] = 2; $row['val_integer']  = $value; }
        elseif  (is_float($value))                    { $row['value_type'] = 3; $row['val_decimal']  = $value; }
        elseif  ($value instanceof \DateTimeInterface){ $row['value_type'] = 5; $row['val_datetime'] = $value->format('Y-m-d H:i:s'); }
        elseif  ($value instanceof \BackedEnum) {
            $v = $value->value;
            if (is_int($v)) { $row['value_type'] = 2; $row['val_integer'] = $v; }
            else             { $row['value_type'] = 1; $row['val_string']  = substr((string) $v, 0, 500); }
        }
        else { $row['value_type'] = 1; $row['val_string'] = substr((string) $value, 0, 500); }

        return $row;
    }
}
```

### 5.2 `PurgeOldLogsAction` — dùng asCommand + asJob

```php
// Modules/ActivityLog/app/Actions/PurgeOldLogsAction.php
use Lorisleiva\Actions\Concerns\AsAction;

class PurgeOldLogsAction
{
    use AsAction;

    public string $commandSignature   = 'activitylog:purge';
    public string $commandDescription = 'Xóa log cũ theo retention policy';

    public function handle(): void
    {
        $cutoff    = now()->subDays(config('activitylog.retain_days', 90));
        $batchSize = 1000;
        $deleted   = 0;

        // Xóa context + http trước (không có FK constraint), sau đó xóa log chính
        // Batch DELETE tránh lock table quá lâu
        do {
            $ids = \DB::table('activity_log')
                ->where('created_at', '<', $cutoff)
                ->limit($batchSize)
                ->pluck('id');

            if ($ids->isEmpty()) break;

            \DB::table('activity_log_contexts')->whereIn('log_id', $ids)->delete();
            \DB::table('activity_log_http')->whereIn('log_id', $ids)->delete();
            $deleted += \DB::table('activity_log')->whereIn('id', $ids)->delete();

            usleep(10_000); // 10ms — nhường CPU giữa các batch
        } while ($ids->count() === $batchSize);

        if ($deleted > 0) {
            ActivityLogger::info('ActivityLog', 'logs_purged', null, [
                'deleted_count' => $deleted,
                'cutoff_date'   => $cutoff->toDateString(),
            ]);
        }
    }

    public function asCommand(\Illuminate\Console\Command $command): void
    {
        $this->handle();
        $command->info('ActivityLog: purge hoàn tất.');
    }
}
```

### 5.3 `ExportActivityLogsAction` — dùng FastExcel

```php
// Modules/ActivityLog/app/Actions/ExportActivityLogsAction.php
use Lorisleiva\Actions\Concerns\AsAction;
use Rap2hpoutre\FastExcel\FastExcel;

class ExportActivityLogsAction
{
    use AsAction;

    public string $jobQueue   = 'actlog';
    public int    $jobTimeout = 300;

    // Danh sách filter hợp lệ — export controller validate trước khi dispatch
    public const ALLOWED_FILTERS = [
        'module', 'action', 'level_min', 'actor_id',
        'date_from', 'date_to', 'search', 'organization_id',
    ];

    public function handle(array $filters, string $exportKey): void
    {
        $path = storage_path("app/exports/actlog_{$exportKey}.xlsx");

        // Eager load http để tránh N+1 (causer không cần load — dùng actor_name snapshot)
        $query = ActivityLog::with(['http'])
            ->tap(fn($q) => $this->applyFilters($q, $filters))
            ->orderByDesc('created_at');

        $collection = $query->lazy(500)->map(fn(ActivityLog $log) => [
            'ID'          => $log->id,
            'Thời gian'   => $log->created_at?->format('d/m/Y H:i:s'),
            'Cấp độ'     => $log->level->label(),
            'Module'      => $log->module,
            'Action'      => $log->action,
            'Actor'       => $log->actor_name ?? ($log->causer_id ? "User#{$log->causer_id}" : 'system'),
            'Actor IP'    => $log->actor_ip,
            'Subject'     => $log->subject_type ? "{$log->subject_type}#{$log->subject_id}" : null,
            'Label'       => $log->subject_label,
            'Mô tả'      => $log->description,
            'Request ID'  => $log->request_id,
            'URL'         => $log->http?->url,
            'Status'      => $log->http?->status_code,
            'Duration ms' => $log->http?->duration_ms,
        ]);

        (new FastExcel($collection))->export($path);

        // Lưu path vào Redis TTL 1h để download
        \Cache::put("actlog:export:{$exportKey}", $path, 3600);
    }

    private function applyFilters(\Illuminate\Database\Eloquent\Builder $q, array $filters): void
    {
        if (!empty($filters['organization_id'])) $q->where('organization_id', $filters['organization_id']);
        if (!empty($filters['module']))           $q->where('module', $filters['module']);
        if (!empty($filters['action']))           $q->where('action', $filters['action']);
        if (!empty($filters['level_min']))        $q->where('level', '>=', $filters['level_min']);
        if (!empty($filters['actor_id']))         $q->where('causer_id', $filters['actor_id']);
        if (!empty($filters['date_from']))        $q->where('created_at', '>=', $filters['date_from'].' 00:00:00');
        if (!empty($filters['date_to']))          $q->where('created_at', '<=', $filters['date_to'].' 23:59:59');
        if (!empty($filters['search'])) {
            $t = '%'.$filters['search'].'%';
            $q->where(fn($q2) => $q2->where('description', 'like', $t)
                                    ->orWhere('action', 'like', $t));
        }
    }
}
```

---

## 6. Model Observers — dùng `LogsActivity` của Spatie

> **Lưu ý namespace Spatie v5**: Dùng `Spatie\Activitylog\Traits\LogsActivity`.
> Các model Survey hiện tại import sai path v4 (`Spatie\Activitylog\Models\Concerns\LogsActivity`) —
> cần fix trong Phase 2 (xem §0.4).

### 6.1 Tận dụng `LogsActivity` trait

Với các Model đơn giản chỉ cần log CRUD, dùng thẳng Spatie trait:

```php
// Trong Survey model — KHÔNG cần Observer thủ công
use Spatie\Activitylog\Traits\LogsActivity;   // ← Spatie v5 namespace
use Spatie\Activitylog\LogOptions;

class Survey extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'slug', 'status', 'assessment_code'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $event) => match($event) {
                'created' => "Tạo khảo sát: {$this->title}",
                'updated' => "Cập nhật khảo sát: {$this->title}",
                'deleted' => "Xóa khảo sát: {$this->title}",
                default   => $event,
            })
            ->useLogName('Survey.survey_{event}');
            // log_name = 'Survey.survey_created' — tương thích với module.action format
    }
}
```

### 6.2 `BaseModelObserver` — khi cần custom context

Khi cần ghi thêm context riêng (before/after, business logic), dùng Observer thủ công
thay vì `LogsActivity`:

```php
// Modules/ActivityLog/app/Observers/BaseModelObserver.php
abstract class BaseModelObserver
{
    abstract protected function module(): string;
    abstract protected function resourceCode(): string;

    protected function createdContext(\Illuminate\Database\Eloquent\Model $m): array  { return []; }
    protected function updatedContext(\Illuminate\Database\Eloquent\Model $m): array  {
        return ['changed_fields' => implode(',', array_keys($m->getChanges()))];
    }
    protected function deletedContext(\Illuminate\Database\Eloquent\Model $m): array  { return []; }
    protected function shouldLogUpdate(\Illuminate\Database\Eloquent\Model $m): bool  {
        return count(array_diff(array_keys($m->getChanges()), ['updated_at'])) > 0;
    }
    protected function deleteLevel(\Illuminate\Database\Eloquent\Model $m): LogLevel  {
        return LogLevel::Warning;
    }

    public function created(\Illuminate\Database\Eloquent\Model $m): void
    {
        ActivityLogger::info($this->module(), "{$this->resourceCode()}_created", $m,
            $this->createdContext($m));
    }

    public function updated(\Illuminate\Database\Eloquent\Model $m): void
    {
        if (!$this->shouldLogUpdate($m)) return;
        ActivityLogger::info($this->module(), "{$this->resourceCode()}_updated", $m,
            $this->updatedContext($m));
    }

    public function deleted(\Illuminate\Database\Eloquent\Model $m): void
    {
        ActivityLogger::log($this->module(), "{$this->resourceCode()}_deleted", $m,
            $this->deletedContext($m), $this->deleteLevel($m));
    }
}
```

**Khi nào dùng `LogsActivity` (Spatie) vs `BaseModelObserver` (custom)?**

| Tình huống | Dùng cái gì |
|-----------|-------------|
| Log CRUD cơ bản, chỉ cần biết field nào đổi | `LogsActivity` trait của Spatie |
| Cần ghi thêm context riêng (business data) | `BaseModelObserver` custom |
| Cần logic đặc biệt (vd: delete level phụ thuộc data) | `BaseModelObserver` custom |
| Model đã dùng `LogsActivity` nhưng muốn thêm context | Kết hợp: giữ Spatie log + gọi thêm `ActivityLogger::info()` trong Action |

---

## 7. Event Listeners

Module Survey đăng ký Listener của mình — ActivityLog không biết về Survey:

```php
// Modules/Survey/app/Providers/EventServiceProvider.php
protected $listen = [
    \Modules\Survey\Events\SurveyActivated::class      => [
        \Modules\Survey\Listeners\LogSurveyActivated::class,
    ],
    \Modules\Survey\Events\ScoringConfigSaved::class   => [
        \Modules\Survey\Listeners\LogScoringConfigSaved::class,
    ],
    \Illuminate\Auth\Events\Failed::class => [
        \Modules\Auth\Listeners\LogLoginFailed::class,
    ],
];

// Modules/Survey/app/Listeners/LogScoringConfigSaved.php
class LogScoringConfigSaved
{
    public function handle(\Modules\Survey\Events\ScoringConfigSaved $event): void
    {
        ActivityLogger::info('Survey', 'scoring_config_saved', $event->survey, [
            'assessment_code' => $event->assessmentCode,
            'new_version'     => $event->newVersion,     // int → val_integer
            'tabs_changed'    => $event->tabsChanged,    // string → val_string
            'is_breaking'     => $event->isBreaking,     // bool → val_boolean
        ]);
    }
}
```

---

## 8. Middleware & Request Tracking

### 8.1 `InjectRequestId`

```php
// Modules/ActivityLog/app/Http/Middleware/InjectRequestId.php
class InjectRequestId
{
    public function handle(\Illuminate\Http\Request $request, \Closure $next): \Symfony\Component\HttpFoundation\Response
    {
        $requestId = $request->header('X-Request-Id', (string) \Str::uuid());
        $request->headers->set('X-Request-Id', $requestId);
        $response  = $next($request);
        $response->headers->set('X-Request-Id', $requestId);
        return $response;
    }
}
```

### 8.2 `CaptureHttpContext`

```php
// Modules/ActivityLog/app/Http/Middleware/CaptureHttpContext.php
class CaptureHttpContext
{
    // TTL đủ lâu cho job queue xử lý trong điều kiện bình thường.
    // Nếu queue bận > TTL, status_code/duration_ms sẽ là NULL — acceptable.
    private const CACHE_TTL_SECONDS = 60;

    public function handle(\Illuminate\Http\Request $request, \Closure $next): \Symfony\Component\HttpFoundation\Response
    {
        $startMs  = (int)(microtime(true) * 1000);
        $response = $next($request);

        if ($requestId = $request->header('X-Request-Id')) {
            \Cache::put("actlog:http_ctx:{$requestId}", [
                'status_code' => $response->getStatusCode(),
                'duration_ms' => (int)(microtime(true) * 1000) - $startMs,
            ], self::CACHE_TTL_SECONDS);
        }
        return $response;
    }
}
```

### 8.3 Đăng ký trong `bootstrap/app.php`

```php
// bootstrap/app.php
->withMiddleware(function (\Illuminate\Foundation\Configuration\Middleware $middleware) {
    // InjectRequestId phải chạy đầu tiên trong toàn bộ stack
    $middleware->prepend(\Modules\ActivityLog\app\Http\Middleware\InjectRequestId::class);

    $middleware->appendToGroup('web', \Modules\ActivityLog\app\Http\Middleware\CaptureHttpContext::class);
    $middleware->appendToGroup('api', \Modules\ActivityLog\app\Http\Middleware\CaptureHttpContext::class);
})
```

---

## 9. Routes & Controllers

### 9.1 Routes

```php
// Modules/ActivityLog/routes/web.php
use Modules\ActivityLog\app\Http\Controllers\ActivityLogController;
use Modules\ActivityLog\app\Http\Controllers\AlertRuleController;
use Modules\ActivityLog\app\Http\Controllers\ActivityLogApiController;

Route::prefix('dashboard/activity-logs')
    ->middleware(['web', 'auth', 'can:activitylog.view'])
    ->name('activitylog.')
    ->group(function () {
        Route::get('/',                      [ActivityLogController::class, 'index'])         ->name('index');
        Route::get('/{log}',                 [ActivityLogController::class, 'show'])          ->name('show');
        Route::post('/export',               [ActivityLogController::class, 'export'])        ->name('export');
        Route::get('/export/download/{key}', [ActivityLogController::class, 'downloadExport'])->name('export.download');

        Route::prefix('alert-rules')->name('alert-rules.')->middleware('can:activitylog.manage_alerts')->group(function () {
            Route::get('/',          [AlertRuleController::class, 'index'])  ->name('index');
            Route::post('/',         [AlertRuleController::class, 'store'])  ->name('store');
            Route::put('/{rule}',    [AlertRuleController::class, 'update']) ->name('update');
            Route::delete('/{rule}', [AlertRuleController::class, 'destroy'])->name('destroy');
        });
    });

Route::prefix('backend/api/activity-logs')
    ->middleware(['web', 'auth', 'can:activitylog.view'])
    ->name('backend.api.activitylog.')
    ->group(function () {
        Route::get('/',      [ActivityLogApiController::class, 'index']) ->name('index');
        Route::get('/stats', [ActivityLogApiController::class, 'stats']) ->name('stats');
        Route::get('/meta',  [ActivityLogApiController::class, 'meta'])  ->name('meta');
    });
```

### 9.2 `ActivityLogApiController`

```php
// Modules/ActivityLog/app/Http/Controllers/ActivityLogApiController.php
use App\Shared\Tenancy\TenantContext;

class ActivityLogApiController extends \Illuminate\Routing\Controller
{
    public function index(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $v = $request->validate([
            'module'       => 'nullable|string|max:64',
            'action'       => 'nullable|string|max:128',
            'level_min'    => 'nullable|integer|min:1|max:5',
            'actor_id'     => 'nullable|integer',
            'subject_type' => 'nullable|string|max:255',
            'subject_id'   => 'nullable|integer',
            'request_id'   => 'nullable|string|size:36',
            'search'       => 'nullable|string|max:100',
            'date_from'    => 'nullable|date',
            'date_to'      => 'nullable|date',
            'page'         => 'nullable|integer|min:0',
            'size'         => 'nullable|integer|min:10|max:100',
            'sort'         => 'nullable|in:created_at,level',
            'dir'          => 'nullable|in:asc,desc',
        ]);

        $page  = $v['page'] ?? 0;
        $size  = $v['size'] ?? 20;
        $query = ActivityLog::query();

        // Luôn scope theo tenant hiện tại — admin chỉ thấy log của org mình
        if (TenantContext::isSet()) {
            $query->forOrganization(TenantContext::getOrganizationId());
        }

        if (!empty($v['module']))       $query->where('module', $v['module']);
        if (!empty($v['action']))       $query->where('action', $v['action']);
        if (!empty($v['level_min']))    $query->where('level', '>=', $v['level_min']);
        if (!empty($v['actor_id']))     $query->where('causer_id', $v['actor_id']);
        if (!empty($v['request_id']))   $query->where('request_id', $v['request_id']);
        if (!empty($v['subject_type']) && !empty($v['subject_id'])) {
            // subject_type trong DB là FQCN — client gửi lên FQCN hoặc class_basename
            // UI nên gửi FQCN đầy đủ (lấy từ response trước)
            $query->where('subject_type', $v['subject_type'])
                  ->where('subject_id', $v['subject_id']);
        }
        if (!empty($v['date_from']))    $query->where('created_at', '>=', $v['date_from'].' 00:00:00');
        if (!empty($v['date_to']))      $query->where('created_at', '<=', $v['date_to'].' 23:59:59');
        if (!empty($v['search'])) {
            $t = '%'.$v['search'].'%';
            $query->where(fn($q) => $q->where('description', 'like', $t)
                                      ->orWhere('action', 'like', $t));
        }

        $total = $query->count();
        $items = $query
            ->orderBy($v['sort'] ?? 'created_at', $v['dir'] ?? 'desc')
            ->offset($page * $size)->limit($size)
            ->get(['id','log_name','description','subject_type','subject_id','subject_label',
                   'causer_id','event','level','module','action','request_id',
                   'actor_name','actor_ip','created_at']);

        return response()->json([
            'data'      => $items,
            'total'     => $total,
            'last_page' => (int) ceil($total / $size),
        ]);
    }

    public function stats(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $days     = min(90, max(1, (int) $request->input('days', 30)));
        $from     = now()->subDays($days);
        $orgId    = TenantContext::isSet() ? TenantContext::getOrganizationId() : null;
        $cacheKey = "actlog:stats:{$days}:" . ($orgId ?? 'all');

        return response()->json(\Cache::remember($cacheKey, 300, function () use ($from, $orgId) {
            $base = ActivityLog::where('created_at', '>=', $from)
                ->when($orgId, fn($q) => $q->where('organization_id', $orgId));

            return [
                'by_level'       => (clone $base)->selectRaw('level, COUNT(*) as count')->groupBy('level')->get(),
                'by_module'      => (clone $base)->selectRaw('module, COUNT(*) as count')
                                        ->groupBy('module')->orderByDesc('count')->limit(10)->get(),
                'by_day'         => (clone $base)->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                                        ->groupBy('date')->orderBy('date')->get(),
                'error_today'    => ActivityLog::where('level', '>=', 4)->whereDate('created_at', today())
                                        ->when($orgId, fn($q) => $q->where('organization_id', $orgId))->count(),
                'critical_today' => ActivityLog::where('level', 5)->whereDate('created_at', today())
                                        ->when($orgId, fn($q) => $q->where('organization_id', $orgId))->count(),
            ];
        }));
    }

    public function meta(): \Illuminate\Http\JsonResponse
    {
        $orgId    = TenantContext::isSet() ? TenantContext::getOrganizationId() : null;
        $cacheKey = 'actlog:meta:' . ($orgId ?? 'all');

        return response()->json(\Cache::remember($cacheKey, 600, function () use ($orgId) {
            $base = ActivityLog::when($orgId, fn($q) => $q->where('organization_id', $orgId));
            return [
                'modules' => (clone $base)->distinct()->orderBy('module')->pluck('module')->filter(),
                'actions' => (clone $base)->distinct()->select('module', 'action')
                                ->orderBy('action')->get()->groupBy('module'),
            ];
        }));
    }
}
```

### 9.3 `ActivityLogController`

```php
// Modules/ActivityLog/app/Http/Controllers/ActivityLogController.php
use App\Shared\Tenancy\TenantContext;

class ActivityLogController extends \Illuminate\Routing\Controller
{
    public function index(): \Illuminate\View\View
    {
        return view('activitylog::logs.index');
    }

    public function show(ActivityLog $log): \Illuminate\View\View
    {
        // Tenant gate: chỉ xem log của org mình
        if (TenantContext::isSet() && $log->organization_id !== TenantContext::getOrganizationId()) {
            abort(403);
        }

        $contexts = ActivityLogContext::where('log_id', $log->id)->orderBy('key_name')->get();
        $http     = ActivityLogHttp::where('log_id', $log->id)->first();

        $sameRequest = $log->request_id
            ? ActivityLog::where('request_id', $log->request_id)->where('id', '!=', $log->id)
                         ->orderBy('created_at')
                         ->get(['id','module','action','level','created_at'])
            : collect();

        $subjectHistory = ($log->subject_type && $log->subject_id)
            ? ActivityLog::where('subject_type', $log->subject_type)
                         ->where('subject_id',   $log->subject_id)
                         ->where('id', '!=', $log->id)
                         ->orderByDesc('created_at')->limit(10)
                         ->get(['id','module','action','level','created_at'])
            : collect();

        return view('activitylog::logs.show',
            compact('log', 'contexts', 'http', 'sameRequest', 'subjectHistory'));
    }

    public function export(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate trước khi dispatch — không pass $request->all() thẳng vào job
        $filters = $request->validate([
            'module'    => 'nullable|string|max:64',
            'action'    => 'nullable|string|max:128',
            'level_min' => 'nullable|integer|min:1|max:5',
            'actor_id'  => 'nullable|integer',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date',
            'search'    => 'nullable|string|max:100',
        ]);

        // Inject organization_id vào filter để job giữ đúng scope tenant
        if (TenantContext::isSet()) {
            $filters['organization_id'] = TenantContext::getOrganizationId();
        }

        $key = (string) \Str::uuid();
        ExportActivityLogsAction::dispatch($filters, $key)
            ->onQueue(config('activitylog.queue', 'actlog'));

        return response()->json(['key' => $key]);
    }

    public function downloadExport(string $key): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $path = \Cache::get("actlog:export:{$key}");
        abort_unless($path && file_exists($path), 404, 'File không tồn tại hoặc đã hết hạn.');
        return response()->download($path)->deleteFileAfterSend();
    }
}
```

---

## 10. Views — Admin UI

### 10.1 `logs/index.blade.php`

```html
@extends('layouts.dashboard')

@section('content')
<div x-data="activityLogIndex()" x-init="init()">

    {{-- Filter panel --}}
    <div class="card mb-4">
        <div class="filter-grid">
            <select x-model="f.module" @change="f.action=''; reloadActions()">
                <option value="">Tất cả module</option>
                <template x-for="m in meta.modules" :key="m">
                    <option :value="m" x-text="m"></option>
                </template>
            </select>

            <select x-model="f.action" :disabled="!f.module">
                <option value="">Tất cả action</option>
                <template x-for="a in currentActions" :key="a.action">
                    <option :value="a.action" x-text="a.action"></option>
                </template>
            </select>

            <select x-model="f.level_min">
                <option value="">Mọi cấp độ</option>
                <option value="3">≥ Warning</option>
                <option value="4">≥ Error</option>
                <option value="5">Critical only</option>
            </select>

            <input type="text"   x-model.debounce.400ms="f.search"
                   placeholder="Tìm action, mô tả...">
            <input type="date"   x-model="f.date_from">
            <input type="date"   x-model="f.date_to">
            <input type="number" x-model="f.actor_id" placeholder="Actor ID">

            <div class="filter-actions">
                <button @click="reload()" class="btn-primary">Lọc</button>
                <button @click="reset()"  class="btn-ghost">Xóa</button>
                <button @click="doExport()" class="btn-outline">Xuất Excel</button>
            </div>
        </div>

        {{-- Summary --}}
        <div class="summary-bar" x-show="stats">
            <span class="badge badge-teal">
                Hôm nay: <b x-text="stats?.total_today ?? 0"></b>
            </span>
            <span class="badge badge-amber">
                Warning: <b x-text="stats?.warnings ?? 0"></b>
            </span>
            <span class="badge badge-red">
                Error: <b x-text="stats?.error_today ?? 0"></b>
            </span>
            <span class="badge badge-crimson" x-show="(stats?.critical_today ?? 0) > 0">
                Critical: <b x-text="stats?.critical_today"></b>
            </span>
        </div>
    </div>

    {{-- Tabulator --}}
    <div id="actlog-table" class="card"></div>

</div>
@endsection

@push('scripts')
<script>
function activityLogIndex() {
    return {
        meta:  { modules: [], actions: {} },
        stats: null,
        table: null,
        f: {
            module:'', action:'', level_min:'',
            search:'', date_from:'', date_to:'', actor_id:'',
            page: 0, size: 20, sort:'created_at', dir:'desc',
        },

        async init() {
            await this.loadMeta();
            this.loadStats();
            this.initTable();
        },

        async loadMeta() {
            const r = await fetch('/backend/api/activity-logs/meta');
            this.meta = await r.json();
        },

        async loadStats() {
            const r    = await fetch('/backend/api/activity-logs/stats?days=1');
            const data = await r.json();
            const byLevel = (v) => data.by_level?.find(l => l.level == v)?.count ?? 0;
            this.stats = {
                total_today:    data.by_level?.reduce((s,l) => s + parseInt(l.count), 0) ?? 0,
                warnings:       byLevel(3),
                error_today:    data.error_today,
                critical_today: data.critical_today,
            };
        },

        get currentActions() {
            return this.meta.actions?.[this.f.module] ?? [];
        },

        reload() { this.table?.replaceData(); },

        reset() {
            Object.assign(this.f, {
                module:'', action:'', level_min:'',
                search:'', date_from:'', date_to:'', actor_id:'',
            });
            this.reload();
        },

        async doExport() {
            const r = await fetch('/dashboard/activity-logs/export', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(this.f),
            });
            const { key } = await r.json();
            const poll = setInterval(async () => {
                const check = await fetch(`/dashboard/activity-logs/export/download/${key}`, { method: 'HEAD' });
                if (check.ok) {
                    clearInterval(poll);
                    window.location = `/dashboard/activity-logs/export/download/${key}`;
                }
            }, 2000);
        },

        levelBadge(level) {
            const map = {
                1: ['gray',    'Debug'],
                2: ['teal',    'Info'],
                3: ['amber',   'Warning'],
                4: ['red',     'Error'],
                5: ['crimson', 'Critical'],
            };
            const [color, label] = map[level] ?? ['gray', level];
            return `<span class="badge badge-${color}">${label}</span>`;
        },

        initTable() {
            const self = this;
            this.table = new Tabulator('#actlog-table', {
                ajaxURL:        '/backend/api/activity-logs',
                ajaxParams:     () => self.f,
                pagination:     true,
                paginationMode: 'remote',
                sortMode:       'remote',
                ajaxResponse(url, p, resp) {
                    return { data: resp.data, last_page: resp.last_page };
                },
                rowFormatter(row) {
                    const l = row.getData().level;
                    if (l >= 5) row.getElement().classList.add('row-critical');
                    else if (l >= 4) row.getElement().classList.add('row-error');
                },
                columns: [
                    { title:'Thời gian',  field:'created_at', width:162, sorter:'datetime',
                      formatter: c => {
                          const d = new Date(c.getValue());
                          return d.toLocaleDateString('vi-VN') + ' ' +
                                 d.toLocaleTimeString('vi-VN', {hour12:false});
                      }},
                    { title:'Cấp độ', field:'level', width:95, hozAlign:'center',
                      formatter: c => self.levelBadge(c.getValue()) },
                    { title:'Module',  field:'module', width:100 },
                    { title:'Action',  field:'action', width:200 },
                    { title:'Actor',   field:'actor_name', width:140 },
                    { title:'IP',      field:'actor_ip',   width:120 },
                    { title:'Label',   field:'subject_label', minWidth:150 },
                    { title:'Mô tả',   field:'description', minWidth:180 },
                    { title:'',
                      formatter: () => '<a class="btn-xs">Xem</a>',
                      width:60, hozAlign:'center',
                      cellClick(e, cell) {
                          window.location = `/dashboard/activity-logs/${cell.getRow().getData().id}`;
                      }
                    },
                ],
            });
        },
    };
}
</script>
@endpush
```

---

## 11. Alerting

### 11.1 `AlertEvaluatorService`

```php
// Modules/ActivityLog/app/Services/AlertEvaluatorService.php
final class AlertEvaluatorService
{
    public function evaluate(LogEntryData $entry): void
    {
        $rules = \Cache::remember('actlog:alert_rules', 300,
            fn() => ActivityLogAlertRule::where('is_active', 1)->get()
        );

        foreach ($rules as $rule) {
            if (!$this->matches($rule, $entry))       continue;
            if ($this->inCooldown($rule))             continue;
            if (!$this->conditionMet($rule, $entry))  continue;
            $this->trigger($rule, $entry);
        }
    }

    private function matches(ActivityLogAlertRule $rule, LogEntryData $entry): bool
    {
        if ($rule->module    && $rule->module    !== $entry->module)         return false;
        if ($rule->action    && $rule->action    !== $entry->action)         return false;
        if ($rule->level_min && $entry->level->value < $rule->level_min)     return false;
        return true;
    }

    private function conditionMet(ActivityLogAlertRule $rule, LogEntryData $entry): bool
    {
        return match ($rule->condition_type) {
            1 => true, // first_occurrence
            2 => $this->checkCount($rule, $entry),
            default => false,
        };
    }

    private function checkCount(ActivityLogAlertRule $rule, LogEntryData $entry): bool
    {
        $key   = "actlog:alert:{$rule->id}:{$entry->module}:{$entry->action}";
        $count = (int) \Cache::increment($key);
        if ($count === 1) \Cache::expire($key, $rule->window_minutes * 60);
        return $count >= $rule->threshold_count;
    }

    private function inCooldown(ActivityLogAlertRule $rule): bool
    {
        return $rule->last_triggered_at
            && \Carbon\Carbon::parse($rule->last_triggered_at)
                ->addMinutes($rule->cooldown_minutes)->isFuture();
    }

    private function trigger(ActivityLogAlertRule $rule, LogEntryData $entry): void
    {
        $rule->update(['last_triggered_at' => now()]);
        \Cache::forget('actlog:alert_rules');
        SendAlertAction::dispatch($rule->id, $entry)->onQueue('actlog');
    }
}
```

### 11.2 `SendAlertAction`

```php
// Modules/ActivityLog/app/Actions/SendAlertAction.php
use Lorisleiva\Actions\Concerns\AsAction;

class SendAlertAction
{
    use AsAction;

    public string $jobQueue  = 'actlog';
    public int    $jobTries  = 3;
    public array  $jobBackoff = [10, 60, 300];

    public function handle(int $ruleId, LogEntryData $entry): void
    {
        $rule = ActivityLogAlertRule::find($ruleId);
        if (!$rule) return;

        match ($rule->notify_channel) {
            1 => $this->sendEmail($rule, $entry),
            2 => $this->sendDatabase($rule, $entry),
            default => null,
        };
    }

    private function sendEmail(ActivityLogAlertRule $rule, LogEntryData $entry): void
    {
        $emails = array_filter(array_map('trim', explode(',', $rule->notify_target)));
        if (empty($emails)) return;
        \Mail::to($emails)->queue(new ActivityAlertMail($rule, $entry));
    }

    private function sendDatabase(ActivityLogAlertRule $rule, LogEntryData $entry): void
    {
        $ids   = array_filter(array_map('intval', explode(',', $rule->notify_target)));
        $users = \App\Models\User::whereIn('id', $ids)->get();
        \Notification::send($users, new ActivityAlertNotification($rule, $entry));
    }
}
```

---

## 12. Export với FastExcel

Đã implement trong `ExportActivityLogsAction` (§5.3). Tóm tắt ưu điểm FastExcel:
- `LazyCollection` + `lazy(500)` → stream 500 rows/lần, không bao giờ OOM.
- Eager load `http` (không cần eager load `causer` vì dùng `actor_name` snapshot).
- `actor_name` lấy từ snapshot column — không cần join sang `users` table.
- `subject_label` lấy từ snapshot column — không cần join sang subject model.
- API đơn giản: `(new FastExcel($collection))->export($path)`.
- Nhanh hơn ~5x so với Laravel Excel ở cùng file size.

---

## 13. Retention & Cleanup

### 13.1 Scheduler

```php
// Modules/ActivityLog/routes/console.php
use Illuminate\Support\Facades\Schedule;

// Cleanup hàng ngày lúc 3 giờ sáng
Schedule::job(
    \Modules\ActivityLog\app\Actions\PurgeOldLogsAction::makeJob()
)->dailyAt('03:00')->name('activitylog:purge')->onOneServer();

// Invalidate stats cache (có org-scoped keys)
Schedule::call(function () {
    // Dùng Cache::tags nếu Redis hỗ trợ, ngược lại flush toàn bộ actlog:stats:* keys
    \Cache::forget('actlog:meta:all');
    // Org-scoped keys cần invalidate riêng hoặc dùng short TTL (300s)
})->everyFiveMinutes();
```

### 13.2 Cấu hình Spatie

Dùng `PurgeOldLogsAction` của ta thay vì `php artisan activitylog:clean` của Spatie —
vì Spatie chỉ xóa bảng chính, ta cần xóa thêm bảng con (`contexts`, `http`).

```php
// config/activitylog.php (Spatie config — publish từ package)
return [
    'enabled'                        => env('ACTIVITY_LOGGER_ENABLED', true),
    'delete_records_older_than_days' => 90, // đồng bộ với retain_days
    'default_log_name'               => 'default',
    'activity_model'                 => \Modules\ActivityLog\app\Models\ActivityLog::class,
    // ↑ Quan trọng: trỏ về custom model để Spatie dùng model mở rộng của ta
    'table_name'                     => 'activity_log',
    'database_connection'            => env('ACTIVITY_LOG_DB_CONNECTION'),
];
```

---

## 14. Permissions với spatie/laravel-permission

```php
// Modules/ActivityLog/database/seeders/ActivityLogPermissionsSeeder.php
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Enums\RoleEnum;

class ActivityLogPermissionsSeeder extends \Illuminate\Database\Seeder
{
    private const PERMISSIONS = [
        'activitylog.view',           // xem danh sách + chi tiết log
        'activitylog.export',         // xuất Excel
        'activitylog.manage_alerts',  // quản lý alert rules
    ];

    // Roles theo RoleEnum — system_admin và ceo nhận full access mặc định
    private const ADMIN_ROLES = [
        RoleEnum::ADMIN->value,  // 'system_admin'
        RoleEnum::CEO->value,    // 'ceo'
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        foreach (self::ADMIN_ROLES as $roleName) {
            $role = Role::where('name', $roleName)->first();
            $role?->givePermissionTo(self::PERMISSIONS);
        }
    }
}
```

**Đăng ký vào `config/permissions.php`** — thêm vào array của từng role:

```php
// Thêm vào array R::ADMIN->value trong config/permissions.php
'activitylog.view',
'activitylog.export',
'activitylog.manage_alerts',

// Thêm vào array R::CEO->value (chỉ view + export, không manage alerts)
'activitylog.view',
'activitylog.export',
```

**Thêm vào `RoleEnum::visibleModules()`** (sidebar):

```php
// Trong RoleEnum::visibleModules(), thêm 'activity_log' vào danh sách của system_admin
self::ADMIN  => [...existing..., 'activity_log'],
self::CEO    => [...existing..., 'activity_log'],
```

---

## 15. Tích hợp module khác

### 15.1 Cheatsheet gọi log

```php
// Cách 1: Static shortcut — dùng hàng ngày
ActivityLogger::info('Survey', 'survey_activated', $survey, [
    'previous_status' => 0,
    'new_status'      => 1,
]);

// Cách 2: Có description
ActivityLogger::error('Survey', 'scoring_job_failed', $response, [
    'response_id' => $response->id,
    'attempt'     => 3,
], 'Job chấm điểm thất bại sau 3 lần retry');

// Cách 3: Dùng LogsActivity trên Model (Spatie tự log)
// Chỉ cần thêm trait vào Model — không cần Observer thủ công

// Cách 4: Context types — tự động detect
ActivityLogger::info('Survey', 'result_calculated', $response, [
    'overall_score'  => 72.5,        // float   → val_decimal
    'band_code'      => 'AI_READY',  // string  → val_string
    'config_version' => 7,           // int     → val_integer
    'is_first_calc'  => true,        // bool    → val_boolean
    'calculated_at'  => now(),       // Carbon  → val_datetime
    'band'           => BandEnum::AIReady, // BackedEnum → val_string/integer
]);
```

### 15.2 Interface `LoggableSubject`

```php
// Modules/ActivityLog/app/Contracts/LoggableSubject.php
interface LoggableSubject
{
    public function getActivityLabel(): string;
    public function getActivityRouteUrl(): ?string;
}

// Survey implement:
class Survey extends Model implements LoggableSubject
{
    public function getActivityLabel(): string      { return $this->title; }
    public function getActivityRouteUrl(): ?string  { return route('backend.surveys.edit', $this); }
}
```

### 15.3 Trait `HasActivityLog` — timeline cho admin

```php
// Modules/ActivityLog/app/Traits/HasActivityLog.php
trait HasActivityLog
{
    public function recentActivityLogs(int $limit = 15): \Illuminate\Support\Collection
    {
        // Dùng get_class() (FQCN) — nhất quán với WriteActivityLogAction::writeMainLog()
        // KHÔNG dùng class_basename() vì DB lưu FQCN đầy đủ
        return ActivityLog::where('subject_type', get_class($this))
            ->where('subject_id', $this->getKey())
            ->where('created_at', '>=', now()->subDays(30))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get(['id','module','action','level','description','actor_name','actor_ip','created_at']);
    }
}
```

---

## 16. Config

```php
// Modules/ActivityLog/config/activitylog.php
return [
    /*
    | Mức log tối thiểu. Bỏ qua log có level < min_level.
    | 1=debug  2=info  3=warning  4=error  5=critical
    | Production: 2 (info). Dev: 1 (debug).
    */
    'min_level' => (int) env('ACTIVITYLOG_MIN_LEVEL', 2),

    /*
    | Số ngày giữ log trong DB trước khi PurgeOldLogsAction xóa.
    */
    'retain_days' => (int) env('ACTIVITYLOG_RETAIN_DAYS', 90),

    /*
    | Queue riêng cho ActivityLog jobs.
    | Tách khỏi queue chính để không cạnh tranh với business jobs.
    */
    'queue' => env('ACTIVITYLOG_QUEUE', 'actlog'),
];
```

> **Lưu ý**: Không cần `subject_map` nữa vì `LogEntryBuilder` lưu thẳng FQCN
> vào `subjectType` — không cần resolve ngược từ `class_basename`.

---

## 17. Migrations hoàn chỉnh

### Migration 1 — Spatie (đã chạy, không cần lại)

Bảng `activity_log` đã được tạo tại:
`database/migrations/vendor/2026_05_13_020054_create_activity_log_table.php`

Không publish lại. Migration bổ sung bên dưới sẽ `ALTER` bảng này.

### Migration 2 — Thêm custom columns vào `activity_log`

```php
// Modules/ActivityLog/database/migrations/2026_XX_XX_000001_add_custom_columns_to_activity_log.php
public function up(): void
{
    Schema::table('activity_log', function (Blueprint $table) {
        $table->unsignedTinyInteger('level')
              ->default(2)->after('log_name')
              ->comment('1=debug 2=info 3=warning 4=error 5=critical');

        $table->unsignedBigInteger('organization_id')
              ->nullable()->after('level')
              ->comment('Tenant context — NULL khi CLI/system job');

        $table->string('module', 64)->nullable()->after('organization_id');
        $table->string('action', 128)->nullable()->after('module');

        $table->string('actor_name', 255)->nullable()->after('action')
              ->comment('Snapshot tên actor tại thời điểm log');

        $table->string('actor_ip', 45)->nullable()->after('actor_name')
              ->comment('IPv4 hoặc IPv6');

        $table->char('request_id', 36)->nullable()->after('actor_ip');
        $table->string('session_id', 255)->nullable()->after('request_id');

        $table->string('subject_label', 255)->nullable()->after('session_id')
              ->comment('Snapshot label của subject (name/title/email)');

        $table->index(['organization_id', 'created_at'], 'idx_org_created');
        $table->index(['level', 'created_at'],            'idx_level');
        $table->index(['module', 'action', 'created_at'], 'idx_module_action');
        $table->index('request_id',                       'idx_request');
    });
}

public function down(): void
{
    Schema::table('activity_log', function (Blueprint $table) {
        $table->dropIndex('idx_org_created');
        $table->dropIndex('idx_level');
        $table->dropIndex('idx_module_action');
        $table->dropIndex('idx_request');
        $table->dropColumn([
            'level', 'organization_id', 'module', 'action',
            'actor_name', 'actor_ip', 'request_id', 'session_id', 'subject_label',
        ]);
    });
}
```

### Migration 3 — `activity_log_contexts`

```php
// Modules/ActivityLog/database/migrations/2026_XX_XX_000002_create_activity_log_contexts_table.php
public function up(): void
{
    Schema::create('activity_log_contexts', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('log_id');
        $table->string('key_name', 64);
        $table->unsignedTinyInteger('value_type')->default(1)
              ->comment('1=string 2=integer 3=decimal 4=boolean 5=datetime');
        $table->string('val_string', 500)->nullable();
        $table->bigInteger('val_integer')->nullable();
        $table->decimal('val_decimal', 20, 6)->nullable();
        $table->boolean('val_boolean')->nullable();
        $table->dateTime('val_datetime')->nullable();
        $table->timestamp('created_at')->nullable();

        $table->index('log_id',                    'idx_log');
        $table->index(['key_name', 'val_integer'],  'idx_key_integer');
    });

    // Prefix index — MySQL only (Blueprint không hỗ trợ prefix index)
    if (\DB::getDriverName() === 'mysql') {
        \DB::statement(
            'ALTER TABLE activity_log_contexts ADD INDEX idx_key_string (key_name, val_string(64))'
        );
    }
}

public function down(): void
{
    Schema::dropIfExists('activity_log_contexts');
}
```

### Migration 4 — `activity_log_http`

```php
// Modules/ActivityLog/database/migrations/2026_XX_XX_000003_create_activity_log_http_table.php
public function up(): void
{
    Schema::create('activity_log_http', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('log_id');
        $table->unsignedTinyInteger('http_method')
              ->comment('1=GET 2=POST 3=PUT 4=PATCH 5=DELETE 6=HEAD 7=OPTIONS');
        $table->string('url', 2000);
        $table->string('route_name', 191)->nullable();
        $table->unsignedSmallInteger('status_code')->nullable();
        $table->unsignedSmallInteger('duration_ms')->nullable();
        $table->string('user_agent', 500)->nullable();
        $table->timestamp('created_at')->nullable();

        $table->unique('log_id', 'uq_log');
        $table->index('route_name',  'idx_route');
        $table->index('status_code', 'idx_status');
    });
}

public function down(): void
{
    Schema::dropIfExists('activity_log_http');
}
```

### Migration 5 — `activity_log_alert_rules`

```php
// Modules/ActivityLog/database/migrations/2026_XX_XX_000004_create_activity_log_alert_rules_table.php
public function up(): void
{
    Schema::create('activity_log_alert_rules', function (Blueprint $table) {
        $table->id();
        $table->string('name', 191);
        $table->string('module', 64)->nullable();
        $table->string('action', 128)->nullable();
        $table->unsignedTinyInteger('level_min')->nullable();
        $table->unsignedTinyInteger('condition_type')
              ->comment('1=first_occurrence 2=count_threshold');
        $table->unsignedSmallInteger('threshold_count')->nullable();
        $table->unsignedSmallInteger('window_minutes')->nullable();
        $table->unsignedTinyInteger('notify_channel')
              ->comment('1=email 2=database');
        $table->string('notify_target', 500);
        $table->unsignedSmallInteger('cooldown_minutes')->default(60);
        $table->dateTime('last_triggered_at')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();

        $table->index(['is_active', 'module', 'action'], 'idx_active');
    });
}

public function down(): void
{
    Schema::dropIfExists('activity_log_alert_rules');
}
```

---

## 18. Seeders

```php
// Modules/ActivityLog/database/seeders/ActivityLogDatabaseSeeder.php
class ActivityLogDatabaseSeeder extends \Illuminate\Database\Seeder
{
    public function run(): void
    {
        $this->call([
            ActivityLogPermissionsSeeder::class,
            DefaultAlertRulesSeeder::class,
        ]);
    }
}

// Modules/ActivityLog/database/seeders/DefaultAlertRulesSeeder.php
class DefaultAlertRulesSeeder extends \Illuminate\Database\Seeder
{
    public function run(): void
    {
        $adminEmail = config('mail.admin_address', 'admin@example.com');

        $rules = [
            [
                'name'             => 'Brute force đăng nhập',
                'module'           => 'Auth',
                'action'           => 'login_failed',
                'level_min'        => 3,
                'condition_type'   => 2,  // count_threshold
                'threshold_count'  => 5,
                'window_minutes'   => 10,
                'notify_channel'   => 1,  // email
                'notify_target'    => $adminEmail,
                'cooldown_minutes' => 30,
            ],
            [
                'name'             => 'Xóa Survey có dữ liệu',
                'module'           => 'Survey',
                'action'           => 'survey_deleted',
                'level_min'        => 5,
                'condition_type'   => 1,  // first_occurrence
                'threshold_count'  => null,
                'window_minutes'   => null,
                'notify_channel'   => 1,
                'notify_target'    => $adminEmail,
                'cooldown_minutes' => 0,
            ],
            [
                'name'             => 'Bất kỳ lỗi Critical',
                'module'           => null,  // tất cả module
                'action'           => null,
                'level_min'        => 5,
                'condition_type'   => 1,
                'threshold_count'  => null,
                'window_minutes'   => null,
                'notify_channel'   => 1,
                'notify_target'    => $adminEmail,
                'cooldown_minutes' => 60,
            ],
            [
                'name'             => 'Scoring config thay đổi',
                'module'           => 'Survey',
                'action'           => 'scoring_config_saved',
                'level_min'        => 2,
                'condition_type'   => 1,
                'threshold_count'  => null,
                'window_minutes'   => null,
                'notify_channel'   => 2,  // database notification
                'notify_target'    => '1',
                'cooldown_minutes' => 0,
            ],
        ];

        foreach ($rules as $rule) {
            ActivityLogAlertRule::firstOrCreate(
                ['name' => $rule['name']],
                array_merge($rule, ['is_active' => true])
            );
        }
    }
}
```

---

## 19. Thứ tự triển khai

| # | Hạng mục | Effort | Package/Tool |
|---|----------|--------|-------------|
| 1 | **[Phase 1]** Fix namespace `LogsActivity` v4→v5 trong models Survey | Rất thấp | |
| 2 | Migration thêm custom columns vào `activity_log` | Thấp | |
| 3 | Migration `activity_log_contexts`, `_http`, `_alert_rules` | Thấp | |
| 4 | Enums: `LogLevel`, `ActorType`, `HttpMethod` | Thấp | PHP 8.4 backed enum |
| 5 | DTOs: `LogEntryData`, `HttpSnapshotData` | Thấp | `spatie/laravel-data` |
| 6 | Models: `ActivityLog` (extend Spatie), `ActivityLogContext`, `ActivityLogHttp`, `ActivityLogAlertRule` | Thấp | |
| 7 | `LogEntryBuilder` | Trung | |
| 8 | `ActivityLogger` facade | Thấp | |
| 9 | `WriteActivityLogAction` (asJob) | Trung | `lorisleiva/laravel-actions` |
| 10 | `InjectRequestId` + `CaptureHttpContext` middleware | Thấp | |
| 11 | Permissions seeder + cập nhật `config/permissions.php` + `RoleEnum::visibleModules()` | Thấp | `spatie/laravel-permission` |
| 12 | **[Phase 2]** Migrate Survey call sites sang `ActivityLogger::*` mới | Trung | Quick win |
| 13 | `ActivityLogApiController` (Tabulator data source) | Trung | |
| 14 | View `logs/index.blade.php` + Tabulator | Trung | Alpine.js |
| 15 | View `logs/show.blade.php` | Trung | |
| 16 | `LogsActivity` trait trên Survey/User models (Spatie CRUD log) | Thấp | `spatie/laravel-activitylog` |
| 17 | `AlertEvaluatorService` + `SendAlertAction` | Trung | `lorisleiva/laravel-actions` |
| 18 | Alert rules seeder + UI quản lý | Trung | |
| 19 | `PurgeOldLogsAction` + scheduler | Thấp | |
| 20 | `ExportActivityLogsAction` | Thấp | `rap2hpoutre/fast-excel` |
| 21 | `HasActivityLog` trait + timeline partial | Thấp | |
| 22 | **[Phase 3]** Migrate Auth + Organization call sites | Thấp | |
| 23 | **[Phase 4]** Xóa `App\Shared\Support\ActivityLogger` cũ | Rất thấp | |
