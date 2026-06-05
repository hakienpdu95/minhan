# Đặc Tả Module: Marketplace Center

> **Hệ thống:** SaaS SME
> **Module:** Marketplace Center
> **Phiên bản:** 1.3.0
> **Ngày:** 2026-06-05
> **Stack:** Laravel 13 · SQLite (dev) / MySQL 8+ / PostgreSQL 15+
> **Liên module:** Recruitment Center (upstream, optional — qua nullable FK + Observer)

---

## Mục lục

1. [Tổng quan & Triết lý thiết kế](#1-tổng-quan--triết-lý-thiết-kế)
2. [Phạm vi](#2-phạm-vi)
3. [Kiến trúc liên thông Recruitment ↔ Marketplace](#3-kiến-trúc-liên-thông-recruitment--marketplace)
4. [Enum Values](#4-enum-values)
5. [ERD — Quan hệ bảng](#5-erd--quan-hệ-bảng)
6. [Đặc tả bảng dữ liệu](#6-đặc-tả-bảng-dữ-liệu)
7. [Luồng nghiệp vụ](#7-luồng-nghiệp-vụ)
8. [Query Patterns](#8-query-patterns)
9. [API Endpoints](#9-api-endpoints)
10. [Business Rules](#10-business-rules)
11. [Indexes & Caching](#11-indexes--caching)
12. [Lộ trình triển khai](#12-lộ-trình-triển-khai)

---

## 1. Tổng quan & Triết lý thiết kế

**Marketplace Center** là cổng thông tin công khai của hệ sinh thái SME — nơi các doanh nghiệp đăng tin tuyển dụng, tìm kiếm nguồn lực freelance, và kết nối dự án ra bên ngoài tổ chức. Module này phục vụ **2 chiều**: org đăng nhu cầu, cá nhân/tổ chức bên ngoài đáp ứng.

### Triết lý thiết kế quan trọng nhất

**Marketplace KHÔNG phải extension của Recruitment.** Đây là 2 hệ thống độc lập với mục đích khác nhau:

| Khía cạnh | Recruitment Center | Marketplace Center |
|---|---|---|
| Đối tượng | Nội bộ tổ chức | Công khai — bất kỳ ai |
| Ứng viên | RC_CANDIDATE (private) | MKT_APPLICANT (public profile) |
| Tin đăng | RC_JOB_POSTING (draft/internal) | MKT_LISTING (public, multi-type) |
| Mục đích | Quản lý quy trình tuyển dụng | Kết nối & khám phá hệ sinh thái |
| Data flow | Source of truth | Consumer + contributor |

**Kết nối RC → Marketplace qua nullable FK:** Khi org đăng job từ Recruitment ra Marketplace, hệ thống copy data vào `mkt_listings` và ghi `rc_job_posting_id` (nullable FK). Sau đó 2 bản ghi hoạt động độc lập — Observer tự đồng bộ trạng thái đóng. Không cần bảng bridge trung gian riêng.

**Ba loại người đăng tin (poster_type):**
- `org`: Tổ chức đã đăng ký tenant trong hệ thống — có `org_id`
- `guest_company`: Doanh nghiệp chưa xác thực — chỉ cần email, không cần là tenant
- `individual`: Cá nhân / freelancer đăng profile tìm dự án

### Người dùng

| Loại | poster_type | Mô tả |
|---|---|---|
| **Org (Tenant)** | `org` | Tổ chức đã đăng ký hệ thống — đăng job/project, xem ứng viên |
| **Guest Company** | `guest_company` | Doanh nghiệp chưa xác thực — đăng tin bằng email, không cần tenant |
| **Individual / Freelancer** | `individual` | Đăng profile năng lực (resource listing), nhận dự án |
| **Applicant** | — | Tạo profile MKT_APPLICANT, ứng tuyển, nhận tin nhắn |
| **Visitor** | — | Browse tin đăng, không apply |

---

## 2. Phạm vi

### Trong phạm vi

- Listing đa loại: `job` (việc làm), `project` (dự án outsource), `resource` (freelancer tìm việc)
- Profile ứng viên/freelancer công khai (MKT_APPLICANT)
- Apply và track đơn ứng tuyển qua Marketplace
- Save/bookmark listing
- Review & rating sau khi hoàn thành hợp tác
- Hỗ trợ 3 loại người đăng: tenant org, guest company (chưa xác thực), individual freelancer
- Publish job từ Recruitment ra Marketplace qua nullable FK + Observer, import ứng viên ngược lại
- Analytics: view count, apply count, conversion per listing

### Ngoài phạm vi

- Payment / hợp đồng thuê freelancer (module riêng)
- Video interview tích hợp
- **Messaging / Chat real-time** — `mkt_conversations` + `mkt_messages` bị loại khỏi scope hiện tại. Giao tiếp giữa org và ứng viên thực hiện qua email (ngoài hệ thống). Có thể bổ sung sau khi các tính năng cốt lõi ổn định.
- AI matching / recommendation engine (có thể mở rộng sau)
- Background check / verification bên thứ ba

---

## 3. Kiến trúc liên thông Recruitment ↔ Marketplace

### 3.1 Luồng publish: Recruitment → Marketplace

```
RC_JOB_POSTING (status='open', is_public=TRUE)
       │
       ▼ HR click "Đăng ra Marketplace"
INSERT mkt_listings:
  poster_type       = 'org'
  org_id            = job.org_id
  rc_job_posting_id = job.id          ← nullable CHAR(36) ref (UUID, ON DELETE SET NULL)
  rc_sync_status    = 'synced'
  auto_close_on_rc  = TRUE
  title, description, requirements,
  benefits, salary_*, employment_type,
  headcount, expire_at               ← copy tại thời điểm publish
  status            = 'active'

Sau khi publish — 2 bản ghi hoạt động độc lập:
  RC_JOB_POSTING thay đổi
    → RcJobPostingObserver::updated()
    → UPDATE mkt_listings SET rc_sync_status='out_of_sync'
       WHERE rc_job_posting_id = job.id AND rc_sync_status='synced'

  HR click "Re-sync":
    → Overwrite lại các trường nội dung từ RC_JOB_POSTING
    → UPDATE rc_sync_status='synced'

  RC_JOB_POSTING đóng (status → 'closed'/'cancelled')
    → RcJobPostingObserver::updated() khi auto_close_on_rc=TRUE
    → UPDATE mkt_listings SET status='closed', closed_at=NOW()
```

> **Không cần bảng `mkt_listing_syncs` riêng.** Monolith NWIDART, cùng DB — Observer đủ xử lý. 3 cột trên `mkt_listings` thay thế hoàn toàn.

### 3.2 Luồng import: Marketplace → Recruitment

```
MKT_APPLICATION (status='submitted')
       │ org quan tâm ứng viên
       ▼ HR click "Import vào Recruitment"

Hệ thống kiểm tra:
  ├─ RC_CANDIDATE tồn tại với email này? → tái sử dụng
  └─ Chưa có → INSERT RC_CANDIDATE từ MKT_APPLICANT data

INSERT RC_APPLICATION:
  job_id             = mkt_listings.rc_job_posting_id  ← lấy từ FK trên mkt_listing
  candidate_id       = rc_candidate.id
  apply_source       = 'marketplace'
  mkt_application_id = mkt_application.id              ← soft ref, không FK

Điều kiện: chỉ import được khi mkt_listing.rc_job_posting_id IS NOT NULL
(listing phải đến từ Recruitment — không thể import vào RC từ listing độc lập)

Sau import:
  UPDATE mkt_applications SET import_status='imported', imported_at=NOW()
```

### 3.3 Luồng đăng tin cho Guest Company (doanh nghiệp chưa xác thực)

```
Doanh nghiệp chưa đăng ký tenant:
  1. Đăng ký tài khoản Marketplace bằng email → INSERT users (không có organization_id)
  2. INSERT mkt_listings:
       poster_type        = 'guest_company'
       org_id             = NULL
       posted_by          = user.id
       guest_company_name = "Công ty ABC"
       guest_company_email= "hr@abc.com"
       listing_type       = 'job' | 'project'
       status             = 'draft' → pending_review → 'active' (sau khi admin duyệt)

  3. Nếu sau này doanh nghiệp đăng ký thành tenant:
       UPDATE mkt_listings SET org_id=org.id, poster_type='org'
       WHERE posted_by = user.id AND poster_type='guest_company'
```

> Guest company listings cần duyệt trước khi active (`pending_review`) để tránh spam.
> Tenant org listings active ngay.

---

## 4. Enum Values

### MKT_LISTING

| Trường | Giá trị | Mô tả |
|---|---|---|
| `listing_type` | `job` | Tuyển dụng nhân sự full/part time |
| | `project` | Dự án outsource, cần nhà thầu/team |
| | `resource` | Cá nhân/team chào dịch vụ, tìm dự án |
| `poster_type` | `org` | Tenant đã đăng ký — org_id NOT NULL |
| | `guest_company` | Doanh nghiệp chưa xác thực — org_id NULL |
| | `individual` | Cá nhân freelancer — listing_type='resource' |
| `status` | `draft` \| `pending_review` \| `active` \| `paused` \| `closed` \| `expired` | `pending_review` dành cho guest_company |
| `work_type` | `onsite` \| `remote` \| `hybrid` \| `flexible` | |
| `employment_type` | `full_time` \| `part_time` \| `contractor` \| `freelance` \| `intern` | `contractor`/`intern` khớp Employee module |
| `experience_level` | `entry` \| `junior` \| `mid` \| `senior` \| `lead` \| `any` | |
| `visibility` | `public` \| `unlisted` \| `members_only` | |
| `rc_sync_status` | `synced` \| `out_of_sync` \| NULL | NULL = listing không đến từ RC |

### MKT_APPLICANT

| Trường | Giá trị |
|---|---|
| `account_type` | `individual` \| `team` \| `agency` |
| `status` | `active` \| `inactive` \| `suspended` \| `open_to_work` \| `not_available` |
| `availability` | `immediate` \| `2_weeks` \| `1_month` \| `negotiable` \| `not_available` |

### MKT_APPLICATION

| Trường | Giá trị |
|---|---|
| `status` | `draft` \| `submitted` \| `viewed` \| `shortlisted` \| `rejected` \| `hired` \| `withdrawn` |
| `import_status` | `not_imported` \| `imported` \| `skipped` |

### MKT_REVIEW

| Trường | Giá trị |
|---|---|
| `reviewer_type` | `org` \| `applicant` |
| `relation_type` | `hired` \| `project_completed` \| `collaboration` |

---

## 5. ERD — Quan hệ bảng

```
[organizations] (existing)           [rc_job_postings] (RC module)
       │  0:N                                │ 0:1 nullable FK
       │  (NULL: guest/individual)           │
       └─────────────┬───────────────────────┘
                     ▼
              MKT_LISTING
   (poster_type: org | guest_company | individual)
                     │
    ┌────────────────┼────────────────────┐
    │                │                    │
   1:N              1:N                  1:N
    ▼                ▼                    ▼
MKT_APPLICATION  MKT_CONVERSATION  MKT_LISTING_BOOKMARK
    │                │
   N:1              1:N
    ▼                ▼
MKT_APPLICANT    MKT_MESSAGE
    │
   1:N──► MKT_APPLICANT_SKILL
   1:N──► MKT_APPLICANT_EXPERIENCE
   1:N──► MKT_APPLICANT_PORTFOLIO

MKT_APPLICATION └─ import_status → rc_candidates (soft ref, no FK)

MKT_LISTING ──N:M──► MKT_TAG  (qua MKT_LISTING_TAG)
MKT_LISTING ──1:N──► MKT_REVIEW
```

### Quan hệ tổng hợp

| Bảng A | Quan hệ | Bảng B | Ghi chú |
|---|---|---|---|
| organizations | 0:N | MKT_LISTING | NULL khi poster_type='guest_company'/'individual' |
| rc_job_postings | 0:1 | MKT_LISTING | Nullable FK — NULL khi listing độc lập |
| MKT_LISTING | 1:N | MKT_APPLICATION | Đơn ứng tuyển |
| MKT_APPLICANT | 1:N | MKT_APPLICATION | Ứng viên apply |
| MKT_APPLICANT | 1:N | MKT_APPLICANT_SKILL | Kỹ năng |
| MKT_APPLICANT | 1:N | MKT_APPLICANT_EXPERIENCE | Kinh nghiệm |
| MKT_APPLICANT | 1:N | MKT_APPLICANT_PORTFOLIO | Portfolio |
| MKT_LISTING | 1:N | MKT_CONVERSATION | Thread chat |
| MKT_CONVERSATION | 1:N | MKT_MESSAGE | Tin nhắn |
| MKT_LISTING | N:M | MKT_TAG | Qua MKT_LISTING_TAG |
| MKT_LISTING | 1:N | MKT_LISTING_BOOKMARK | Lưu bookmark |
| MKT_LISTING | 1:N | MKT_REVIEW | Đánh giá sau hợp tác |

---

## 6. Đặc tả bảng dữ liệu

> **Ghi chú kiểu FK liên module:**
> - FK trỏ sang bảng **hiện có** (`organizations`, `users`, `departments`, `job_titles`) dùng `UNSIGNED BIGINT`
> - `rc_job_posting_id` trỏ sang `rc_job_postings.id` — bảng RC dùng **UUID** làm PK, nên kiểu cột là `CHAR(36)` nullable, không phải BIGINT. Dùng `ON DELETE SET NULL`.
> - `department_id` và `position_id` trỏ sang bảng tenant hiện có (`departments`, `job_titles`) — dùng `UNSIGNED BIGINT` nullable, `ON DELETE SET NULL`
> - Ref lỏng sang RC (`imported_rc_candidate_id`, `imported_rc_application_id`) là `CHAR(36)` thuần, không FK constraint — validate ở app layer khi cần
> - Nội bộ `mkt_*` ↔ `mkt_*` dùng UUID

---

### 6.1 MKT_LISTING — Tin đăng công khai

Bảng trung tâm của Marketplace. Hỗ trợ 3 loại: `job`, `project`, `resource`. Trường `listing_type` quyết định ngữ nghĩa của các trường khác.

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `org_id` | UNSIGNED BIGINT | NULL | FK, INDEX | NULL | FK → organizations.id — NULL khi guest_company hoặc individual |
| `posted_by` | UNSIGNED BIGINT | NOT NULL | FK | | FK → users.id |
| `poster_type` | ENUM | NOT NULL | INDEX | `org` | org \| guest_company \| individual |
| `listing_type` | ENUM | NOT NULL | INDEX | `job` | job \| project \| resource |
| `title` | VARCHAR(300) | NOT NULL | | | |
| `slug` | VARCHAR(320) | NOT NULL | UNIQUE | | URL-friendly, globally unique |
| `description` | TEXT | NOT NULL | | | Mô tả đầy đủ |
| `requirements` | TEXT | NULL | | NULL | Yêu cầu (cho job/project) |
| `benefits` | TEXT | NULL | | NULL | Quyền lợi (cho job) |
| `status` | ENUM | NOT NULL | INDEX | `draft` | Xem enum — thêm `pending_review` |
| `visibility` | ENUM | NOT NULL | | `public` | |
| `work_type` | ENUM | NOT NULL | | `flexible` | |
| `employment_type` | ENUM | NULL | | NULL | Dùng cho listing_type=job |
| `experience_level` | ENUM | NOT NULL | | `any` | |
| `salary_min` | DECIMAL(15,2) | NULL | | NULL | |
| `salary_max` | DECIMAL(15,2) | NULL | | NULL | |
| `salary_currency` | CHAR(3) | NOT NULL | | `VND` | |
| `salary_is_negotiable` | BOOLEAN | NOT NULL | | FALSE | |
| `salary_is_visible` | BOOLEAN | NOT NULL | | TRUE | FALSE = ẩn mức lương |
| `budget_min` | DECIMAL(15,2) | NULL | | NULL | Ngân sách (cho listing_type=project) |
| `budget_max` | DECIMAL(15,2) | NULL | | NULL | |
| `duration_days` | INT | NULL | | NULL | Thời gian dự án (ngày) |
| `location` | VARCHAR(200) | NULL | | NULL | |
| `department_id` | UNSIGNED BIGINT | NULL | FK, INDEX | NULL | FK → departments.id ON DELETE SET NULL — phòng ban tuyển (chỉ dùng khi poster_type='org') |
| `position_id` | UNSIGNED BIGINT | NULL | FK, INDEX | NULL | FK → job_titles.id ON DELETE SET NULL — vị trí cần tuyển (chỉ dùng khi poster_type='org') |
| `headcount` | SMALLINT | NOT NULL | | 1 | Số lượng cần |
| `application_count` | INT | NOT NULL | | 0 | Denormalized counter |
| `view_count` | INT | NOT NULL | | 0 | Denormalized counter |
| `bookmark_count` | INT | NOT NULL | | 0 | Denormalized counter |
| `rc_job_posting_id` | CHAR(36) | NULL | INDEX | NULL | Ref → rc_job_postings.id (UUID) ON DELETE SET NULL — NULL nếu listing không đến từ RC |
| `rc_sync_status` | ENUM | NULL | | NULL | synced \| out_of_sync — NULL khi không đến từ RC |
| `auto_close_on_rc` | BOOLEAN | NOT NULL | | TRUE | Tự đóng khi RC_JOB_POSTING đóng |
| `guest_company_name` | VARCHAR(200) | NULL | | NULL | Tên công ty — khi poster_type='guest_company' |
| `guest_company_email` | VARCHAR(150) | NULL | | NULL | Email liên hệ |
| `guest_company_website` | VARCHAR(300) | NULL | | NULL | |
| `guest_company_logo_url` | TEXT | NULL | | NULL | |
| `expire_at` | TIMESTAMP | NULL | INDEX | NULL | NULL = không hết hạn |
| `closed_at` | TIMESTAMP | NULL | | NULL | |
| `created_at` | TIMESTAMP | NOT NULL | INDEX | NOW() | |
| `updated_at` | TIMESTAMP | NOT NULL | | NOW() | |

```sql
CREATE UNIQUE INDEX idx_mkt_listing_slug      ON mkt_listings(slug);
CREATE INDEX idx_mkt_listing_browse           ON mkt_listings(listing_type, status, created_at DESC)
  WHERE status = 'active';
CREATE INDEX idx_mkt_listing_org              ON mkt_listings(org_id, status);
CREATE INDEX idx_mkt_listing_poster_type      ON mkt_listings(poster_type, status);
CREATE INDEX idx_mkt_listing_department       ON mkt_listings(department_id, status)
  WHERE department_id IS NOT NULL;
CREATE INDEX idx_mkt_listing_position         ON mkt_listings(position_id, status)
  WHERE position_id IS NOT NULL;
CREATE INDEX idx_mkt_listing_rc_source        ON mkt_listings(rc_job_posting_id)
  WHERE rc_job_posting_id IS NOT NULL;
CREATE INDEX idx_mkt_listing_rc_sync          ON mkt_listings(rc_job_posting_id, rc_sync_status)
  WHERE rc_sync_status = 'out_of_sync';
CREATE INDEX idx_mkt_listing_pending_review   ON mkt_listings(poster_type, status)
  WHERE status = 'pending_review';
CREATE INDEX idx_mkt_listing_expire           ON mkt_listings(expire_at, status)
  WHERE expire_at IS NOT NULL AND status = 'active';
CREATE FULLTEXT INDEX idx_mkt_listing_search  ON mkt_listings(title, description, requirements, location);
```

---

### 6.2 ~~MKT_LISTING_SYNC~~ — Đã loại bỏ

> Bảng này đã bị loại bỏ. Trạng thái sync được quản lý trực tiếp qua 3 cột trên `mkt_listings`:
> - `rc_job_posting_id` — nullable FK về RC
> - `rc_sync_status` — `synced | out_of_sync | NULL`
> - `auto_close_on_rc` — boolean
>
> **Observer xử lý tự động:** `RcJobPostingObserver` trong RC module lắng nghe sự kiện `updated`/`saved` trên `rc_job_postings` và cập nhật `mkt_listings` tương ứng. Không cần bảng trung gian trong monolith.

---

### 6.3 MKT_APPLICANT — Hồ sơ công khai ứng viên / freelancer

Profile công khai của cá nhân hoặc team trên Marketplace. Khác với `RC_CANDIDATE` (private, nội bộ) — MKT_APPLICANT là identity công khai, có thể tự tạo mà không cần org invite.

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `account_type` | ENUM | NOT NULL | | `individual` | individual \| team \| agency |
| `display_name` | VARCHAR(150) | NOT NULL | | | Tên hiển thị công khai |
| `slug` | VARCHAR(160) | NOT NULL | UNIQUE | | URL profile: marketplace.com/u/slug |
| `headline` | VARCHAR(200) | NULL | | NULL | "Senior Laravel Developer · 5 năm kinh nghiệm" |
| `bio` | TEXT | NULL | | NULL | Giới thiệu bản thân |
| `email` | VARCHAR(150) | NOT NULL | UNIQUE | | Email đăng nhập + liên hệ (định danh duy nhất) |
| `password_hash` | VARCHAR(255) | NULL | | NULL | Mật khẩu bcrypt — NULL nếu đăng ký qua OAuth (tính năng tương lai) |
| `email_verified_at` | TIMESTAMP | NULL | | NULL | Thời điểm xác minh email — NULL = chưa xác minh |
| `phone` | VARCHAR(20) | NULL | | NULL | |
| `location` | VARCHAR(150) | NULL | | NULL | Thành phố / Quốc gia |
| `avatar_url` | TEXT | NULL | | NULL | |
| `website_url` | VARCHAR(300) | NULL | | NULL | |
| `linkedin_url` | VARCHAR(300) | NULL | | NULL | |
| `github_url` | VARCHAR(300) | NULL | | NULL | |
| `years_experience` | SMALLINT | NULL | | NULL | |
| `expected_salary_min` | DECIMAL(15,2) | NULL | | NULL | |
| `expected_salary_max` | DECIMAL(15,2) | NULL | | NULL | |
| `salary_currency` | CHAR(3) | NOT NULL | | `VND` | |
| `status` | ENUM | NOT NULL | INDEX | `active` | |
| `availability` | ENUM | NOT NULL | | `negotiable` | |
| `is_email_public` | BOOLEAN | NOT NULL | | FALSE | |
| `is_phone_public` | BOOLEAN | NOT NULL | | FALSE | |
| `profile_complete_pct` | SMALLINT | NOT NULL | | 0 | % hoàn thiện profile (denormalized) |
| `total_applications` | INT | NOT NULL | | 0 | Denormalized counter |
| `hired_count` | INT | NOT NULL | | 0 | Đã được tuyển/hợp tác thành công |
| `avg_rating` | DECIMAL(3,2) | NULL | | NULL | Điểm đánh giá trung bình |
| `created_at` | TIMESTAMP | NOT NULL | | NOW() | |
| `updated_at` | TIMESTAMP | NOT NULL | | NOW() | |

```sql
CREATE UNIQUE INDEX idx_mkt_appl_slug     ON mkt_applicants(slug);
CREATE UNIQUE INDEX idx_mkt_appl_email    ON mkt_applicants(email);
CREATE INDEX idx_mkt_appl_status          ON mkt_applicants(status, availability);
CREATE FULLTEXT INDEX idx_mkt_appl_search ON mkt_applicants(display_name, headline, bio, location);
```

---

### 6.4 MKT_APPLICANT_SKILL — Kỹ năng ứng viên

Tách thành bảng riêng (không TEXT comma-separated) để query theo skill được, filter, analytics.

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `applicant_id` | UUID | NOT NULL | FK, INDEX | | |
| `skill_name` | VARCHAR(100) | NOT NULL | INDEX | | "Laravel", "React", "Figma" |
| `proficiency_level` | ENUM | NOT NULL | | `intermediate` | beginner \| intermediate \| advanced \| expert |
| `years_used` | SMALLINT | NULL | | NULL | Số năm dùng kỹ năng này |
| `sort_order` | SMALLINT | NOT NULL | | 0 | |

```sql
CREATE UNIQUE INDEX idx_mkt_skill_unique ON mkt_applicant_skills(applicant_id, skill_name);
CREATE INDEX idx_mkt_skill_name          ON mkt_applicant_skills(skill_name, proficiency_level);
```

---

### 6.5 MKT_APPLICANT_EXPERIENCE — Kinh nghiệm làm việc

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `applicant_id` | UUID | NOT NULL | FK, INDEX | | |
| `company_name` | VARCHAR(200) | NOT NULL | | | |
| `title` | VARCHAR(150) | NOT NULL | | | |
| `description` | TEXT | NULL | | NULL | |
| `start_month` | SMALLINT | NOT NULL | | | 1–12 |
| `start_year` | SMALLINT | NOT NULL | | | |
| `end_month` | SMALLINT | NULL | | NULL | NULL = hiện tại |
| `end_year` | SMALLINT | NULL | | NULL | NULL = hiện tại |
| `is_current` | BOOLEAN | NOT NULL | | FALSE | |
| `sort_order` | SMALLINT | NOT NULL | | 0 | |

```sql
CREATE INDEX idx_mkt_exp_applicant ON mkt_applicant_experiences(applicant_id, start_year DESC);
```

---

### 6.6 MKT_APPLICANT_PORTFOLIO — Portfolio / dự án nổi bật

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `applicant_id` | UUID | NOT NULL | FK, INDEX | | |
| `title` | VARCHAR(200) | NOT NULL | | | |
| `description` | TEXT | NULL | | NULL | |
| `project_url` | VARCHAR(300) | NULL | | NULL | |
| `thumbnail_url` | TEXT | NULL | | NULL | |
| `tech_stack` | VARCHAR(300) | NULL | | NULL | "Laravel, React, MySQL" — plain text |
| `completed_year` | SMALLINT | NULL | | NULL | |
| `sort_order` | SMALLINT | NOT NULL | | 0 | |

```sql
CREATE INDEX idx_mkt_portfolio_applicant ON mkt_applicant_portfolios(applicant_id, sort_order);
```

---

### 6.7 MKT_APPLICATION — Đơn ứng tuyển qua Marketplace

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `listing_id` | UUID | NOT NULL | FK, INDEX | | FK → MKT_LISTING.id |
| `applicant_id` | UUID | NOT NULL | FK, INDEX | | FK → MKT_APPLICANT.id |
| `status` | ENUM | NOT NULL | INDEX | `submitted` | |
| `cover_letter` | TEXT | NULL | | NULL | |
| `expected_salary` | DECIMAL(15,2) | NULL | | NULL | |
| `available_from` | DATE | NULL | | NULL | Có thể bắt đầu từ ngày |
| `portfolio_url` | VARCHAR(300) | NULL | | NULL | Link portfolio đính kèm đơn này |
| `import_status` | ENUM | NOT NULL | | `not_imported` | not_imported \| imported \| skipped |
| `imported_rc_candidate_id` | UUID | NULL | | NULL | Ref to RC_CANDIDATE.id sau khi import — không FK cứng |
| `imported_rc_application_id` | UUID | NULL | | NULL | Ref to RC_APPLICATION.id |
| `imported_at` | TIMESTAMP | NULL | | NULL | |
| `imported_by` | UNSIGNED BIGINT | NULL | FK | NULL | FK → users.id |
| `viewed_at` | TIMESTAMP | NULL | | NULL | Org xem lần đầu |
| `applied_at` | TIMESTAMP | NOT NULL | INDEX | NOW() | |
| `updated_at` | TIMESTAMP | NOT NULL | | NOW() | |

```sql
CREATE UNIQUE INDEX idx_mkt_app_unique   ON mkt_applications(listing_id, applicant_id);
CREATE INDEX idx_mkt_app_listing         ON mkt_applications(listing_id, status);
CREATE INDEX idx_mkt_app_applicant       ON mkt_applications(applicant_id, status);
CREATE INDEX idx_mkt_app_import          ON mkt_applications(import_status, listing_id)
  WHERE import_status = 'not_imported';
```

---

### ~~6.8 MKT_CONVERSATION~~ + ~~6.9 MKT_MESSAGE~~ — Đã loại khỏi scope

> **Messaging real-time bị loại bỏ.** `mkt_conversations` và `mkt_messages` không được implement trong phiên bản hiện tại.
>
> **Lý do:** Tính năng này đòi hỏi WebSocket infrastructure (Laravel Echo + Pusher/Soketi) làm tăng đáng kể độ phức tạp vận hành mà chưa cần thiết ở giai đoạn đầu.
>
> **Thay thế:** Org liên hệ ứng viên qua email (địa chỉ email hiển thị trong profile/application). Có thể bổ sung messaging sau khi các tính năng cốt lõi (listing, apply, review) đã ổn định.
>
> **Schema dự phòng:** Giữ thiết kế bảng trong tài liệu này để tham chiếu khi implement sau.
```

---

### 6.10 MKT_TAG — Nhãn phân loại listing

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `name` | VARCHAR(80) | NOT NULL | UNIQUE | | "Laravel", "UI/UX", "Kế toán" |
| `slug` | VARCHAR(90) | NOT NULL | UNIQUE | | |
| `listing_type` | ENUM | NULL | INDEX | NULL | NULL = dùng cho mọi loại listing |
| `use_count` | INT | NOT NULL | | 0 | Denormalized — số listing đang dùng tag |

### 6.11 MKT_LISTING_TAG — Pivot listing–tag

| Trường | Kiểu | Key | Mô tả |
|---|---|---|---|
| `listing_id` | UUID | PK, FK | CASCADE DELETE |
| `tag_id` | UUID | PK, FK | |

```sql
CREATE INDEX idx_mkt_ltag_tag ON mkt_listing_tags(tag_id);
```

---

### 6.12 MKT_LISTING_BOOKMARK — Lưu tin đăng

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `listing_id` | UUID | NOT NULL | FK, INDEX | | |
| `applicant_id` | UUID | NOT NULL | FK | | |
| `note` | VARCHAR(300) | NULL | | NULL | Ghi chú cá nhân |
| `created_at` | TIMESTAMP | NOT NULL | | NOW() | |

```sql
CREATE UNIQUE INDEX idx_mkt_bookmark_unique ON mkt_listing_bookmarks(listing_id, applicant_id);
CREATE INDEX idx_mkt_bookmark_applicant     ON mkt_listing_bookmarks(applicant_id, created_at DESC);
```

---

### 6.13 MKT_REVIEW — Đánh giá sau hợp tác

Đánh giá hai chiều: org đánh giá applicant và ngược lại, sau khi hợp tác thành công.

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `listing_id` | UUID | NOT NULL | FK, INDEX | | |
| `application_id` | UUID | NOT NULL | FK | | FK → MKT_APPLICATION.id |
| `reviewer_type` | ENUM | NOT NULL | | | org \| applicant |
| `reviewer_id` | UUID | NOT NULL | | | users.id hoặc mkt_applicants.id |
| `reviewee_id` | UUID | NOT NULL | | | Người được review |
| `relation_type` | ENUM | NOT NULL | | `hired` | hired \| project_completed \| collaboration |
| `overall_rating` | SMALLINT | NOT NULL | | | 1–5 |
| `title` | VARCHAR(200) | NULL | | NULL | Tiêu đề review |
| `content` | TEXT | NULL | | NULL | Nội dung |
| `rating_quality` | SMALLINT | NULL | | NULL | Chất lượng công việc (1–5) |
| `rating_communication` | SMALLINT | NULL | | NULL | Giao tiếp (1–5) |
| `rating_punctuality` | SMALLINT | NULL | | NULL | Đúng hạn (1–5) |
| `is_public` | BOOLEAN | NOT NULL | | TRUE | |
| `created_at` | TIMESTAMP | NOT NULL | INDEX | NOW() | |

```sql
CREATE UNIQUE INDEX idx_mkt_review_unique ON mkt_reviews(application_id, reviewer_type, reviewer_id);
CREATE INDEX idx_mkt_review_reviewee      ON mkt_reviews(reviewee_id, overall_rating);
```

---

## 7. Luồng nghiệp vụ

### 7.1 Publish job từ Recruitment ra Marketplace

```
Điều kiện:
  RC_JOB_POSTING.status = 'open'
  RC_JOB_POSTING.is_public = TRUE
  Chưa có mkt_listing với rc_job_posting_id = job.id (chưa publish lần nào)

HR click "Đăng ra Marketplace":
  INSERT mkt_listings:
    poster_type        = 'org'
    org_id             = job.org_id
    posted_by          = current_user.id
    listing_type       = 'job'
    title              = job.title           ← copy tại thời điểm publish
    description        = job.description
    requirements       = job.requirements
    benefits           = job.benefits
    salary_min/max     = job.salary_min/max
    employment_type    = job.employment_type
    headcount          = job.headcount
    expire_at          = job.close_date
    rc_job_posting_id  = job.id
    rc_sync_status     = 'synced'
    auto_close_on_rc   = TRUE
    status             = 'active'

  → Trả về mkt_listing.slug để HR preview
```

### 7.2 Re-sync và tự đóng listing — do Observer

```
RcJobPostingObserver::updated($job):

  1. Nếu job.isDirty(['title','description','requirements','benefits',
                       'salary_min','salary_max','close_date','headcount']):
       UPDATE mkt_listings
          SET rc_sync_status = 'out_of_sync'
        WHERE rc_job_posting_id = job.id
          AND rc_sync_status = 'synced'
       → Hệ thống hiện badge "⚠ Listing đang lệch với job gốc" trên dashboard

  2. Nếu job.isDirty('status') AND job.status IN ('closed','cancelled'):
       UPDATE mkt_listings
          SET status = 'closed', closed_at = NOW()
        WHERE rc_job_posting_id = job.id
          AND auto_close_on_rc = TRUE
          AND status = 'active'

HR click "Re-sync" trên dashboard:
  UPDATE mkt_listings SET
    title = job.title, description = job.description, ...
    rc_sync_status = 'synced'
  WHERE rc_job_posting_id = job.id
```

### 7.3 Ứng viên ngoài apply qua Marketplace

```
Người dùng tìm thấy MKT_LISTING trên Marketplace
  │
  ├─ Chưa có profile: Đăng ký → INSERT MKT_APPLICANT
  └─ Đã có profile: Đăng nhập

Apply:
  1. Kiểm tra: đã apply listing này chưa?
     (UNIQUE INDEX listing_id + applicant_id)
  2. INSERT MKT_APPLICATION:
       listing_id   = listing.id
       applicant_id = applicant.id
       status       = 'submitted'
  3. UPDATE mkt_listings.application_count += 1
  4. Notify org: "Có ứng viên mới từ Marketplace"
  5. INSERT MKT_CONVERSATION (nếu chưa có)
```

### 7.4 Org xem xét và import ứng viên vào Recruitment

```
HR xem danh sách MKT_APPLICATION của listing
  │
  ├─ Không quan tâm:
  │   UPDATE application.status = 'rejected'
  │
  └─ Quan tâm → Import vào Recruitment:
        1. Tìm RC_CANDIDATE theo email:
             Đã có → dùng lại
             Chưa có → INSERT RC_CANDIDATE từ MKT_APPLICANT data
        2. Tìm RC_JOB_POSTING qua mkt_listing.rc_job_posting_id:
             Guard: rc_job_posting_id IS NOT NULL (chỉ import được nếu listing đến từ RC)
        3. INSERT RC_APPLICATION:
             candidate_id = rc_candidate.id
             job_id       = rc_job_posting.id
             apply_source = 'marketplace'
        4. UPDATE MKT_APPLICATION:
             import_status            = 'imported'
             imported_rc_candidate_id = rc_candidate.id
             imported_rc_application_id = rc_application.id
             imported_at              = NOW()
        5. Tiếp tục xử lý trong Recruitment pipeline
```

### 7.5 Freelancer / Individual đăng profile tìm dự án

```
Freelancer đăng ký tài khoản → INSERT users (không có organization_id)
Tạo MKT_APPLICANT profile:
  ├─ Thêm MKT_APPLICANT_SKILL
  ├─ Thêm MKT_APPLICANT_EXPERIENCE
  └─ Thêm MKT_APPLICANT_PORTFOLIO

Đăng resource listing:
  INSERT MKT_LISTING:
    poster_type  = 'individual'
    listing_type = 'resource'
    org_id       = NULL
    posted_by    = user.id
    status       = 'active'  -- không cần duyệt (individual)

Org tìm freelancer:
  Search MKT_LISTING (listing_type='resource') + MKT_APPLICANT profile
  → Liên hệ qua MKT_CONVERSATION
  → KHÔNG qua Recruitment pipeline (đây là outsource, không hire full-time)
```

### 7.6 Doanh nghiệp chưa xác thực đăng tin (guest_company)

```
Doanh nghiệp chưa đăng ký tenant:
  1. Đăng ký tài khoản Marketplace → INSERT users (organization_id = NULL)
  2. INSERT mkt_listings:
       poster_type         = 'guest_company'
       org_id              = NULL
       posted_by           = user.id
       listing_type        = 'job' | 'project'
       guest_company_name  = "Công ty ABC"  ← bắt buộc
       guest_company_email = "hr@abc.com"   ← bắt buộc
       guest_company_website, guest_company_logo_url ← optional
       status              = 'pending_review'  ← chờ admin duyệt

  3. Admin duyệt → status = 'active'
     Admin từ chối → status = 'closed', gửi email lý do

Upgrade guest → tenant (sau khi đăng ký đầy đủ):
  UPDATE mkt_listings
     SET org_id = new_org.id,
         poster_type = 'org',
         guest_company_* = NULL
   WHERE posted_by = user.id
     AND poster_type = 'guest_company'
```

### 7.7 Luồng review sau hợp tác

```
Khi MKT_APPLICATION.status = 'hired' hoặc project hoàn thành:
  Hệ thống mở khóa tính năng review cho cả 2 bên

Org review applicant:
  INSERT MKT_REVIEW (reviewer_type='org', reviewee_id=applicant.id)
  UPDATE MKT_APPLICANT.avg_rating = (tính lại)
  UPDATE MKT_APPLICANT.hired_count += 1

Applicant review org:
  INSERT MKT_REVIEW (reviewer_type='applicant', reviewee_id=org_user.id)
```

---

## 8. Query Patterns

### 8.1 Browse listings (trang chính Marketplace)

```sql
SELECT
    l.id, l.slug, l.title, l.listing_type,
    l.poster_type,
    l.work_type, l.employment_type, l.experience_level,
    l.salary_min, l.salary_max, l.salary_is_visible,
    l.location, l.application_count, l.view_count,
    l.created_at,
    -- Tên công ty: ưu tiên org name, fallback về guest_company_name
    COALESCE(o.name, l.guest_company_name)     AS company_name,
    COALESCE(o.logo_url, l.guest_company_logo_url) AS company_logo
FROM mkt_listings l
LEFT JOIN organizations o ON o.id = l.org_id  -- LEFT JOIN vì org_id nullable
WHERE l.status      = 'active'
  AND l.visibility  = 'public'
  AND (:type IS NULL OR l.listing_type = :type)
  AND (:location IS NULL OR l.location ILIKE '%'||:location||'%')
  AND (:level IS NULL OR l.experience_level = :level)
  AND (:work_type IS NULL OR l.work_type = :work_type)
ORDER BY l.created_at DESC
LIMIT 20 OFFSET :offset;
```

### 8.2 Tìm kiếm full-text + filter tag

```sql
SELECT l.id, l.slug, l.title, l.listing_type,
       ts_rank(to_tsvector(l.title||' '||l.description), plainto_tsquery(:q)) AS rank
FROM mkt_listings l
JOIN mkt_listing_tags lt ON lt.listing_id = l.id
JOIN mkt_tags t          ON t.id = lt.tag_id
WHERE l.status = 'active'
  AND (:q IS NULL OR to_tsvector(l.title||' '||l.description) @@ plainto_tsquery(:q))
  AND (:tag IS NULL OR t.slug = :tag)
ORDER BY rank DESC, l.created_at DESC;
```

### 8.3 Dashboard org — tổng hợp listings đang active

```sql
SELECT
    l.id, l.title, l.listing_type, l.status,
    l.poster_type,
    l.application_count, l.view_count,
    COUNT(a.id) FILTER (WHERE a.status = 'submitted')       AS new_applications,
    COUNT(a.id) FILTER (WHERE a.import_status = 'imported') AS imported,
    l.rc_sync_status,
    l.rc_job_posting_id IS NOT NULL                         AS from_recruitment
FROM mkt_listings l
LEFT JOIN mkt_applications a ON a.listing_id = l.id
WHERE l.org_id = :org_id
GROUP BY l.id
ORDER BY l.created_at DESC;
```

### 8.4 Ứng viên chưa được import (pending review)

```sql
SELECT
    a.id            AS application_id,
    a.status,
    a.applied_at,
    ap.display_name,
    ap.headline,
    ap.avg_rating,
    ap.hired_count,
    l.title         AS listing_title
FROM mkt_applications a
JOIN mkt_applicants ap  ON ap.id = a.applicant_id
JOIN mkt_listings l     ON l.id  = a.listing_id
WHERE l.org_id         = :org_id
  AND a.import_status  = 'not_imported'
  AND a.status         NOT IN ('rejected', 'withdrawn')
ORDER BY a.applied_at DESC;
```

### 8.5 Matching freelancer theo skill (tìm resource)

```sql
SELECT
    ap.id, ap.display_name, ap.slug, ap.headline,
    ap.avg_rating, ap.hired_count, ap.availability,
    ap.location,
    STRING_AGG(s.skill_name, ', ' ORDER BY s.sort_order) AS skills
FROM mkt_applicants ap
JOIN mkt_applicant_skills s ON s.applicant_id = ap.id
WHERE ap.status      = 'active'
  AND ap.availability != 'not_available'
  AND s.skill_name   IN (:skill1, :skill2, :skill3)
GROUP BY ap.id, ap.display_name, ap.slug, ap.headline,
         ap.avg_rating, ap.hired_count, ap.availability, ap.location
HAVING COUNT(DISTINCT s.skill_name) >= :min_skill_match
ORDER BY ap.avg_rating DESC NULLS LAST, ap.hired_count DESC;
```

### 8.6 Analytics listing — conversion funnel

```sql
SELECT
    l.title,
    l.view_count,
    l.application_count,
    COUNT(a.id) FILTER (WHERE a.status = 'shortlisted')  AS shortlisted,
    COUNT(a.id) FILTER (WHERE a.status = 'hired')         AS hired,
    ROUND(l.application_count * 100.0 / NULLIF(l.view_count, 0), 1) AS apply_rate_pct,
    ROUND(
      COUNT(a.id) FILTER (WHERE a.status = 'hired') * 100.0
      / NULLIF(l.application_count, 0), 1
    )                                                      AS hire_rate_pct
FROM mkt_listings l
LEFT JOIN mkt_applications a ON a.listing_id = l.id
WHERE l.org_id = :org_id
  AND l.created_at BETWEEN :start AND :end
GROUP BY l.id, l.title, l.view_count, l.application_count
ORDER BY l.created_at DESC;
```

---

## 9. API Endpoints

### Public (không cần auth)

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/marketplace/listings` | Browse listings (filter, search, paginate) |
| GET | `/api/marketplace/listings/:slug` | Chi tiết listing |
| GET | `/api/marketplace/listings/:slug/similar` | Listing tương tự |
| GET | `/api/marketplace/profiles/:slug` | Profile applicant/freelancer |
| GET | `/api/marketplace/tags` | Danh sách tags phổ biến |

### Applicant (auth required)

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/marketplace/me/profile` | Profile của tôi |
| PUT | `/api/marketplace/me/profile` | Cập nhật profile |
| POST | `/api/marketplace/me/skills` | Thêm kỹ năng |
| DELETE | `/api/marketplace/me/skills/:id` | Xóa kỹ năng |
| POST | `/api/marketplace/me/experiences` | Thêm kinh nghiệm |
| POST | `/api/marketplace/me/portfolios` | Thêm portfolio |
| GET | `/api/marketplace/me/applications` | Danh sách đơn tôi đã nộp |
| POST | `/api/marketplace/listings/:slug/apply` | Nộp đơn |
| POST | `/api/marketplace/listings/:slug/bookmark` | Bookmark |
| DELETE | `/api/marketplace/listings/:slug/bookmark` | Xóa bookmark |
| GET | `/api/marketplace/me/bookmarks` | Danh sách bookmark |
| ~~GET~~ | ~~`/api/marketplace/me/conversations`~~ | ~~Inbox~~ — deferred |
| ~~POST~~ | ~~`/api/marketplace/conversations/:id/messages`~~ | ~~Gửi tin nhắn~~ — deferred |

### Org / Guest Company (auth required)

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/marketplace/org/listings` | Quản lý listings của org |
| POST | `/api/marketplace/org/listings` | Tạo listing trực tiếp (không từ RC) |
| PUT | `/api/marketplace/org/listings/:id` | Cập nhật |
| POST | `/api/marketplace/org/listings/:id/close` | Đóng listing |
| POST | `/api/marketplace/org/listings/publish-from-rc` | Publish job từ Recruitment → Marketplace |
| POST | `/api/marketplace/org/listings/:id/resync` | Re-sync listing đang out_of_sync với RC |
| GET | `/api/marketplace/org/listings/:id/applications` | Xem ứng viên |
| POST | `/api/marketplace/org/applications/:id/shortlist` | Shortlist |
| POST | `/api/marketplace/org/applications/:id/reject` | Từ chối |
| POST | `/api/marketplace/org/applications/:id/import` | Import vào Recruitment |
| POST | `/api/marketplace/org/applications/import-bulk` | Import nhiều ứng viên |
| GET | `/api/marketplace/org/analytics` | Analytics: view, apply, conversion |

### Guest Company (auth required, poster_type='guest_company')

| Method | Endpoint | Mô tả |
|---|---|---|
| POST | `/api/marketplace/guest/listings` | Đăng tin (status→pending_review) |
| PUT | `/api/marketplace/guest/listings/:id` | Cập nhật (chỉ khi pending hoặc paused) |
| GET | `/api/marketplace/guest/listings` | Listings của mình |

### Admin

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/marketplace/admin/pending-listings` | Danh sách listing chờ duyệt |
| POST | `/api/marketplace/admin/listings/:id/approve` | Duyệt → active |
| POST | `/api/marketplace/admin/listings/:id/reject` | Từ chối + lý do |

### Reviews

| Method | Endpoint | Mô tả |
|---|---|---|
| POST | `/api/marketplace/applications/:id/review` | Gửi review |
| GET | `/api/marketplace/profiles/:slug/reviews` | Xem reviews của profile |

---

## 10. Business Rules

### BR-MKT-001: Listing — poster_type và org_id
- `poster_type = 'org'`: `org_id NOT NULL`, là tenant hợp lệ — listing active ngay
- `poster_type = 'guest_company'`: `org_id = NULL`, `guest_company_name + guest_company_email` bắt buộc — listing vào `pending_review`
- `poster_type = 'individual'`: `org_id = NULL`, `listing_type` phải là `'resource'` — listing active ngay
- Mỗi `rc_job_posting_id` chỉ liên kết 1 `mkt_listing` active tại 1 thời điểm — enforce bằng partial unique index
- Publish từ RC: chỉ khi `rc_job_postings.status = 'open'` và `is_public = TRUE`
- Re-sync: chỉ overwrite trường nội dung, không reset `application_count` / `view_count`
- Org tạo listing trực tiếp (không từ RC): `rc_job_posting_id = NULL`, `rc_sync_status = NULL`

### BR-MKT-002: Applicant profile
- `slug` là unique globally — tự sinh từ `display_name`, thêm suffix random nếu trùng
- `email` là định danh đăng nhập Marketplace — unique trong `mkt_applicants`
- `profile_complete_pct` tính bằng Observer sau mỗi lần update, không tính mỗi request

### BR-MKT-003: Application
- 1 applicant chỉ apply 1 lần per listing — enforce bằng unique index
- Không apply listing đã `closed` hoặc `expired`
- Rút đơn (`withdrawn`): chỉ khi status còn `submitted` hoặc `viewed`
- Import vào RC là idempotent — nếu đã `imported`, không import lại

### ~~BR-MKT-004: Messaging~~ — Deferred (xem mục "Ngoài phạm vi")

### BR-MKT-005: Review
- Chỉ cho phép review khi `MKT_APPLICATION.status = 'hired'` hoặc project đánh dấu completed
- Mỗi bên chỉ review 1 lần per application
- Review không thể xóa sau khi published — chỉ ẩn bởi Admin

### BR-MKT-006: Quan hệ với Recruitment
- `mkt_listings.rc_job_posting_id` là nullable FK với `ON DELETE SET NULL` — nếu RC job bị xóa, listing vẫn tồn tại, `rc_job_posting_id` trở thành NULL, `rc_sync_status` trở thành NULL
- `mkt_applications.imported_rc_candidate_id` và `imported_rc_application_id` là UUID thuần, không FK — validate ở app layer khi cần
- Import vào RC chỉ được khi `mkt_listing.rc_job_posting_id IS NOT NULL`
- Xóa RC job không kéo theo xóa MKT listing — listing trở thành listing độc lập

---

## 11. Indexes & Caching

```sql
-- Browse public listings (hot path nhất)
CREATE INDEX idx_mkt_browse_main
  ON mkt_listings(status, listing_type, created_at DESC)
  WHERE status = 'active' AND visibility = 'public';

-- Filter theo salary range
CREATE INDEX idx_mkt_salary
  ON mkt_listings(salary_min, salary_max, status)
  WHERE status = 'active' AND salary_min IS NOT NULL;

-- Org dashboard: listings chưa xử lý
CREATE INDEX idx_mkt_org_pending
  ON mkt_applications(listing_id, import_status, applied_at DESC)
  WHERE import_status = 'not_imported';

-- Freelancer matching theo skill
CREATE INDEX idx_mkt_skill_match
  ON mkt_applicant_skills(skill_name, proficiency_level);

-- Listing out-of-sync (badge cảnh báo trên dashboard RC)
CREATE INDEX idx_mkt_listing_outofsync
  ON mkt_listings(org_id, rc_sync_status)
  WHERE rc_sync_status = 'out_of_sync';

-- Guest company listings chờ duyệt (admin queue)
CREATE INDEX idx_mkt_listing_pending_review
  ON mkt_listings(poster_type, status, created_at)
  WHERE status = 'pending_review';

-- Đảm bảo mỗi rc_job_posting_id chỉ có 1 listing active
CREATE UNIQUE INDEX idx_mkt_listing_rc_unique_active
  ON mkt_listings(rc_job_posting_id)
  WHERE rc_job_posting_id IS NOT NULL AND status != 'closed';
```

### Caching

| Cache key | TTL | Invalidate khi |
|---|---|---|
| `mkt:listings:browse:{hash_params}` | 2 phút | Listing mới, status change |
| `mkt:listing:{slug}` | 5 phút | Cập nhật listing |
| `mkt:profile:{slug}` | 10 phút | Cập nhật applicant profile |
| `mkt:tags:popular` | 30 phút | use_count thay đổi |
| `mkt:org:{id}:dashboard` | 3 phút | Apply mới, status change |

---

## 12. Lộ trình triển khai

### Phase 1 — Listing & Public Browse (tuần 1–2)

> Mục tiêu: Tenant đăng tin tuyển dụng, cổng thông tin hiển thị công khai.

- [ ] Migration: `mkt_listings` (bao gồm `department_id`, `position_id` BIGINT FK mới; `rc_job_posting_id` CHAR(36)) + `mkt_tags` + `mkt_listing_tags`
- [ ] **Tenant dashboard** — Org đăng tin trực tiếp (poster_type='org', active ngay):
  - Tạo/sửa/đóng/tạm dừng listing
  - Chọn department và position từ dữ liệu org hiện có
  - Re-sync badge: cảnh báo `rc_sync_status = 'out_of_sync'`
- [ ] **Guest company flow** — DN chưa xác thực (poster_type='guest_company'):
  - Đăng tin → status `pending_review` → Admin duyệt
  - Bắt buộc: `guest_company_name` + `guest_company_email`
- [ ] **Public browse API** (không cần auth):
  - `GET /api/portal/listings` — filter: type, location, employment_type, salary, experience_level
  - `GET /api/portal/listings/:slug` — chi tiết + company info (LEFT JOIN organizations, COALESCE guest_company_name)
  - `GET /api/portal/tags` — danh sách tags
- [ ] Admin panel: duyệt/từ chối `pending_review` listings
- [ ] Sidebar: section **"Marketplace"** gồm: Quản lý tin đăng, Ứng viên

### Phase 2 — Applicant Auth & Apply (tuần 3–4)

> Mục tiêu: Ứng viên tạo profile, ứng tuyển, org xem và quản lý ứng viên.

- [ ] Migration: `mkt_applicants` (với `password_hash`, `email_verified_at` — auth tách biệt `users` table), `mkt_applicant_skills`, `mkt_applicant_experiences`, `mkt_applicant_portfolios`
- [ ] Migration: `mkt_applications`, `mkt_listing_bookmarks`
- [ ] **Marketplace guard** — Laravel auth guard riêng (`marketplace`), route group `/portal/`
  - Đăng ký: email + password → `email_verified_at` flow
  - Đăng nhập: email + password
- [ ] **Applicant profile**: tạo/sửa profile công khai, thêm skill/experience/portfolio
- [ ] **Apply flow**:
  - Check UNIQUE (listing_id, applicant_id) — BR-MKT-003
  - INSERT `mkt_applications` + UPDATE `application_count`
  - Guard: không apply listing đã `closed/expired`
- [ ] Bookmark listing (`mkt_listing_bookmarks`)
- [ ] **Org dashboard — quản lý ứng viên**:
  - Danh sách `mkt_applications` của listing: xem, shortlist, reject
  - Xem profile applicant
  - **Import vào Recruitment pipeline** (khi `mkt_listing.rc_job_posting_id IS NOT NULL`):
    - Tìm/tạo `RC_CANDIDATE` theo email
    - INSERT `RC_APPLICATION` với `apply_source='marketplace'`
    - UPDATE `mkt_application.import_status='imported'`

### Phase 3 — Review, Analytics & Polish (tuần 5–6)

> Mục tiêu: Đánh giá sau hợp tác, analytics hiệu quả listing, listing expiry.

- [ ] Migration: `mkt_reviews`
- [ ] Review flow: mở khóa khi `application.status = 'hired'` — cả 2 bên review 1 lần per application
- [ ] UPDATE `mkt_applicants.avg_rating` + `hired_count` sau khi review (Observer)
- [ ] **Analytics per listing** (org dashboard):
  - View count, apply rate, shortlist rate, hire rate
  - Conversion funnel: views → apply → shortlist → hired
- [ ] Listing expiry cron: quét `expire_at < NOW()` và `status='active'` → `expired`
- [ ] Trending tags, top listings
- [ ] Re-sync badge: `rc_sync_status = 'out_of_sync'` → notify HR trên dashboard Recruitment

### ~~Phase 4 — Messaging~~ — Deferred

> `mkt_conversations` + `mkt_messages` + real-time (Echo/Pusher) bị loại khỏi scope hiện tại.
> Giao tiếp org ↔ ứng viên thực hiện qua email ngoài hệ thống.
> Bổ sung sau khi các tính năng cốt lõi (listing, apply, review, analytics) đã vận hành ổn định.

---

*Version 1.3.0 — Marketplace Center Module Specification*
*Liên module: Recruitment Center (optional upstream, qua nullable CHAR(36) FK + Observer), Workforce Center (downstream)*
*Stack: Laravel 13 · SQLite (dev) / MySQL 8+ / PostgreSQL 15+*
*Thay đổi 1.3.0: loại bỏ messaging real-time (mkt_conversations, mkt_messages) khỏi scope — deferred; giảm từ 4 phases → 3 phases (2026-06-05)*