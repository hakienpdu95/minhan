# Đặc Tả Module: Recruitment Center

> **Hệ thống:** SaaS SME
> **Module:** Recruitment Center
> **Phiên bản:** 1.1.0
> **Ngày:** 2026-06-05
> **Stack:** Laravel 13 · SQLite (dev) / MySQL 8+ / PostgreSQL 15+
> **Liên module:** Marketplace Center (downstream — publish job ra cổng công khai)

---

## Mục lục

1. [Tổng quan](#1-tổng-quan)
2. [Phạm vi](#2-phạm-vi)
3. [Kiến trúc tổng thể](#3-kiến-trúc-tổng-thể)
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

## 1. Tổng quan

**Recruitment Center** quản lý toàn bộ vòng đời tuyển dụng trong doanh nghiệp SME — từ đăng tin tuyển dụng, tiếp nhận hồ sơ ứng viên, điều phối phỏng vấn, đánh giá, đến phát offer và onboarding handoff sang Workforce Center.

### 4 trục cốt lõi

| Trục | Chức năng |
|---|---|
| **Job Posting** | Tạo và quản lý tin tuyển dụng, liên kết với vị trí và phòng ban |
| **Candidate Management** | Hồ sơ ứng viên tập trung, nguồn ứng tuyển, notes và attachments |
| **Application Pipeline** | Pipeline stages tùy chỉnh, di chuyển ứng viên qua các vòng |
| **Interview & Evaluation** | Lên lịch phỏng vấn, panel đánh giá, điểm theo tiêu chí, offer |

### Người dùng

| Vai trò | Quyền |
|---|---|
| **HR Admin** | Toàn quyền: cấu hình pipeline, quản lý tất cả job và ứng viên |
| **Hiring Manager** | Tạo job request, xem ứng viên của job mình, duyệt offer |
| **Recruiter** | Xử lý pipeline, lên lịch phỏng vấn, ghi chú, gửi offer |
| **Interviewer** | Xem thông tin ứng viên được assign, nộp đánh giá |

### Quan hệ với Marketplace Center

**Recruitment** là ATS (Applicant Tracking System) **nội bộ** của tổ chức. **Marketplace** là cổng thông tin tuyển dụng **công khai** — nơi tin đăng được phân phối ra bên ngoài. Hai module độc lập về mục đích, kết nối qua nullable FK.

| Khía cạnh | Recruitment Center | Marketplace Center |
|---|---|---|
| Người dùng chính | HR, Hiring Manager (nội bộ, đã xác thực) | Ứng viên bên ngoài (public) |
| Dữ liệu ứng viên | `RC_CANDIDATE` — private, scoped per org | `MKT_APPLICANT` — public profile |
| Tin tuyển dụng | `RC_JOB_POSTING` — quản lý nội bộ | `MKT_LISTING` — hiển thị công khai |
| Mục đích | Pipeline, phỏng vấn, offer, onboarding handoff | Kết nối DN và ứng viên ngoài hệ thống |
| Bắt buộc | ✅ Khi cần ATS nội bộ | ✅ Khi cần đăng tin ra ngoài |

**Kết nối (cả hai đều optional):**
- **RC → MKT:** HR click "Đăng ra Marketplace" → `INSERT mkt_listings` với `rc_job_posting_id = job.id`. `RcJobPostingObserver` tự đồng bộ trạng thái đóng.
- **MKT → RC:** HR thấy ứng viên hay trên Marketplace → "Import vào Recruitment" → `INSERT rc_candidate + rc_application` với `apply_source='marketplace'`.

---

## 2. Phạm vi

### Trong phạm vi

- Quản lý tin tuyển dụng (job posting) với salary range, headcount, deadline
- Tích hợp với `job_titles` và `departments` sẵn có
- Hồ sơ ứng viên tập trung: thông tin cá nhân, kinh nghiệm, kỹ năng, nguồn ứng tuyển
- Pipeline stages tùy chỉnh per org (không hardcode các vòng)
- Application tracking: di chuyển ứng viên qua stages, log lịch sử
- Phỏng vấn: lịch, interview panel, đánh giá theo tiêu chí có điểm số
- Offer letter: điều khoản, gửi, theo dõi phản hồi
- Notes và file đính kèm (CV, bài test) trên ứng viên/đơn ứng tuyển
- Analytics: funnel conversion, time-to-hire, source effectiveness

### Ngoài phạm vi

- Gửi email tự động / email marketing — tích hợp email service riêng
- Career page public (job board) — frontend riêng consume API
- Onboarding flow sau khi hired — thuộc Workforce Center
- Background check / reference check tích hợp bên thứ ba

---

## 3. Kiến trúc tổng thể

```
┌─────────────────────────────────────────────────────────────────┐
│                    RECRUITMENT CENTER                           │
│                                                                 │
│  ┌─────────────────────┐    ┌──────────────────────────────┐   │
│  │  Job Management     │    │  Candidate Pool              │   │
│  │  RC_JOB_POSTING     │    │  RC_CANDIDATE                │   │
│  │  └─FK→ job_titles   │    │  └─ notes, attachments       │   │
│  │  └─FK→ departments  │    └──────────────┬───────────────┘   │
│  └──────────┬──────────┘                   │                   │
│             │ 1:N                          │ 1:N               │
│             └──────────┬───────────────────┘                   │
│                        ▼                                        │
│           ┌────────────────────────┐                           │
│           │  RC_APPLICATION        │                           │
│           │  current_stage_id ─────┼──► RC_PIPELINE_STAGE     │
│           └──────┬─────────────────┘    (configurable)        │
│                  │                                             │
│         ┌────────┴────────┐                                    │
│         │                 │                                    │
│         ▼                 ▼                                    │
│  RC_APPLICATION_    RC_INTERVIEW                               │
│  STAGE_LOG          └─ panelists                               │
│  (audit trail)      └─ evaluations                             │
│                          └─ criteria scores                    │
│                     RC_OFFER                                   │
└─────────────────────────────────────────────────────────────────┘

Tích hợp xuôi (handoff):
  RC_APPLICATION (status=hired) → INSERT employees (Workforce Center)
  RC_JOB_POSTING.position_id   → job_titles.id
  RC_JOB_POSTING.department_id → departments.id
```

### Luồng ứng viên qua pipeline

```
Job Posting (open)
    │
    ▼ apply
RC_APPLICATION (status=active)
    │
    ├─ current_stage_id → "Screening"
    │       │ pass/fail → log RC_APPLICATION_STAGE_LOG
    ├─ current_stage_id → "Technical Interview"
    │       │ schedule RC_INTERVIEW → panelists → evaluations
    ├─ current_stage_id → "HR Interview"
    │       │ schedule RC_INTERVIEW → evaluations
    ├─ current_stage_id → "Final Interview"
    │       │ decision
    ├─ current_stage_id → "Offer"
    │       │ RC_OFFER → sent → accepted/rejected
    └─ status = hired / rejected / withdrawn
```

---

## 4. Enum Values

### RC_JOB_POSTING

| Trường | Giá trị |
|---|---|
| `employment_type` | `full_time` \| `part_time` \| `contractor` \| `intern` |
| `work_location` | `onsite` \| `remote` \| `hybrid` |
| `status` | `draft` \| `open` \| `paused` \| `closed` \| `cancelled` |

### RC_PIPELINE_STAGE

| Trường | Giá trị | Mô tả |
|---|---|---|
| `stage_type` | `screening` | Sàng lọc hồ sơ ban đầu |
| | `assessment` | Bài test / assignment |
| | `interview` | Phỏng vấn (có thể lên lịch RC_INTERVIEW) |
| | `offer` | Giai đoạn offer |
| | `hired` | Đã nhận việc — terminal stage |
| | `rejected` | Bị từ chối — terminal stage |

### RC_CANDIDATE

| Trường | Giá trị |
|---|---|
| `status` | `active` \| `hired` \| `blacklisted` \| `inactive` |
| `source` | `direct` \| `linkedin` \| `referral` \| `job_board` \| `agency` \| `career_page` \| `other` |

### RC_APPLICATION

| Trường | Giá trị |
|---|---|
| `status` | `active` \| `hired` \| `rejected` \| `withdrawn` \| `on_hold` |
| `apply_source` | `direct` \| `linkedin` \| `referral` \| `job_board` \| `agency` \| `career_page` \| `marketplace` \| `other` |

### RC_APPLICATION_STAGE_LOG

| Trường | Giá trị |
|---|---|
| `result` | `passed` \| `failed` \| `skipped` \| `moved_back` |

### RC_INTERVIEW

| Trường | Giá trị |
|---|---|
| `interview_type` | `phone_screen` \| `video` \| `onsite` \| `technical` \| `case_study` \| `panel` |
| `status` | `scheduled` \| `confirmed` \| `completed` \| `cancelled` \| `no_show` |

### RC_INTERVIEW_PANELIST

| Trường | Giá trị |
|---|---|
| `role` | `interviewer` \| `observer` \| `note_taker` |
| `response_status` | `pending` \| `accepted` \| `declined` |

### RC_INTERVIEW_EVALUATION

| Trường | Giá trị |
|---|---|
| `verdict` | `strong_yes` \| `yes` \| `neutral` \| `no` \| `strong_no` |

### RC_OFFER

| Trường | Giá trị |
|---|---|
| `status` | `draft` \| `pending_approval` \| `approved` \| `sent` \| `accepted` \| `rejected` \| `expired` \| `revoked` |

### RC_CANDIDATE_NOTE

| Trường | Giá trị |
|---|---|
| `note_type` | `general` \| `interview_note` \| `concern` \| `follow_up` \| `reference_check` |

### RC_CANDIDATE_ATTACHMENT

| Trường | Giá trị |
|---|---|
| `file_type` | `cv` \| `cover_letter` \| `portfolio` \| `test_result` \| `certificate` \| `other` |

---

## 5. ERD — Quan hệ bảng

```
[job_titles] (existing)         [departments] (existing)
       │                                │
      1:N                              1:N
       └───────────────┬───────────────┘
                       ▼
              RC_JOB_POSTING ──1:N──► RC_APPLICATION
                                            │
              RC_CANDIDATE ──1:N────────────┤
                    │                       │
                   1:N                     1:N──► RC_APPLICATION_STAGE_LOG
                    │                       │         └─FK─► RC_PIPELINE_STAGE
              RC_CANDIDATE_NOTE            1:N──► RC_INTERVIEW
              RC_CANDIDATE_ATTACHMENT           ├─1:N──► RC_INTERVIEW_PANELIST
                                                └─1:N──► RC_INTERVIEW_EVALUATION
                                                              └─1:N──► RC_EVALUATION_CRITERION
                                            │
                                           1:N──► RC_OFFER

RC_PIPELINE_STAGE ──1:N──► RC_APPLICATION (current_stage_id)
```

### Quan hệ tổng hợp

| Bảng A | Quan hệ | Bảng B | Ghi chú |
|---|---|---|---|
| RC_JOB_POSTING | N:1 | job_titles | FK → vị trí tuyển |
| RC_JOB_POSTING | N:1 | departments | FK → phòng ban tuyển |
| RC_JOB_POSTING | 1:N | RC_APPLICATION | Mỗi job nhận nhiều đơn |
| RC_CANDIDATE | 1:N | RC_APPLICATION | 1 ứng viên ứng tuyển nhiều job |
| RC_APPLICATION | N:1 | RC_PIPELINE_STAGE | Stage hiện tại |
| RC_APPLICATION | 1:N | RC_APPLICATION_STAGE_LOG | Lịch sử di chuyển stage |
| RC_APPLICATION | 1:N | RC_INTERVIEW | Các buổi phỏng vấn |
| RC_APPLICATION | 1:N | RC_OFFER | Offer được gửi |
| RC_INTERVIEW | 1:N | RC_INTERVIEW_PANELIST | Thành phần panel |
| RC_INTERVIEW | 1:N | RC_INTERVIEW_EVALUATION | Đánh giá từ interviewer |
| RC_INTERVIEW_EVALUATION | 1:N | RC_EVALUATION_CRITERION | Điểm từng tiêu chí |
| RC_CANDIDATE | 1:N | RC_CANDIDATE_NOTE | Ghi chú |
| RC_CANDIDATE | 1:N | RC_CANDIDATE_ATTACHMENT | File đính kèm |

---

## 6. Đặc tả bảng dữ liệu

> **Ghi chú kiểu FK liên module:** Tất cả cột FK trỏ sang bảng **hiện có** trong hệ thống (`organizations`, `departments`, `job_titles`, `users`, `employees`, `branches`) dùng kiểu `UNSIGNED BIGINT` (Laravel `foreignId()`), KHÔNG phải UUID. Các cột nội bộ module `rc_*` ↔ `rc_*` vẫn dùng UUID.

---

### 6.1 RC_PIPELINE_STAGE — Các giai đoạn tuyển dụng

Cấu hình tùy chỉnh per org. Không hardcode các vòng — org tự định nghĩa pipeline của mình.

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `org_id` | UNSIGNED BIGINT | NOT NULL | FK, INDEX | | FK → organizations.id |
| `name` | VARCHAR(100) | NOT NULL | | | "Screening", "Technical Round 1" |
| `stage_type` | ENUM | NOT NULL | INDEX | | Xem mục 4 |
| `sort_order` | SMALLINT | NOT NULL | | 0 | Thứ tự trong pipeline |
| `require_score` | BOOLEAN | NOT NULL | | FALSE | Bắt buộc có điểm đánh giá trước khi chuyển stage |
| `send_notification` | BOOLEAN | NOT NULL | | TRUE | Gửi notification cho ứng viên khi vào stage này |
| `color_hex` | CHAR(7) | NULL | | NULL | Màu hiển thị trên kanban board |
| `is_active` | BOOLEAN | NOT NULL | | TRUE | |

```sql
CREATE INDEX idx_rc_stage_org ON rc_pipeline_stages(org_id, sort_order)
  WHERE is_active = TRUE;
```

---

### 6.2 RC_JOB_POSTING — Tin tuyển dụng

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `org_id` | UNSIGNED BIGINT | NOT NULL | FK, INDEX | | FK → organizations.id |
| `department_id` | UNSIGNED BIGINT | NOT NULL | FK | | FK → departments.id |
| `position_id` | UNSIGNED BIGINT | NULL | FK | NULL | FK → job_titles.id — NULL nếu chưa có vị trí định nghĩa |
| `owner_id` | UNSIGNED BIGINT | NOT NULL | FK | | FK → users.id — Hiring Manager chịu trách nhiệm |
| `title` | VARCHAR(200) | NOT NULL | | | Tiêu đề tin đăng |
| `code` | VARCHAR(30) | NOT NULL | UNIQUE(org_id) | | JOB-2024-001 — tự sinh |
| `description` | TEXT | NOT NULL | | | Mô tả công việc chi tiết |
| `requirements` | TEXT | NULL | | NULL | Yêu cầu ứng viên |
| `benefits` | TEXT | NULL | | NULL | Quyền lợi |
| `employment_type` | ENUM | NOT NULL | | `full_time` | |
| `work_location` | ENUM | NOT NULL | | `onsite` | |
| `headcount` | SMALLINT | NOT NULL | | 1 | Số lượng cần tuyển |
| `hired_count` | SMALLINT | NOT NULL | | 0 | Đã tuyển được (denormalized) |
| `salary_min` | DECIMAL(15,2) | NULL | | NULL | |
| `salary_max` | DECIMAL(15,2) | NULL | | NULL | |
| `salary_currency` | CHAR(3) | NOT NULL | | `VND` | |
| `salary_is_negotiable` | BOOLEAN | NOT NULL | | FALSE | |
| `status` | ENUM | NOT NULL | INDEX | `draft` | |
| `open_date` | DATE | NULL | INDEX | NULL | Ngày đăng tuyển |
| `close_date` | DATE | NULL | INDEX | NULL | Hạn nộp hồ sơ |
| `is_public` | BOOLEAN | NOT NULL | | FALSE | TRUE = hiển thị trên career page |
| `created_by` | UNSIGNED BIGINT | NOT NULL | FK | | FK → users.id |
| `updated_by` | UNSIGNED BIGINT | NULL | FK | NULL | FK → users.id |
| `created_at` | TIMESTAMP | NOT NULL | | NOW() | |
| `updated_at` | TIMESTAMP | NOT NULL | | NOW() | |

```sql
CREATE UNIQUE INDEX idx_rc_job_code     ON rc_job_postings(org_id, code);
CREATE INDEX idx_rc_job_status          ON rc_job_postings(org_id, status, open_date);
CREATE INDEX idx_rc_job_dept            ON rc_job_postings(department_id, status);
CREATE INDEX idx_rc_job_close           ON rc_job_postings(close_date, status)
  WHERE close_date IS NOT NULL AND status = 'open';
CREATE FULLTEXT INDEX idx_rc_job_search ON rc_job_postings(title, description, requirements);
```

---

### 6.3 RC_CANDIDATE — Hồ sơ ứng viên

Hồ sơ tập trung, độc lập với từng đơn ứng tuyển. Một ứng viên có thể ứng tuyển nhiều vị trí trong cùng org.

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `org_id` | UNSIGNED BIGINT | NOT NULL | FK, INDEX | | FK → organizations.id |
| `full_name` | VARCHAR(150) | NOT NULL | | | |
| `email` | VARCHAR(150) | NOT NULL | UNIQUE(org_id) | | Email là định danh duy nhất trong org |
| `phone` | VARCHAR(20) | NULL | | NULL | |
| `date_of_birth` | DATE | NULL | | NULL | |
| `gender` | ENUM | NULL | | NULL | male \| female \| other |
| `current_title` | VARCHAR(150) | NULL | | NULL | Chức danh hiện tại |
| `current_company` | VARCHAR(150) | NULL | | NULL | Công ty hiện tại |
| `years_experience` | SMALLINT | NULL | | NULL | Số năm kinh nghiệm |
| `skills` | TEXT | NULL | | NULL | Danh sách kỹ năng, phân tách bởi dấu phẩy |
| `source` | VARCHAR(30) | NOT NULL | INDEX | `direct` | Kênh biết đến (xem enum) |
| `referred_by` | UNSIGNED BIGINT | NULL | FK | NULL | FK → users.id — nếu source=referral |
| `linkedin_url` | VARCHAR(300) | NULL | | NULL | |
| `portfolio_url` | VARCHAR(300) | NULL | | NULL | |
| `address` | TEXT | NULL | | NULL | |
| `status` | ENUM | NOT NULL | INDEX | `active` | |
| `blacklist_reason` | TEXT | NULL | | NULL | Lý do blacklist nếu status=blacklisted |
| `created_by` | UNSIGNED BIGINT | NOT NULL | FK | | FK → users.id |
| `updated_by` | UNSIGNED BIGINT | NULL | FK | NULL | FK → users.id |
| `created_at` | TIMESTAMP | NOT NULL | | NOW() | |
| `updated_at` | TIMESTAMP | NOT NULL | | NOW() | |

```sql
CREATE UNIQUE INDEX idx_rc_cand_email      ON rc_candidates(org_id, email);
CREATE INDEX idx_rc_cand_status            ON rc_candidates(org_id, status);
CREATE INDEX idx_rc_cand_source            ON rc_candidates(org_id, source);
CREATE FULLTEXT INDEX idx_rc_cand_search   ON rc_candidates(full_name, email, current_title, current_company, skills);
```

> **Về trường `skills`:** Lưu dạng `Laravel,PHP,MySQL` — parse ở app layer. Đủ dùng cho SME với tìm kiếm FULLTEXT. Nếu sau cần query phức tạp (filter theo skill cụ thể), tách bảng `RC_CANDIDATE_SKILL(candidate_id, skill_name, level)`.

---

### 6.4 RC_APPLICATION — Đơn ứng tuyển

Bảng trung tâm nối `RC_CANDIDATE` với `RC_JOB_POSTING`. Mỗi row là một lần ứng tuyển cụ thể.

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `job_id` | UUID | NOT NULL | FK, INDEX | | FK → RC_JOB_POSTING.id |
| `candidate_id` | UUID | NOT NULL | FK, INDEX | | FK → RC_CANDIDATE.id |
| `current_stage_id` | UUID | NOT NULL | FK | | Stage hiện tại trong pipeline |
| `status` | ENUM | NOT NULL | INDEX | `active` | |
| `apply_source` | VARCHAR(30) | NOT NULL | | `direct` | Kênh nộp đơn lần này |
| `cover_letter` | TEXT | NULL | | NULL | |
| `expected_salary` | DECIMAL(15,2) | NULL | | NULL | Mức lương kỳ vọng |
| `notice_period_days` | SMALLINT | NULL | | NULL | Thời gian thông báo nghỉ việc (ngày) |
| `assigned_to` | UNSIGNED BIGINT | NULL | FK | NULL | FK → users.id — Recruiter phụ trách đơn này |
| `rejection_reason` | TEXT | NULL | | NULL | Lý do từ chối (khi status=rejected) |
| `mkt_application_id` | UUID | NULL | | NULL | Ref → mkt_applications.id khi import từ Marketplace — không FK cứng |
| `applied_at` | TIMESTAMP | NOT NULL | INDEX | NOW() | |
| `updated_at` | TIMESTAMP | NOT NULL | | NOW() | |

```sql
CREATE UNIQUE INDEX idx_rc_app_unique   ON rc_applications(job_id, candidate_id);
CREATE INDEX idx_rc_app_stage           ON rc_applications(current_stage_id, status);
CREATE INDEX idx_rc_app_assigned        ON rc_applications(assigned_to, status);
CREATE INDEX idx_rc_app_candidate       ON rc_applications(candidate_id, status);
```

---

### 6.5 RC_APPLICATION_STAGE_LOG — Lịch sử di chuyển stage

**Immutable audit trail.** Chỉ INSERT — không UPDATE, không DELETE. Ghi lại mỗi lần ứng viên được chuyển stage.

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `application_id` | UUID | NOT NULL | FK, INDEX | | |
| `stage_id` | UUID | NOT NULL | FK | | Stage được log |
| `result` | ENUM | NOT NULL | | | passed \| failed \| skipped \| moved_back |
| `note` | TEXT | NULL | | NULL | Ghi chú khi di chuyển |
| `actioned_by` | UNSIGNED BIGINT | NOT NULL | FK | | FK → users.id — Người thực hiện |
| `actioned_at` | TIMESTAMP | NOT NULL | INDEX | NOW() | |

```sql
CREATE INDEX idx_rc_stagelog_app  ON rc_application_stage_logs(application_id, actioned_at DESC);
CREATE INDEX idx_rc_stagelog_stage ON rc_application_stage_logs(stage_id, result);
```

---

### 6.6 RC_INTERVIEW — Buổi phỏng vấn

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `application_id` | UUID | NOT NULL | FK, INDEX | | |
| `stage_id` | UUID | NOT NULL | FK | | Stage tương ứng của interview |
| `interview_type` | ENUM | NOT NULL | | `video` | |
| `title` | VARCHAR(200) | NULL | | NULL | "Vòng Technical Round 1" |
| `scheduled_at` | TIMESTAMP | NOT NULL | INDEX | | Thời điểm phỏng vấn |
| `duration_minutes` | SMALLINT | NOT NULL | | 60 | |
| `location` | VARCHAR(300) | NULL | | NULL | Địa điểm (nếu onsite) |
| `meeting_url` | TEXT | NULL | | NULL | Link video call |
| `meeting_id` | VARCHAR(100) | NULL | | NULL | ID cuộc họp (Zoom/Meet) |
| `status` | ENUM | NOT NULL | INDEX | `scheduled` | |
| `interviewer_note` | TEXT | NULL | | NULL | Note nội bộ trước interview |
| `created_by` | UNSIGNED BIGINT | NOT NULL | FK | | FK → users.id |
| `created_at` | TIMESTAMP | NOT NULL | | NOW() | |
| `updated_at` | TIMESTAMP | NOT NULL | | NOW() | |

```sql
CREATE INDEX idx_rc_interview_app      ON rc_interviews(application_id, status);
CREATE INDEX idx_rc_interview_schedule ON rc_interviews(scheduled_at, status)
  WHERE status = 'scheduled';
```

---

### 6.7 RC_INTERVIEW_PANELIST — Thành viên panel phỏng vấn

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `interview_id` | UUID | NOT NULL | FK, INDEX | | FK → RC_INTERVIEW.id, CASCADE DELETE |
| `user_id` | UNSIGNED BIGINT | NOT NULL | FK | | FK → users.id |
| `role` | ENUM | NOT NULL | | `interviewer` | interviewer \| observer \| note_taker |
| `response_status` | ENUM | NOT NULL | | `pending` | pending \| accepted \| declined |
| `responded_at` | TIMESTAMP | NULL | | NULL | |

```sql
CREATE UNIQUE INDEX idx_rc_panelist_unique ON rc_interview_panelists(interview_id, user_id);
CREATE INDEX idx_rc_panelist_user          ON rc_interview_panelists(user_id, response_status);
```

---

### 6.8 RC_INTERVIEW_EVALUATION — Đánh giá phỏng vấn

Mỗi interviewer nộp 1 đánh giá per interview. Có điểm tổng và điểm từng tiêu chí.

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `interview_id` | UUID | NOT NULL | FK, INDEX | | FK → RC_INTERVIEW.id |
| `evaluator_id` | UNSIGNED BIGINT | NOT NULL | FK | | FK → users.id |
| `overall_score` | SMALLINT | NOT NULL | | | Điểm tổng (1–10) |
| `strengths` | TEXT | NULL | | NULL | Điểm mạnh của ứng viên |
| `weaknesses` | TEXT | NULL | | NULL | Điểm yếu / quan ngại |
| `recommendation` | TEXT | NULL | | NULL | Đề xuất / ghi chú bổ sung |
| `verdict` | ENUM | NOT NULL | | | strong_yes \| yes \| neutral \| no \| strong_no |
| `is_submitted` | BOOLEAN | NOT NULL | | FALSE | FALSE = draft, chưa chính thức |
| `submitted_at` | TIMESTAMP | NULL | | NULL | |

```sql
CREATE UNIQUE INDEX idx_rc_eval_unique ON rc_interview_evaluations(interview_id, evaluator_id);
CREATE INDEX idx_rc_eval_verdict       ON rc_interview_evaluations(interview_id, verdict);
```

---

### 6.9 RC_EVALUATION_CRITERION — Điểm từng tiêu chí

Điểm chi tiết per criterion trong một bản đánh giá. Org tự định nghĩa tiêu chí khi tạo đánh giá.

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `evaluation_id` | UUID | NOT NULL | FK, INDEX | | FK → RC_INTERVIEW_EVALUATION.id, CASCADE DELETE |
| `criterion_name` | VARCHAR(100) | NOT NULL | | | "Technical Skills", "Communication", "Culture Fit" |
| `score` | SMALLINT | NOT NULL | | | Điểm (1–10) |
| `comment` | TEXT | NULL | | NULL | Nhận xét cho tiêu chí này |

```sql
CREATE INDEX idx_rc_criterion_eval ON rc_evaluation_criteria(evaluation_id);
```

---

### 6.10 RC_OFFER — Offer tuyển dụng

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `application_id` | UUID | NOT NULL | FK, INDEX | | FK → RC_APPLICATION.id |
| `salary_offered` | DECIMAL(15,2) | NOT NULL | | | Mức lương đề nghị |
| `currency` | CHAR(3) | NOT NULL | | `VND` | |
| `start_date` | DATE | NOT NULL | | | Ngày dự kiến bắt đầu làm |
| `probation_days` | SMALLINT | NOT NULL | | 60 | Thời gian thử việc (ngày) |
| `benefits_note` | TEXT | NULL | | NULL | Ghi chú các quyền lợi bổ sung |
| `expire_at` | DATE | NULL | | NULL | Hạn trả lời offer |
| `status` | ENUM | NOT NULL | INDEX | `draft` | Xem mục 4 |
| `approved_by` | UNSIGNED BIGINT | NULL | FK | NULL | FK → users.id — Người duyệt offer |
| `approved_at` | TIMESTAMP | NULL | | NULL | |
| `sent_at` | TIMESTAMP | NULL | | NULL | Thời điểm gửi offer cho ứng viên |
| `responded_at` | TIMESTAMP | NULL | | NULL | Thời điểm ứng viên trả lời |
| `rejection_reason` | TEXT | NULL | | NULL | Lý do từ chối nếu status=rejected |
| `created_by` | UNSIGNED BIGINT | NOT NULL | FK | | FK → users.id |
| `created_at` | TIMESTAMP | NOT NULL | | NOW() | |
| `updated_at` | TIMESTAMP | NOT NULL | | NOW() | |

```sql
CREATE INDEX idx_rc_offer_app    ON rc_offers(application_id, status);
CREATE INDEX idx_rc_offer_expire ON rc_offers(expire_at, status)
  WHERE expire_at IS NOT NULL AND status = 'sent';
```

---

### 6.11 RC_CANDIDATE_NOTE — Ghi chú ứng viên

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `candidate_id` | UUID | NOT NULL | FK, INDEX | | FK → RC_CANDIDATE.id |
| `application_id` | UUID | NULL | FK | NULL | NULL = ghi chú chung ứng viên, không gắn đơn |
| `content` | TEXT | NOT NULL | | | Nội dung ghi chú |
| `note_type` | ENUM | NOT NULL | | `general` | Xem mục 4 |
| `is_private` | BOOLEAN | NOT NULL | | FALSE | TRUE = chỉ người tạo và HR Admin thấy |
| `created_by` | UNSIGNED BIGINT | NOT NULL | FK | | FK → users.id |
| `created_at` | TIMESTAMP | NOT NULL | INDEX | NOW() | |

```sql
CREATE INDEX idx_rc_note_candidate ON rc_candidate_notes(candidate_id, created_at DESC);
CREATE INDEX idx_rc_note_app       ON rc_candidate_notes(application_id)
  WHERE application_id IS NOT NULL;
```

---

### 6.12 RC_CANDIDATE_ATTACHMENT — File đính kèm ứng viên

| Trường | Kiểu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | |
| `candidate_id` | UUID | NOT NULL | FK, INDEX | | FK → RC_CANDIDATE.id |
| `application_id` | UUID | NULL | FK | NULL | NULL = file chung ứng viên |
| `file_type` | ENUM | NOT NULL | | `cv` | Xem mục 4 |
| `file_name` | VARCHAR(255) | NOT NULL | | | Tên file gốc |
| `file_url` | TEXT | NOT NULL | | | URL đầy đủ để tải |
| `file_size_kb` | INT | NOT NULL | | | |
| `storage_provider` | VARCHAR(20) | NOT NULL | | `s3` | s3 \| gcs \| local |
| `storage_key` | VARCHAR(500) | NOT NULL | | | Object key nội bộ |
| `uploaded_by` | UNSIGNED BIGINT | NOT NULL | FK | | FK → users.id |
| `uploaded_at` | TIMESTAMP | NOT NULL | | NOW() | |

```sql
CREATE INDEX idx_rc_attach_candidate ON rc_candidate_attachments(candidate_id, file_type);
CREATE INDEX idx_rc_attach_app       ON rc_candidate_attachments(application_id)
  WHERE application_id IS NOT NULL;
```

---

## 7. Luồng nghiệp vụ

### 7.1 Tạo và đăng tin tuyển dụng

```
Hiring Manager tạo RC_JOB_POSTING
  ├─ Điền: title, department_id, position_id, headcount
  ├─ Viết: description, requirements, benefits
  ├─ Cấu hình: salary range, employment_type, work_location, close_date
  ├─ status = 'draft' → HR review → status = 'open'
  └─ is_public = TRUE → Hiển thị trên career page
```

### 7.2 Tiếp nhận ứng tuyển

```
Ứng viên nộp hồ sơ (qua career page hoặc recruiter thêm tay)
  │
  ├─ Tìm RC_CANDIDATE theo email + org_id:
  │   Đã có → tái sử dụng, cập nhật thông tin nếu cần
  │   Chưa có → INSERT RC_CANDIDATE mới
  │
  ├─ Kiểm tra: đã ứng tuyển job này chưa?
  │   Đã có application active → thông báo trùng, không tạo mới
  │   Chưa có → INSERT RC_APPLICATION
  │       current_stage_id = stage đầu tiên (sort_order = 0)
  │       status = 'active'
  │
  └─ Upload CV → INSERT RC_CANDIDATE_ATTACHMENT (file_type='cv')
```

### 7.3 Di chuyển ứng viên qua pipeline

```
Recruiter / Hiring Manager quyết định pass/fail stage
  │
  ├─ Validate: stage require_score=TRUE?
  │   → Kiểm tra đã có RC_INTERVIEW_EVALUATION submitted chưa
  │
  ├─ INSERT RC_APPLICATION_STAGE_LOG:
  │   stage_id = current_stage_id
  │   result   = passed / failed
  │   note     = ghi chú lý do
  │
  ├─ UPDATE RC_APPLICATION:
  │   PASSED → current_stage_id = stage tiếp theo theo sort_order
  │   FAILED → status = 'rejected', rejection_reason
  │
  └─ Nếu stage mới có send_notification=TRUE:
       Gửi email thông báo cho ứng viên
```

### 7.4 Lên lịch và tiến hành phỏng vấn

```
Recruiter tạo RC_INTERVIEW cho application
  ├─ Chọn: interview_type, scheduled_at, duration_minutes
  ├─ Nhập: location hoặc meeting_url
  ├─ Thêm RC_INTERVIEW_PANELIST (interviewer + observer)
  └─ Gửi invitation → UPDATE panelist.response_status

Sau phỏng vấn:
  UPDATE RC_INTERVIEW.status = 'completed'

Mỗi interviewer nộp đánh giá:
  INSERT RC_INTERVIEW_EVALUATION
    overall_score, verdict, strengths, weaknesses
  INSERT RC_EVALUATION_CRITERION (nhiều tiêu chí)
  UPDATE is_submitted = TRUE, submitted_at = NOW()
```

### 7.5 Tổng hợp kết quả phỏng vấn

```sql
-- Tổng hợp verdict của tất cả evaluators cho 1 interview
SELECT
    COUNT(*) FILTER (WHERE verdict IN ('strong_yes', 'yes')) AS positive,
    COUNT(*) FILTER (WHERE verdict IN ('no', 'strong_no')) AS negative,
    COUNT(*) FILTER (WHERE verdict = 'neutral') AS neutral,
    ROUND(AVG(overall_score), 1) AS avg_score
FROM rc_interview_evaluations
WHERE interview_id = :interview_id
  AND is_submitted = TRUE;
```

### 7.6 Gửi Offer

```
HR tạo RC_OFFER (status='draft')
  ├─ Điền: salary_offered, start_date, probation_days, benefits_note, expire_at
  ├─ Gửi duyệt → status = 'pending_approval'
  ├─ Hiring Manager duyệt → status = 'approved'
  └─ Gửi cho ứng viên → status = 'sent', sent_at = NOW()

Ứng viên phản hồi:
  ACCEPTED → status = 'accepted', responded_at = NOW()
               UPDATE RC_APPLICATION.status = 'hired'
               UPDATE RC_CANDIDATE.status = 'hired'
               UPDATE RC_JOB_POSTING.hired_count += 1
               Nếu hired_count >= headcount → UPDATE job.status = 'closed'
               TRIGGER: INSERT employees record (handoff sang Workforce)
  REJECTED → status = 'rejected', rejection_reason
               UPDATE RC_APPLICATION.status = 'rejected'
```

### 7.7 Handoff sang Workforce Center

```
Khi RC_OFFER.status = 'accepted':

  ── Bước 1: Validate trước khi handoff ──
  Guard: job.position_id IS NULL → throw, yêu cầu HR gán vị trí trước khi handoff
  Guard: employees đã tồn tại với email = candidate.email trong org → skip (idempotent)

  ── Bước 2: Resolve branch_id (employees.branch_id NOT NULL) ──
  branch_id = Department::find(job.department_id)->branch_id
  Nếu null (department chưa gán chi nhánh):
    branch_id = Branch::where('organization_id', org_id)
                       ->where('status', 'active')
                       ->orderBy('id')->value('id')   -- lấy branch đầu tiên active
  Guard: không tìm được branch nào → throw, yêu cầu HR cấu hình chi nhánh

  ── Bước 3: Auto-generate employee_code (UNIQUE per org) ──
  Lấy MAX số thứ tự từ employee_code hiện có trong org (format EMP-YYYY-NNNN)
  employee_code = 'EMP-' . date('Y') . '-' . str_pad(next_seq, 4, '0', STR_PAD_LEFT)

  ── Bước 4: Tạo user account nếu chưa có ──
  user = User::firstOrCreate(
    ['email' => candidate.email, 'organization_id' => org_id],
    ['name' => candidate.full_name, 'password' => Hash::make(Str::random(16))]
  )

  ── Bước 5: INSERT employees ──
  employees:
    full_name           = candidate.full_name
    email               = candidate.email
    user_id             = user.id             -- liên kết tài khoản
    organization_id     = org_id
    branch_id           = resolved (bước 2)
    department_id       = job.department_id   -- BIGINT FK → departments.id
    job_title_id        = job.position_id     -- BIGINT FK → job_titles.id (nullable)
    employee_code       = auto-generated (bước 3)
    hired_at            = offer.start_date
    salary_base         = offer.salary_offered
    salary_currency     = offer.currency
    probation_end_date  = offer.start_date + INTERVAL offer.probation_days DAY
    employment_type     = job.employment_type -- 'full_time'|'part_time'|'contractor'|'intern'
    status              = 'probation'
    created_by          = current_user.id

  ── Bước 6: INSERT employee_history ──
  employee_history:
    change_type         = 'hire'
    new_department_id   = job.department_id
    new_branch_id       = branch_id
    new_job_title_id    = job.position_id
    new_status          = 'probation'
    new_employment_type = job.employment_type
    effective_date      = offer.start_date
    note                = 'Onboarding từ Recruitment Center — RC_OFFER #' . offer.id
    changed_by          = current_user.id

  ── Bước 7: Khởi tạo leave_balances (Module Leave) ──
  Lấy danh sách leave_policies active của org (dùng LeavePolicy::scopeForEmployee):
    policies = LeavePolicy::where('organization_id', org_id)
                           ->where('status', 'active')
                           ->get()
  Với mỗi policy → INSERT leave_balances:
    organization_id = org_id
    employee_id     = employee.id
    policy_id       = policy.id
    leave_type      = policy.leave_type
    year            = YEAR(offer.start_date)
    entitled_days   = policy.days_per_year
    used_days       = 0, pending_days = 0
  (Dùng INSERT IGNORE / firstOrCreate để idempotent)
```

> **Căn chỉnh với schema thực tế (2026-06-05):**
> - `employees.id` là BIGINT auto-increment; `employees.uuid` là trường riêng
> - `employees.branch_id` NOT NULL — phải resolve từ department hoặc fallback org branch
> - `employees.employee_code` UNIQUE per org — phải auto-generate
> - `leave_balances.policy_id` NOT NULL FK → `leave_policies` — phải query active policies trước
> - `employee_history.change_type` CHECK constraint: chỉ chấp nhận 'hire' | 'branch_transfer' | ...


---

## 8. Query Patterns

### 8.1 Recruitment funnel — tỷ lệ chuyển đổi qua stages

```sql
SELECT
    ps.name                  AS stage_name,
    ps.sort_order,
    COUNT(DISTINCT a.id)     AS total_applications,
    COUNT(DISTINCT CASE WHEN sl.result = 'passed' THEN sl.application_id END) AS passed,
    ROUND(
      COUNT(DISTINCT CASE WHEN sl.result = 'passed' THEN sl.application_id END)
      * 100.0 / NULLIF(COUNT(DISTINCT a.id), 0), 1
    )                        AS pass_rate_pct
FROM rc_pipeline_stages ps
LEFT JOIN rc_application_stage_logs sl ON sl.stage_id = ps.id
LEFT JOIN rc_applications a            ON a.id = sl.application_id
WHERE ps.org_id = :org_id
GROUP BY ps.id, ps.name, ps.sort_order
ORDER BY ps.sort_order;
```

### 8.2 Kanban board — ứng viên theo từng stage của 1 job

```sql
SELECT
    ps.id   AS stage_id,
    ps.name AS stage_name,
    ps.sort_order,
    ps.color_hex,
    COUNT(a.id) AS candidate_count
FROM rc_pipeline_stages ps
LEFT JOIN rc_applications a
       ON a.current_stage_id = ps.id
      AND a.job_id = :job_id
      AND a.status = 'active'
WHERE ps.org_id = :org_id
  AND ps.is_active = TRUE
GROUP BY ps.id, ps.name, ps.sort_order, ps.color_hex
ORDER BY ps.sort_order;
```

### 8.3 Danh sách ứng viên trong 1 stage

```sql
SELECT
    a.id            AS application_id,
    c.full_name,
    c.email,
    c.current_title,
    c.current_company,
    c.years_experience,
    a.apply_source,
    a.expected_salary,
    a.applied_at,
    u.full_name     AS assigned_recruiter
FROM rc_applications a
JOIN rc_candidates c  ON c.id = a.candidate_id
LEFT JOIN users u     ON u.id = a.assigned_to
WHERE a.job_id           = :job_id
  AND a.current_stage_id = :stage_id
  AND a.status           = 'active'
ORDER BY a.applied_at DESC;
```

### 8.4 Time-to-hire report

```sql
SELECT
    j.title             AS job_title,
    j.code,
    COUNT(a.id)         AS total_hired,
    ROUND(AVG(
      EXTRACT(DAY FROM o.responded_at - j.open_date)
    ), 1)               AS avg_days_to_hire,
    MIN(EXTRACT(DAY FROM o.responded_at - j.open_date)) AS min_days,
    MAX(EXTRACT(DAY FROM o.responded_at - j.open_date)) AS max_days
FROM rc_job_postings j
JOIN rc_applications a ON a.job_id = j.id AND a.status = 'hired'
JOIN rc_offers o       ON o.application_id = a.id AND o.status = 'accepted'
WHERE j.org_id = :org_id
  AND j.open_date BETWEEN :start AND :end
GROUP BY j.id, j.title, j.code
ORDER BY avg_days_to_hire;
```

### 8.5 Source effectiveness — hiệu quả kênh tuyển dụng

```sql
SELECT
    c.source,
    COUNT(DISTINCT c.id)                                   AS total_candidates,
    COUNT(DISTINCT a.id)                                   AS total_applications,
    COUNT(DISTINCT CASE WHEN a.status = 'hired' THEN a.id END) AS hired,
    ROUND(
      COUNT(DISTINCT CASE WHEN a.status = 'hired' THEN a.id END)
      * 100.0 / NULLIF(COUNT(DISTINCT a.id), 0), 1
    )                                                      AS hire_rate_pct
FROM rc_candidates c
LEFT JOIN rc_applications a ON a.candidate_id = c.id
WHERE c.org_id = :org_id
  AND c.created_at BETWEEN :start AND :end
GROUP BY c.source
ORDER BY hired DESC;
```

### 8.6 Interview schedule của interviewer trong tuần

```sql
SELECT
    i.scheduled_at,
    i.duration_minutes,
    i.interview_type,
    i.meeting_url,
    i.status,
    c.full_name     AS candidate_name,
    j.title         AS job_title
FROM rc_interview_panelists ip
JOIN rc_interviews i       ON i.id = ip.interview_id
JOIN rc_applications a     ON a.id = i.application_id
JOIN rc_candidates c       ON c.id = a.candidate_id
JOIN rc_job_postings j     ON j.id = a.job_id
WHERE ip.user_id       = :user_id
  AND i.scheduled_at   BETWEEN :week_start AND :week_end
  AND i.status         = 'scheduled'
ORDER BY i.scheduled_at;
```

---

## 9. API Endpoints

### Pipeline & Jobs

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/recruitment/pipeline-stages` | Danh sách stages của org |
| POST | `/api/recruitment/pipeline-stages` | Tạo stage |
| PUT | `/api/recruitment/pipeline-stages/:id` | Cập nhật stage |
| PUT | `/api/recruitment/pipeline-stages/reorder` | Sắp xếp lại thứ tự |
| GET | `/api/recruitment/jobs` | Danh sách job (filter: status, dept) |
| GET | `/api/recruitment/jobs/:id` | Chi tiết job |
| GET | `/api/recruitment/jobs/:id/pipeline` | Kanban view: stages + ứng viên |
| POST | `/api/recruitment/jobs` | Tạo job mới |
| PUT | `/api/recruitment/jobs/:id` | Cập nhật |
| POST | `/api/recruitment/jobs/:id/publish` | Publish (draft → open) |
| POST | `/api/recruitment/jobs/:id/close` | Đóng tuyển dụng |

### Candidates

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/recruitment/candidates` | Danh sách ứng viên (search, filter) |
| GET | `/api/recruitment/candidates/:id` | Hồ sơ đầy đủ + lịch sử ứng tuyển |
| POST | `/api/recruitment/candidates` | Thêm ứng viên thủ công |
| PUT | `/api/recruitment/candidates/:id` | Cập nhật hồ sơ |
| POST | `/api/recruitment/candidates/:id/notes` | Thêm ghi chú |
| POST | `/api/recruitment/candidates/:id/attachments` | Upload file |
| POST | `/api/recruitment/candidates/:id/blacklist` | Blacklist |

### Applications

| Method | Endpoint | Mô tả |
|---|---|---|
| POST | `/api/recruitment/jobs/:id/applications` | Tạo đơn ứng tuyển |
| GET | `/api/recruitment/applications/:id` | Chi tiết đơn |
| POST | `/api/recruitment/applications/:id/move` | Di chuyển sang stage khác |
| POST | `/api/recruitment/applications/:id/reject` | Từ chối ứng viên |
| PATCH | `/api/recruitment/applications/:id/assign` | Phân công recruiter |

### Interviews

| Method | Endpoint | Mô tả |
|---|---|---|
| POST | `/api/recruitment/applications/:id/interviews` | Tạo lịch phỏng vấn |
| PUT | `/api/recruitment/interviews/:id` | Cập nhật lịch |
| POST | `/api/recruitment/interviews/:id/cancel` | Hủy phỏng vấn |
| GET | `/api/recruitment/interviews/my-schedule` | Lịch phỏng vấn của tôi |
| POST | `/api/recruitment/interviews/:id/evaluations` | Nộp đánh giá |
| GET | `/api/recruitment/interviews/:id/evaluations` | Xem tổng hợp đánh giá |

### Offers

| Method | Endpoint | Mô tả |
|---|---|---|
| POST | `/api/recruitment/applications/:id/offers` | Tạo offer |
| PUT | `/api/recruitment/offers/:id` | Cập nhật offer |
| POST | `/api/recruitment/offers/:id/submit-approval` | Gửi duyệt |
| POST | `/api/recruitment/offers/:id/approve` | Duyệt offer |
| POST | `/api/recruitment/offers/:id/send` | Gửi cho ứng viên |
| POST | `/api/recruitment/offers/:id/accept` | Ứng viên chấp nhận |
| POST | `/api/recruitment/offers/:id/reject` | Ứng viên từ chối |

### Analytics

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/recruitment/analytics/funnel` | Conversion funnel theo job |
| GET | `/api/recruitment/analytics/time-to-hire` | Time-to-hire report |
| GET | `/api/recruitment/analytics/source` | Source effectiveness |
| GET | `/api/recruitment/analytics/overview` | Tổng hợp: open jobs, pending, hired |

---

## 10. Business Rules

### BR-RC-001: Job Posting
- Chỉ chuyển `draft → open` khi có đủ: `title`, `description`, `department_id`, `headcount >= 1`
- Khi `hired_count >= headcount`: tự động chuyển `status = 'closed'`
- Không xóa job đã có application — chỉ cancel hoặc close
- Job `cancelled`: toàn bộ application active chuyển sang `on_hold`, recruiter được thông báo

### BR-RC-002: Candidate deduplication
- `email` là định danh duy nhất per org — không cho phép tạo 2 candidate cùng email trong cùng org
- Khi ứng viên nộp lại qua career page: tìm candidate hiện có theo email, không tạo mới
- Một ứng viên không được ứng tuyển cùng 1 job 2 lần khi vẫn còn application `active`

### BR-RC-003: Pipeline stage movement
- Chỉ di chuyển forward hoặc backward 1 stage tại 1 thời điểm (không skip nhiều stage)
- Nếu `stage.require_score = TRUE`: phải có ít nhất 1 `RC_INTERVIEW_EVALUATION` với `is_submitted=TRUE` trước khi pass
- Stage có `stage_type = 'hired'` hoặc `'rejected'`: terminal — không di chuyển tiếp
- `RC_APPLICATION_STAGE_LOG` là immutable — không xóa, không sửa

### BR-RC-004: Interview
- Không tạo interview khi application đã `rejected` hoặc `withdrawn`
- Interviewer phải là member trong org (có trong `users` table)
- Nộp evaluation chỉ khi interviewer là panelist của interview đó
- Mỗi panelist chỉ nộp 1 evaluation per interview

### BR-RC-005: Offer
- Chỉ tạo 1 offer active (status không phải `rejected`/`expired`/`revoked`) per application tại 1 thời điểm
- Offer `sent`: chỉ ứng viên hoặc HR Admin cập nhật status (accept/reject)
- Khi offer `expired` (quá `expire_at` và chưa respond): cron tự động chuyển status
- Sau khi offer `accepted`: application và candidate locked (không di chuyển stage, không sửa pipeline)

### BR-RC-006: Handoff sang Workforce
- Chỉ INSERT `employees` khi offer `status = 'accepted'`
- Nếu `job.position_id IS NULL`: nhắc HR gán vị trí (`job_titles.id`) trước khi handoff
- Tạo user account trước khi INSERT employees (bắt buộc có `user_id`)
- Handoff là idempotent — nếu employees đã tồn tại với `email` này trong org thì skip, không tạo trùng

---

## 11. Indexes & Caching

```sql
-- Job listing (thường xuyên nhất)
CREATE INDEX idx_rc_job_active
  ON rc_job_postings(org_id, status, open_date DESC)
  WHERE status = 'open';

-- Kanban board (query hot nhất trong day-to-day)
CREATE INDEX idx_rc_app_kanban
  ON rc_applications(job_id, current_stage_id, status)
  WHERE status = 'active';

-- Candidate profile lookup
CREATE INDEX idx_rc_cand_lookup
  ON rc_candidates(org_id, status, created_at DESC);

-- Offer expiry cron
CREATE INDEX idx_rc_offer_expiry
  ON rc_offers(expire_at, status)
  WHERE status = 'sent' AND expire_at IS NOT NULL;

-- Interviewer schedule
CREATE INDEX idx_rc_interview_my
  ON rc_interview_panelists(user_id, response_status)
  INCLUDE (interview_id);

-- Pending evaluations
CREATE INDEX idx_rc_eval_pending
  ON rc_interview_evaluations(interview_id, is_submitted)
  WHERE is_submitted = FALSE;

-- Analytics: funnel theo job
CREATE INDEX idx_rc_stagelog_funnel
  ON rc_application_stage_logs(stage_id, result, actioned_at);
```

### Caching

| Cache key | TTL | Invalidate khi |
|---|---|---|
| `recruitment:org:{id}:pipeline` | 30 phút | Thêm/sửa/xóa stage |
| `recruitment:job:{id}:kanban-count` | 2 phút | Move application, reject, hire |
| `recruitment:job:{id}:stats` | 5 phút | Thêm application, thay đổi status |
| `recruitment:analytics:{id}:{month}` | 1 giờ | Offer accepted, reject |

---

## 12. Lộ trình triển khai

### Phase 1 — Job Posting & Pipeline Setup (tuần 1–2)

> Mục tiêu: HR có thể tạo tin tuyển dụng nội bộ và publish ra Marketplace.

- [ ] Migration: `rc_pipeline_stages`, `rc_job_postings`
- [ ] Seed pipeline stages mặc định khi org mới đăng ký (Screening → Technical → HR → Offer)
- [ ] CRUD `RC_JOB_POSTING`: tạo, sửa, publish (`draft → open`), đóng (`open → closed`)
  - Liên kết với `departments` và `job_titles` hiện có (BIGINT FK)
  - Validate BR-RC-001 trước khi publish
- [ ] Action `PublishToMarketplaceAction`: khi HR click "Đăng ra Marketplace"
  - Guard: `status='open'` và `is_public=TRUE`
  - Guard: chưa có `mkt_listing` với `rc_job_posting_id = job.id` đang active
  - INSERT `mkt_listings` với `rc_job_posting_id = job.id` (CHAR(36)), `poster_type='org'`
- [ ] `RcJobPostingObserver`: lắng nghe `updated` → sync `mkt_listings.rc_sync_status` + tự đóng listing khi job đóng
- [ ] Sidebar: section **"Tuyển dụng"** gồm: Tin tuyển dụng, Cấu hình Pipeline

### Phase 2 — Candidate & Application Pipeline (tuần 3–4)

> Mục tiêu: HR tiếp nhận và xử lý hồ sơ ứng viên qua kanban board.

- [ ] Migration: `rc_candidates`, `rc_applications`, `rc_application_stage_logs`
- [ ] Tiếp nhận ứng viên thủ công (recruiter add tay)
- [ ] Import ứng viên từ Marketplace (`apply_source='marketplace'`, `mkt_application_id` soft ref)
  - Chỉ import khi `mkt_listing.rc_job_posting_id IS NOT NULL`
  - Tìm/tạo `RC_CANDIDATE` theo email + org_id (BR-RC-002)
- [ ] Kanban board: pipeline stages + di chuyển ứng viên
  - Validate `require_score` trước khi chuyển stage
  - INSERT `rc_application_stage_logs` immutable audit trail
- [ ] `rc_candidate_notes` + `rc_candidate_attachments` (CV upload)
- [ ] Gắn recruiter phụ trách (`assigned_to`)

### Phase 3 — Interview & Evaluation (tuần 5–6)

> Mục tiêu: Lên lịch phỏng vấn, panel đánh giá, tổng hợp kết quả.

- [ ] Migration: `rc_interviews`, `rc_interview_panelists`, `rc_interview_evaluations`, `rc_evaluation_criteria`
- [ ] Tạo lịch phỏng vấn: chọn loại, thời gian, meeting URL, thêm panelists
- [ ] Invitation flow: gửi invite → `panelist.response_status` (pending/accepted/declined)
- [ ] Mỗi interviewer nộp đánh giá: `overall_score`, `verdict`, điểm từng tiêu chí
- [ ] View "Lịch phỏng vấn của tôi" (`/dashboard/recruitment/my-interviews`)
- [ ] Tổng hợp verdict + average score per interview (xem BR-RC-004)

### Phase 4 — Offer & Handoff sang Workforce (tuần 7–8)

> Mục tiêu: Phát offer, xử lý phản hồi, tự động tạo employee khi accepted.

- [ ] Migration: `rc_offers`
- [ ] Luồng offer: `draft → pending_approval → approved → sent → accepted/rejected`
- [ ] Offer expiry cron: quét `expire_at < NOW()` và `status='sent'` → chuyển `expired`
- [ ] `HandoffAction` khi offer `accepted` (xem chi tiết mục 7.7):
  - Resolve `branch_id` từ `department.branch_id` → fallback org's first active branch
  - Auto-generate `employee_code` (format `EMP-YYYY-NNNN`)
  - Tạo user account nếu chưa có
  - INSERT `employees` + `employee_history` (change_type='hire')
  - Init `leave_balances` per active `leave_policies` của org
  - Idempotent: skip nếu employee email đã tồn tại trong org
- [ ] Notification: offer sắp hết hạn (D-3, D-1)

### Phase 5 — Analytics & Career Page API (tuần 9)

> Mục tiêu: Báo cáo hiệu quả tuyển dụng + API cho career page.

- [ ] Recruitment funnel: tỷ lệ chuyển đổi qua từng stage (mục 8.1)
- [ ] Time-to-hire report (mục 8.4)
- [ ] Source effectiveness: Marketplace vs Direct vs Referral (mục 8.5)
- [ ] Public career page API: `GET /api/careers/jobs?org={slug}` — phục vụ website riêng của DN
- [ ] Offer expiry alert dashboard widget

---

*Version 1.1.0 — Recruitment Center Module Specification*
*Stack: Laravel 13 · SQLite (dev) / MySQL 8+ / PostgreSQL 15+*
*Thay đổi 1.1.0: thêm quan hệ Marketplace; mở rộng handoff 7 bước (branch_id, employee_code, leave_balances); chia lại 5 phases (2026-06-05)*