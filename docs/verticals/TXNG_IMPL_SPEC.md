# VERTICAL TXNG — Implementation Spec

> **Vertical code:** `txng`
> **Phiên bản:** v1.0 — 2026-06-16
> **Nguồn:** `spec/duan/chitiettxng.docx` + `spec/trahoavang/TRIỂN KHAI TXNG.docx`
> **Phụ thuộc:** `docs/PLATFORM_DESIGN.md` — đọc trước để hiểu Platform Core

---

## Mục lục

1. [Tổng quan Vertical](#1-tổng-quan-vertical)
2. [Roles & Permissions](#2-roles--permissions)
3. [Navigation Map](#3-navigation-map)
4. [Database Mapping](#4-database-mapping)
5. [Module 1 — TXNG Readiness Assessment](#5-module-1--txng-readiness-assessment)
6. [Module 2 — TXNG Deployment Management](#6-module-2--txng-deployment-management)
7. [Module 3 — TXNG AI Assistant](#7-module-3--txng-ai-assistant)
8. [Module 4 — TXNG Academy](#8-module-4--txng-academy)
9. [Export — CheckVN](#9-export--checkvn)
10. [Reports](#10-reports)
11. [Acceptance Criteria](#11-acceptance-criteria)
12. [Sprint Plan](#12-sprint-plan)

---

## 1. Tổng quan Vertical

### 1.1 Định vị

```
CheckVN   = Nền tảng TXNG quốc gia — lưu trữ vĩnh viễn khu/lô/cây, QR, nhật ký
THUCHOCVN = Enablement Layer — đánh giá, quản lý triển khai, chuẩn bị dữ liệu,
            đào tạo nhân sự để HTX nhập lên CheckVN thành công

Luồng giá trị:
HTX → [THUCHOCVN: Đánh giá → Chuẩn bị dữ liệu → Đào tạo] → CheckVN → QR → Người tiêu dùng
```

**THUCHOCVN làm — CheckVN làm:**

| THUCHOCVN (hệ thống này) | CheckVN (hệ thống ngoài) |
|---|---|
| Đánh giá sẵn sàng HTX | Lưu trữ vĩnh viễn khu/lô/cây |
| Quản lý tiến độ dự án triển khai | Phát hành và quản lý QR code |
| Thu thập + chuẩn hóa dữ liệu (tạm thời) | Nhận nhật ký canh tác hàng ngày |
| Xuất file Excel đúng chuẩn CheckVN | Tra cứu lịch sử lô/sản phẩm |
| Đào tạo người dùng CheckVN | Cổng xác minh cho người tiêu dùng |
| Cấp chứng nhận năng lực TXNG | — |

> **Nguyên tắc cốt lõi:** Sau khi `DeploymentTarget` chuyển sang `completed`, THUCHOCVN không tiếp nhận thêm dữ liệu vận hành của HTX đó. Mọi hoạt động canh tác, nhật ký, QR đều thuộc về CheckVN từ đây.

### 1.2 Đối tượng sử dụng

| Nhóm | Vai trò | Dùng THUCHOCVN để làm gì |
|---|---|---|
| Nội bộ THUCHOCVN | PM, Surveyor, Data Ops, Data Entry, Trainer | Quản lý + triển khai dự án TXNG |
| HTX (trong dự án) | Người đại diện | Làm survey readiness, xem tiến độ, nhận đào tạo |
| Cơ quan quản lý | Viewer | Xem báo cáo tiến độ triển khai |

### 1.3 Khi nào bật Vertical này

Org được bật `txng` khi là **đơn vị tư vấn/triển khai TXNG cho HTX** — tức là nội bộ THUCHOCVN sử dụng vertical này để quản lý các dự án triển khai CheckVN cho nhiều HTX khách hàng.

HTX khách hàng **không cần** tài khoản THUCHOCVN để vận hành — họ dùng CheckVN trực tiếp sau khi bàn giao.

### 1.4 Bốn module chính

| # | Module | Mục tiêu | Dùng Platform Core nào |
|---|---|---|---|
| M1 | TXNG Readiness Assessment | Đánh giá HTX sẵn sàng triển khai CheckVN chưa | Survey + Assessment |
| M2 | TXNG Deployment Management | Quản lý tiến độ triển khai từng HTX | Project + Task + (4 bảng mới) |
| M3 | TXNG Data Preparation | Thu thập, chuẩn hóa dữ liệu tạm → xuất Excel vào CheckVN | AiCopilot + staging tables |
| M4 | TXNG Academy | Đào tạo nhân sự dùng CheckVN, cấp chứng nhận | Assessment Sandbox + Certification |

---

## 2. Roles & Permissions

### 2.1 Roles đặc thù Vertical TXNG

| Role code | Tên hiển thị | Phạm vi | Làm gì |
|---|---|---|---|
| `txng_pm` | PM Triển khai | Toàn bộ vertical | Tạo/xóa dự án, phân công nhân sự, theo dõi tiến độ |
| `txng_surveyor` | Cán bộ Khảo sát | HTX được phân công | Nhập khu/lô/cây, GPS, ảnh, hồ sơ tại thực địa |
| `txng_data_ops` | Vận hành Dữ liệu | HTX được phân công | Chuẩn hóa dữ liệu trong THUCHOCVN, chạy AI Validator, export file |
| `txng_data_entry` | Nhập liệu CheckVN | HTX được phân công | Nhận file từ Data Ops → **nhập thủ công vào CheckVN** (hệ thống bên ngoài) |
| `txng_trainer` | Giảng viên | HTX được phân công | Đào tạo HTX staff dùng CheckVN + bàn giao |

> **Phân biệt quan trọng:**
> - `txng_data_ops` làm việc **trong THUCHOCVN** — chuẩn hóa, validate, export ra file Excel.
> - `txng_data_entry` làm việc **trên CheckVN** (hệ thống ngoài) — nhận file Excel từ Data Ops rồi nhập tay lên CheckVN. Trong THUCHOCVN họ chỉ cần xem dữ liệu đã chuẩn hóa và tick checklist giai đoạn Nhập CheckVN.

*Các role Platform Core (system_admin, ceo, viewer) giữ nguyên quyền tương ứng.*

### 2.2 Ma trận quyền chi tiết

| Tính năng | system_admin | txng_pm | txng_surveyor | txng_data_ops | txng_data_entry | txng_trainer | viewer |
|---|---|---|---|---|---|---|---|
| Tạo/xóa dự án | ✅ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Thêm/xóa HTX khỏi dự án | ✅ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Phân công nhân sự | ✅ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Xem Dashboard dự án | ✅ | ✅ | ✅ (HTX mình) | ✅ (HTX mình) | ✅ (HTX mình) | ✅ (HTX mình) | ✅ |
| Nhập khu/lô/cây | ✅ | ✅ | ✅ | ✗ | ✗ | ✗ | ✗ |
| Upload ảnh/GPS | ✅ | ✅ | ✅ | ✗ | ✗ | ✗ | ✗ |
| Upload hồ sơ pháp lý | ✅ | ✅ | ✅ | ✗ | ✗ | ✗ | ✗ |
| Chuẩn hóa dữ liệu | ✅ | ✅ | ✗ | ✅ | ✗ | ✗ | ✗ |
| Chạy AI Validator | ✅ | ✅ | ✗ | ✅ | ✗ | ✗ | ✗ |
| Xem dữ liệu đã chuẩn hóa | ✅ | ✅ | ✗ | ✅ | ✅ | ✗ | ✗ |
| Tải file export CheckVN | ✅ | ✅ | ✗ | ✅ | ✅ | ✗ | ✗ |
| Tick checklist "Nhập CheckVN" | ✅ | ✅ | ✗ | ✅ | ✅ | ✗ | ✗ |
| Tạo/đóng Issue | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✗ |
| Tick checklist Đào tạo | ✅ | ✅ | ✗ | ✗ | ✗ | ✅ | ✗ |
| Upload biên bản bàn giao | ✅ | ✅ | ✗ | ✗ | ✗ | ✅ | ✗ |
| Export CheckVN (generate) | ✅ | ✅ | ✗ | ✅ | ✗ | ✗ | ✗ |
| Xem báo cáo | ✅ | ✅ | ✗ | ✗ | ✗ | ✗ | ✅ |

> **Giải thích `txng_data_entry`:** Họ chỉ có 3 quyền trong THUCHOCVN — xem dữ liệu đã sạch, tải file Excel về, tick checklist khi đã nhập xong vào CheckVN. Toàn bộ thao tác nhập liệu thực tế diễn ra trên hệ thống CheckVN (bên ngoài), THUCHOCVN không can thiệp vào quá trình đó.

### 2.3 Scope — Mọi role (trừ PM/admin) chỉ thấy HTX được phân công

```php
// DeploymentTargetPolicy.php
public function view(User $user, DeploymentTarget $target): bool
{
    if ($user->hasRole(['system_admin', 'txng_pm'])) return true;

    return $target->project->members()
        ->where('user_id', $user->id)
        ->whereIn('role', [
            'txng_surveyor',
            'txng_data_ops',
            'txng_data_entry',   // ← thêm mới
            'txng_trainer',
        ])
        ->exists()
        && $target->assigned_employee_id === $user->employee?->id;
}
```

### 2.4 Navigation theo role

| Nhóm menu | txng_pm | txng_surveyor | txng_data_ops | txng_data_entry | txng_trainer |
|---|---|---|---|---|---|
| Dashboard | ✅ toàn dự án | ✅ HTX mình | ✅ HTX mình | ✅ HTX mình | ✅ HTX mình |
| Dự án / HTX | ✅ | ✅ xem | ✅ xem | ✅ xem | ✅ xem |
| Khảo sát năng lực | ✅ | ✅ | ✗ | ✗ | ✗ |
| Vùng sản xuất (CRUD) | ✅ | ✅ | ✗ | ✗ | ✗ |
| Vùng sản xuất (xem) | ✅ | ✅ | ✅ | ✅ | ✅ |
| Chuẩn hóa dữ liệu | ✅ | ✗ | ✅ | ✗ | ✗ |
| Export CheckVN | ✅ | ✗ | ✅ (generate) | ✅ (download) | ✗ |
| Công việc / Issue | ✅ | ✅ | ✅ | ✅ | ✅ |
| Đào tạo / Bàn giao | ✅ | ✗ | ✗ | ✗ | ✅ |
| Báo cáo | ✅ | ✗ | ✗ | ✗ | ✗ |
| Academy / Sandbox | ✅ | ✅ | ✅ | ✅ | ✅ |

---

## 3. Navigation Map

### 3.1 Sidebar TXNG (hiện khi vertical `txng` được bật)

```
TXNG
├── Dashboard                /txng/dashboard
├── Dự án triển khai         /txng/projects
├── HTX                      /txng/htx
├── Khảo sát năng lực        /txng/readiness
│
CHUẨN BỊ DỮ LIỆU            ← staging workspace, không phải hệ thống canh tác
├── Vùng sản xuất            /txng/data/sites
├── Khu - Lô - Cây           /txng/data/areas
├── Hồ sơ pháp lý            /txng/data/legal-docs
├── Export CheckVN           /txng/export
│
CÔNG VIỆC
├── Công việc                /txng/tasks
├── Tiến độ                  /txng/progress
├── Issue                    /txng/issues
├── Bàn giao                 /txng/handover
│
BÁO CÁO
├── Báo cáo                  /txng/reports
│
ĐÀO TẠO
├── TXNG Academy             /txng/academy
├── Sandbox                  /txng/sandbox
├── Chứng nhận               /txng/certifications
│
CẤU HÌNH
└── Cấu hình Vertical        /txng/settings
```

> **Lưu ý thiết kế:** Nhóm "CHUẨN BỊ DỮ LIỆU" là workspace tạm thời trong phạm vi dự án — không phải module quản lý canh tác thường trực. Menu này chỉ active khi `deployment_target.current_phase` đang trong các giai đoạn `surveying → collecting → standardizing → importing`. Khi dự án `completed`, dữ liệu bị archive và menu chỉ còn xem.

### 3.2 Route → Controller → View mapping

| Route | Controller | View | Middleware |
|---|---|---|---|
| GET `/txng/dashboard` | `TxngDashboardController@index` | `txng/dashboard` | `vertical:txng` |
| GET `/txng/projects` | `TxngProjectController@index` | `txng/projects/index` | `vertical:txng` |
| GET `/txng/projects/{id}` | `TxngProjectController@show` | `txng/projects/show` | `vertical:txng` |
| GET `/txng/htx` | `DeploymentTargetController@index` | `txng/htx/index` | `vertical:txng` |
| GET `/txng/htx/{id}` | `DeploymentTargetController@show` | `txng/htx/show` | `vertical:txng` |
| GET `/txng/readiness` | `TxngReadinessController@index` | `txng/readiness/index` | `vertical:txng` |
| GET `/txng/tasks` | `TxngTaskController@index` | `txng/tasks/kanban` | `vertical:txng` |
| GET `/txng/issues` | `DeploymentIssueController@index` | `txng/issues/index` | `vertical:txng` |
| GET `/txng/progress` | `TxngProgressController@index` | `txng/progress/index` | `vertical:txng` |
| GET `/txng/handover` | `TxngHandoverController@index` | `txng/handover/index` | `vertical:txng` |
| GET `/txng/export` | `CheckVnExportController@index` | `txng/export/index` | `vertical:txng` |
| POST `/txng/export/{site}` | `CheckVnExportController@generate` | — (download ZIP) | `vertical:txng` |
| GET `/txng/reports` | `TxngReportController@index` | `txng/reports/index` | `vertical:txng` |
| GET `/txng/academy` | `TxngAcademyController@index` | `txng/academy/index` | `vertical:txng` |

---

## 4. Database Mapping

### 4.1 Spec gốc → Generic tables

Spec (`chitiettxng.docx`) dùng prefix `txng_`. Platform dùng generic names — mapping như sau:

| Spec (txng_*) | Platform generic | Ghi chú |
|---|---|---|
| `txng_projects` | `projects` | Filter `project_type = 'txng'` |
| `txng_htx` | `deployment_targets` | + fields: `client_name`, `tax_code`, `representative`, `client_phone` |
| `txng_project_members` | `project_members` | Dùng nguyên, thêm `role` enum TXNG roles |
| `txng_tasks` | `tasks` | Dùng nguyên, `taskable_type = DeploymentTarget` |
| `txng_issues` | `deployment_issues` | Schema đã thiết kế trong PLATFORM_DESIGN |
| `txng_progress_logs` | `deployment_progress_logs` | Schema đã thiết kế trong PLATFORM_DESIGN |
| `tbl_khu` (vùng trồng) | `production_areas` | **Staging only** — tồn tại trong scope dự án, không phải DB canh tác |
| `tbl_lo` | `production_lots` | **Staging only** |
| `tbl_cay` | `production_items` | **Staging only** |
| `tbl_nhatky` | `production_activity_logs` | **Staging only** — chỉ nhập lịch sử cũ để chuẩn bị DM_NHATKY.xlsx, không phải ghi hàng ngày |
| `tbl_htx` (chủ thể) | `deployment_targets` | Thông tin HTX được lưu trong `deployment_targets.client_*` |

### 4.1b Vòng đời dữ liệu (Data Lifecycle)

```
                  TRONG DỰ ÁN (THUCHOCVN)              SAU BÀN GIAO (CheckVN)
                  ────────────────────────              ────────────────────────
deployment_target  surveying → importing → completed    (không còn cập nhật)
production_sites   active (ghi/sửa tự do)     →  archived (chỉ đọc)
production_areas   active                      →  archived
production_lots    active                      →  archived
production_items   active                      →  archived
production_act_logs active (nhập lịch sử cũ)  →  archived (CheckVN tiếp quản)
legal_docs         active                      →  archived

Trigger archive: DeploymentTarget.status = 'completed'
  → Tất cả production_* của deployment này → status = 'archived'
  → Không nhận thêm bất kỳ write operation nào từ UI
  → Chỉ đọc được trong báo cáo lịch sử dự án
```

**Nhật ký canh tác hàng ngày (sau bàn giao) = CheckVN, không phải THUCHOCVN.**
HTX staff sau khi nhận bàn giao sẽ đăng nhập trực tiếp vào CheckVN để ghi nhật ký.

### 4.2 HTX Client — hai trường hợp

```
Trường hợp 1: HTX chưa có tài khoản THUCHOCVN
  deployment_targets.organization_id = NULL
  deployment_targets.client_name      = "HTX Hoa Sơn"
  deployment_targets.tax_code         = "0123456789"
  deployment_targets.representative   = "Nguyễn Văn A"
  deployment_targets.client_phone     = "0912345678"
  deployment_targets.client_province  = "Quảng Ninh"

Trường hợp 2: HTX đã có tài khoản (là tenant)
  deployment_targets.organization_id  = 42   ← FK → organizations
  deployment_targets.client_name      = NULL (lấy từ org)
  (các fields client_* để NULL — dùng org data)
```

### 4.3 Bổ sung fields vào `deployment_targets`

```sql
ALTER TABLE deployment_targets
  ADD COLUMN client_name        VARCHAR(255) NULL AFTER organization_id,
  ADD COLUMN tax_code           VARCHAR(20)  NULL AFTER client_name,
  ADD COLUMN representative     VARCHAR(255) NULL AFTER tax_code,
  ADD COLUMN client_phone       VARCHAR(20)  NULL AFTER representative,
  ADD COLUMN client_email       VARCHAR(255) NULL AFTER client_phone,
  ADD COLUMN client_province    VARCHAR(100) NULL AFTER client_email,
  ADD COLUMN client_district    VARCHAR(100) NULL AFTER client_province,
  ADD COLUMN client_address     TEXT         NULL AFTER client_district,
  ADD COLUMN assigned_employee_id BIGINT UNSIGNED NULL AFTER client_address,
  ADD CONSTRAINT fk_dt_assigned FK (assigned_employee_id) REFERENCES employees(id) ON DELETE SET NULL;
```

### 4.4 Bổ sung `project_type` vào `projects`

```sql
ALTER TABLE projects
  ADD COLUMN project_type VARCHAR(30) NOT NULL DEFAULT 'general' AFTER code;
  -- CHECK: general | txng | consulting | manufacturing

INDEX (organization_id, project_type)
```

### 4.5 Mã tự động sinh (auto-code)

| Entity | Pattern | Ví dụ | Sinh ở |
|---|---|---|---|
| Khu (Area) | Chữ cái in hoa tăng dần | A, B, C... | `ProductionAreaObserver` |
| Lô (Lot) | {area_code}{số thứ tự 2 chữ số} | A01, A02, B01 | `ProductionLotObserver` |
| Cây/Item | {lot_code}-C{số thứ tự 3 chữ số} | A01-C001, A01-C002 | `ProductionItemObserver` |
| Dự án | TXN-{năm}-{số thứ tự 4 chữ số} | TXN-2026-0001 | `ProjectObserver` |

---

## 5. Module 1 — TXNG Readiness Assessment

### 5.1 Mục tiêu

Đánh giá HTX sẵn sàng triển khai TXNG chưa → ra điểm → gap analysis → kế hoạch.

### 5.2 Survey Template: `txng_readiness_v1`

**4 sections, 20 câu hỏi:**

#### Section 1: Hạ tầng (5 câu — trọng số 25%)

| # | Câu hỏi | Kiểu | Điểm max |
|---|---|---|---|
| 1 | HTX có smartphone cho nhân viên nhập liệu? | Radio (Có/Không) | 5 |
| 2 | HTX có internet tại vùng trồng? | Radio (Có/3G-4G/Không) | 5 |
| 3 | HTX có máy tính hoặc máy tính bảng? | Radio (Có/Không) | 5 |
| 4 | HTX có thiết bị chụp ảnh? | Radio (Smartphone/Camera/Không) | 5 |
| 5 | Đường đến vùng trồng có thể di chuyển bằng xe máy? | Radio (Có/Một phần/Không) | 5 |

#### Section 2: Nhân sự (5 câu — trọng số 25%)

| # | Câu hỏi | Kiểu | Điểm max |
|---|---|---|---|
| 6 | Có người được phân công phụ trách TXNG? | Radio (Có/Không) | 5 |
| 7 | Người phụ trách biết dùng điện thoại cơ bản? | Rating 1–5 | 5 |
| 8 | Người phụ trách biết dùng Excel cơ bản? | Radio (Tốt/Biết sơ/Không) | 5 |
| 9 | Số nhân viên có thể hỗ trợ nhập liệu? | Number | 5 |
| 10 | HTX đã từng sử dụng phần mềm quản lý? | Radio (Có/Không/Đang dùng) | 5 |

#### Section 3: Dữ liệu (5 câu — trọng số 25%)

| # | Câu hỏi | Kiểu | Điểm max |
|---|---|---|---|
| 11 | HTX có nhật ký chăm sóc (giấy hoặc số)? | Radio (Số/Giấy/Không) | 5 |
| 12 | Nhật ký được ghi đều đặn? | Rating 1–5 | 5 |
| 13 | HTX có ảnh vùng trồng, sản phẩm? | Radio (Nhiều/Ít/Không) | 5 |
| 14 | Biết tọa độ GPS của vùng trồng? | Radio (Biết/Có thể đo/Không) | 5 |
| 15 | Hồ sơ pháp lý (ĐKKD, OCOP...) đã đầy đủ? | Checkbox (ĐKKD/OCOP/ATTP/VietGAP) | 5 |

#### Section 4: Quy trình (5 câu — trọng số 25%)

| # | Câu hỏi | Kiểu | Điểm max |
|---|---|---|---|
| 16 | HTX có quy trình chăm sóc được viết ra? | Radio (Có/Một phần/Không) | 5 |
| 17 | Quy trình có được thực hiện nhất quán? | Rating 1–5 | 5 |
| 18 | Sản phẩm đã có quy cách đóng gói chuẩn? | Radio (Có/Đang xây/Không) | 5 |
| 19 | HTX đã từng tham gia TXNG/truy xuất trước? | Radio (Có/Biết về TXNG/Không) | 5 |
| 20 | Lãnh đạo HTX cam kết tham gia dự án? | Radio (Cao/Trung bình/Thấp) | 5 |

### 5.3 Scoring config: `TXNG_READINESS`

```
assessment_code:  TXNG_READINESS
aggregation_mode: weighted_domain
survey_slug:      txng_readiness_v1

Domains:
  D1_INFRA    weight=0.25   Section 1 (câu 1–5)
  D2_PEOPLE   weight=0.25   Section 2 (câu 6–10)
  D3_DATA     weight=0.25   Section 3 (câu 11–15)
  D4_PROCESS  weight=0.25   Section 4 (câu 16–20)

Score bands:
  Chưa sẵn sàng   0–39    badge: error    → AI: "Cần hỗ trợ toàn diện trước khi triển khai"
  Cơ bản          40–59   badge: warning  → AI: "Có thể bắt đầu nhưng cần hỗ trợ chặt chẽ"
  Khá sẵn sàng    60–79   badge: info     → AI: "Sẵn sàng triển khai với hỗ trợ định kỳ"
  Sẵn sàng        80–100  badge: success  → AI: "Có thể triển khai độc lập"
```

### 5.4 Màn hình: Kết quả Readiness

```
┌──────────────────────────────────────────────────────────┐
│  HTX Hoa Sơn — TXNG Readiness Score                     │
│                                                          │
│         ┌─────────────┐                                 │
│         │    62 / 100 │  Khá sẵn sàng  [badge: info]   │
│         └─────────────┘                                 │
│                                                          │
│  Domain Breakdown:                                       │
│  Hạ tầng   ████████░░  80/100                           │
│  Nhân sự   █████░░░░░  50/100                           │
│  Dữ liệu   ██████░░░░  55/100                           │
│  Quy trình ███████░░░  65/100                           │
│                                                          │
│  ┌─ Gap Analysis ───────────────────────────────────┐   │
│  │ Hạng mục        Hiện tại  Yêu cầu  Gap          │   │
│  │ Hồ sơ pháp lý   60%       100%     -40%  🔴     │   │
│  │ Nhật ký          30%       100%     -70%  🔴     │   │
│  │ GPS              90%       100%     -10%  🟡     │   │
│  │ Hình ảnh         50%       100%     -50%  🔴     │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  AI Gợi ý ưu tiên:                                      │
│  1. Thu thập nhật ký chăm sóc (ưu tiên cao)            │
│  2. Chụp bổ sung ảnh vùng trồng                        │
│  3. Bổ sung GPS cho các lô còn thiếu                   │
│  4. Hoàn thiện hồ sơ pháp lý (OCOP, ATTP)             │
│                                                          │
│  [Tạo Kế hoạch Triển khai]  [Xem lại Survey]           │
└──────────────────────────────────────────────────────────┘
```

### 5.5 Workflow: Chạy Readiness Assessment

```
PM hoặc Surveyor:
  B1. Vào /txng/readiness → chọn HTX
  B2. Nhấn "Gửi khảo sát"
      → Chọn hình thức: Tự điền (PM điền thay) / Gửi link (token)
      → Nếu gửi link: hệ thống tạo survey_token + gửi SMS/email cho đại diện HTX
  B3. Đại diện HTX điền form (không cần login nếu dùng token)
  B4. Submit → CalculateSurveyScoreAction → AssessmentResult
  B5. PM xem kết quả → nhấn "Tạo Kế hoạch" → tạo DeploymentTarget với phase = 'surveying'
```

---

## 6. Module 2 — TXNG Deployment Management

### 6.1 Màn hình: Dashboard Tổng quan

```
┌──────────────────────────────────────────────────────────────┐
│  TXNG Deployment Dashboard                                   │
│                                                              │
│  ┌──────┐  ┌──────────┐  ┌──────────┐  ┌──────┐           │
│  │ 8    │  │ 5        │  │ 3        │  │ 25   │           │
│  │ Dự   │  │ Đang     │  │ Hoàn     │  │ HTX  │           │
│  │ án   │  │ triển    │  │ thành    │  │      │           │
│  └──────┘  └──────────┘  └──────────┘  └──────┘           │
│                                                              │
│  Theo giai đoạn:                                            │
│  Khảo sát ■■■  3    Chuẩn hóa ■  1    Nhập liệu ■  1     │
│  Đào tạo  ■■  2    Hoàn thành ■■■  3                      │
│                                                              │
│  Theo nhân sự:                                              │
│  ┌─────────────────────────────────────────┐               │
│  │ Hà (Surveyor)   5 HTX  |  ████████ 80% │               │
│  │ Lan (Data Ops)  3 HTX  |  ██████░░ 60% │               │
│  │ Minh (Trainer)  2 HTX  |  ████░░░░ 40% │               │
│  └─────────────────────────────────────────┘               │
│                                                              │
│  Issues mở: 🔴 3 Critical  🟡 5 High  🟢 8 Medium         │
└──────────────────────────────────────────────────────────────┘
```

### 6.2 Màn hình: Danh sách Dự án

**Tabulator columns:**

| Cột | Data field | Sortable | Filterable |
|---|---|---|---|
| Mã dự án | `code` | ✅ | ✅ |
| Tên dự án | `name` | ✅ | ✅ (search) |
| Địa phương | `province` | ✅ | ✅ (select) |
| PM | `pm_employee.name` | ✗ | ✅ (select) |
| Số HTX | `deployment_targets_count` | ✅ | ✗ |
| Tiến độ | progress bar | ✅ | ✗ |
| Trạng thái | `status` badge | ✅ | ✅ (select) |
| Ngày bàn giao | `target_date` | ✅ | ✗ |
| Thao tác | [Xem] [Sửa] | ✗ | ✗ |

**Status badges:**

| Status | Badge | Màu |
|---|---|---|
| draft | Nháp | ghost |
| surveying | Đang khảo sát | info |
| collecting | Thu hồ sơ | warning |
| standardizing | Chuẩn hóa | warning |
| importing | Nhập CheckVN | primary |
| training | Đào tạo | secondary |
| handover | Bàn giao | accent |
| completed | Hoàn thành | success |
| cancelled | Hủy | error |

### 6.3 Màn hình: Chi tiết Dự án — 5 Tabs

#### Tab 1 — HTX

```
┌──────────────────────────────────────────────────────────┐
│  Dự án: TXN-2026-0001 — Trà Hoa Vàng Quảng Ninh        │
│  PM: Nguyễn Hà  |  Ngày BG: 30/09/2026  |  ████████░░ 78% │
├──────────────────────────────────────────────────────────┤
│  [HTX] [Checklist] [Task] [Issue] [Bàn giao]            │
├──────────────────────────────────────────────────────────┤
│  [+ Thêm HTX]                                           │
│                                                          │
│  HTX Hoa Sơn      Nhập CheckVN  ████████░░ 80%  Hà      │
│  HTX ABC          Chuẩn hóa     ██████░░░░ 60%  Lan      │
│  HTX DEF          Khảo sát      ████░░░░░░ 40%  Minh     │
│                                                          │
│  [Click vào HTX → mở DeploymentTarget detail]           │
└──────────────────────────────────────────────────────────┘
```

#### Tab 2 — Checklist

Checklist được nhóm theo 6 phase. Mỗi phase có thể collapse/expand:

```
Phase 1 — Khảo sát  ████████░░  7/9 done
  ✅ Thông tin chủ thể               Hà  |  12/06/2026
  ✅ Hồ sơ pháp lý cơ bản           Hà  |  12/06/2026
  ✅ GPS vùng trồng                  Hà  |  13/06/2026
  ✅ Khảo sát khu                    Hà  |  13/06/2026
  ✅ Khảo sát lô                     Hà  |  14/06/2026
  ✅ Khảo sát cây                    Hà  |  14/06/2026
  ✅ Hình ảnh thực địa               Hà  |  14/06/2026
  ☐  Nhật ký chăm sóc               —
  ☐  Mẫu sản phẩm                   —

Phase 2 — Thu hồ sơ  ████░░░░░░  2/6 done
  ✅ Đăng ký kinh doanh              Lan |  15/06/2026
  ✅ CCCD người đại diện             Lan |  15/06/2026
  ☐  Chứng nhận OCOP                —
  ☐  Chứng nhận ATTP                —
  ☐  Logo HTX                       —
  ☐  Ảnh sản phẩm chuẩn            —
...
```

**Checklist item codes theo phase:**

| Phase | Item codes |
|---|---|
| `surveying` | `subject_info`, `basic_docs`, `gps_data`, `zone_survey`, `lot_survey`, `item_survey`, `field_photos`, `activity_log`, `product_sample` |
| `collecting` | `business_reg`, `personal_id`, `tax_code_cert`, `ocop_cert`, `food_safety_cert`, `htx_logo`, `product_photos` |
| `standardizing` | `subject_std`, `zone_std`, `lot_std`, `item_std`, `log_std`, `product_std` |
| `importing` | `subject_entry`, `product_entry`, `zone_entry`, `lot_entry`, `item_entry`, `media_upload`, `log_entry` |
| `training` | `login_training`, `view_data`, `enter_log`, `upload_photo`, `manage_qr` |
| `handover` | `docs_handover`, `data_handover`, `manual_handover`, `minutes_signed`, `account_transfer` |

#### Tab 3 — Task (Kanban)

6 cột tương ứng 6 phase. Mỗi card task có: tiêu đề, assignee avatar, deadline, priority badge.

```
Khảo sát    Thu hồ sơ   Chuẩn hóa   Nhập CKV    Đào tạo    Bàn giao
──────────  ──────────  ──────────  ──────────  ─────────  ─────────
[Khảo sát   [Thu ĐKKD   [Chuẩn mã                          
 HTX Hoa     HTX Hoa     khu/lô]                           
 Sơn]        Sơn]       
 Hà ⚑High   Lan ⚑Med   Lan ⚑High  
            [Thu OCOP
             HTX ABC]
             Lan ⚑Low
```

#### Tab 4 — Issue

**Tabulator columns:** Mã issue | Tiêu đề | HTX | Severity | Status | Người xử lý | Ngày tạo | Hành động

**Chi tiết Issue:**
- Tạo tự động từ AI Validator (Sandbox 4)
- Tạo thủ công bởi bất kỳ member nào
- Severity: `critical / high / medium / low`
- Status: `open → in_progress → resolved → closed`
- Owner được giao từ PM

#### Tab 5 — Bàn giao

```
┌──────────────────────────────────────────────────────────┐
│  Bàn giao — HTX Hoa Sơn                                 │
│                                                          │
│  Checklist bàn giao:                                    │
│  ✅ Hồ sơ pháp lý đầy đủ                               │
│  ✅ Dữ liệu đã nhập CheckVN                             │
│  ☐  Tài liệu hướng dẫn sử dụng                         │
│  ☐  Biên bản bàn giao (có chữ ký)                      │
│  ☐  Chuyển giao tài khoản                               │
│                                                          │
│  Upload biên bản: [Chọn file PDF/Word]                  │
│  Ngày bàn giao chính thức: [Date picker]                │
│  Chữ ký PM: _______________                             │
│  Chữ ký đại diện HTX: _______________                  │
│                                                          │
│  [Hoàn tất Bàn giao] → status → 'completed'            │
└──────────────────────────────────────────────────────────┘
```

### 6.4 Màn hình: Chi tiết HTX (DeploymentTarget)

```
HTX Hoa Sơn                                    [Sửa]
MST: 0123456789  |  Đại diện: Nguyễn Văn A  |  SĐT: 0912345678
Địa chỉ: Khu Hoa Sơn, Bình Liêu, Quảng Ninh
Phụ trách: Nguyễn Hà  |  Giai đoạn: Đang nhập CheckVN
Readiness Score: 62/100  [Xem chi tiết]

Tiến độ:      Khảo sát ✅  Thu hồ sơ ✅  Chuẩn hóa ✅  Nhập CKV ⏳  Đào tạo ☐  Bàn giao ☐

[Tabs]: Dữ liệu chuẩn bị | Hồ sơ pháp lý | Issues | Lịch sử

Tab: Dữ liệu chuẩn bị  [chỉ active khi phase ≠ completed]
  Site: HTX Hoa Sơn Farm  |  3 khu  |  12 lô  |  1.200 cây
  [Khu Dốc Đỏ A]  4 lô  |  480 cây   GPS: ✅  Ảnh: ✅
  [Khu Camping]   5 lô  |  400 cây   GPS: ✅  Ảnh: ⚠️ thiếu 2 lô
  [Khu HTX]       3 lô  |  320 cây   GPS: ⚠️  Ảnh: ✅
  [Xem dữ liệu đã archive]  (nếu phase = completed)

Tab: Hồ sơ pháp lý
  ✅ ĐKKD — Số 0123/HTX — HH 31/12/2027
  ✅ CCCD đại diện
  ⚠️ OCOP — Chưa có
  ⚠️ ATTP — Chưa có
```

### 6.5 Workflow: Tạo dự án và phân công

```
PM:
  B1. /txng/projects → [+ Tạo dự án mới]
  B2. Điền: Tên, Mã (tự sinh TXN-2026-0001), Địa phương, Ngày BG, Mô tả
  B3. Submit → tạo Project (project_type = 'txng')
  B4. Tab Members → Add PM, Surveyors, Data Ops, Trainers
  B5. Tab HTX → [+ Thêm HTX]
      → Tìm theo MST hoặc tạo mới (client_name, tax_code, representative, phone, province)
      → Assign phụ trách → Chọn Surveyor
  B6. Hệ thống tạo deployment_checklist_items tự động cho phase 'surveying'
  B7. Task được tạo: "Khảo sát HTX {tên}" → assignee = Surveyor được chọn
```

### 6.6 Workflow: Surveyor nhập khu/lô/cây (Staging Data)

> Dữ liệu nhập ở bước này là **staging** — mục đích duy nhất là chuẩn bị file Excel export vào CheckVN. Không phải database canh tác thường trực.

```
Surveyor (tại thực địa — mobile-friendly):
  B1. /txng/data/sites → chọn Site của HTX
  B2. Tab Khu (Areas):
      [+ Tạo khu]
      → Tên khu: "Khu Dốc Đỏ A"
      → GPS: [📍 Lấy vị trí hiện tại] hoặc [Nhập thủ công lat/lng]
      → Diện tích: 2.5 ha
      → Mã tự sinh: A (do Observer)
  B3. Tab Lô → chọn Khu A:
      [+ Tạo lô]
      → GPS, diện tích, số cây dự kiến: 50
      → Mã tự sinh: A01
  B4. Tab Cây → chọn Lô A01:
      Chọn hình thức nhập:
      ├── Nhập từng cây: form 1 cây/lần
      └── Nhập hàng loạt: số lượng (50) → hệ thống tạo A01-C001..C050
      Fields mỗi cây: loại (to=50/lô, TB=10/lô, nhỏ=30/lô), tuổi, tình trạng
  B5. Upload ảnh (multi-upload, tối thiểu 1 ảnh/lô)
      → Media gắn vào ProductionLot hoặc ProductionArea
  B6. Save → checklist tự cập nhật: "Khảo sát lô ✅"

Validation:
  - GPS bắt buộc ở cấp Khu (warning nếu thiếu, không block)
  - Ảnh: warning nếu lô chưa có ảnh nào
  - Số cây thực tế ≠ dự kiến → highlight, ghi ghi chú
```

---

## 7. Module 3 — TXNG AI Assistant

### 7.1 Tổng quan 4 AI agents

| Agent code | Trigger | Input | Output |
|---|---|---|---|
| `txng-ocr-agent` | Upload file | File ảnh CCCD/ĐKKD | Structured JSON fields |
| `txng-standardize-agent` | Data Ops click "Chuẩn hóa" | Raw area/lot/item names | Standardized codes + names |
| `txng-validator-agent` | Click "Kiểm tra dữ liệu" | production_* tables data | Issue list + quality score |
| `txng-coach-agent` | Chat input hoặc auto-trigger | Deployment status | Priority list + Q&A answers |

### 7.2 Agent: `txng-ocr-agent`

**Trigger:** Upload ảnh hồ sơ pháp lý (CCCD, ĐKKD, OCOP...)

**Input → Processing → Output:**

```
Input:  File ảnh (CCCD.jpg)
        doc_type = 'personal_id'

Processing (AiCopilot driver: Claude vision):
  Prompt: "Đây là ảnh CCCD Việt Nam. Trích xuất:
           - Họ tên: ...
           - Số CCCD: ...
           - Ngày sinh: ...
           - Địa chỉ thường trú: ...
           Trả về JSON."

Output JSON:
  {
    "name": "Nguyễn Văn A",
    "id_number": "012345678901",
    "dob": "1975-03-15",
    "address": "Thôn Hoa Sơn, Xã Vô Ngại, Huyện Bình Liêu, Quảng Ninh"
  }

UI action:
  → Auto-fill form DeploymentTarget:
      representative = "Nguyễn Văn A"
      client_address = "Thôn Hoa Sơn..."
  → User xem lại → [Xác nhận] hoặc [Sửa thủ công]
```

**Tương tự cho ĐKKD:**
```
Output: { "company_name": "HTX Hoa Sơn", "tax_code": "0123456789",
          "registered_address": "...", "registration_date": "2018-05-10" }
→ Auto-fill: client_name, tax_code, client_address
```

### 7.3 Agent: `txng-standardize-agent`

**Trigger:** Data Ops vào tab Chuẩn hóa → click [Chạy AI Chuẩn hóa]

**Input:**
```
Raw data từ production_areas/lots/items của HTX:
  Khu: "lô chủ" / "khu đồi" / "vườn già"
  Lô:  "lô cây to" / "vườn nhỏ" / "cây già TB"
  Cây: 200 cây chưa có code
```

**Processing:**
```
Prompt: "Chuẩn hóa tên khu/lô cho hệ thống TXNG CheckVN.
         Quy tắc: Khu = chữ cái in hoa (A, B, C...)
                  Lô  = {mã khu}{số 2 chữ số} (A01, A02...)
         Input: [danh sách tên thô]
         Trả về: mapping tên gốc → mã chuẩn + tên chuẩn"

Output:
  { "lô chủ"  → { code: "A", name: "Khu A (Khu Chủ)" },
    "khu đồi" → { code: "B", name: "Khu B (Khu Đồi)" },
    "lô cây to" → { code: "A01", name: "Lô A01 - Cây To", parent: "A" } }
```

**UI:**
```
┌─ Đề xuất Chuẩn hóa ─────────────────────────────────────┐
│  Tên gốc         →  Mã chuẩn   Tên chuẩn                │
│  "lô chủ"        →  A          Khu A (Khu Chủ)   [✓][✗] │
│  "khu đồi"       →  B          Khu B (Khu Đồi)   [✓][✗] │
│  "lô cây to"     →  A01        Lô A01 - Cây To   [✓][✗] │
│                                                          │
│  [Chấp nhận tất cả]  [Xem lại từng cái]  [Hủy]         │
└──────────────────────────────────────────────────────────┘
```

Sau khi xác nhận → cập nhật `production_areas.code`, `production_lots.code` + sinh `production_items.code`.

### 7.4 Agent: `txng-validator-agent`

**Trigger:** Data Ops click [Kiểm tra dữ liệu] trên DeploymentTarget

**Input:**
```
Truy vấn từ database:
  - production_items COUNT, null GPS count, no-media count per lot
  - production_activity_logs count per lot
  - production_legal_docs: missing doc_types
  - deployment_checklist_items: incomplete items
```

**Processing:**
```
Logic (không cần AI, chạy bằng PHP query):
  GPS_null_lots     = lots WHERE lat IS NULL
  no_photo_lots     = lots WHERE media_count = 0
  no_log_lots       = lots WHERE activity_log_count = 0
  missing_docs      = ['ocop_cert', 'food_safety_cert'] NOT IN legal_docs
  data_quality_score = (complete_items / total_items) * 100

AI layer (Claude): Chỉ dùng để generate ưu tiên + text gợi ý
  Prompt: "Dự án có các vấn đề: [list]. Đề xuất 5 ưu tiên xử lý theo impact."
```

**Output → UI:**
```
┌─ Kết quả Kiểm tra Dữ liệu ──────────────────────────────┐
│  Data Quality Score: 73%  [badge: warning]               │
│                                                          │
│  Issues phát hiện:                                       │
│  🔴 GPS thiếu: 2 lô (A03, B02)          severity: high  │
│  🟡 Ảnh thiếu: 4 lô                      severity: med   │
│  🔴 Nhật ký thiếu: 3 lô                  severity: high  │
│  🟡 OCOP cert chưa có                    severity: med   │
│  🟢 ATTP cert chưa có                    severity: low   │
│                                                          │
│  AI Gợi ý ưu tiên:                                      │
│  1. Bổ sung GPS lô A03, B02 (blocking import)           │
│  2. Thu nhật ký 3 lô thiếu                              │
│  3. Chụp bổ sung ảnh 4 lô                              │
│                                                          │
│  [Tạo Issues tự động]  [Export báo cáo]                 │
└──────────────────────────────────────────────────────────┘

→ [Tạo Issues tự động] → tạo deployment_issues cho từng item
   severity mapping: GPS thiếu = high, nhật ký thiếu = high,
                     ảnh thiếu = medium, docs thiếu = medium/low
```

### 7.5 Agent: `txng-coach-agent`

**Dạng chatbot Q&A** + auto-trigger khi deployment tiến độ thấp.

**Knowledge base** (từ KcItem đã có):
- Hướng dẫn sử dụng CheckVN
- Quy cách nhập từng loại dữ liệu
- FAQ triển khai TXNG

**Input → Output ví dụ:**
```
User hỏi: "Nhật ký thu hoạch cần những thông tin gì?"
AI trả lời: "Nhật ký thu hoạch theo chuẩn CheckVN cần:
             • Ngày thu hoạch (bắt buộc)
             • Mã lô/khu (bắt buộc)
             • Sản lượng (kg) (bắt buộc)
             • Người thực hiện (bắt buộc)
             • Ảnh minh chứng (khuyến nghị)
             • Phương pháp thu hái (nếu có)"

User hỏi: "Chuẩn bị gì trước khi nhập CheckVN?"
AI trả lời: "Trước khi nhập lên CheckVN, cần có đủ:
             ✅ Thông tin chủ thể (HTX/DN) + hồ sơ pháp lý
             ✅ Danh sách khu/lô đã chuẩn hóa mã
             ✅ Số lượng cây/đơn vị mỗi lô
             ✅ GPS ít nhất cấp khu
             ✅ Ít nhất 1 ảnh/lô
             ✅ Nhật ký ít nhất 3 tháng gần nhất"
```

**Auto-trigger:** Khi `deployment_progress_logs` không cập nhật sau 7 ngày → Agent tự gửi notification:
> "Dự án HTX Hoa Sơn chưa cập nhật 7 ngày. Tiến độ hiện tại 65%. Ưu tiên: Thu nhật ký 3 lô còn thiếu."

---

## 8. Module 4 — TXNG Academy

### 8.1 Tổng quan 5 Sandboxes

| # | Sandbox | Mục tiêu | Cert sau khi hoàn thành |
|---|---|---|---|
| S1 | Legal Document Collector | Thu thập + số hóa hồ sơ | TXNG Foundation – Legal Docs |
| S2 | Farm Survey | Khảo sát thực địa + nhập khu/lô/cây | TXNG Foundation – Farm Survey |
| S3 | Data Standardization | Chuẩn hóa + export Excel | TXNG Practitioner – Data Std |
| S4 | Data Validator | Kiểm tra chất lượng + issue management | TXNG Practitioner – Data Quality |
| S5 | Deployment Coach | Quản lý triển khai toàn diện | TXNG Professional – Deployment |

### 8.2 Sandbox 1 — Legal Document Collector

**Mục tiêu học:** Biết thu thập, phân loại, kiểm tra thiếu hồ sơ HTX, dùng OCR.

**Môi trường:** Sandbox environment với HTX giả (dữ liệu demo).

**Bài tập:**
```
Nhiệm vụ: HTX Mẫu có 3 tài liệu (CCCD.jpg, DKKD.pdf, OCOP.jpg)
          Bạn cần: Upload → Phân loại → Kiểm tra thiếu → Hoàn chỉnh hồ sơ

B1. Upload CCCD.jpg → AI OCR → xác nhận thông tin trích xuất
B2. Upload ĐKKD.pdf → AI OCR → xác nhận MST, tên HTX
B3. Upload OCOP.jpg → phân loại = 'ocop_cert' → nhập ngày HH
B4. Hệ thống show: Còn thiếu ATTP cert, logo HTX
B5. Tìm và upload file còn thiếu (có trong thư viện demo)
B6. Hoàn thành → chấm điểm
```

**Rubric chấm điểm (100đ):**

| Tiêu chí | Điểm | Cách tính |
|---|---|---|
| Upload đúng loại file | 20 | 4đ / file đúng |
| Phân loại đúng doc_type | 30 | 6đ / phân loại đúng |
| Xác nhận OCR đúng | 20 | Tỷ lệ field chính xác × 20 |
| Phát hiện hồ sơ thiếu | 15 | 5đ / thiếu phát hiện đúng |
| Hoàn thành bộ hồ sơ | 15 | 15đ nếu 100% hồ sơ đủ |

**Pass:** ≥ 70đ → unlock Certification "TXNG Foundation – Legal Documents"

### 8.3 Sandbox 2 — Farm Survey

**Mục tiêu học:** Biết khảo sát thực địa — tạo khu/lô/cây, GPS, ảnh.

**Bài tập:**
```
Kịch bản: HTX Mẫu có vùng trồng 3 khu, 12 lô, ~1.200 cây
          Bạn đóng vai Surveyor

B1. Tạo Site: "HTX Mẫu Farm" (GPS demo cung cấp)
B2. Tạo 3 khu (A, B, C) + GPS từng khu
B3. Tạo lô trong mỗi khu (A: 4 lô, B: 5 lô, C: 3 lô)
B4. Nhập số cây: A01=50 (to), A02=30 (nhỏ), ...
B5. Upload ảnh demo cho mỗi lô (file ảnh mẫu được cung cấp)
B6. AI kiểm tra: báo thiếu ảnh lô B03, thiếu GPS lô C02
B7. Bổ sung và hoàn chỉnh
B8. Export: xem DM_KHU.xlsx và DM_LO.xlsx tạo ra
```

**Rubric (100đ):**

| Tiêu chí | Điểm | Cách tính |
|---|---|---|
| Khảo sát khu (số lượng + GPS) | 25 | (khu đúng / 3) × 25 |
| Khảo sát lô (số lượng + GPS) | 25 | (lô đúng / 12) × 25 |
| GPS đầy đủ | 25 | (GPS items / total) × 25 |
| Hình ảnh đầy đủ | 25 | (lô có ảnh / 12) × 25 |

**Pass:** ≥ 70đ → unlock "TXNG Foundation – Farm Survey"

### 8.4 Sandbox 3 — Data Standardization

**Mục tiêu học:** Chuẩn hóa dữ liệu thô → format CheckVN.

**Bài tập:**
```
Kịch bản: Nhận dữ liệu thô từ Surveyor
  Khu:  "lô chủ" / "khu đồi" / "vườn già bản Nà Làng"
  Lô:   "lô cây to" / "vườn cây nhỏ xã" / "cây già TB"
  Cây:  200 cây chưa mã hóa

B1. Xem raw data
B2. Tự chuẩn hóa thủ công: đặt tên chuẩn cho khu/lô
B3. Chạy AI đề xuất → so sánh với kết quả của mình
B4. Xem xét, chọn đề xuất hoặc giữ cách của mình
B5. Hệ thống sinh mã tự động cho 200 cây
B6. Export 5 file Excel → kiểm tra format đúng chuẩn CheckVN
```

**Rubric (100đ):**

| Tiêu chí | Điểm |
|---|---|
| Mã khu đúng format (A,B,C) | 20 |
| Mã lô đúng format (A01, A02) | 20 |
| Mã cây đúng format (A01-C001) | 20 |
| Không có mã trùng | 20 |
| Export đủ 5 file, đúng cột | 20 |

**Pass:** ≥ 70đ → unlock "TXNG Practitioner – Data Standardization"

### 8.5 Sandbox 4 — Data Validator

**Mục tiêu học:** Kiểm tra chất lượng dữ liệu, tạo và xử lý issue.

**Bài tập:**
```
Kịch bản: Dữ liệu 12 lô, 1200 cây đã nhập — có chứa lỗi cố tình
  Lỗi ẩn: Lô A03 thiếu GPS / B02 thiếu GPS / 4 lô thiếu ảnh / 3 lô không có nhật ký

B1. Chạy AI Validator
B2. Đọc kết quả — xác định lỗi nào AI tìm đúng, lỗi nào AI bỏ sót
B3. Tạo Issue cho từng lỗi (title, severity, assignee)
B4. Giả lập xử lý: upload GPS demo, upload ảnh demo
B5. Đóng issue đã xử lý
B6. Chạy lại Validator → Data Quality Score phải ≥ 95%
```

**Rubric (100đ):**

| Tiêu chí | Điểm |
|---|---|
| Phát hiện đúng số lỗi | 40 |
| Tạo Issue đúng severity | 30 |
| Theo dõi và đóng issue đúng | 30 |

**Pass:** ≥ 70đ → unlock "TXNG Practitioner – Data Quality"

### 8.6 Sandbox 5 — Deployment Coach

**Mục tiêu học:** Quản lý toàn bộ tiến độ triển khai một HTX từ đầu đến cuối.

**Bài tập:**
```
Kịch bản: Bạn là PM, phụ trách triển khai HTX Mẫu từ đầu

B1. Tạo dự án → thêm HTX → phân công nhân sự
B2. Chạy Readiness Assessment → đọc kết quả (score: 62)
B3. AI Coach gợi ý kế hoạch → review + chấp nhận/điều chỉnh
B4. Theo dõi checklist — tick từng bước hoàn thành
B5. Xử lý 2 issue phát sinh (do AI inject vào)
B6. Điều phối Sandbox 1-4 cho nhân viên
B7. Hoàn thành checklist Đào tạo + Bàn giao
B8. Mark deployment = completed
```

**Rubric (100đ):**

| Tiêu chí | Điểm |
|---|---|
| Hoàn thành checklist đủ 6 phase | 40 |
| Xử lý issue đúng cách | 30 |
| Kế hoạch triển khai hợp lý | 30 |

**Pass:** ≥ 70đ → unlock "TXNG Professional – Deployment Specialist"

### 8.7 Ba cấp Certification

| Cert | Điều kiện | Phạm vi năng lực |
|---|---|---|
| TXNG Foundation | Pass S1 + S2 | Khảo sát + số hóa hồ sơ |
| TXNG Practitioner | Foundation + Pass S3 + S4 | Chuẩn hóa + kiểm tra dữ liệu |
| TXNG Professional | Practitioner + Pass S5 + 1 dự án thực tế | Triển khai độc lập cho HTX |

### 8.8 Career Pathway

```
B1 Nhập liệu cơ bản       → txng_surveyor + TXNG Foundation
B2 Quản trị dữ liệu       → txng_data_ops + TXNG Practitioner
B3 Chuyên viên TXNG        → cả 2 role + TXNG Professional
B4 Chuyên gia Triển khai   → txng_pm + ≥ 5 dự án hoàn thành
B5 Quản lý Dự án TXNG      → txng_pm + KPI PM score ≥ 85%
```

---

## 9. Export — CheckVN

### 9.1 Trigger

`GET /txng/export` → chọn Project + HTX (DeploymentTarget) → `POST /txng/export/{target_id}`

**Điều kiện:** `deployment_target.current_phase` phải là `standardizing` trở lên.

### 9.2 Năm file xuất

#### DM_CHUTHE.xlsx — Chủ thể (HTX)

| Cột CheckVN | Nguồn data |
|---|---|
| MA_CHUTHE | `deployment_targets.tax_code` |
| TEN_CHUTHE | `deployment_targets.client_name` |
| NGUOI_DAIDO | `deployment_targets.representative` |
| SDT | `deployment_targets.client_phone` |
| EMAIL | `deployment_targets.client_email` |
| TINH | `deployment_targets.client_province` |
| HUYEN | `deployment_targets.client_district` |
| DIA_CHI | `deployment_targets.client_address` |
| MA_DKKD | legal_docs WHERE doc_type='business_registration' → doc_number |
| NGAY_CAP | legal_docs WHERE doc_type='business_registration' → issued_at |

#### DM_KHU.xlsx — Khu vực

| Cột CheckVN | Nguồn data |
|---|---|
| MA_CHUTHE | tax_code từ deployment_target |
| MA_KHU | `production_areas.code` |
| TEN_KHU | `production_areas.name` |
| GPS_LAT | `production_areas.lat` |
| GPS_LNG | `production_areas.lng` |
| DIEN_TICH | `production_areas.area_sqm` / 10000 (→ ha) |

#### DM_LO.xlsx — Lô

| Cột CheckVN | Nguồn data |
|---|---|
| MA_CHUTHE | tax_code |
| MA_KHU | `production_areas.code` |
| MA_LO | `production_lots.code` |
| TEN_LO | `production_lots.name` |
| GPS_LAT | `production_lots.lat` |
| GPS_LNG | `production_lots.lng` |
| SO_LUONG | `production_lots.item_count_actual` |
| NGAY_BAT_DAU | `production_lots.started_at` |

#### DM_CAY.xlsx — Cây/Đơn vị

| Cột CheckVN | Nguồn data |
|---|---|
| MA_CHUTHE | tax_code |
| MA_LO | `production_lots.code` |
| MA_CAY | `production_items.code` |
| LOAI_CAY | `production_items.age_category` → map: mature="Cây già", juvenile="Cây TB", seedling="Cây non" |
| GPS_LAT | `production_items.lat` |
| GPS_LNG | `production_items.lng` |
| NGAY_TRONG | `production_items.planted_at` |
| TINH_TRANG | `production_items.condition_status` |

#### DM_NHATKY.xlsx — Nhật ký

| Cột CheckVN | Nguồn data |
|---|---|
| MA_CHUTHE | tax_code |
| MA_LO | lot.code (join qua subject) |
| NGAY | `production_activity_logs.performed_at` |
| LOAI_HD | `production_activity_logs.activity_type` → map sang tên tiếng Việt |
| SO_LUONG | `production_activity_logs.quantity` |
| DON_VI | `production_activity_logs.unit` |
| NGUOI_TH | employee.name |
| GHI_CHU | `production_activity_logs.notes` |

### 9.3 Quy tắc export

```
- Nếu GPS = NULL → cột GPS xuất trống (không lỗi, không block)
- Item bị soft-deleted → không xuất
- Encoding: UTF-8 BOM (Excel Windows đọc được tiếng Việt)
- Tên file: {tax_code}_{date}_DM_CHUTHE.xlsx
- Output: ZIP gồm 5 files → tên ZIP: {project_code}_{tax_code}_{date}_checkvn.zip
- Download trigger ngay (không queue nếu < 5000 items), queue nếu ≥ 5000 items
```

### 9.4 Xử lý lỗi export

| Lỗi | Hành động |
|---|---|
| Không có khu/lô nào | Block: "Chưa có dữ liệu vùng sản xuất" |
| Mã khu/lô có ký tự đặc biệt | Warning + sanitize tự động |
| Ngày format sai | Chuẩn hóa về YYYY-MM-DD |
| Item count > 10.000 | Auto-switch sang queue job + notify khi xong |

---

## 10. Reports

### 10.1 Báo cáo PM

**Route:** `GET /txng/reports/pm?project_id=&date_from=&date_to=`

**Layout:**
```
┌─ Báo cáo Tổng quan Dự án ────────────────────────────┐
│  Dự án: TXN-2026-0001  |  Kỳ: 01/06 – 16/06/2026   │
│                                                        │
│  TỔNG QUAN                                            │
│  25 HTX | 150 lô | 5.000 cây | 95% hoàn thành         │
│                                                        │
│  TIẾN ĐỘ THEO HTX                                     │
│  HTX Hoa Sơn  ████████░░  80%  Nhập CheckVN           │
│  HTX ABC      ██████░░░░  60%  Chuẩn hóa              │
│  HTX DEF      ████░░░░░░  40%  Khảo sát               │
│                                                        │
│  ISSUES                                               │
│  🔴 Critical: 2  🟡 High: 5  🟢 Medium: 3  ✅ Done: 12 │
│                                                        │
│  NHÂN SỰ                                              │
│  Hà (Surveyor)  5 HTX / 2.500 cây / 95% KPI          │
│  Lan (Data Ops) 3 HTX / 1.500 cây / 88% KPI          │
└────────────────────────────────────────────────────────┘
Export: [Excel] [PDF]
```

**Data source:**
```php
DeploymentTarget::with(['productionSite.areas.lots.items', 'issues'])
    ->where('project_id', $projectId)
    ->get()
    ->map(fn($t) => [
        'htx_name'      => $t->client_name,
        'phase'         => $t->current_phase,
        'percent'       => $t->latest_progress?->percent ?? 0,
        'item_count'    => $t->productionSite?->items_count ?? 0,
        'open_issues'   => $t->issues->where('status', 'open')->count(),
    ]);
```

### 10.2 Báo cáo Tỉnh / Địa phương

**Route:** `GET /txng/reports/province?province=&year=`

**Layout:**
```
┌─ Báo cáo Triển khai TXNG ─ Quảng Ninh ─ 2026 ───────┐
│                                                        │
│  Tổng HTX tham gia: 25                               │
│  Đã hoàn thành:     18 (72%)                          │
│  Đang triển khai:   7  (28%)                          │
│                                                        │
│  Sản phẩm đã TXNG: Trà Hoa Vàng Bình Liêu            │
│  QR đã phát hành: 1.200 (từ CheckVN — manual input)  │
│                                                        │
│  Số cây đã số hóa: 12.500                            │
│  Số lô đã số hóa: 150                                │
│  Số khu đã số hóa: 36                                │
└────────────────────────────────────────────────────────┘
Export: [Excel] [PDF với letterhead]
```

---

## 11. Acceptance Criteria

### M1 — Readiness Assessment

- [ ] Tạo được survey từ template `txng_readiness_v1` trong < 30 giây
- [ ] Gửi link token đến email/SĐT HTX — không cần login
- [ ] Điểm tính đúng theo weighted domain (4 domain × 25%)
- [ ] Gap analysis hiển thị đúng 4 hạng mục với %
- [ ] AI gợi ý ưu tiên xuất hiện sau khi submit

### M2 — Deployment Management

- [ ] Tạo dự án → tự sinh mã TXN-YYYY-XXXX
- [ ] Thêm HTX (có hoặc không có tài khoản) đều được
- [ ] Checklist tự tạo items khi HTX được thêm vào dự án (phase: surveying)
- [ ] Kanban kéo-thả task giữa 6 cột (có thể dùng JS drag-drop hoặc select)
- [ ] Progress bar tự tính từ checklist items hoàn thành
- [ ] Phase tự động cập nhật khi 100% checklist của phase đó done

### M3 — AI Assistant

- [ ] OCR CCCD: trích xuất đúng ≥ 3/4 field (tên, số, ngày sinh, địa chỉ)
- [ ] AI Validator: phát hiện đúng ≥ 90% GPS null, media thiếu
- [ ] Tạo Issues tự động từ kết quả validator — 1 issue / 1 lỗi
- [ ] Coach Q&A trả lời trong < 5 giây

### M4 — Academy

- [ ] Mỗi Sandbox chạy độc lập trong môi trường demo (không ảnh hưởng data thật)
- [ ] Chấm điểm tự động theo rubric — không cần người review
- [ ] Pass threshold = 70đ → tự động unlock cert
- [ ] Cert hiển thị trong WorkforceProfile của nhân viên

### Export — CheckVN

- [ ] ZIP có đúng 5 files với tên đúng chuẩn
- [ ] DM_CAY.xlsx có đúng cột theo spec CheckVN
- [ ] File encoding UTF-8 BOM — mở bằng Excel không bị lỗi font
- [ ] < 5 giây với 1.000 items; queue tự động khi ≥ 5.000 items
- [ ] Hàng thiếu GPS: xuất trống, không lỗi

---

## 12. Sprint Plan

### 12.1 Định nghĩa MVP

Spec gốc (`chitiettxng.docx`) đề xuất MVP **2 tuần, 2 sprint** tập trung vào Module 2 — Deployment Management:

> Sprint 1: Dự án / HTX / Checklist / Task / Progress
> Sprint 2: Issue / Dashboard / Báo cáo / AI kiểm tra dữ liệu / Biên bản bàn giao

Platform Core (Auth, Employee, Project, Task) **đã có sẵn**. Tuy nhiên trước khi làm được MVP, cần 1 sprint chuẩn bị hạ tầng Vertical. Tổng MVP = **~3 tuần**.

### 12.2 MVP Track — ~3 tuần

```
Tuần 0 (Pre-MVP)     Tuần 1 (MVP Sprint 1)     Tuần 2 (MVP Sprint 2)
─────────────────    ─────────────────────────  ─────────────────────
Sprint 0: Infra      Sprint 1: Core Ops         Sprint 2: Intelligence
  Vertical config      Dự án TXNG                 Issue management
  org_verticals        HTX (DeployTarget)         Dashboard tổng quan
  Roles 5 role         Checklist 6 phase          Báo cáo PM
  RequireVertical      Task + Kanban              AI Validator cơ bản
  middleware           Progress tracking          Biên bản bàn giao
  3 ngày               5 ngày                     5 ngày
```

**Output MVP:** Team nội bộ THUCHOCVN quản lý được toàn bộ tiến độ triển khai HTX — từ tạo dự án → phân công → checklist → issue → dashboard → bàn giao. **Chưa có:** nhập khu/lô/cây, export CheckVN, Academy.

### 12.3 Sprint chi tiết — MVP

| Sprint | Thời gian | Scope | Output kiểm tra |
|---|---|---|---|
| **0 — Infra** | 3 ngày | `organization_verticals` + `RequireVertical` middleware + 5 roles TXNG + sidebar config | Org bật txng → thấy menu; org khác → 403; 5 roles gán được quyền |
| **1 — Core Ops** | 5 ngày | `deployment_targets` + `deployment_checklist_items` + `deployment_progress_logs` — Project tabs: HTX / Checklist / Task Kanban / Tiến độ | Tạo project → thêm HTX → checklist 6 phase tự sinh → kéo task Kanban |
| **2 — Intelligence** | 5 ngày | `deployment_issues` + AI Validator (query-based, không cần AI thực) + Dashboard TXNG + Báo cáo PM + Tab Bàn giao + upload biên bản | Chạy validator → issues tự tạo; Dashboard số đúng; export báo cáo PDF |

### 12.4 Post-MVP Track — Sau MVP

| Sprint | Thời gian | Scope | Output kiểm tra |
|---|---|---|---|
| **3 — Readiness** | 3 ngày | Survey template `txng_readiness_v1` + `TXNG_READINESS` seeder + màn hình kết quả + gap table | Điền survey → điểm 4 domain + AI gợi ý ưu tiên |
| **4 — Production Data** | 5 ngày | 6 bảng production_* + models + CRUD mobile-first (Sites/Areas/Lots/Items/Logs/LegalDocs) + auto-code Observer | Tạo khu A → lô A01 → cây A01-C001 tự sinh; chụp GPS mobile |
| **5 — Export CheckVN** | 3 ngày | `composer require maatwebsite/excel` + 5 Export classes + ZIP download + field mapping | Download ZIP → 5 file Excel đúng cột, đúng font UTF-8 BOM |
| **6 — AI Agents** | 4 ngày | OCR agent (CCCD/ĐKKD) + Standardize agent + Coach Q&A (AiCopilot driver: Claude) | Upload CCCD → auto-fill form; Chuẩn hóa tên khu/lô → đề xuất AI |
| **7 — Academy** | 5 ngày | 5 Sandbox environments + rubric scoring tự động + 3 Cert definitions + Career Pathway seeder | Làm S1 → pass 70đ → cert TXNG Foundation → hiện WorkforceProfile |
| **8 — Notifications** | 2 ngày | WorkflowAutomation triggers (35 triggers) + 9 email templates + user preferences | Phase done → PM nhận in-app < 30s; critical issue → email < 2 phút |
| **9 — Reports & Polish** | 3 ngày | Báo cáo Tỉnh + Cross-project analytics + mobile UX polish + Acceptance Criteria test | Báo cáo tỉnh xuất PDF letterhead; lighthouse mobile score ≥ 80 |

### 12.5 Tổng thời gian

```
MVP   (Sprint 0–2):  ~3 tuần  → Team dùng được cho dự án thực
Full  (Sprint 0–9):  ~9 tuần  → Vertical hoàn chỉnh 4 module
```

> **Ghi chú:** Spec gốc đề xuất "MVP 2 tuần" với giả định Platform Core chưa có. Thực tế Platform Core **đã có** (Auth, Project, Task, Employee...) nên Sprint 0 (Infra) chỉ mất 3 ngày thay vì 1 tuần — tổng MVP ~3 tuần là hợp lý.

---

## 13. Mobile / Field UX

### 13.1 Nguyên tắc

Mobile UX trong section này phục vụ **THUCHOCVN staff** (txng_pm, txng_surveyor, txng_data_ops) trong suốt vòng đời dự án — không áp dụng cho HTX staff sau bàn giao. Sau khi `DeploymentTarget.status = 'completed'`, HTX staff sử dụng **CheckVN** để nhập nhật ký sản xuất hàng ngày.

Thiết bị thực địa **bắt buộc có kết nối mạng** (3G/4G) để đăng nhập và sử dụng — không có chế độ offline. Không cần PWA hay service worker. Chỉ cần responsive web chạy tốt trên Chrome mobile.

```
Yêu cầu thiết bị tối thiểu:
  Android 8+ / iOS 13+  |  Chrome / Safari mới nhất
  3G trở lên (4G khuyến nghị cho upload ảnh)
  Camera + GPS (bắt buộc cho surveyor)
  Màn hình ≥ 5 inch
```

### 13.2 Các màn hình cần mobile-first

Không phải toàn bộ hệ thống cần tối ưu mobile — chỉ những màn hình surveyor dùng ngoài đồng:

| Màn hình | Role dùng | Lý do mobile-first |
|---|---|---|
| Tạo/Sửa Khu (Area) | Surveyor | Đứng ngoài đồng, nhập GPS tại chỗ |
| Tạo/Sửa Lô (Lot) | Surveyor | Như trên |
| Tạo/Sửa Cây (Item) | Surveyor | Nhập từng cây, nhiều lần liên tiếp |
| Upload ảnh | Surveyor | Chụp trực tiếp từ camera |
| Checklist phase | Surveyor | Tick nhanh khi xong từng bước |

Các màn hình còn lại (Dashboard, Report, Project list...) — responsive thông thường là đủ.

### 13.3 Form nhập Khu / Lô — Mobile layout

```
┌─────────────────────────────┐
│  ← Khu A — Lô mới          │
├─────────────────────────────┤
│  Tên lô                     │
│  ┌───────────────────────┐  │
│  │ Lô cây to             │  │
│  └───────────────────────┘  │
│                             │
│  GPS                        │
│  ┌──────────────────────┐   │
│  │ 📍 Lấy vị trí ngay  │   │  ← button lớn, tap 1 lần
│  └──────────────────────┘   │
│  Lat: 21.4521  Lng: 107.123 │  ← hiện sau khi tap
│                             │
│  Diện tích (m²)             │
│  ┌───────────────────────┐  │
│  │ 2500                  │  │
│  └───────────────────────┘  │
│                             │
│  Số cây dự kiến             │
│  ┌───────────────────────┐  │
│  │ 50                    │  │
│  └───────────────────────┘  │
│                             │
│  ┌─────────────────────────┐│
│  │      LƯU LÔ            ││  ← button full-width, to
│  └─────────────────────────┘│
└─────────────────────────────┘
```

**Quy tắc GPS capture:**
```
Tap [📍 Lấy vị trí ngay]
  → Browser Geolocation API: navigator.geolocation.getCurrentPosition()
  → Accuracy threshold: chỉ chấp nhận nếu accuracy ≤ 50m
  → Nếu accuracy > 50m: hiện warning "GPS chưa ổn định, thử lại"
  → Nếu từ chối permission: hiện input thủ công lat/lng
  → Timeout 10 giây: nếu không lấy được → fallback thủ công
```

### 13.4 Form nhập hàng loạt Cây — Mobile

Surveyor thường nhập 30–100 cây một lúc. Không dùng form 1 cây/lần — dùng quick-entry:

```
┌─────────────────────────────┐
│  ← Lô A01 — Thêm cây       │
├─────────────────────────────┤
│  Hình thức nhập:            │
│  ● Nhập số lượng (nhanh)   │  ← default
│  ○ Nhập từng cây (chi tiết)│
├─────────────────────────────┤
│  [Nhập số lượng]            │
│                             │
│  Loại cây                   │
│  [To ▼]  Số lượng: [50]    │
│  [TB ▼]  Số lượng: [10]    │
│  [Nhỏ ▼] Số lượng: [30]    │
│                             │
│  Tuổi phổ biến              │
│  ● >20 năm ○ 10-20 ○ 3-10  │
│                             │
│  ┌─────────────────────────┐│
│  │   TẠO 90 CÂY           ││
│  └─────────────────────────┘│
│  → Hệ thống tạo A01-C001   │
│    đến A01-C090 tự động     │
└─────────────────────────────┘
```

### 13.5 Upload ảnh — Mobile

```
┌─────────────────────────────┐
│  Ảnh Lô A01                 │
├─────────────────────────────┤
│  ┌──────┐ ┌──────┐         │
│  │ 📷  │ │ 🖼️  │         │  ← ảnh đã có
│  └──────┘ └──────┘         │
│                             │
│  ┌─────────────────────────┐│
│  │  📷 Chụp ảnh mới       ││  ← mở camera trực tiếp
│  └─────────────────────────┘│
│  ┌─────────────────────────┐│
│  │  🖼️  Chọn từ thư viện ││
│  └─────────────────────────┘│
│                             │
│  Tối thiểu: 1 ảnh/lô       │
│  ⚠️  Lô B03 chưa có ảnh   │
└─────────────────────────────┘
```

**Kỹ thuật:**
```html
<!-- Mở camera trực tiếp trên mobile -->
<input type="file" accept="image/*" capture="environment" id="camera-input">

<!-- Chọn từ thư viện -->
<input type="file" accept="image/*" multiple id="gallery-input">
```

**Upload flow:**
```
Chọn/chụp ảnh
  → Resize client-side xuống max 1920px (canvas API) trước khi upload
  → Upload lên Spatie MediaLibrary (multipart)
  → Progress bar hiện % upload
  → Xong → thumbnail xuất hiện ngay
  → GPS từ EXIF ảnh: nếu ảnh có EXIF GPS → hỏi "Dùng GPS từ ảnh này không?"
```

### 13.6 UX rules chung cho mobile

```
Touch targets:
  - Button tối thiểu 44×44px
  - Khoảng cách giữa các element tương tác ≥ 8px
  - Input height ≥ 48px

Typography:
  - Font size tối thiểu 16px (tránh browser auto-zoom khi focus input)
  - Label rõ ràng, không placeholder-only

Navigation:
  - Back button luôn ở top-left
  - Action button chính (Lưu / Tạo) luôn ở bottom, full-width
  - Không dùng hover-state, dùng active-state

Forms:
  - Một cột duy nhất trên mobile (không grid 2 cột)
  - Select dùng native <select> hoặc bottom sheet, không dùng TomSelect dropdown
    trên màn hình < 480px
  - Number input dùng inputmode="decimal" để mở numeric keyboard

GPS:
  - Hiện indicator "Đang lấy GPS..." khi đang chờ
  - Kết quả GPS hiện dưới dạng "21.4521° N, 107.123° E" (không raw số)

Upload ảnh:
  - Resize trước khi upload (max 1920px, quality 85%)
  - Không block form submit nếu upload đang chạy — queue background
```

### 13.7 Blade layout mobile

Các view mobile-first dùng layout riêng:

```php
// resources/views/layouts/mobile.blade.php
// Không có sidebar, không có top nav phức tạp
// Chỉ: back button + page title + content + bottom action bar

@extends('layouts.mobile')  // cho views: area/create, lot/create, item/create, log/create
@extends('layouts.backend') // cho views còn lại (desktop-first, responsive thêm)
```

---

## 14. Notification Config

### 14.1 Channels

Hệ thống dùng Laravel Notifications với 2 channel:

| Channel | Dùng khi | Cấu hình |
|---|---|---|
| **In-app** | Mọi event | Lưu vào bảng `notifications` (Laravel built-in) |
| **Email** | Event quan trọng + user bật email notify | Queue job, Laravel Mailable |

> SMS không triển khai trong scope này — có thể bổ sung sau qua Twilio/ESMS.

### 14.2 Trigger map đầy đủ

#### Nhóm: Dự án & Phân công

| Event | Người nhận | In-app | Email | Nội dung |
|---|---|---|---|---|
| Dự án mới được tạo | PM của dự án | ✅ | ✅ | "Dự án [TXN-2026-0001] đã được tạo, bạn là PM" |
| Được thêm vào dự án | Member được thêm | ✅ | ✅ | "Bạn được phân công vào dự án [TXN-2026-0001] với vai trò [Surveyor]" |
| HTX mới được thêm vào dự án | PM + toàn bộ member | ✅ | ✗ | "HTX Hoa Sơn vừa được thêm — phụ trách: Nguyễn Hà" |
| HTX được assign cho mình | Surveyor được assign | ✅ | ✅ | "Bạn phụ trách HTX Hoa Sơn trong dự án [TXN-2026-0001]" |

#### Nhóm: Tiến độ Checklist

| Event | Người nhận | In-app | Email | Nội dung |
|---|---|---|---|---|
| Phase hoàn thành 100% | PM + member tiếp theo trong quy trình | ✅ | ✅ | "HTX Hoa Sơn hoàn thành giai đoạn Khảo sát ✅ — tiếp theo: Thu hồ sơ" |
| Toàn bộ deployment hoàn thành | PM + CEO | ✅ | ✅ | "HTX Hoa Sơn đã hoàn tất triển khai TXNG 🎉" |
| Checklist bị trễ hạn > 3 ngày | PM | ✅ | ✅ | "HTX Hoa Sơn — giai đoạn Thu hồ sơ chưa hoàn thành sau 3 ngày" |
| Tiến độ không cập nhật > 7 ngày | PM | ✅ | ✅ | "Dự án TXN-2026-0001: HTX Hoa Sơn chưa cập nhật 7 ngày" |

#### Nhóm: Issue

| Event | Người nhận | In-app | Email | Nội dung |
|---|---|---|---|---|
| Issue mới được tạo (severity: critical/high) | PM + owner được giao | ✅ | ✅ | "🔴 Issue mới [Critical]: GPS thiếu lô A03 — HTX Hoa Sơn" |
| Issue mới (severity: medium/low) | Owner được giao | ✅ | ✗ | "Issue mới [Medium]: Thiếu ảnh lô B02 — cần xử lý" |
| Issue được giao cho mình | Owner | ✅ | ✅ | "Issue [GPS thiếu lô A03] đã được giao cho bạn" |
| Issue được resolve | PM + người tạo | ✅ | ✗ | "Issue [GPS thiếu lô A03] đã được xử lý ✅" |
| Issue critical chưa xử lý > 48h | PM | ✅ | ✅ | "🔴 Issue Critical [GPS thiếu] chưa xử lý sau 48 giờ" |

#### Nhóm: AI Results

| Event | Người nhận | In-app | Email | Nội dung |
|---|---|---|---|---|
| AI Validator chạy xong | Người trigger + PM | ✅ | ✗ | "Kiểm tra dữ liệu HTX Hoa Sơn: 73% — 7 issues phát hiện" |
| AI Validator: Data Quality < 60% | PM | ✅ | ✅ | "⚠️ Chất lượng dữ liệu HTX Hoa Sơn thấp (58%) — cần xử lý trước khi export" |
| Export CheckVN hoàn thành (queue) | Người trigger | ✅ | ✅ | "Export CheckVN HTX Hoa Sơn sẵn sàng — [Tải xuống]" |
| Export thất bại | Người trigger + PM | ✅ | ✅ | "❌ Export thất bại: [lý do] — liên hệ admin" |

#### Nhóm: Readiness Assessment

| Event | Người nhận | In-app | Email | Nội dung |
|---|---|---|---|---|
| HTX nộp Readiness Survey | PM phụ trách | ✅ | ✅ | "HTX Hoa Sơn vừa hoàn thành khảo sát năng lực — Score: 62/100" |
| Readiness Score < 40 | PM | ✅ | ✅ | "⚠️ HTX Hoa Sơn chưa sẵn sàng TXNG (Score: 35) — cần hỗ trợ toàn diện" |

#### Nhóm: Academy & Certification

| Event | Người nhận | In-app | Email | Nội dung |
|---|---|---|---|---|
| Sandbox hoàn thành + pass | Nhân viên + HR | ✅ | ✗ | "Bạn vừa pass Sandbox [Legal Document Collector] — 85đ 🎉" |
| Sandbox fail | Nhân viên | ✅ | ✗ | "Bạn chưa pass [Data Validator] — 62đ. Thử lại nhé!" |
| Certification được cấp | Nhân viên + HR + CEO | ✅ | ✅ | "🏆 [Nguyễn Hà] vừa đạt chứng nhận TXNG Practitioner" |

### 14.3 Cách implement — WorkflowAutomation triggers

Dùng `WorkflowAutomation` module sẵn có — không viết notification logic rải rác trong code:

```
Trigger: deployment_target.current_phase changed
  Condition: new_phase = 'collecting'
  Action: notify → recipients=[pm, member_type=txng_data_ops]
          template: 'txng.phase_changed'
          data: { htx_name, phase_label, project_code }

Trigger: deployment_issue.created
  Condition: severity IN ['critical', 'high']
  Action: notify → recipients=[pm, issue.owner]
          template: 'txng.issue_high'
          channel: [in_app, email]

Trigger: deployment_issue.created
  Condition: severity IN ['medium', 'low']
  Action: notify → recipients=[issue.owner]
          template: 'txng.issue_low'
          channel: [in_app]

Trigger: scheduled (daily 08:00)
  Condition: deployment_progress_logs.last_updated < NOW() - 7 days
             AND deployment_target.status = 'active'
  Action: notify → recipients=[pm]
          template: 'txng.stale_progress'

Trigger: scheduled (daily 08:00)
  Condition: deployment_checklist_items.phase overdue > 3 days
  Action: notify → recipients=[pm]
          template: 'txng.checklist_overdue'

Trigger: deployment_issue (severity=critical, status=open)
  Condition: created_at < NOW() - 48 hours
  Action: notify → recipients=[pm]
          template: 'txng.critical_issue_overdue'
          channel: [in_app, email]
```

### 14.4 Notification templates

Mỗi template là 1 Blade view trong `Modules/WorkflowAutomation/resources/views/notifications/txng/`:

| Template | Subject email | In-app text |
|---|---|---|
| `txng.phase_changed` | "[TXNG] {htx_name} hoàn thành giai đoạn {phase}" | "{htx_name} ✅ {phase_label} — tiếp theo: {next_phase}" |
| `txng.issue_high` | "[TXNG] Issue {severity}: {title}" | "🔴 {title} — {htx_name} / giao: {owner}" |
| `txng.issue_low` | — | "Issue mới [{severity}]: {title}" |
| `txng.stale_progress` | "[TXNG] Nhắc: {htx_name} chưa cập nhật 7 ngày" | "⏰ {htx_name} chưa cập nhật 7 ngày" |
| `txng.checklist_overdue` | "[TXNG] Trễ hạn: {htx_name} — {phase}" | "⚠️ {htx_name} trễ giai đoạn {phase}" |
| `txng.critical_issue_overdue` | "[TXNG] 🔴 Issue Critical chưa xử lý 48h" | "🔴 Issue critical [{title}] — 48h chưa xử lý" |
| `txng.readiness_done` | "[TXNG] Kết quả khảo sát {htx_name}: {score}/100" | "{htx_name} nộp khảo sát — Score: {score}" |
| `txng.export_ready` | "[TXNG] Export CheckVN sẵn sàng tải" | "Export {htx_name} sẵn sàng — [Tải xuống]" |
| `txng.cert_issued` | "[TXNG] Chứng nhận mới: {cert_name}" | "🏆 {employee_name} đạt {cert_name}" |

### 14.5 User preferences

Mỗi user tự cấu hình kênh nhận trong `/account/notification-settings`:

```
Nhóm TXNG:
  ☑ Phân công dự án                [In-app] [Email]
  ☑ Thay đổi giai đoạn HTX         [In-app] [Email]
  ☑ Issue mới (Critical/High)       [In-app] [Email]
  ☑ Issue mới (Medium/Low)          [In-app] [ ]
  ☑ Nhắc tiến độ 7 ngày           [In-app] [Email]
  ☑ Kết quả Export CheckVN        [In-app] [Email]
  ☑ Chứng nhận mới                 [In-app] [Email]
```

### 14.6 Acceptance criteria — Notifications

- [ ] Phase chuyển → PM nhận in-app trong < 30 giây
- [ ] Issue critical tạo → PM + owner nhận email trong < 2 phút
- [ ] Scheduled jobs chạy đúng 08:00 hàng ngày (Laravel Scheduler)
- [ ] User tắt email notify → không nhận email, vẫn nhận in-app
- [ ] Notification có link trực tiếp đến record liên quan (click → mở đúng màn hình)
- [ ] Không gửi duplicate (idempotency key per event+recipient)
