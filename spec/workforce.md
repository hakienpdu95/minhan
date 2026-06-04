# Đặc Tả Module: Workforce Center

> **Hệ thống:** SaaS SME
> **Module:** Workforce Center
> **Phiên bản:** 3.0.0
> **Ngày:** 2026-06-03
> **Stack:** Laravel 11 · MySQL 8+ / PostgreSQL 15+

---

## Mục lục

1. [Tổng quan](#1-tổng-quan)
2. [Phạm vi](#2-phạm-vi)
3. [Enum Values](#3-enum-values)
4. [ERD](#4-erd)
5. [Đặc tả bảng dữ liệu](#5-đặc-tả-bảng-dữ-liệu)
6. [Luồng nghiệp vụ](#6-luồng-nghiệp-vụ)
7. [Query Patterns](#7-query-patterns)
8. [API Endpoints](#8-api-endpoints)
9. [Business Rules](#9-business-rules)
10. [Indexes & Caching](#10-indexes--caching)
11. [Lộ trình triển khai](#11-lộ-trình-triển-khai)

---

## 1. Tổng quan

**Workforce Center** quản lý nhân sự trong doanh nghiệp SME gồm 3 sub-module chính, tích hợp với bảng `departments` và `users` sẵn có của hệ thống.

| Sub-module | Chức năng cốt lõi |
|---|---|
| **Quản lý nhân viên** | Hồ sơ, vị trí, hợp đồng, lịch sử thay đổi |
| **Quản lý nghỉ phép** | Policy, balance, đơn xin nghỉ, duyệt |
| **Quản lý mục tiêu KPI** | Thiết lập mục tiêu, đo lường tự động từ Project module, tính điểm |

### Người dùng

| Vai trò | Quyền |
|---|---|
| **HR Admin** | Toàn quyền 3 sub-module |
| **Manager** | Xem direct reports; duyệt nghỉ phép; đặt KPI cho nhân viên trong team |
| **Employee** | Xem hồ sơ bản thân; đăng ký nghỉ; xem tiến độ KPI của mình |

---

## 2. Phạm vi

### Trong phạm vi

- Hồ sơ nhân viên: thông tin cơ bản, hợp đồng, lịch sử thay đổi vị trí/lương (immutable)
- Vị trí/chức danh với salary band, liên kết phòng ban sẵn có
- Chính sách nghỉ phép theo org hoặc override theo vị trí
- Balance nghỉ phép: entitled, used, pending, carried-over
- Luồng duyệt đơn nghỉ theo manager chain
- Mục tiêu KPI cá nhân: manual hoặc tự động từ `KPI_METRIC` / task của Project module
- Điểm KPI tổng hợp theo trọng số, snapshot bất biến cuối kỳ

### Ngoài phạm vi

- Payroll & bảng lương
- Tuyển dụng
- Chấm công / timekeeping
- Competency framework & performance review cycle
- Learning & Development

---

## 3. Enum Values

### WK_EMPLOYEE

| Trường | Giá trị |
|---|---|
| `employment_type` | `full_time` \| `part_time` \| `contractor` \| `intern` \| `probation` |
| `status` | `active` \| `probation` \| `on_leave` \| `resigned` \| `terminated` |
| `gender` | `male` \| `female` \| `other` |

### WK_POSITION_HISTORY

| Trường | Giá trị |
|---|---|
| `change_type` | `hire` \| `promotion` \| `transfer` \| `demotion` \| `salary_change` \| `separation` |

### WK_LEAVE_POLICY

| Trường | Giá trị |
|---|---|
| `leave_type` | `annual` \| `sick` \| `maternity` \| `paternity` \| `unpaid` \| `compensatory` \| `bereavement` \| `other` |

### WK_TIMEOFF

| Trường | Giá trị |
|---|---|
| `status` | `pending` \| `approved` \| `rejected` \| `cancelled` |

### WK_KPI_GOAL

| Trường | Giá trị |
|---|---|
| `goal_type` | `manual` \| `linked_kpi` \| `linked_tasks` |
| `status` | `draft` \| `active` \| `completed` \| `cancelled` |
| `aggregation_type` | `latest` \| `sum` \| `avg` \| `percentage` |
| `direction` | `higher_better` \| `lower_better` |

---

## 4. ERD

```
[users] ──1:1──► WK_EMPLOYEE
                      │
          ┌───────────┼────────────────────┐
          │           │                    │
         1:N         1:N                  1:N
          │           │                    │
          ▼           ▼                    ▼
  WK_POSITION_   WK_TIMEOFF          WK_KPI_GOAL
  HISTORY        └─FK─► WK_LEAVE_    └─FK─► WK_KPI_SOURCE
                        BALANCE      └─FK─► WK_KPI_SNAPSHOT
                        └─FK─► WK_LEAVE_POLICY


[departments] (existing)
      │
     1:N
      ▼
WK_POSITION
      │
     1:N
      ▼
WK_EMPLOYEE


Tham chiếu cross-module (không FK cứng, dùng polymorphic):
WK_KPI_SOURCE.source_id → kpi_metrics.id  (Project module)
WK_KPI_SOURCE.source_id → projects.id     (Project module)
```

### Quan hệ tổng hợp

| Bảng A | Quan hệ | Bảng B | Ghi chú |
|---|---|---|---|
| WK_EMPLOYEE | 1:1 | users | user_id — tài khoản |
| WK_EMPLOYEE | N:1 | WK_POSITION | Vị trí hiện tại |
| WK_EMPLOYEE | N:1 | departments | Phòng ban (bảng sẵn có) |
| WK_EMPLOYEE | N:1 | WK_EMPLOYEE (self) | manager_id |
| WK_EMPLOYEE | 1:N | WK_POSITION_HISTORY | Audit trail — immutable |
| WK_EMPLOYEE | 1:N | WK_TIMEOFF | Đơn nghỉ phép |
| WK_EMPLOYEE | 1:N | WK_KPI_GOAL | Mục tiêu KPI |
| WK_POSITION | 1:N | WK_LEAVE_POLICY | Override policy theo vị trí |
| WK_LEAVE_POLICY | 1:N | WK_LEAVE_BALANCE | Balance per nhân viên/năm |
| WK_LEAVE_BALANCE | 1:N | WK_TIMEOFF | Đơn dùng balance này |
| WK_KPI_GOAL | 1:1 | WK_KPI_SOURCE | Cấu hình nguồn đo tự động |
| WK_KPI_GOAL | 1:N | WK_KPI_SNAPSHOT | Snapshot bất biến cuối kỳ |

---

## 5. Đặc tả bảng dữ liệu

### 5.1 WK_POSITION — Vị trí / Chức danh

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `org_id` | UUID | NOT NULL | FK, INDEX | | FK → organizations.id |
| `department_id` | UUID | NULL | FK | NULL | FK → departments.id, NULL = cross-dept |
| `name` | VARCHAR(150) | NOT NULL | | | "Senior Software Engineer" |
| `code` | VARCHAR(30) | NOT NULL | UNIQUE(org_id) | | SSE-L4 |
| `level` | SMALLINT | NOT NULL | | 1 | 1 (junior) → 5 (director+) |
| `salary_min` | DECIMAL(15,2) | NULL | | NULL | |
| `salary_max` | DECIMAL(15,2) | NULL | | NULL | |
| `salary_currency` | CHAR(3) | NOT NULL | | `VND` | |
| `is_manager_role` | BOOLEAN | NOT NULL | | FALSE | |
| `is_active` | BOOLEAN | NOT NULL | | TRUE | |
| `created_by` | UUID | NOT NULL | FK | | |
| `created_at` | TIMESTAMP | NOT NULL | | NOW() | |
| `updated_at` | TIMESTAMP | NOT NULL | | NOW() | |

```sql
CREATE UNIQUE INDEX idx_wk_pos_code ON wk_positions(org_id, code);
CREATE INDEX idx_wk_pos_dept       ON wk_positions(department_id, is_active);
```

---

### 5.2 WK_EMPLOYEE — Hồ sơ nhân viên

Bảng trung tâm. FK sang `users` (auth), `departments` (existing), `wk_positions`.
Thông tin nhạy cảm (lương, bank) chỉ HR Admin truy cập được — kiểm soát bằng policy.

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `user_id` | UUID | NOT NULL | FK, UNIQUE | | FK → users.id |
| `org_id` | UUID | NOT NULL | FK, INDEX | | |
| `position_id` | UUID | NOT NULL | FK | | Vị trí hiện tại |
| `department_id` | UUID | NOT NULL | FK | | FK → departments.id |
| `manager_id` | UUID | NULL | FK self | NULL | Manager trực tiếp |
| `employee_code` | VARCHAR(30) | NOT NULL | UNIQUE(org_id) | | NV-001 — bất biến sau tạo |
| `full_name` | VARCHAR(150) | NOT NULL | | | Denormalized từ users |
| `date_of_birth` | DATE | NULL | | NULL | |
| `gender` | ENUM | NULL | | NULL | |
| `phone` | VARCHAR(20) | NULL | | NULL | |
| `personal_email` | VARCHAR(150) | NULL | | NULL | |
| `address` | TEXT | NULL | | NULL | |
| `national_id` | VARCHAR(20) | NULL | | NULL | CMND/CCCD |
| `national_id_issued` | DATE | NULL | | NULL | |
| `tax_code` | VARCHAR(20) | NULL | | NULL | |
| `bank_account` | VARCHAR(30) | NULL | | NULL | |
| `bank_name` | VARCHAR(100) | NULL | | NULL | |
| `join_date` | DATE | NOT NULL | INDEX | | |
| `probation_end_date` | DATE | NULL | | NULL | |
| `employment_type` | ENUM | NOT NULL | INDEX | `probation` | |
| `status` | ENUM | NOT NULL | INDEX | `probation` | |
| `contract_start` | DATE | NULL | | NULL | |
| `contract_end` | DATE | NULL | INDEX | NULL | NULL = không thời hạn |
| `salary_base` | DECIMAL(15,2) | NULL | | NULL | |
| `salary_currency` | CHAR(3) | NOT NULL | | `VND` | |
| `work_location` | VARCHAR(50) | NULL | | NULL | office \| remote \| hybrid |
| `avatar_url` | TEXT | NULL | | NULL | |
| `emergency_contact_name` | VARCHAR(150) | NULL | | NULL | |
| `emergency_contact_phone` | VARCHAR(20) | NULL | | NULL | |
| `resigned_at` | DATE | NULL | | NULL | |
| `resignation_reason` | TEXT | NULL | | NULL | |
| `notes` | TEXT | NULL | | NULL | Ghi chú nội bộ HR |
| `created_by` | UUID | NOT NULL | FK | | |
| `updated_by` | UUID | NULL | FK | NULL | |
| `created_at` | TIMESTAMP | NOT NULL | | NOW() | |
| `updated_at` | TIMESTAMP | NOT NULL | | NOW() | |

```sql
CREATE UNIQUE INDEX idx_wk_emp_user       ON wk_employees(user_id);
CREATE UNIQUE INDEX idx_wk_emp_code       ON wk_employees(org_id, employee_code);
CREATE INDEX idx_wk_emp_dept              ON wk_employees(department_id, status);
CREATE INDEX idx_wk_emp_manager           ON wk_employees(manager_id);
CREATE INDEX idx_wk_emp_position          ON wk_employees(position_id, status);
CREATE INDEX idx_wk_emp_contract_alert    ON wk_employees(org_id, contract_end, status)
  WHERE contract_end IS NOT NULL AND status = 'active';
CREATE FULLTEXT INDEX idx_wk_emp_search   ON wk_employees(full_name, employee_code);
```

---

### 5.3 WK_POSITION_HISTORY — Lịch sử vị trí (immutable)

**Chỉ INSERT — không UPDATE, không DELETE.** Mọi thay đổi về vị trí, phòng ban, lương đều tạo row mới.

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `employee_id` | UUID | NOT NULL | FK, INDEX | | |
| `position_id` | UUID | NOT NULL | FK | | Snapshot vị trí |
| `department_id` | UUID | NOT NULL | FK | | Snapshot phòng ban |
| `manager_id` | UUID | NULL | FK | NULL | Snapshot manager |
| `change_type` | ENUM | NOT NULL | | | hire \| promotion \| transfer \| demotion \| salary_change \| separation |
| `effective_date` | DATE | NOT NULL | INDEX | | |
| `salary_base` | DECIMAL(15,2) | NULL | | NULL | Snapshot lương |
| `change_reason` | TEXT | NULL | | NULL | |
| `changed_by` | UUID | NOT NULL | FK | | |
| `created_at` | TIMESTAMP | NOT NULL | | NOW() | |

```sql
CREATE INDEX idx_wk_hist_emp ON wk_position_histories(employee_id, effective_date DESC);
```

---

### 5.4 WK_LEAVE_POLICY — Chính sách nghỉ phép

Override theo vị trí hoặc phòng ban. Mức ưu tiên: `position_id` > `department_id` > org-level.

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `org_id` | UUID | NOT NULL | FK, INDEX | | |
| `leave_type` | ENUM | NOT NULL | | | Xem mục 3 |
| `name` | VARCHAR(100) | NOT NULL | | | "Nghỉ phép năm 2024" |
| `days_per_year` | DECIMAL(5,1) | NOT NULL | | | Số ngày/năm |
| `carry_over_days` | DECIMAL(5,1) | NOT NULL | | 0 | Ngày chuyển sang năm sau |
| `min_advance_days` | SMALLINT | NOT NULL | | 1 | Phải đặt trước tối thiểu N ngày |
| `max_consecutive_days` | SMALLINT | NULL | | NULL | |
| `requires_approval` | BOOLEAN | NOT NULL | | TRUE | |
| `position_id` | UUID | NULL | FK | NULL | Override vị trí cụ thể |
| `department_id` | UUID | NULL | FK | NULL | Override phòng ban cụ thể |
| `effective_from` | DATE | NOT NULL | | | |
| `is_active` | BOOLEAN | NOT NULL | | TRUE | |

```sql
CREATE INDEX idx_wk_policy_org ON wk_leave_policies(org_id, leave_type, is_active);
```

---

### 5.5 WK_LEAVE_BALANCE — Số dư nghỉ phép

Một row per nhân viên / policy / năm. Cập nhật atomic trong cùng transaction với WK_TIMEOFF — không tính lại từ đầu mỗi query.

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `employee_id` | UUID | NOT NULL | FK, INDEX | | |
| `policy_id` | UUID | NOT NULL | FK | | |
| `leave_type` | ENUM | NOT NULL | | | Denormalized — tránh JOIN khi query balance |
| `year` | SMALLINT | NOT NULL | | | |
| `entitled_days` | DECIMAL(5,1) | NOT NULL | | | Ngày được phép trong năm |
| `used_days` | DECIMAL(5,1) | NOT NULL | | 0 | Đã dùng (approved) |
| `pending_days` | DECIMAL(5,1) | NOT NULL | | 0 | Đang chờ duyệt (tạm giữ) |
| `carried_over` | DECIMAL(5,1) | NOT NULL | | 0 | Chuyển từ năm trước |
| `adjusted` | DECIMAL(5,1) | NOT NULL | | 0 | Điều chỉnh thủ công bởi HR |
| `updated_at` | TIMESTAMP | NOT NULL | | NOW() | |

**Công thức:** `remaining = entitled_days + carried_over + adjusted − used_days − pending_days`

```sql
CREATE UNIQUE INDEX idx_wk_balance_unique ON wk_leave_balances(employee_id, policy_id, year);
CREATE INDEX idx_wk_balance_emp           ON wk_leave_balances(employee_id, year, leave_type);
```

---

### 5.6 WK_TIMEOFF — Đơn xin nghỉ phép

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `employee_id` | UUID | NOT NULL | FK, INDEX | | |
| `balance_id` | UUID | NOT NULL | FK | | FK → WK_LEAVE_BALANCE — khóa balance khi tạo đơn |
| `leave_type` | ENUM | NOT NULL | | | Denormalized từ balance |
| `date_from` | DATE | NOT NULL | INDEX | | |
| `date_to` | DATE | NOT NULL | | | |
| `days_count` | DECIMAL(5,1) | NOT NULL | | | Tính server-side (trừ weekend / holiday) |
| `status` | ENUM | NOT NULL | INDEX | `pending` | |
| `reason` | TEXT | NULL | | NULL | |
| `attachment_url` | TEXT | NULL | | NULL | Giấy tờ đính kèm |
| `approved_by` | UUID | NULL | FK | NULL | |
| `approved_at` | TIMESTAMP | NULL | | NULL | |
| `rejected_reason` | TEXT | NULL | | NULL | |
| `created_at` | TIMESTAMP | NOT NULL | | NOW() | |

```sql
CREATE INDEX idx_wk_timeoff_emp     ON wk_timeoffs(employee_id, status, date_from);
CREATE INDEX idx_wk_timeoff_pending ON wk_timeoffs(approved_by, status)
  WHERE status = 'pending';
```

---

### 5.7 WK_KPI_GOAL — Mục tiêu KPI nhân viên

Hỗ trợ 2 loại: `manual` (tự nhập) và `linked_*` (tự động từ Project module).
Cây mục tiêu OKR qua `parent_goal_id`: Objective → Key Results.

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `employee_id` | UUID | NOT NULL | FK, INDEX | | |
| `cycle_label` | VARCHAR(30) | NOT NULL | INDEX | | "Q3-2024", "H1-2024", "2024" |
| `cycle_start` | DATE | NOT NULL | | | |
| `cycle_end` | DATE | NOT NULL | INDEX | | |
| `parent_goal_id` | UUID | NULL | FK self | NULL | Objective cha (OKR) |
| `title` | VARCHAR(300) | NOT NULL | | | |
| `description` | TEXT | NULL | | NULL | |
| `goal_type` | ENUM | NOT NULL | | `manual` | manual \| linked_kpi \| linked_tasks |
| `target_value` | DECIMAL(15,4) | NOT NULL | | | Giá trị đích |
| `current_value` | DECIMAL(15,4) | NOT NULL | | 0 | Tiến độ thực tế (cập nhật auto hoặc tay) |
| `unit` | VARCHAR(30) | NULL | | NULL | %, VND, tasks, điểm |
| `direction` | ENUM | NOT NULL | | `higher_better` | higher_better \| lower_better |
| `achievement_pct` | DECIMAL(6,2) | NOT NULL | | 0 | Tính tự động khi current_value thay đổi |
| `weight_percent` | SMALLINT | NOT NULL | | 10 | Trọng số (tổng = 100 per cycle per employee) |
| `status` | ENUM | NOT NULL | INDEX | `draft` | |
| `last_synced_at` | TIMESTAMP | NULL | | NULL | Lần sync gần nhất (nếu linked) |
| `approved_by` | UUID | NULL | FK | NULL | Manager duyệt mục tiêu |
| `approved_at` | TIMESTAMP | NULL | | NULL | |
| `created_by` | UUID | NOT NULL | FK | | |
| `created_at` | TIMESTAMP | NOT NULL | | NOW() | |
| `updated_at` | TIMESTAMP | NOT NULL | | NOW() | |

```sql
CREATE INDEX idx_wk_kpi_emp       ON wk_kpi_goals(employee_id, cycle_label, status);
CREATE INDEX idx_wk_kpi_cycle_end ON wk_kpi_goals(cycle_end, status)
  WHERE status = 'active';
```

---

### 5.8 WK_KPI_SOURCE — Cấu hình nguồn đo tự động

Chỉ tồn tại khi `goal_type IN ('linked_kpi', 'linked_tasks')`. 1 goal → 1 source.
Không dùng FK cứng sang Project module — dùng `source_type + source_id` (polymorphic) để tránh circular dependency giữa module.

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `goal_id` | UUID | NOT NULL | FK, UNIQUE | | FK → WK_KPI_GOAL.id |
| `source_type` | ENUM | NOT NULL | | | `kpi_metric` \| `project_tasks` |
| `source_id` | UUID | NOT NULL | INDEX | | ID của KPI_METRIC hoặc PROJECT |
| `aggregation_type` | ENUM | NOT NULL | | `latest` | latest \| sum \| avg \| percentage |
| `filter_assignee_only` | BOOLEAN | NOT NULL | | TRUE | Chỉ tính data của nhân viên này |
| `date_range_type` | ENUM | NOT NULL | | `cycle_period` | cycle_period \| rolling_30d \| rolling_90d |
| `multiplier` | DECIMAL(8,4) | NOT NULL | | 1.0 | Hệ số quy đổi đơn vị |
| `last_synced_at` | TIMESTAMP | NULL | | NULL | |

```sql
CREATE UNIQUE INDEX idx_wk_kpisrc_goal ON wk_kpi_sources(goal_id);
CREATE INDEX idx_wk_kpisrc_src         ON wk_kpi_sources(source_type, source_id);
```

**Enum `source_type`:**

| Giá trị | Source ID trỏ đến | Cách tính |
|---|---|---|
| `kpi_metric` | `kpi_metrics.id` | Lấy `actual_value` từ `kpi_entries` mới nhất trong kỳ |
| `project_tasks` | `projects.id` | `done_tasks / total_tasks × 100` — filter `assignee_id` nếu cần |

---

### 5.9 WK_KPI_SNAPSHOT — Điểm cuối kỳ (immutable)

Tạo khi HR/Manager chốt kỳ đánh giá. Sau khi INSERT không bao giờ UPDATE.

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `goal_id` | UUID | NOT NULL | FK, INDEX | | |
| `employee_id` | UUID | NOT NULL | FK, INDEX | | Denormalized để query nhanh |
| `cycle_label` | VARCHAR(30) | NOT NULL | | | Denormalized |
| `target_value` | DECIMAL(15,4) | NOT NULL | | | Freeze tại thời điểm chốt |
| `final_value` | DECIMAL(15,4) | NOT NULL | | | Freeze tại thời điểm chốt |
| `achievement_pct` | DECIMAL(6,2) | NOT NULL | | | |
| `weight_percent` | SMALLINT | NOT NULL | | | Freeze |
| `weighted_score` | DECIMAL(6,2) | NOT NULL | | | `achievement_pct × weight_percent / 100` |
| `kpi_total_score` | DECIMAL(6,2) | NULL | | NULL | Tổng weighted_score của tất cả goal trong cycle (chỉ set trên row tổng) |
| `snapped_by` | UUID | NOT NULL | FK | | |
| `snapped_at` | TIMESTAMP | NOT NULL | | NOW() | |

```sql
CREATE UNIQUE INDEX idx_wk_snap_goal   ON wk_kpi_snapshots(goal_id);
CREATE INDEX idx_wk_snap_emp_cycle     ON wk_kpi_snapshots(employee_id, cycle_label);
```

---

## 6. Luồng nghiệp vụ

### 6.1 Onboarding nhân viên

```
HR tạo WK_EMPLOYEE
  ├─ Set: position_id, department_id, manager_id, status='probation'
  ├─ INSERT WK_POSITION_HISTORY (change_type='hire', effective_date=join_date)
  └─ Khởi tạo WK_LEAVE_BALANCE cho năm hiện tại (per policy đang active)
```

### 6.2 Thay đổi vị trí / lương

```
HR thực hiện:
  ├─ UPDATE WK_EMPLOYEE: position_id / department_id / manager_id / salary_base
  └─ INSERT WK_POSITION_HISTORY (change_type phù hợp, effective_date)
       → Không UPDATE row cũ — immutable by design
```

### 6.3 Đơn nghỉ phép

```
Employee tạo WK_TIMEOFF
  ├─ Lookup WK_LEAVE_BALANCE: remaining = entitled + carried_over + adjusted − used − pending
  ├─ Kiểm tra remaining >= days_count (ngoại lệ: sick, unpaid không cần kiểm tra)
  ├─ INSERT WK_TIMEOFF (status='pending')
  ├─ UPDATE WK_LEAVE_BALANCE: pending_days += days_count   ← cùng transaction
  └─ Notify manager

Manager hành động:
  APPROVED → UPDATE timeoff.status = 'approved'
              UPDATE balance: pending_days −= N, used_days += N
  REJECTED → UPDATE timeoff.status = 'rejected'
              UPDATE balance: pending_days −= N  ← hoàn lại
```

### 6.4 KPI Goal — thiết lập

```
Manager / HR tạo WK_KPI_GOAL cho nhân viên
  ├─ Chọn goal_type:
  │   'manual'       → nhập target_value, không tạo WK_KPI_SOURCE
  │   'linked_kpi'   → chọn KPI_METRIC từ Project module
  │                    INSERT WK_KPI_SOURCE (source_type='kpi_metric', source_id=metric.id)
  │   'linked_tasks' → chọn PROJECT
  │                    INSERT WK_KPI_SOURCE (source_type='project_tasks', source_id=project.id)
  ├─ Đặt weight_percent (tổng tất cả goal trong cycle = 100)
  └─ Khi đủ điều kiện: Manager approve → status = 'active'
```

### 6.5 KPI Goal — đo lường tự động

```
Trigger: KPI_ENTRY được INSERT/UPDATE trong Project module
  └─ KpiEntryObserver::saved()
       └─ Tìm WK_KPI_SOURCE có source_type='kpi_metric', source_id=metric.id
            └─ Dispatch SyncGoalProgressJob (async queue)

SyncGoalProgressJob:
  1. Tính new_value theo aggregation_type + date_range_type:
       latest     → kpi_entries.actual_value mới nhất trong kỳ
       sum        → SUM(actual_value) trong kỳ
       avg        → AVG(actual_value) trong kỳ
       percentage → kpi_entries.achievement_pct mới nhất

  2. new_value = new_value × source.multiplier

  3. Tính achievement_pct:
       higher_better → min(new_value / target × 100, 150)   ← cap 150%
       lower_better  → max((2 − new_value/target) × 100, 0)

  4. UPDATE WK_KPI_GOAL:
       current_value   = new_value
       achievement_pct = achievement_pct
       last_synced_at  = NOW()

Trigger tương tự cho project_tasks:
  TaskObserver::statusChanged() khi task → 'done'
    → Dispatch SyncGoalProgressJob với source_type='project_tasks'
    → Job đếm: done_tasks / total_tasks × 100
```

### 6.6 KPI — chốt kỳ & tính điểm

```
Manager / HR chốt kỳ đánh giá
  ├─ Sync lần cuối tất cả linked goals của nhân viên
  ├─ INSERT WK_KPI_SNAPSHOT cho từng goal:
  │   target_value    = goal.target_value      (freeze)
  │   final_value     = goal.current_value     (freeze)
  │   achievement_pct = goal.achievement_pct   (freeze)
  │   weight_percent  = goal.weight_percent    (freeze)
  │   weighted_score  = achievement_pct × weight_percent / 100
  │
  ├─ Tính KPI tổng:
  │   kpi_raw   = Σ(weighted_score)            → thang 100
  │   kpi_score = kpi_raw / 100 × 5            → thang 5
  │
  └─ Cập nhật WK_KPI_GOAL.status = 'completed' cho tất cả goal trong cycle
```

**Ví dụ tính điểm:**

| Mục tiêu | Loại | Target | Actual | Achieve% | Weight | Đóng góp |
|---|---|---|---|---|---|---|
| Bug fix rate | linked_kpi | 90% | 94% | 104% | 30% | 31.2 |
| Task completion | linked_tasks | 85% | 78% | 92% | 25% | 23.0 |
| Doanh số tháng | manual | 50tr | 48tr | 96% | 30% | 28.8 |
| Tài liệu kỹ thuật | manual | 5 docs | 4 | 80% | 15% | 12.0 |
| **Tổng** | | | | | **100%** | **95.0** |

`kpi_score = 95.0 / 100 × 5 = 4.75 / 5.0`

### 6.7 Offboarding

```
HR offboard nhân viên
  ├─ UPDATE WK_EMPLOYEE: status = 'resigned'/'terminated', resigned_at
  ├─ INSERT WK_POSITION_HISTORY (change_type='separation')
  ├─ Reassign direct reports: manager_id → new_manager_id
  └─ Hủy WK_TIMEOFF đang 'pending' → hoàn balance
```

---

## 7. Query Patterns

### 7.1 Danh sách nhân viên với thông tin cơ bản (1 query)

```sql
SELECT
    e.id,
    e.employee_code,
    e.full_name,
    e.status,
    e.employment_type,
    e.join_date,
    e.work_location,
    p.name   AS position_name,
    p.level  AS position_level,
    d.name   AS department_name,
    m.full_name AS manager_name
FROM wk_employees e
JOIN wk_positions  p  ON p.id = e.position_id
JOIN departments   d  ON d.id = e.department_id
LEFT JOIN wk_employees m ON m.id = e.manager_id
WHERE e.org_id = :org_id
  AND e.status NOT IN ('resigned', 'terminated')
ORDER BY d.name, e.full_name;
```

### 7.2 Balance nghỉ phép nhân viên (không JOIN đơn)

```sql
SELECT
    lb.leave_type,
    lb.entitled_days,
    lb.carried_over,
    lb.adjusted,
    lb.used_days,
    lb.pending_days,
    (lb.entitled_days + lb.carried_over + lb.adjusted
     - lb.used_days - lb.pending_days) AS remaining_days
FROM wk_leave_balances lb
WHERE lb.employee_id = :employee_id
  AND lb.year = YEAR(NOW())
ORDER BY lb.leave_type;
```

### 7.3 Pending timeoff theo manager

```sql
SELECT
    t.id, t.leave_type, t.date_from, t.date_to, t.days_count,
    e.full_name AS employee_name,
    e.employee_code,
    d.name AS department_name
FROM wk_timeoffs t
JOIN wk_employees e ON e.id = t.employee_id
JOIN departments  d ON d.id = e.department_id
WHERE t.approved_by = :manager_employee_id
  AND t.status = 'pending'
ORDER BY t.date_from;
```

### 7.4 Dashboard KPI nhân viên — tiến độ realtime

```sql
SELECT
    g.id,
    g.title,
    g.goal_type,
    g.target_value,
    g.current_value,
    g.achievement_pct,
    g.weight_percent,
    g.status,
    g.last_synced_at,
    s.source_type,
    ROUND(g.achievement_pct * g.weight_percent / 100, 2) AS weighted_contribution
FROM wk_kpi_goals g
LEFT JOIN wk_kpi_sources s ON s.goal_id = g.id
WHERE g.employee_id  = :employee_id
  AND g.cycle_label  = :cycle_label
  AND g.status NOT IN ('draft', 'cancelled')
ORDER BY g.weight_percent DESC;
```

### 7.5 Điểm KPI tổng kết kỳ

```sql
SELECT
    e.full_name,
    e.employee_code,
    d.name AS department,
    SUM(snap.weighted_score)                          AS kpi_raw_score,
    ROUND(SUM(snap.weighted_score) / 100 * 5, 2)     AS kpi_score_5,
    COUNT(snap.goal_id)                               AS goal_count
FROM wk_kpi_snapshots snap
JOIN wk_employees e ON e.id = snap.employee_id
JOIN departments  d ON d.id = e.department_id
WHERE snap.cycle_label = :cycle_label
  AND e.org_id         = :org_id
GROUP BY snap.employee_id, e.full_name, e.employee_code, d.name
ORDER BY kpi_raw_score DESC;
```

### 7.6 Headcount theo phòng ban

```sql
SELECT
    d.name                                                       AS department,
    COUNT(e.id) FILTER (WHERE e.status = 'active')              AS active,
    COUNT(e.id) FILTER (WHERE e.status = 'probation')           AS probation,
    COUNT(e.id) FILTER (WHERE e.employment_type = 'contractor') AS contractors
FROM departments d
LEFT JOIN wk_employees e
       ON e.department_id = d.id
      AND e.org_id        = :org_id
      AND e.status NOT IN ('resigned', 'terminated')
WHERE d.org_id = :org_id
GROUP BY d.id, d.name
ORDER BY active DESC;
```

---

## 8. API Endpoints

### Nhân viên

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/workforce/employees` | Danh sách (filter: dept, status, type, search) |
| GET | `/api/workforce/employees/:id` | Hồ sơ đầy đủ |
| GET | `/api/workforce/employees/:id/history` | Lịch sử vị trí |
| POST | `/api/workforce/employees` | Tạo nhân viên |
| PUT | `/api/workforce/employees/:id` | Cập nhật hồ sơ |
| POST | `/api/workforce/employees/:id/transfer` | Thay đổi vị trí/phòng ban |
| POST | `/api/workforce/employees/:id/offboard` | Nghỉ việc |
| GET | `/api/workforce/positions` | Danh sách vị trí |
| POST | `/api/workforce/positions` | Tạo vị trí |
| PUT | `/api/workforce/positions/:id` | Cập nhật vị trí |

### Nghỉ phép

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/workforce/leave/policies` | Danh sách policy |
| POST | `/api/workforce/leave/policies` | Tạo policy |
| GET | `/api/workforce/leave/balance` | Balance của tôi |
| GET | `/api/workforce/employees/:id/leave/balance` | Balance nhân viên cụ thể (HR/Manager) |
| POST | `/api/workforce/leave/timeoffs` | Đăng ký nghỉ |
| GET | `/api/workforce/leave/timeoffs/pending` | Đơn chờ tôi duyệt |
| POST | `/api/workforce/leave/timeoffs/:id/approve` | Duyệt |
| POST | `/api/workforce/leave/timeoffs/:id/reject` | Từ chối |
| POST | `/api/workforce/leave/timeoffs/:id/cancel` | Tự hủy |

### KPI Goals

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/workforce/kpi/goals` | Mục tiêu của tôi theo cycle |
| GET | `/api/workforce/employees/:id/kpi/goals` | KPI nhân viên cụ thể (Manager/HR) |
| POST | `/api/workforce/kpi/goals` | Tạo mục tiêu |
| PUT | `/api/workforce/kpi/goals/:id` | Cập nhật (chỉ khi draft/active) |
| PATCH | `/api/workforce/kpi/goals/:id/progress` | Cập nhật current_value (manual) |
| POST | `/api/workforce/kpi/goals/:id/approve` | Manager duyệt mục tiêu |
| POST | `/api/workforce/kpi/goals/:id/sync` | Sync thủ công một goal linked |
| POST | `/api/workforce/kpi/cycles/:label/close` | Chốt kỳ — tạo snapshot |
| GET | `/api/workforce/kpi/snapshots` | Lịch sử điểm KPI các kỳ |
| GET | `/api/workforce/kpi/leaderboard` | Bảng xếp hạng KPI cycle (HR/Manager) |

---

## 9. Business Rules

### BR-WK-001: Immutable history
- Mọi thay đổi `position_id`, `department_id`, `manager_id`, `salary_base` → bắt buộc INSERT `WK_POSITION_HISTORY`
- Không UPDATE/DELETE bất kỳ row nào trong `WK_POSITION_HISTORY`
- `employee_code` bất biến sau khi tạo

### BR-WK-002: Manager chain
- Không tạo vòng tròn: A → B → C → A (kiểm tra CTE trước khi UPDATE)
- Manager phải có `is_manager_role = TRUE` hoặc HR override tường minh
- Trước khi offboard manager: bắt buộc reassign direct reports

### BR-WK-003: Leave balance — atomic
- INSERT WK_TIMEOFF và UPDATE WK_LEAVE_BALANCE phải trong cùng 1 transaction
- `days_count` tính server-side — không tin input client
- Không hủy đơn `approved` khi `date_from < NOW()`
- Không tạo đơn nếu `remaining < days_count` (ngoại lệ: `sick`, `unpaid`)

### BR-WK-004: KPI weight
- Tổng `weight_percent` của tất cả goal `status IN ('active', 'completed')` trong cùng cycle/employee = 100
- Chỉ áp dụng constraint khi approve goal hoặc close cycle — không chặn khi đang draft
- Goal `draft` và `cancelled` không tính vào tổng weight

### BR-WK-005: KPI snapshot — immutable
- Snapshot chỉ tạo khi cycle close — không tạo trước
- Sau khi INSERT snapshot: không UPDATE, không DELETE
- Achievement cap: `higher_better` max 150%, `lower_better` min 0%

### BR-WK-006: KPI source — no hard FK
- `WK_KPI_SOURCE.source_id` không có FK cứng sang Project module (tránh circular dependency)
- Validation khi tạo: kiểm tra `source_id` tồn tại ở app layer, không ở DB
- Nếu source bị xóa ở Project module: set `goal_type = 'manual'`, xóa WK_KPI_SOURCE, notify HR

---

## 10. Indexes & Caching

```sql
-- Danh sách nhân viên active (query phổ biến nhất)
CREATE INDEX idx_wk_emp_active
  ON wk_employees(org_id, department_id, status)
  WHERE status IN ('active', 'probation');

-- Contract sắp hết hạn (cron hàng ngày)
CREATE INDEX idx_wk_contract_alert
  ON wk_employees(org_id, contract_end)
  WHERE contract_end IS NOT NULL AND status = 'active';

-- Pending timeoff theo manager (thường check hàng ngày)
CREATE INDEX idx_wk_leave_pending
  ON wk_timeoffs(approved_by, status, date_from)
  WHERE status = 'pending';

-- KPI goal active theo cycle (dashboard)
CREATE INDEX idx_wk_kpi_active
  ON wk_kpi_goals(employee_id, cycle_label, status)
  WHERE status IN ('active', 'completed');

-- KPI source lookup khi sync
CREATE INDEX idx_wk_kpisrc_lookup
  ON wk_kpi_sources(source_type, source_id);

-- Leaderboard KPI theo cycle
CREATE INDEX idx_wk_snap_leaderboard
  ON wk_kpi_snapshots(cycle_label, employee_id);
```

### Caching

| Cache key | TTL | Invalidate khi |
|---|---|---|
| `workforce:org:{id}:headcount` | 10 phút | Thay đổi status/dept nhân viên |
| `workforce:emp:{id}:balance:{year}` | Xóa ngay | Mỗi UPDATE WK_LEAVE_BALANCE |
| `workforce:emp:{id}:kpi:{cycle}` | 5 phút | Sau mỗi SyncGoalProgressJob |
| `workforce:kpi:leaderboard:{cycle}` | 15 phút | Sau mỗi snapshot |

---

## 11. Lộ trình triển khai

### Phase 1 — Nhân viên (tuần 1–2)
- [ ] `wk_positions` + `wk_employees` + `wk_position_history`
- [ ] Tích hợp `users` và `departments` sẵn có
- [ ] CRUD nhân viên + onboarding + offboarding
- [ ] Headcount dashboard

### Phase 2 — Nghỉ phép (tuần 3–4)
- [ ] `wk_leave_policies` + `wk_leave_balances`
- [ ] `wk_timeoffs` + luồng duyệt
- [ ] Balance atomic transaction
- [ ] Cron: contract alert, leave reminder

### Phase 3 — KPI Goals (tuần 5–7)
- [ ] `wk_kpi_goals` + `wk_kpi_sources` + `wk_kpi_snapshots`
- [ ] Manual goal CRUD + weight validation
- [ ] KpiEntryObserver + TaskObserver + SyncGoalProgressJob
- [ ] Leaderboard + close cycle + snapshot

---

*Version 3.0.0 — Workforce Center — Employee · Leave · KPI*
*Stack: Laravel 11 · MySQL 8+ / PostgreSQL 15+*