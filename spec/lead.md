# Lead Module — Full Specification v2

> **Vị trí**: `Modules/Lead/`
> **Stack**: Laravel 13 · PHP 8.4 · MySQL 8+ · Redis · `nwidart/laravel-modules` · Alpine.js
> **Packages**:
> - `lorisleiva/laravel-actions ^2.10` — Action as Job/Command
> - `spatie/laravel-data ^4.23` — typed DTOs
> - `spatie/laravel-permission ^7.4` — phân quyền
> - `rap2hpoutre/fast-excel ^5.7` — export
> - `laravelcm/laravel-subscriptions` — subscription context nếu cần
>
> **Tài liệu liên quan**:
> - `survey_feature_inventory.md` — module Survey hiện có
> - `activity_log_spec_v3.md` — module ActivityLog
> - `workflow_automation_spec_v2.md` — module Workflow Automation
>
> **Cập nhật**: 2026-05-26

---

## Mục lục

1. [Phân tích nghiệp vụ & mô hình dữ liệu](#1-phân-tích-nghiệp-vụ)
2. [Database Schema](#2-database-schema)
3. [Enums](#3-enums)
4. [DTOs với spatie/laravel-data](#4-dtos)
5. [Models](#5-models)
6. [Actions với lorisleiva](#6-actions)
7. [Services](#7-services)
8. [Routes & Controllers](#8-routes--controllers)
9. [Views — Admin UI](#9-views--admin-ui)
10. [Tích hợp Workflow Automation](#10-tích-hợp-workflow-automation)
11. [Tích hợp Survey](#11-tích-hợp-survey)
12. [Tích hợp ActivityLog](#12-tích-hợp-activitylog)
13. [Permissions](#13-permissions)
14. [Config](#14-config)
15. [Migrations hoàn chỉnh](#15-migrations-hoàn-chỉnh)
16. [Seeders](#16-seeders)
17. [Thứ tự triển khai](#17-thứ-tự-triển-khai)

---

## 1. Phân tích nghiệp vụ

### 1.1 Hai khái niệm cốt lõi

Từ danh sách trường bạn yêu cầu, mô hình dữ liệu hợp lý nhất là tách thành **2 thực thể**:

```
┌─────────────────────────────────┐      ┌─────────────────────────────────┐
│         CONTACT                 │      │         LEAD (Opportunity)       │
│  (Người liên hệ)                │◄────►│  (Cơ hội kinh doanh)            │
│                                 │      │                                  │
│  • Tên người liên hệ            │      │  • Tình trạng cơ hội (stage)     │
│  • Email                        │      │  • Nguồn cơ hội                  │
│  • Điện thoại                   │      │  • Người phụ trách               │
│  • Công ty                      │      │  • Giá trị dự kiến               │
│  • Chức vụ                      │      │  • Ngày chốt dự kiến             │
│  • Website                      │      │  • Mô tả                         │
│  • Địa chỉ / Tỉnh / Phường      │      │  • org_id (tổ chức sở hữu)       │
└─────────────────────────────────┘      └─────────────────────────────────┘
```

**Tại sao tách Contact riêng?**
- Một Contact có thể gắn với nhiều Lead theo thời gian (lần tiếp xúc 1, lần tiếp xúc 2...).
- Thông tin Contact (địa chỉ, công ty) ít đổi hơn thông tin cơ hội (stage, giá trị, ngày chốt).
- Tái sử dụng Contact khi tạo Lead mới — không nhập lại từ đầu.
- Phù hợp với pattern CRM thực tế (HubSpot, Salesforce đều tách Contact / Deal).

**Tuy nhiên, nếu hệ thống nhỏ** và không cần tái sử dụng Contact, có thể lưu tất cả trên `leads`. Spec này thiết kế **bảng `lead_contacts` riêng nhưng linh hoạt** — Lead luôn có đủ thông tin ngay cả khi Contact bị xóa (dùng snapshot).

### 1.2 Concept `org_id` — Multi-tenant per Organization

```
Organization (org) ← nhiều Lead thuộc về 1 org
     │
     ├── Pipeline riêng per org (lead_pipeline_stages có org_id)
     ├── Lead của org A không thấy được từ org B
     └── Người phụ trách chỉ thấy lead trong org mình
```

`org_id` ánh xạ với khái niệm **Tổ chức** trong hệ thống — có thể là công ty, chi nhánh, hoặc team sales. Mỗi org có pipeline stages riêng hoặc dùng chung pipeline global.

### 1.3 Pipeline mặc định

```
Mới → Đã liên hệ → Đủ điều kiện → Đã gửi đề xuất → Đang đàm phán → Thành công
                                                                    → Thất bại
                                                                    → Không phù hợp
```

### 1.4 Mapping trường yêu cầu → bảng DB

| Trường yêu cầu | Bảng | Column |
|----------------|------|--------|
| Tình trạng cơ hội | `leads` | `stage_id` → `lead_pipeline_stages` |
| Nguồn cơ hội | `leads` | `source` (TINYINT enum) |
| Người phụ trách | `leads` | `assigned_to` → `users.id` |
| Tên người liên hệ | `lead_contacts` | `full_name` |
| Công ty | `lead_contacts` | `company` |
| Chức vụ | `lead_contacts` | `job_title` |
| Địa chỉ | `lead_contacts` | `address` |
| Email người liên hệ | `lead_contacts` | `email` |
| Website | `lead_contacts` | `website` |
| Điện thoại | `lead_contacts` | `phone` |
| Tỉnh/Thành phố | `lead_contacts` | `province_code` → `provinces` |
| Phường/Xã | `lead_contacts` | `ward_code` → `wards` |
| Giá trị dự kiến | `leads` | `expected_value` (DECIMAL) |
| Ngày chốt dự kiến | `leads` | `expected_close_date` (DATE) |
| Mô tả | `leads` | `description` (TEXT) |
| org_id | `leads` | `org_id` |

---

## 2. Database Schema

### 2.1 `organizations` — tổ chức sở hữu Lead

> **Lưu ý**: Nếu hệ thống đã có bảng `organizations` (hoặc `tenants`) thì bỏ qua migration này và chỉ dùng FK.

```sql
CREATE TABLE `organizations` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `code`          VARCHAR(32)  NOT NULL UNIQUE,
    `name`          VARCHAR(191) NOT NULL,
    `is_active`     TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP NULL,
    `updated_at`    TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2.2 `lead_pipeline_stages` — cấu hình tình trạng cơ hội

```sql
CREATE TABLE `lead_pipeline_stages` (
    `id`            SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,

    `org_id`        INT UNSIGNED NULL,
    -- NULL = stage toàn cục (dùng chung mọi org)
    -- NOT NULL = stage riêng của org đó

    `code`          VARCHAR(32)  NOT NULL,
    -- 'new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost'
    -- UNIQUE per org: (org_id, code)

    `label`         VARCHAR(64)  NOT NULL,
    -- 'Mới', 'Đã liên hệ', 'Đủ điều kiện', ...

    `color`         VARCHAR(16)  NOT NULL DEFAULT 'gray',
    -- 'gray' | 'blue' | 'teal' | 'purple' | 'amber' | 'green' | 'red'
    -- Map sang Tailwind / CSS class trong UI

    `sort_order`    TINYINT UNSIGNED NOT NULL DEFAULT 0,

    `is_won`        TINYINT(1) NOT NULL DEFAULT 0,
    -- Stage kết thúc thắng — tính conversion rate, đánh dấu Lead là Converted

    `is_lost`       TINYINT(1) NOT NULL DEFAULT 0,
    -- Stage kết thúc thua — đánh dấu Lead là Archived

    `probability`   TINYINT UNSIGNED NOT NULL DEFAULT 0,
    -- % xác suất chốt (0–100) — dùng để tính weighted pipeline value
    -- VD: Qualified = 30%, Proposal = 60%, Negotiation = 80%, Won = 100%

    `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP NULL,
    `updated_at`    TIMESTAMP NULL,

    UNIQUE KEY `uq_org_code`  (`org_id`, `code`),
    INDEX      `idx_org_order`(`org_id`, `sort_order`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2.3 `lead_contacts` — thông tin người liên hệ

```sql
CREATE TABLE `lead_contacts` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,

    `org_id`          INT UNSIGNED NULL,
    -- Thuộc org nào (nếu multi-org)

    -- ── Thông tin cá nhân ─────────────────────────────────────
    `full_name`       VARCHAR(191) NOT NULL,
    -- Tên đầy đủ người liên hệ

    `email`           VARCHAR(191) NULL,
    -- Email người liên hệ (nullable vì có thể chỉ có phone)

    `phone`           VARCHAR(32) NULL,
    -- Điện thoại — lưu dạng string để chứa mọi định dạng (+84, 0xxx, ...)

    `phone_alt`       VARCHAR(32) NULL,
    -- Số điện thoại phụ

    -- ── Thông tin công ty ─────────────────────────────────────
    `company`         VARCHAR(191) NULL,
    -- Tên công ty / tổ chức

    `job_title`       VARCHAR(128) NULL,
    -- Chức vụ: 'CEO', 'Marketing Director', 'IT Manager', ...

    `website`         VARCHAR(500) NULL,
    -- Website công ty hoặc cá nhân

    -- ── Địa chỉ ───────────────────────────────────────────────
    `address`         VARCHAR(500) NULL,
    -- Số nhà, tên đường, tòa nhà

    `ward_code`       VARCHAR(8) NULL,
    -- Mã Phường/Xã theo ĐVHCVN (VD: '00001')
    -- Tra cứu từ bảng wards hoặc gọi API địa chính

    `ward_name`       VARCHAR(64) NULL,
    -- Snapshot tên Phường/Xã — tránh join khi hiển thị

    `district_code`   VARCHAR(8) NULL,
    -- Mã Quận/Huyện

    `district_name`   VARCHAR(64) NULL,
    -- Snapshot tên Quận/Huyện

    `province_code`   VARCHAR(8) NULL,
    -- Mã Tỉnh/Thành phố (VD: '01' = Hà Nội, '79' = HCM)

    `province_name`   VARCHAR(64) NULL,
    -- Snapshot tên Tỉnh/TP — tránh join

    `country_code`    CHAR(2) NOT NULL DEFAULT 'VN',
    -- ISO 3166-1 alpha-2

    -- ── Tracking ──────────────────────────────────────────────
    `created_by`      BIGINT UNSIGNED NULL,
    `created_at`      TIMESTAMP NULL,
    `updated_at`      TIMESTAMP NULL,
    `deleted_at`      TIMESTAMP NULL,

    -- ── Indexes ───────────────────────────────────────────────
    INDEX `idx_email`   (`email`),
    INDEX `idx_phone`   (`phone`),
    INDEX `idx_company` (`company`),
    INDEX `idx_org`     (`org_id`),
    INDEX `idx_province`(`province_code`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Tại sao snapshot `ward_name`, `province_name`?**
- Bảng đơn vị hành chính có thể thay đổi (sáp nhập, đổi tên). Snapshot giữ nguyên tên tại thời điểm nhập.
- Hiển thị danh sách Lead không cần JOIN thêm 3 bảng địa chính → query nhanh hơn.

**Tại sao `email` nullable trên Contact?**
- Thực tế CRM: nhiều Lead được tạo từ cuộc gọi điện — chỉ có số điện thoại, chưa có email.
- Email là UNIQUE trên `leads` (để Workflow idempotent), không phải trên `lead_contacts`.

### 2.4 `leads` — cơ hội kinh doanh (bảng chính)

```sql
CREATE TABLE `leads` (
    `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,

    -- ── Tổ chức sở hữu ────────────────────────────────────────
    `org_id`                INT UNSIGNED NULL,
    -- Tổ chức tạo ra cơ hội này
    -- NULL = không dùng multi-org hoặc lead toàn hệ thống

    -- ── Liên kết Contact ─────────────────────────────────────
    `contact_id`            BIGINT UNSIGNED NOT NULL,
    -- FK → lead_contacts.id
    -- Luôn phải có Contact khi tạo Lead

    -- Snapshot thông tin Contact tại thời điểm tạo Lead
    -- (tránh mất data khi Contact bị cập nhật/xóa)
    `contact_name`          VARCHAR(191) NOT NULL,
    `contact_email`         VARCHAR(191) NULL,
    `contact_phone`         VARCHAR(32)  NULL,
    `contact_company`       VARCHAR(191) NULL,
    `contact_job_title`     VARCHAR(128) NULL,

    -- ── Tình trạng cơ hội (Stage / Pipeline) ─────────────────
    `stage_id`              SMALLINT UNSIGNED NOT NULL,
    -- FK → lead_pipeline_stages.id (enforce ở app layer)

    `stage_changed_at`      DATETIME NULL,
    -- Thời điểm chuyển stage gần nhất — tính "time in stage"

    -- ── Nguồn cơ hội ─────────────────────────────────────────
    `source`                TINYINT UNSIGNED NOT NULL DEFAULT 1,
    -- 1=manual  2=survey  3=import  4=api  5=workflow  6=referral  7=event  8=website

    `source_detail`         VARCHAR(191) NULL,
    -- Mô tả chi tiết nguồn: tên event, tên campaign, tên người giới thiệu...

    -- ── Người phụ trách ──────────────────────────────────────
    `assigned_to`           BIGINT UNSIGNED NULL,
    -- FK → users.id — NULL = chưa phân công

    `assigned_at`           DATETIME NULL,

    -- ── Giá trị & thời gian ─────────────────────────────────
    `expected_value`        DECIMAL(15,2) NULL,
    -- Giá trị hợp đồng dự kiến (VND hoặc đơn vị tiền tệ cấu hình)
    -- NULL = chưa xác định

    `currency`              CHAR(3) NOT NULL DEFAULT 'VND',
    -- ISO 4217: 'VND', 'USD', 'EUR'

    `expected_close_date`   DATE NULL,
    -- Ngày chốt dự kiến — dùng DATE không phải DATETIME (chỉ cần ngày)
    -- NULL = chưa xác định

    `actual_close_date`     DATE NULL,
    -- Ngày chốt thực tế (điền khi stage = won/lost)

    `actual_value`          DECIMAL(15,2) NULL,
    -- Giá trị thực tế khi chốt (có thể khác expected)

    -- ── Mô tả cơ hội ────────────────────────────────────────
    `description`           TEXT NULL,
    -- Mô tả chi tiết về cơ hội, nhu cầu khách hàng, bối cảnh

    -- ── Liên kết Survey (nếu đến từ Survey) ─────────────────
    `survey_response_id`    BIGINT UNSIGNED NULL,
    -- Snapshot Survey data — không join lại Survey module

    `survey_band_code`      VARCHAR(64) NULL,
    `survey_score`          DECIMAL(5,2) NULL,

    -- ── Lead Score (tính tự động) ────────────────────────────
    `lead_score`            TINYINT UNSIGNED NOT NULL DEFAULT 0,
    -- 0–100 — tính bởi ScoreLeadAction

    `score_updated_at`      DATETIME NULL,

    -- ── Trạng thái tổng thể ──────────────────────────────────
    `status`                TINYINT UNSIGNED NOT NULL DEFAULT 1,
    -- 1=active  2=converted  3=archived  4=on_hold

    -- ── Tracking ─────────────────────────────────────────────
    `last_activity_at`      DATETIME NULL,
    -- Cập nhật mỗi lần có LeadActivity mới — filter "inactive leads"

    `created_by`            BIGINT UNSIGNED NULL,
    `updated_by`            BIGINT UNSIGNED NULL,
    `created_at`            TIMESTAMP NULL,
    `updated_at`            TIMESTAMP NULL,
    `deleted_at`            TIMESTAMP NULL,

    -- ── Indexes ───────────────────────────────────────────────
    INDEX `idx_org_stage`     (`org_id`, `stage_id`, `status`),
    INDEX `idx_assigned`      (`assigned_to`, `org_id`, `stage_id`),
    INDEX `idx_contact`       (`contact_id`),
    INDEX `idx_source`        (`source`, `org_id`),
    INDEX `idx_score`         (`lead_score`, `stage_id`),
    INDEX `idx_close_date`    (`expected_close_date`, `status`),
    INDEX `idx_activity`      (`last_activity_at`),
    INDEX `idx_survey`        (`survey_response_id`),
    INDEX `idx_value`         (`expected_value`, `status`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Tại sao snapshot `contact_*` fields trên bảng leads?**
- Khi Contact cập nhật thông tin (đổi công ty, đổi số điện thoại), lịch sử Lead cũ vẫn ghi nhận đúng thông tin tại thời điểm đó.
- Query danh sách Lead có đủ thông tin liên hệ mà không cần JOIN sang `lead_contacts`.
- Pattern này giống `actor_name` trong ActivityLog — snapshot, không phụ thuộc bảng gốc.

**Tại sao `expected_close_date` là DATE thay vì DATETIME?**
- Ngày chốt là khái niệm theo ngày, không theo giờ phút.
- `DATE` nhỏ hơn `DATETIME` (3 bytes vs 8 bytes), index hiệu quả hơn.
- Query "leads sắp đến hạn trong 7 ngày" dùng `BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)` — rõ ràng hơn với DATE.

### 2.5 `lead_activities` — nhật ký thao tác

```sql
CREATE TABLE `lead_activities` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `lead_id`       BIGINT UNSIGNED NOT NULL,

    `type`          TINYINT UNSIGNED NOT NULL,
    -- 1=call  2=email  3=meeting  4=note  5=stage_change
    -- 6=assign  7=score_update  8=system  9=task  10=visit

    `title`         VARCHAR(191) NOT NULL,
    `description`   TEXT NULL,

    `outcome`       VARCHAR(64) NULL,
    -- 'interested' | 'not_now' | 'no_answer' | 'follow_up' | 'converted' | 'rejected'

    `scheduled_at`  DATETIME NULL,
    `completed_at`  DATETIME NULL,

    -- Metadata nhỏ (2 cặp key-value — đủ cho activity đơn giản)
    `meta_key_1`    VARCHAR(64)  NULL,   -- 'duration_minutes', 'attendees'
    `meta_val_1`    VARCHAR(255) NULL,
    `meta_key_2`    VARCHAR(64)  NULL,
    `meta_val_2`    VARCHAR(255) NULL,

    `actor_id`      BIGINT UNSIGNED NULL,
    `actor_name`    VARCHAR(191) NULL,   -- snapshot

    `created_at`    TIMESTAMP NULL,

    INDEX `idx_lead`  (`lead_id`, `created_at`),
    INDEX `idx_type`  (`type`, `lead_id`),
    INDEX `idx_sched` (`scheduled_at`, `completed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2.6 `lead_notes` — ghi chú (có thể ghim)

```sql
CREATE TABLE `lead_notes` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `lead_id`       BIGINT UNSIGNED NOT NULL,
    `content`       TEXT NOT NULL,
    `is_pinned`     TINYINT(1) NOT NULL DEFAULT 0,
    `author_id`     BIGINT UNSIGNED NULL,
    `author_name`   VARCHAR(191) NULL,
    `created_at`    TIMESTAMP NULL,
    `updated_at`    TIMESTAMP NULL,
    `deleted_at`    TIMESTAMP NULL,

    INDEX `idx_lead` (`lead_id`, `is_pinned`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2.7 `lead_stage_history` — lịch sử đổi tình trạng

```sql
CREATE TABLE `lead_stage_history` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `lead_id`         BIGINT UNSIGNED NOT NULL,
    `stage_from_id`   SMALLINT UNSIGNED NULL,    -- NULL = lần đầu tiên
    `stage_to_id`     SMALLINT UNSIGNED NOT NULL,
    `stage_from_label`VARCHAR(64) NULL,           -- snapshot
    `stage_to_label`  VARCHAR(64) NOT NULL,       -- snapshot
    `changed_by`      BIGINT UNSIGNED NULL,
    `changed_by_name` VARCHAR(191) NULL,          -- snapshot
    `note`            VARCHAR(500) NULL,
    `changed_at`      DATETIME NOT NULL,
    `created_at`      TIMESTAMP NULL,

    INDEX `idx_lead`  (`lead_id`, `changed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2.8 `lead_meta` — metadata mở rộng (EAV, không JSON)

Cho phép lưu data tùy ý per lead mà không thêm column vào bảng chính.

```sql
CREATE TABLE `lead_meta` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `lead_id`      BIGINT UNSIGNED NOT NULL,
    `key_name`     VARCHAR(64) NOT NULL,
    `value_type`   TINYINT UNSIGNED NOT NULL DEFAULT 1,
    -- 1=string  2=integer  3=decimal  4=boolean  5=datetime

    `val_string`   VARCHAR(500) NULL,
    `val_integer`  BIGINT       NULL,
    `val_decimal`  DECIMAL(20,6) NULL,
    `val_boolean`  TINYINT(1)   NULL,
    `val_datetime` DATETIME     NULL,
    `created_at`   TIMESTAMP NULL,

    UNIQUE KEY `uq_lead_key`  (`lead_id`, `key_name`),
    INDEX `idx_key_string`    (`key_name`, `val_string`(64)),
    INDEX `idx_key_integer`   (`key_name`, `val_integer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2.9 Quan hệ tổng quan

```
organizations (1) ──────────────────────────────────────┐
                                                        │ org_id
lead_pipeline_stages (N) ←── org_id (nullable)         │
         │ stage_id                                     │
         ▼                                              ▼
lead_contacts (1) ←──── contact_id ────── leads (N) ───── users (assigned_to)
                                              │
                    ┌─────────────────────────┤
                    │                         │
                    ▼                         ▼
            lead_activities (N)    lead_stage_history (N)
            lead_notes (N)
            lead_meta (N)
```

---

## 3. Enums

```php
// Modules/Lead/app/Enums/LeadSource.php
enum LeadSource: int
{
    case Manual   = 1;
    case Survey   = 2;
    case Import   = 3;
    case Api      = 4;
    case Workflow = 5;
    case Referral = 6;
    case Event    = 7;
    case Website  = 8;

    public function label(): string
    {
        return match($this) {
            self::Manual   => 'Thủ công',
            self::Survey   => 'Survey',
            self::Import   => 'Import file',
            self::Api      => 'API',
            self::Workflow => 'Tự động (Workflow)',
            self::Referral => 'Giới thiệu',
            self::Event    => 'Sự kiện',
            self::Website  => 'Website',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Survey, self::Workflow => 'badge-purple',
            self::Manual                 => 'badge-gray',
            self::Import                 => 'badge-amber',
            self::Event                  => 'badge-teal',
            default                      => 'badge-blue',
        };
    }
}

// Modules/Lead/app/Enums/LeadStatus.php
enum LeadStatus: int
{
    case Active    = 1;
    case Converted = 2;  // Đã thành khách hàng (stage is_won = true)
    case Archived  = 3;  // Đã kết thúc thất bại (stage is_lost = true)
    case OnHold    = 4;  // Tạm dừng theo đuổi

    public function label(): string
    {
        return match($this) {
            self::Active    => 'Đang theo dõi',
            self::Converted => 'Đã chuyển đổi',
            self::Archived  => 'Lưu trữ',
            self::OnHold    => 'Tạm dừng',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Active    => 'badge-teal',
            self::Converted => 'badge-green',
            self::Archived  => 'badge-gray',
            self::OnHold    => 'badge-amber',
        };
    }
}

// Modules/Lead/app/Enums/LeadActivityType.php
enum LeadActivityType: int
{
    case Call        = 1;
    case Email       = 2;
    case Meeting     = 3;
    case Note        = 4;
    case StageChange = 5;
    case Assign      = 6;
    case ScoreUpdate = 7;
    case System      = 8;
    case Task        = 9;
    case Visit       = 10;  // Thăm khách hàng tại chỗ

    public function label(): string
    {
        return match($this) {
            self::Call        => 'Cuộc gọi',
            self::Email       => 'Email',
            self::Meeting     => 'Cuộc họp',
            self::Note        => 'Ghi chú',
            self::StageChange => 'Đổi tình trạng',
            self::Assign      => 'Phân công',
            self::ScoreUpdate => 'Cập nhật điểm',
            self::System      => 'Hệ thống',
            self::Task        => 'Công việc',
            self::Visit       => 'Thăm khách hàng',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Call        => 'ti-phone',
            self::Email       => 'ti-mail',
            self::Meeting     => 'ti-calendar-event',
            self::Note        => 'ti-file-text',
            self::StageChange => 'ti-arrows-right-left',
            self::Assign      => 'ti-user-check',
            self::ScoreUpdate => 'ti-chart-bar',
            self::System      => 'ti-settings-2',
            self::Task        => 'ti-checkbox',
            self::Visit       => 'ti-map-pin',
        };
    }
}
```

---

## 4. DTOs

```php
// Modules/Lead/app/Data/ContactData.php
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\{Max, Nullable, Email as EmailRule};

class ContactData extends Data
{
    public function __construct(
        #[Max(191)]
        public readonly string  $fullName,

        #[EmailRule, Max(191), Nullable]
        public readonly ?string $email,

        #[Max(32), Nullable]
        public readonly ?string $phone,

        #[Max(32), Nullable]
        public readonly ?string $phoneAlt,

        #[Max(191), Nullable]
        public readonly ?string $company,

        #[Max(128), Nullable]
        public readonly ?string $jobTitle,

        #[Max(500), Nullable]
        public readonly ?string $website,

        #[Max(500), Nullable]
        public readonly ?string $address,

        #[Max(8), Nullable]
        public readonly ?string $wardCode,

        #[Max(64), Nullable]
        public readonly ?string $wardName,

        #[Max(8), Nullable]
        public readonly ?string $districtCode,

        #[Max(64), Nullable]
        public readonly ?string $districtName,

        #[Max(8), Nullable]
        public readonly ?string $provinceCode,

        #[Max(64), Nullable]
        public readonly ?string $provinceName,

        public readonly string  $countryCode = 'VN',
        public readonly ?int    $orgId = null,
    ) {}
}

// Modules/Lead/app/Data/LeadData.php
class LeadData extends Data
{
    public function __construct(
        // Contact — có thể truyền contact_id (dùng contact có sẵn)
        // hoặc truyền ContactData (tạo contact mới đồng thời)
        public readonly ?int         $contactId,
        public readonly ?ContactData $contact,
        // Validate: phải có 1 trong 2

        // Tình trạng cơ hội
        public readonly int          $stageId,

        // Nguồn
        public readonly LeadSource   $source,
        public readonly ?string      $sourceDetail,

        // Người phụ trách
        public readonly ?int         $assignedTo,

        // Giá trị & thời gian
        public readonly ?float       $expectedValue,
        public readonly string       $currency = 'VND',
        public readonly ?string      $expectedCloseDate,  // 'Y-m-d'

        // Mô tả
        public readonly ?string      $description,

        // Org
        public readonly ?int         $orgId,

        // Survey context (nếu đến từ Workflow)
        public readonly ?int         $surveyResponseId,
        public readonly ?string      $surveyBandCode,
        public readonly ?float       $surveyScore,

        // Meta tùy ý
        public readonly array        $meta = [],
    ) {}
}

// Modules/Lead/app/Data/LeadFilterData.php
class LeadFilterData extends Data
{
    public function __construct(
        public readonly ?int    $orgId,
        public readonly ?int    $stageId,
        public readonly ?int    $assignedTo,
        public readonly ?int    $source,
        public readonly ?string $search,
        // full-text: contact_name, contact_email, contact_company

        public readonly ?float  $scoreMin,
        public readonly ?float  $scoreMax,
        public readonly ?float  $valueMin,
        public readonly ?float  $valueMax,
        public readonly ?string $closeDateFrom,  // 'Y-m-d'
        public readonly ?string $closeDateTo,
        public readonly ?string $dateFrom,       // created_at from
        public readonly ?string $dateTo,
        public readonly ?int    $status,
        public readonly ?string $provinceCode,
        public readonly string  $sort = 'created_at',
        public readonly string  $dir  = 'desc',
        public readonly int     $page = 0,
        public readonly int     $size = 20,
    ) {}
}

// Modules/Lead/app/Data/LeadActivityData.php
class LeadActivityData extends Data
{
    public function __construct(
        public readonly int              $leadId,
        public readonly LeadActivityType $type,
        public readonly string           $title,
        public readonly ?string          $description,
        public readonly ?string          $outcome,
        public readonly ?\DateTimeInterface $scheduledAt,
        public readonly ?\DateTimeInterface $completedAt,
        public readonly ?string          $metaKey1 = null,
        public readonly ?string          $metaVal1 = null,
    ) {}
}
```

---

## 5. Models

### 5.1 `Lead` model

```php
// Modules/Lead/app/Models/Lead.php
use Modules\WorkflowAutomation\Contracts\WorkflowSubject;
use Modules\ActivityLog\app\Traits\HasActivityLog;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Lead extends Model implements WorkflowSubject
{
    use SoftDeletes, LogsActivity, HasActivityLog;

    protected $casts = [
        'source'              => LeadSource::class,
        'status'              => LeadStatus::class,
        'expected_value'      => 'decimal:2',
        'actual_value'        => 'decimal:2',
        'survey_score'        => 'decimal:2',
        'expected_close_date' => 'date',
        'actual_close_date'   => 'date',
        'stage_changed_at'    => 'datetime',
        'assigned_at'         => 'datetime',
        'last_activity_at'    => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────

    public function contact(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LeadContact::class, 'contact_id');
    }

    public function stage(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LeadPipelineStage::class, 'stage_id');
    }

    public function assignee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    public function organization(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    public function activities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeadActivity::class)->orderByDesc('created_at');
    }

    public function notes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeadNote::class)
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at');
    }

    public function meta(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeadMeta::class);
    }

    public function stageHistory(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeadStageHistory::class)->orderByDesc('changed_at');
    }

    // ── WorkflowSubject interface ─────────────────────────────

    public static function workflowSubjectType(): string
    {
        return 'Lead';
    }

    public static function workflowUpdatableFields(): array
    {
        return [
            ['field' => 'stage_id',             'label' => 'Tình trạng',        'type' => 'integer'],
            ['field' => 'assigned_to',           'label' => 'Người phụ trách',   'type' => 'integer'],
            ['field' => 'status',                'label' => 'Trạng thái',        'type' => 'integer'],
            ['field' => 'lead_score',            'label' => 'Lead score',        'type' => 'integer'],
            ['field' => 'expected_value',        'label' => 'Giá trị dự kiến',  'type' => 'decimal'],
            ['field' => 'expected_close_date',   'label' => 'Ngày chốt dự kiến','type' => 'string'],
        ];
    }

    public static function resolveFromPayload(
        \Modules\WorkflowAutomation\Data\TriggerPayload $payload
    ): ?static {
        if ($payload->subjectType === 'Lead' && $payload->subjectId) {
            return static::find($payload->subjectId);
        }
        if ($payload->actorEmail) {
            return static::where('contact_email', $payload->actorEmail)->first();
        }
        return null;
    }

    // ── Spatie ActivityLog ────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'stage_id', 'assigned_to', 'status', 'lead_score',
                'expected_value', 'expected_close_date',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ── Interface LoggableSubject (ActivityLog) ───────────────

    public function getActivityLabel(): string
    {
        return $this->contact_name ?: $this->contact_email ?: "Lead #{$this->id}";
    }

    public function getActivityRouteUrl(): ?string
    {
        return route('leads.show', $this);
    }

    // ── Accessors ────────────────────────────────────────────

    public function getDaysInStageAttribute(): int
    {
        return (int) ($this->stage_changed_at ?? $this->created_at)->diffInDays(now());
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->expected_close_date
            && $this->expected_close_date->isPast()
            && $this->status === LeadStatus::Active;
    }

    public function getWeightedValueAttribute(): ?float
    {
        if (!$this->expected_value) return null;
        $probability = $this->stage?->probability ?? 0;
        return round($this->expected_value * $probability / 100, 2);
    }

    public function getMetaMapAttribute(): array
    {
        return $this->meta
            ->mapWithKeys(fn($m) => [$m->key_name => $m->typedValue()])
            ->all();
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeForOrg($q, ?int $orgId)
    {
        return $orgId ? $q->where('org_id', $orgId) : $q;
    }

    public function scopeActive($q)
    {
        return $q->where('status', LeadStatus::Active);
    }

    public function scopeAssignedTo($q, int $userId)
    {
        return $q->where('assigned_to', $userId);
    }

    public function scopeInStage($q, int $stageId)
    {
        return $q->where('stage_id', $stageId);
    }

    public function scopeOverdue($q)
    {
        return $q->where('expected_close_date', '<', today())
                 ->where('status', LeadStatus::Active);
    }

    public function scopeClosingIn($q, int $days)
    {
        return $q->whereBetween('expected_close_date', [today(), today()->addDays($days)])
                 ->where('status', LeadStatus::Active);
    }

    public function scopeStale($q, int $days = 14)
    {
        return $q->where(fn($q2) =>
            $q2->where('last_activity_at', '<', now()->subDays($days))
               ->orWhereNull('last_activity_at')
        )->where('status', LeadStatus::Active);
    }
}
```

### 5.2 `LeadContact` model

```php
class LeadContact extends Model
{
    use SoftDeletes;

    protected $table = 'lead_contacts';

    protected $fillable = [
        'org_id', 'full_name', 'email', 'phone', 'phone_alt',
        'company', 'job_title', 'website', 'address',
        'ward_code', 'ward_name', 'district_code', 'district_name',
        'province_code', 'province_name', 'country_code', 'created_by',
    ];

    public function leads(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Lead::class, 'contact_id');
    }

    /** Địa chỉ đầy đủ dạng string */
    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->address,
            $this->ward_name,
            $this->district_name,
            $this->province_name,
        ]));
    }
}
```

### 5.3 `LeadPipelineStage` model

```php
class LeadPipelineStage extends Model
{
    protected $casts = [
        'is_won'    => 'boolean',
        'is_lost'   => 'boolean',
        'is_active' => 'boolean',
    ];

    public function leads(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Lead::class, 'stage_id');
    }

    /** Lấy stages cho org (kết hợp stages global + stages riêng của org) */
    public static function forOrg(?int $orgId): \Illuminate\Support\Collection
    {
        return static::where('is_active', 1)
            ->where(fn($q) =>
                $q->whereNull('org_id')
                  ->when($orgId, fn($q2) => $q2->orWhere('org_id', $orgId))
            )
            ->orderBy('sort_order')
            ->get();
    }
}
```

---

## 6. Actions

### 6.1 `CreateLeadAction`

```php
// Modules/Lead/app/Actions/CreateLeadAction.php
use Lorisleiva\Actions\Concerns\AsAction;

class CreateLeadAction
{
    use AsAction;

    public function handle(LeadData $data, ?int $createdBy = null): Lead
    {
        // ── 1. Resolve hoặc tạo Contact ──────────────────────
        $contact = $this->resolveContact($data, $createdBy);

        // ── 2. Idempotent check: cùng org + contact → không tạo trùng ──
        $existing = Lead::withTrashed()
            ->where('contact_id', $contact->id)
            ->where('org_id', $data->orgId)
            ->whereNotIn('status', [LeadStatus::Converted->value, LeadStatus::Archived->value])
            ->first();

        if ($existing) {
            if ($existing->trashed()) $existing->restore();
            return $this->mergeIntoExisting($existing, $data, $contact);
        }

        // ── 3. Lấy stage mặc định ────────────────────────────
        $stageId = $data->stageId
            ?: LeadPipelineStage::forOrg($data->orgId)->first()?->id
            ?: 1;

        // ── 4. Tạo Lead ─────────────────────────────────────
        $lead = Lead::create([
            'org_id'                => $data->orgId,
            'contact_id'            => $contact->id,

            // Snapshot contact
            'contact_name'          => $contact->full_name,
            'contact_email'         => $contact->email,
            'contact_phone'         => $contact->phone,
            'contact_company'       => $contact->company,
            'contact_job_title'     => $contact->job_title,

            'stage_id'              => $stageId,
            'stage_changed_at'      => now(),
            'source'                => $data->source,
            'source_detail'         => $data->sourceDetail,
            'assigned_to'           => $data->assignedTo,
            'assigned_at'           => $data->assignedTo ? now() : null,
            'expected_value'        => $data->expectedValue,
            'currency'              => $data->currency,
            'expected_close_date'   => $data->expectedCloseDate,
            'description'           => $data->description,
            'survey_response_id'    => $data->surveyResponseId,
            'survey_band_code'      => $data->surveyBandCode,
            'survey_score'          => $data->surveyScore,
            'lead_score'            => 0,
            'status'                => LeadStatus::Active,
            'created_by'            => $createdBy,
        ]);

        // ── 5. Lưu meta ─────────────────────────────────────
        if (!empty($data->meta)) {
            $this->saveMeta($lead, $data->meta);
        }

        // ── 6. Ghi activity đầu tiên ────────────────────────
        LogLeadActivityAction::run(new LeadActivityData(
            leadId:      $lead->id,
            type:        LeadActivityType::System,
            title:       'Lead được tạo từ ' . $data->source->label(),
            description: $data->surveyBandCode
                ? "Band: {$data->surveyBandCode} · Score: {$data->surveyScore}%"
                : $data->sourceDetail,
            outcome:     null,
            scheduledAt: null,
            completedAt: null,
        ));

        // ── 7. Tính lead score async ─────────────────────────
        ScoreLeadAction::dispatch($lead->id)->onQueue(config('lead.queue', 'default'));

        // ── 8. Fire Workflow event ──────────────────────────
        $this->fireWorkflowEvent($lead, $data);

        return $lead;
    }

    private function resolveContact(LeadData $data, ?int $createdBy): LeadContact
    {
        // Dùng contact_id có sẵn
        if ($data->contactId) {
            return LeadContact::findOrFail($data->contactId);
        }

        // Tạo mới từ ContactData
        $cd = $data->contact;
        if (!$cd) {
            throw new \InvalidArgumentException('Phải cung cấp contact_id hoặc contact data');
        }

        // Tìm contact cùng email + org nếu có
        if ($cd->email) {
            $found = LeadContact::where('email', $cd->email)
                ->where('org_id', $cd->orgId ?? $data->orgId)
                ->first();
            if ($found) return $found;
        }

        return LeadContact::create([
            'org_id'        => $cd->orgId ?? $data->orgId,
            'full_name'     => $cd->fullName,
            'email'         => $cd->email,
            'phone'         => $cd->phone,
            'phone_alt'     => $cd->phoneAlt,
            'company'       => $cd->company,
            'job_title'     => $cd->jobTitle,
            'website'       => $cd->website,
            'address'       => $cd->address,
            'ward_code'     => $cd->wardCode,
            'ward_name'     => $cd->wardName,
            'district_code' => $cd->districtCode,
            'district_name' => $cd->districtName,
            'province_code' => $cd->provinceCode,
            'province_name' => $cd->provinceName,
            'country_code'  => $cd->countryCode,
            'created_by'    => $createdBy,
        ]);
    }

    private function mergeIntoExisting(Lead $existing, LeadData $data, LeadContact $contact): Lead
    {
        $updates = [];

        // Cập nhật snapshot contact nếu thông tin mới đầy đủ hơn
        if (!$existing->contact_phone && $contact->phone) {
            $updates['contact_phone'] = $contact->phone;
        }
        if (!$existing->contact_company && $contact->company) {
            $updates['contact_company'] = $contact->company;
        }

        // Cập nhật giá trị dự kiến nếu mới cao hơn
        if ($data->expectedValue && $data->expectedValue > ($existing->expected_value ?? 0)) {
            $updates['expected_value'] = $data->expectedValue;
        }

        // Cập nhật survey data nếu score mới cao hơn
        if ($data->surveyScore && $data->surveyScore > ($existing->survey_score ?? 0)) {
            $updates['survey_band_code']   = $data->surveyBandCode;
            $updates['survey_score']       = $data->surveyScore;
            $updates['survey_response_id'] = $data->surveyResponseId;
        }

        if (!empty($updates)) {
            $existing->update($updates);
        }

        return $existing->fresh();
    }

    private function saveMeta(Lead $lead, array $meta): void
    {
        foreach ($meta as $key => $value) {
            LeadMeta::updateOrCreate(
                ['lead_id' => $lead->id, 'key_name' => substr($key, 0, 64)],
                $this->buildMetaRow($value)
            );
        }
    }

    private function buildMetaRow(mixed $value): array
    {
        $base = ['value_type' => 1, 'val_string' => null, 'val_integer' => null,
                 'val_decimal' => null, 'val_boolean' => null, 'val_datetime' => null];

        if      (is_bool($value))                      { $base['value_type']=4; $base['val_boolean'] =(int)$value; }
        elseif  (is_int($value))                       { $base['value_type']=2; $base['val_integer'] =$value; }
        elseif  (is_float($value))                     { $base['value_type']=3; $base['val_decimal'] =$value; }
        elseif  ($value instanceof \DateTimeInterface)  { $base['value_type']=5; $base['val_datetime']=$value->format('Y-m-d H:i:s'); }
        else    { $base['value_type']=1; $base['val_string']=substr((string)$value, 0, 500); }

        return $base;
    }

    private function fireWorkflowEvent(Lead $lead, LeadData $data): void
    {
        if (!class_exists(\Modules\WorkflowAutomation\Core\WorkflowDispatcher::class)) return;

        \Modules\WorkflowAutomation\Core\WorkflowDispatcher::fire(
            new \Modules\WorkflowAutomation\Data\TriggerPayload(
                triggerType:  'lead.created',
                sourceModule: 'Lead',
                actorId:      $lead->created_by,
                actorEmail:   $lead->contact_email,
                actorName:    $lead->contact_name,
                actorRole:    null,
                subjectType:  'Lead',
                subjectId:    $lead->id,
                subjectLabel: $lead->getActivityLabel(),
                extra: [
                    'org_id'           => $lead->org_id,
                    'source'           => $data->source->value,
                    'stage_id'         => $lead->stage_id,
                    'survey_band_code' => $data->surveyBandCode,
                    'survey_score'     => $data->surveyScore,
                    'expected_value'   => $data->expectedValue,
                    'province_code'    => $lead->contact?->province_code,
                ],
                requestId: request()->header('X-Request-Id', (string) \Str::uuid()),
            )
        );
    }
}
```

### 6.2 `UpdateLeadAction`

```php
class UpdateLeadAction
{
    use AsAction;

    public function handle(Lead $lead, LeadData $data, int $updatedBy): Lead
    {
        // Cập nhật thông tin cơ hội
        $lead->update([
            'source'              => $data->source,
            'source_detail'       => $data->sourceDetail,
            'expected_value'      => $data->expectedValue,
            'currency'            => $data->currency,
            'expected_close_date' => $data->expectedCloseDate,
            'description'         => $data->description,
            'updated_by'          => $updatedBy,
        ]);

        // Cập nhật Contact nếu có thay đổi
        if ($data->contact && $lead->contact) {
            $lead->contact->update([
                'full_name'     => $data->contact->fullName,
                'email'         => $data->contact->email,
                'phone'         => $data->contact->phone,
                'phone_alt'     => $data->contact->phoneAlt,
                'company'       => $data->contact->company,
                'job_title'     => $data->contact->jobTitle,
                'website'       => $data->contact->website,
                'address'       => $data->contact->address,
                'ward_code'     => $data->contact->wardCode,
                'ward_name'     => $data->contact->wardName,
                'district_code' => $data->contact->districtCode,
                'district_name' => $data->contact->districtName,
                'province_code' => $data->contact->provinceCode,
                'province_name' => $data->contact->provinceName,
            ]);

            // Cập nhật snapshot trên Lead
            $lead->update([
                'contact_name'      => $data->contact->fullName,
                'contact_email'     => $data->contact->email,
                'contact_phone'     => $data->contact->phone,
                'contact_company'   => $data->contact->company,
                'contact_job_title' => $data->contact->jobTitle,
            ]);
        }

        if (!empty($data->meta)) {
            app(CreateLeadAction::class)->saveMeta($lead, $data->meta);
        }

        return $lead->fresh();
    }
}
```

### 6.3 `ChangeLeadStageAction`

```php
class ChangeLeadStageAction
{
    use AsAction;

    public function handle(
        Lead    $lead,
        int     $newStageId,
        ?string $note      = null,
        ?int    $changedBy = null,
    ): Lead {
        $oldStageId = $lead->stage_id;
        if ($oldStageId === $newStageId) return $lead;

        $newStage    = LeadPipelineStage::findOrFail($newStageId);
        $oldStage    = LeadPipelineStage::find($oldStageId);

        // Cập nhật stage + trạng thái nếu vào stage kết thúc
        $updateData = [
            'stage_id'         => $newStageId,
            'stage_changed_at' => now(),
        ];

        if ($newStage->is_won) {
            $updateData['status']            = LeadStatus::Converted;
            $updateData['actual_close_date'] = today();
        } elseif ($newStage->is_lost) {
            $updateData['status']    = LeadStatus::Archived;
            $updateData['actual_close_date'] = today();
        }

        $lead->update($updateData);

        // Ghi lịch sử stage
        LeadStageHistory::create([
            'lead_id'           => $lead->id,
            'stage_from_id'     => $oldStageId,
            'stage_to_id'       => $newStageId,
            'stage_from_label'  => $oldStage?->label,
            'stage_to_label'    => $newStage->label,
            'changed_by'        => $changedBy,
            'changed_by_name'   => $changedBy
                ? \App\Models\User::find($changedBy)?->name
                : 'system',
            'note'              => $note,
            'changed_at'        => now(),
        ]);

        // Ghi activity
        LogLeadActivityAction::run(new LeadActivityData(
            leadId:      $lead->id,
            type:        LeadActivityType::StageChange,
            title:       "Đổi tình trạng: {$oldStage?->label} → {$newStage->label}",
            description: $note,
            outcome:     $newStage->is_won ? 'converted' : ($newStage->is_lost ? 'lost' : null),
            scheduledAt: null,
            completedAt: now(),
        ));

        // Fire Workflow event
        if (class_exists(\Modules\WorkflowAutomation\Core\WorkflowDispatcher::class)) {
            \Modules\WorkflowAutomation\Core\WorkflowDispatcher::fire(
                new \Modules\WorkflowAutomation\Data\TriggerPayload(
                    triggerType:  'lead.stage_changed',
                    sourceModule: 'Lead',
                    actorId:      $changedBy,
                    actorEmail:   $lead->contact_email,
                    actorName:    $lead->contact_name,
                    actorRole:    null,
                    subjectType:  'Lead',
                    subjectId:    $lead->id,
                    subjectLabel: $lead->getActivityLabel(),
                    extra: [
                        'stage_from_id'    => $oldStageId,
                        'stage_to_id'      => $newStageId,
                        'stage_to_label'   => $newStage->label,
                        'is_won'           => (bool) $newStage->is_won,
                        'is_lost'          => (bool) $newStage->is_lost,
                        'days_in_prev_stage' => $lead->days_in_stage,
                        'expected_value'   => $lead->expected_value,
                    ],
                    requestId: request()->header('X-Request-Id', (string) \Str::uuid()),
                )
            );
        }

        return $lead->fresh();
    }
}
```

### 6.4 `ScoreLeadAction`

```php
class ScoreLeadAction
{
    use AsAction;

    public string $jobQueue = 'default';

    public function handle(int $leadId): void
    {
        $lead = Lead::with(['activities', 'contact'])->find($leadId);
        if (!$lead) return;

        $oldScore = $lead->lead_score;
        $score    = 0;

        // Điểm từ Survey result (0–40)
        if ($lead->survey_score !== null) {
            $score += (int) min(40, $lead->survey_score * 0.4);
        }

        // Điểm từ giá trị dự kiến (0–20)
        // Tham chiếu: deal < 10tr = 5đ, 10-50tr = 10đ, > 50tr = 20đ
        if ($lead->expected_value) {
            $score += match(true) {
                $lead->expected_value >= 50_000_000 => 20,
                $lead->expected_value >= 10_000_000 => 10,
                default                             => 5,
            };
        }

        // Điểm từ tiến độ stage (0–20)
        $maxOrder  = LeadPipelineStage::where('is_won', 0)->where('is_lost', 0)->max('sort_order') ?: 1;
        $stageOrder= $lead->stage?->sort_order ?? 0;
        $score    += (int) (($stageOrder / $maxOrder) * 20);

        // Điểm từ số activity (0–10)
        $score += min(10, $lead->activities->count() * 2);

        // Điểm từ thông tin đầy đủ (0–10)
        $score += $lead->contact?->phone    ? 2 : 0;
        $score += $lead->contact?->company  ? 3 : 0;
        $score += $lead->contact?->job_title? 2 : 0;
        $score += $lead->expected_close_date? 2 : 0;
        $score += $lead->description        ? 1 : 0;

        $score = min(100, max(0, $score));

        if ($score !== $oldScore) {
            $lead->update([
                'lead_score'       => $score,
                'score_updated_at' => now(),
            ]);

            // Fire event nếu thay đổi >= 10 điểm
            if (abs($score - $oldScore) >= 10
                && class_exists(\Modules\WorkflowAutomation\Core\WorkflowDispatcher::class)
            ) {
                \Modules\WorkflowAutomation\Core\WorkflowDispatcher::fire(
                    new \Modules\WorkflowAutomation\Data\TriggerPayload(
                        triggerType:  'lead.score_updated',
                        sourceModule: 'Lead',
                        subjectType:  'Lead',
                        subjectId:    $lead->id,
                        subjectLabel: $lead->getActivityLabel(),
                        extra: [
                            'score_before'   => $oldScore,
                            'score_after'    => $score,
                            'stage_id'       => $lead->stage_id,
                            'expected_value' => $lead->expected_value,
                        ],
                        requestId: (string) \Str::uuid(),
                    )
                );
            }
        }
    }
}
```

---

## 7. Services

### 7.1 `LeadQueryService`

```php
class LeadQueryService
{
    public function paginate(LeadFilterData $f): array
    {
        $query = Lead::with([
            'stage:id,label,color,probability',
            'assignee:id,name',
        ])->whereNull('deleted_at');

        $this->applyFilters($query, $f);

        $total = $query->count();
        $data  = $query
            ->orderBy($this->allowedSort($f->sort), $f->dir === 'asc' ? 'asc' : 'desc')
            ->offset($f->page * $f->size)->limit($f->size)
            ->get([
                'id', 'org_id', 'contact_id',
                'contact_name', 'contact_email', 'contact_phone', 'contact_company', 'contact_job_title',
                'stage_id', 'source', 'assigned_to', 'lead_score',
                'expected_value', 'currency', 'expected_close_date',
                'survey_band_code', 'survey_score',
                'status', 'last_activity_at', 'stage_changed_at', 'created_at',
            ]);

        return [
            'data'      => $data,
            'total'     => $total,
            'last_page' => (int) ceil($total / max($f->size, 1)),
        ];
    }

    public function applyFilters(\Illuminate\Database\Eloquent\Builder $q, LeadFilterData $f): void
    {
        if ($f->orgId)        $q->where('org_id', $f->orgId);
        if ($f->stageId)      $q->where('stage_id', $f->stageId);
        if ($f->assignedTo)   $q->where('assigned_to', $f->assignedTo);
        if ($f->source)       $q->where('source', $f->source);
        if ($f->status)       $q->where('status', $f->status);
        if ($f->scoreMin)     $q->where('lead_score', '>=', $f->scoreMin);
        if ($f->scoreMax)     $q->where('lead_score', '<=', $f->scoreMax);
        if ($f->valueMin)     $q->where('expected_value', '>=', $f->valueMin);
        if ($f->valueMax)     $q->where('expected_value', '<=', $f->valueMax);
        if ($f->closeDateFrom)$q->where('expected_close_date', '>=', $f->closeDateFrom);
        if ($f->closeDateTo)  $q->where('expected_close_date', '<=', $f->closeDateTo);
        if ($f->dateFrom)     $q->where('created_at', '>=', $f->dateFrom . ' 00:00:00');
        if ($f->dateTo)       $q->where('created_at', '<=', $f->dateTo   . ' 23:59:59');

        // Filter theo tỉnh/TP (join sang contact)
        if ($f->provinceCode) {
            $q->whereHas('contact', fn($q2) =>
                $q2->where('province_code', $f->provinceCode)
            );
        }

        if ($f->search) {
            $t = '%' . $f->search . '%';
            $q->where(fn($q2) => $q2
                ->where('contact_name',    'like', $t)
                ->orWhere('contact_email', 'like', $t)
                ->orWhere('contact_company','like', $t)
                ->orWhere('contact_phone', 'like', $t)
                ->orWhere('description',   'like', $t)
            );
        }
    }

    private function allowedSort(string $sort): string
    {
        return in_array($sort, [
            'created_at', 'lead_score', 'last_activity_at',
            'expected_value', 'expected_close_date', 'stage_changed_at',
        ]) ? $sort : 'created_at';
    }
}
```

### 7.2 `LeadStatsService`

```php
class LeadStatsService
{
    public function summary(?int $orgId, int $days = 30): array
    {
        $from = now()->subDays($days);

        $baseQuery = fn() => Lead::whereNull('deleted_at')
            ->when($orgId, fn($q) => $q->where('org_id', $orgId));

        return [
            // Tổng quan
            'total_active'    => $baseQuery()->where('status', LeadStatus::Active)->count(),
            'total_converted' => $baseQuery()->where('status', LeadStatus::Converted)->count(),
            'new_this_period' => $baseQuery()->where('created_at', '>=', $from)->count(),
            'won_this_period' => $baseQuery()
                ->where('status', LeadStatus::Converted)
                ->where('actual_close_date', '>=', $from)->count(),

            // Pipeline value
            'pipeline_value'  => $baseQuery()
                ->where('status', LeadStatus::Active)
                ->sum('expected_value'),
            'weighted_value'  => $baseQuery()
                ->where('status', LeadStatus::Active)
                ->join('lead_pipeline_stages', 'leads.stage_id', '=', 'lead_pipeline_stages.id')
                ->selectRaw('SUM(expected_value * probability / 100) as val')
                ->value('val'),

            // Phân bố theo stage
            'by_stage'        => Lead::whereNull('deleted_at')
                ->when($orgId, fn($q) => $q->where('org_id', $orgId))
                ->where('status', LeadStatus::Active)
                ->selectRaw('stage_id, COUNT(*) as count, SUM(expected_value) as total_value')
                ->groupBy('stage_id')
                ->with('stage:id,label,color')
                ->get(),

            // Leads sắp đến hạn
            'overdue'         => $baseQuery()->overdue()->count(),
            'closing_7d'      => $baseQuery()->closingIn(7)->count(),

            // Lead stale (không có activity >= 14 ngày)
            'stale'           => $baseQuery()->stale(14)->count(),

            // Conversion rate (30 ngày)
            'conversion_rate' => $this->conversionRate($orgId, $from),
        ];
    }

    private function conversionRate(?int $orgId, \Carbon\Carbon $from): float
    {
        $total = Lead::whereNull('deleted_at')
            ->when($orgId, fn($q) => $q->where('org_id', $orgId))
            ->where('created_at', '>=', $from)->count();

        if ($total === 0) return 0;

        $won = Lead::whereNull('deleted_at')
            ->when($orgId, fn($q) => $q->where('org_id', $orgId))
            ->where('status', LeadStatus::Converted)
            ->where('actual_close_date', '>=', $from)->count();

        return round($won / $total * 100, 1);
    }
}
```

---

## 8. Routes & Controllers

### 8.1 Routes

```php
// Modules/Lead/routes/web.php
Route::prefix('dashboard/leads')
    ->middleware(['web', 'auth', 'can:lead.view'])
    ->name('leads.')
    ->group(function () {

        Route::get('/',              [LeadController::class, 'index'])  ->name('index');
        Route::get('/create',        [LeadController::class, 'create']) ->name('create') ->middleware('can:lead.create');
        Route::post('/',             [LeadController::class, 'store'])  ->name('store')  ->middleware('can:lead.create');
        Route::get('/{lead}',        [LeadController::class, 'show'])   ->name('show');
        Route::get('/{lead}/edit',   [LeadController::class, 'edit'])   ->name('edit')   ->middleware('can:lead.update');
        Route::put('/{lead}',        [LeadController::class, 'update']) ->name('update') ->middleware('can:lead.update');
        Route::delete('/{lead}',     [LeadController::class, 'destroy'])->name('destroy')->middleware('can:lead.delete');

        // Thao tác nhanh
        Route::patch('/{lead}/stage',  [LeadController::class, 'changeStage'])->name('stage')  ->middleware('can:lead.update');
        Route::patch('/{lead}/assign', [LeadController::class, 'assign'])     ->name('assign') ->middleware('can:lead.assign');

        // Activities
        Route::post('/{lead}/activities',             [LeadActivityController::class, 'store'])  ->name('activities.store');
        Route::put('/{lead}/activities/{activity}',   [LeadActivityController::class, 'update']) ->name('activities.update');
        Route::delete('/{lead}/activities/{activity}',[LeadActivityController::class, 'destroy'])->name('activities.destroy');

        // Notes
        Route::post('/{lead}/notes',              [LeadNoteController::class, 'store'])     ->name('notes.store');
        Route::patch('/{lead}/notes/{note}/pin',  [LeadNoteController::class, 'togglePin']) ->name('notes.pin');
        Route::delete('/{lead}/notes/{note}',     [LeadNoteController::class, 'destroy'])   ->name('notes.destroy');

        // Export
        Route::post('/export',                   [LeadController::class, 'export'])        ->name('export')        ->middleware('can:lead.export');
        Route::get('/export/download/{key}',     [LeadController::class, 'downloadExport'])->name('export.download');
    });

// Backend JSON API cho Tabulator
Route::prefix('backend/api/leads')
    ->middleware(['web', 'auth', 'can:lead.view'])
    ->name('backend.api.leads.')
    ->group(function () {
        Route::get('/',          [LeadApiController::class, 'index'])   ->name('index');
        Route::get('/stats',     [LeadApiController::class, 'stats'])   ->name('stats');
        Route::get('/pipeline',  [LeadApiController::class, 'pipeline'])->name('pipeline');
        Route::get('/stages',    [LeadApiController::class, 'stages'])  ->name('stages');
        Route::get('/provinces', [LeadApiController::class, 'provinces'])->name('provinces');
        // Trả về danh sách tỉnh/TP có Lead để dùng trong filter dropdown
    });
```

---

## 9. Views — Admin UI

### 9.1 `leads/index.blade.php`

Hai view mode toggle bằng Alpine.js:

**List view** — Tabulator với columns:
- Tên người liên hệ (click → show)
- Công ty
- Tình trạng (badge màu theo `stage.color`)
- Giá trị dự kiến (format VND)
- Ngày chốt dự kiến (đỏ nếu quá hạn)
- Lead Score (mini progress bar)
- Nguồn (badge)
- Người phụ trách
- Activity gần nhất (relative time)

**Pipeline (Kanban)** — cột per stage:
```html
<div x-data="leadPipeline()" x-init="init()">
    <div class="pipeline-wrap">
        <template x-for="stage in stages" :key="stage.id">
            <div class="stage-col">
                <div class="stage-hdr" :class="`bdr-${stage.color}`">
                    <span x-text="stage.label"></span>
                    <span class="count" x-text="stage.count"></span>
                    <span class="value" x-text="formatVND(stage.total_value)"></span>
                </div>
                <template x-for="lead in leadsByStage[stage.id] ?? []" :key="lead.id">
                    <div class="lead-card" @click="goto(lead.id)"
                         :class="{'card-overdue': isOverdue(lead), 'card-hot': lead.lead_score >= 70}">
                        <div class="name" x-text="lead.contact_name"></div>
                        <div class="company" x-text="lead.contact_company ?? '—'"></div>
                        <div class="row-meta">
                            <span class="score" x-text="lead.lead_score + 'đ'"></span>
                            <span x-show="lead.expected_value" class="value"
                                  x-text="formatVND(lead.expected_value)"></span>
                        </div>
                        <div class="close-date" x-show="lead.expected_close_date"
                             :class="{'overdue': isOverdue(lead)}"
                             x-text="formatDate(lead.expected_close_date)"></div>
                        <div class="assignee" x-text="lead.assignee?.name ?? 'Chưa phân công'"></div>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
```

### 9.2 `leads/show.blade.php`

Layout 2 cột (2/3 + 1/3):

**Cột trái — Timeline & Form**:
- **Stage progress bar**: các stage hiển thị ngang, stage hiện tại highlight theo màu, click để chuyển stage
- **Mô tả cơ hội**: text area inline-edit
- **Form thêm activity nhanh**: loại (icon button: call/email/meeting/task) → title → outcome → lưu
- **Timeline activities**: group theo ngày, icon theo type, outcome badge

**Cột phải — Sidebar thông tin**:

```
┌─────────────────────────────┐
│  Lead Score: 74/100  ████░  │
│  [Giá trị dự kiến: 45tr VND]│
│  [Ngày chốt: 30/06/2026]    │  ← đỏ nếu quá hạn
│  [Người phụ trách: Minh T.] │  ← dropdown assign
└─────────────────────────────┘
┌─────────────────────────────┐
│  THÔNG TIN LIÊN HỆ          │
│  👤 Nguyễn Thị Linh          │
│  💼 Marketing Director      │
│  🏢 TechCorp VN              │
│  📧 linh@techcorp.vn         │
│  📞 0912 345 678             │
│  🌐 techcorp.vn              │
│  📍 Q.1, TP.HCM              │
│  [Sửa thông tin liên hệ]    │
└─────────────────────────────┘
┌─────────────────────────────┐
│  NGUỒN CƠ HỘI               │
│  Survey · AI_READY · 68.4%  │
│  [Xem kết quả Survey ↗]     │
└─────────────────────────────┘
┌─────────────────────────────┐
│  LỊCH SỬ TÌNH TRẠNG         │
│  [Kanban mini timeline]     │
└─────────────────────────────┘
┌─────────────────────────────┐
│  GHI CHÚ (pinnable)         │
│  [+ Thêm ghi chú]           │
└─────────────────────────────┘
```

### 9.3 `leads/create.blade.php`

Form 3 bước (Alpine.js wizard):

**Bước 1 — Thông tin người liên hệ**:
- Tên người liên hệ *(bắt buộc)*
- Email người liên hệ
- Điện thoại · Điện thoại phụ
- Công ty · Chức vụ · Website
- Địa chỉ · Tỉnh/TP (dropdown) · Quận/Huyện (cascade) · Phường/Xã (cascade)

**Bước 2 — Thông tin cơ hội**:
- Tình trạng cơ hội (stage dropdown)
- Nguồn cơ hội · Chi tiết nguồn
- Người phụ trách (user dropdown)
- Giá trị dự kiến · Đơn vị tiền tệ
- Ngày chốt dự kiến
- Mô tả

**Bước 3 — Xác nhận & lưu**

---

## 10. Tích hợp Workflow Automation

### 10.1 `LeadServiceProvider`

```php
public function boot(): void
{
    // Triggers
    if (app()->bound(TriggerRegistry::class)) {
        $reg = app(TriggerRegistry::class);
        $reg->register(new LeadCreatedTrigger());
        $reg->register(new LeadStageChangedTrigger());
        $reg->register(new LeadScoreUpdatedTrigger());
        $reg->register(new LeadAssignedTrigger());
    }

    // Executors
    if (app()->bound(ActionRegistry::class)) {
        $reg = app(ActionRegistry::class);
        $reg->register(new CreateLeadExecutor());
        $reg->register(new UpdateLeadStageExecutor());
        $reg->register(new AssignLeadExecutor());
    }

    // Subject Registry
    if (app()->bound(SubjectRegistry::class)) {
        app(SubjectRegistry::class)->register(
            type:            'Lead',
            fqcn:            Lead::class,
            label:           'Cơ hội (Lead)',
            updatableFields: Lead::workflowUpdatableFields(),
        );
    }

    // Observers
    Lead::observe(LeadObserver::class);

    \Cache::forget('wf:meta');
}
```

### 10.2 `LeadCreatedTrigger`

```php
class LeadCreatedTrigger implements TriggerSource
{
    public function type(): string   { return 'lead.created'; }
    public function label(): string  { return 'Lead cơ hội mới được tạo'; }
    public function module(): string { return 'Lead'; }

    public function availableFields(): array
    {
        return [
            ['key' => 'extra.source',           'label' => 'Nguồn',              'type' => 'integer'],
            ['key' => 'extra.org_id',            'label' => 'Org ID',             'type' => 'integer'],
            ['key' => 'extra.survey_band_code',  'label' => 'Band Survey',        'type' => 'string'],
            ['key' => 'extra.survey_score',      'label' => 'Score Survey %',     'type' => 'decimal'],
            ['key' => 'extra.expected_value',    'label' => 'Giá trị dự kiến',   'type' => 'decimal'],
            ['key' => 'extra.province_code',     'label' => 'Mã Tỉnh/TP',        'type' => 'string'],
            ['key' => 'actor.email',             'label' => 'Email liên hệ',     'type' => 'string'],
        ];
    }

    public function configFields(): array
    {
        return [
            ['key' => 'org_id', 'label' => 'Chỉ áp dụng cho Org',
             'type' => 'model_select', 'model' => 'organizations', 'required' => false],
            ['key' => 'source', 'label' => 'Nguồn',
             'type' => 'select', 'required' => false,
             'options' => collect(LeadSource::cases())->map(fn($s) =>
                 ['value' => $s->value, 'label' => $s->label()])->all()],
        ];
    }

    public function matches(TriggerPayload $payload, array $config): bool
    {
        if (!empty($config['org_id']) && (int)$config['org_id'] !== ($payload->extra['org_id'] ?? null)) {
            return false;
        }
        if (!empty($config['source']) && (int)$config['source'] !== ($payload->extra['source'] ?? null)) {
            return false;
        }
        return true;
    }
}
```

### 10.3 `CreateLeadExecutor`

```php
class CreateLeadExecutor implements ActionExecutor
{
    public function type(): string   { return 'lead.create'; }
    public function label(): string  { return 'Tạo cơ hội Lead mới'; }
    public function module(): string { return 'Lead'; }

    public function stepConfigFields(): array
    {
        return [
            ['key' => 'lead_assigned_to', 'label' => 'Người phụ trách',
             'type' => 'user_select', 'required' => false],
            ['key' => 'lead_source',      'label' => 'Nguồn',
             'type' => 'select', 'required' => false,
             'options' => collect(LeadSource::cases())->map(fn($s) =>
                 ['value' => $s->value, 'label' => $s->label()])->all()],
        ];
    }

    public function execute(WorkflowStep $step, TriggerPayload $payload): ActionResult
    {
        $start = microtime(true);
        try {
            if (empty($payload->actorEmail) && empty($payload->actorName)) {
                return ActionResult::fail('Không đủ thông tin liên hệ để tạo Lead');
            }

            $contactData = new ContactData(
                fullName:     $payload->actorName ?? $payload->actorEmail ?? 'Unknown',
                email:        $payload->actorEmail,
                phone:        null,
                phoneAlt:     null,
                company:      null,
                jobTitle:     null,
                website:      null,
                address:      null,
                wardCode:     null, wardName:     null,
                districtCode: null, districtName: null,
                provinceCode: null, provinceName: null,
            );

            $data = new LeadData(
                contactId:         null,
                contact:           $contactData,
                stageId:           0, // default stage
                source:            LeadSource::from($step->lead_source ?? LeadSource::Workflow->value),
                sourceDetail:      "Workflow: {$step->workflow->name}",
                assignedTo:        $step->lead_assigned_to,
                expectedValue:     null,
                currency:          'VND',
                expectedCloseDate: null,
                description:       null,
                orgId:             null,
                surveyResponseId:  $payload->subjectType === 'SurveyResponse' ? $payload->subjectId : null,
                surveyBandCode:    $payload->extra['band_code']     ?? null,
                surveyScore:       $payload->extra['overall_score'] ?? null,
            );

            $lead = CreateLeadAction::run($data);
            $ms   = (int)((microtime(true) - $start) * 1000);

            return ActionResult::ok($ms, ['lead_id' => $lead->id]);
        } catch (\Throwable $e) {
            return ActionResult::fail(
                $e->getMessage(),
                (int)((microtime(true) - $start) * 1000)
            );
        }
    }
}
```

---

## 11. Tích hợp Survey

Survey không import Lead. Kết nối qua Workflow Engine (xem workflow_automation_spec_v2.md). Tuy nhiên trang `leads/show.blade.php` hiển thị link xem Survey result nếu Lead có `survey_response_id`:

```php
// LeadController::show()
$surveyResultUrl = null;
if ($lead->survey_response_id && \Route::has('backend.surveys.responses.show')) {
    $surveyResultUrl = route('backend.surveys.responses.show', $lead->survey_response_id);
}
```

---

## 12. Tích hợp ActivityLog

```php
// LeadObserver — ghi log CRUD tự động
class LeadObserver extends BaseModelObserver
{
    protected function module(): string       { return 'Lead'; }
    protected function resourceCode(): string { return 'lead'; }

    protected function updatedContext(\Illuminate\Database\Eloquent\Model $m): array
    {
        return [
            'changed_fields' => implode(',', array_keys($m->getChanges())),
            'stage_before'   => $m->getOriginal('stage_id'),
            'stage_after'    => $m->stage_id,
            'value_before'   => $m->getOriginal('expected_value'),
            'value_after'    => $m->expected_value,
        ];
    }
}
```

---

## 13. Permissions

```php
$permissions = [
    'lead.view',            // xem danh sách + chi tiết
    'lead.create',          // tạo Lead thủ công
    'lead.update',          // sửa thông tin, đổi stage
    'lead.delete',          // soft delete
    'lead.assign',          // phân công người phụ trách
    'lead.export',          // xuất Excel
    'lead.view_all',        // xem tất cả lead (không chỉ lead được assign)
    'lead.manage_pipeline', // thêm/sửa/xóa pipeline stages
];
```

---

## 14. Config

```php
// Modules/Lead/config/lead.php
return [
    'queue'                     => env('LEAD_QUEUE', 'default'),
    'stale_days'                => env('LEAD_STALE_DAYS', 14),
    'hot_score_threshold'       => env('LEAD_HOT_SCORE', 70),
    'default_currency'          => env('LEAD_DEFAULT_CURRENCY', 'VND'),
    'stage_history_retain_days' => env('LEAD_STAGE_HISTORY_DAYS', 365),
    'default_assignee_id'       => env('LEAD_DEFAULT_ASSIGNEE', null),
];
```

---

## 15. Migrations hoàn chỉnh

```
Modules/Lead/database/migrations/
├── 2026_01_01_000001_create_organizations_table.php
│   -- Bỏ qua nếu đã có bảng organizations trong hệ thống
│
├── 2026_01_01_000002_create_lead_pipeline_stages_table.php
│   -- Chạy trước leads (leads.stage_id tham chiếu bảng này)
│
├── 2026_01_01_000003_create_lead_contacts_table.php
│   -- Chạy trước leads (leads.contact_id tham chiếu bảng này)
│
├── 2026_01_01_000004_create_leads_table.php
│   -- Bảng chính
│
├── 2026_01_01_000005_create_lead_activities_table.php
├── 2026_01_01_000006_create_lead_notes_table.php
├── 2026_01_01_000007_create_lead_meta_table.php
└── 2026_01_01_000008_create_lead_stage_history_table.php
```

**Thứ tự migration**: `organizations` → `lead_pipeline_stages` → `lead_contacts` → `leads` → bảng con.

---

## 16. Seeders

```php
// LeadDatabaseSeeder
class LeadDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LeadPipelineStagesSeeder::class,
            LeadPermissionsSeeder::class,
        ]);
    }
}

// LeadPipelineStagesSeeder — pipeline mặc định (global, org_id = NULL)
class LeadPipelineStagesSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['code' => 'new',         'label' => 'Mới',                'color' => 'gray',   'sort_order' => 1, 'probability' => 10,  'is_won' => 0, 'is_lost' => 0],
            ['code' => 'contacted',   'label' => 'Đã liên hệ',         'color' => 'blue',   'sort_order' => 2, 'probability' => 20,  'is_won' => 0, 'is_lost' => 0],
            ['code' => 'qualified',   'label' => 'Đủ điều kiện',       'color' => 'teal',   'sort_order' => 3, 'probability' => 40,  'is_won' => 0, 'is_lost' => 0],
            ['code' => 'proposal',    'label' => 'Đã gửi đề xuất',     'color' => 'purple', 'sort_order' => 4, 'probability' => 60,  'is_won' => 0, 'is_lost' => 0],
            ['code' => 'negotiation', 'label' => 'Đang đàm phán',      'color' => 'amber',  'sort_order' => 5, 'probability' => 80,  'is_won' => 0, 'is_lost' => 0],
            ['code' => 'won',         'label' => 'Thành công',         'color' => 'green',  'sort_order' => 6, 'probability' => 100, 'is_won' => 1, 'is_lost' => 0],
            ['code' => 'lost',        'label' => 'Thất bại',           'color' => 'red',    'sort_order' => 7, 'probability' => 0,   'is_won' => 0, 'is_lost' => 1],
            ['code' => 'unqualified', 'label' => 'Không phù hợp',      'color' => 'gray',   'sort_order' => 8, 'probability' => 0,   'is_won' => 0, 'is_lost' => 1],
        ];

        foreach ($stages as $stage) {
            LeadPipelineStage::firstOrCreate(
                ['code' => $stage['code'], 'org_id' => null],
                array_merge($stage, ['org_id' => null, 'is_active' => 1])
            );
        }
    }
}
```

---

## 17. Thứ tự triển khai

| # | Hạng mục | Effort | Ghi chú |
|---|----------|--------|---------|
| 1 | Migration `organizations` (nếu chưa có) | Thấp | |
| 2 | Migration `lead_pipeline_stages` | Thấp | Phải chạy trước leads |
| 3 | Migration `lead_contacts` | Thấp | Phải chạy trước leads |
| 4 | Migration `leads` + 4 bảng con | Thấp | |
| 5 | Enums: `LeadSource`, `LeadStatus`, `LeadActivityType` | Thấp | |
| 6 | DTOs: `ContactData`, `LeadData`, `LeadFilterData`, `LeadActivityData` | Thấp | spatie/laravel-data |
| 7 | Models: `Lead`, `LeadContact`, `LeadPipelineStage`, `LeadActivity`, `LeadNote`, `LeadMeta`, `LeadStageHistory` | Trung | |
| 8 | Seeders: pipeline stages + permissions | Thấp | |
| 9 | `CreateLeadAction` — idempotent, tạo Contact đồng thời | Trung | Core nhất |
| 10 | `UpdateLeadAction` + `ChangeLeadStageAction` | Trung | |
| 11 | `AssignLeadAction` + `LogLeadActivityAction` | Thấp | |
| 12 | `ScoreLeadAction` (async job) | Trung | |
| 13 | **Workflow integration** — `LeadServiceProvider` đăng ký đầy đủ | Thấp | Quick win |
| 14 | `CreateLeadExecutor` — Workflow → tạo Lead từ Survey | Thấp | Use case chính |
| 15 | `LeadQueryService` + `LeadStatsService` | Trung | |
| 16 | `LeadApiController` (index + stats + pipeline) | Trung | |
| 17 | `LeadObserver` + ActivityLog integration | Thấp | |
| 18 | View `leads/index.blade.php` — List view | Trung | Tabulator |
| 19 | View `leads/index.blade.php` — Pipeline (Kanban) | Trung | Alpine.js |
| 20 | View `leads/create.blade.php` — wizard 3 bước + địa chỉ cascade | Cao | Form phức tạp nhất |
| 21 | View `leads/show.blade.php` — 2 cột đầy đủ | Cao | |
| 22 | `ExportLeadsAction` (FastExcel) | Thấp | |
| 23 | `LeadActivityController` + `LeadNoteController` | Thấp | |