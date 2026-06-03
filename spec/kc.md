# Đặc Tả Module: Knowledge Center (Quản lý Kho Tri Thức)

> **Hệ thống:** SaaS SME  
> **Module:** Knowledge Center  
> **Phiên bản đặc tả:** 1.0.0  
> **Ngày cập nhật:** 2026-06-03  
> **Trạng thái:** Draft

---

## Mục lục

1. [Tổng quan module](#1-tổng-quan-module)
2. [Phạm vi & mục tiêu](#2-phạm-vi--mục-tiêu)
3. [Các sub-module](#3-các-sub-module)
4. [Enum & Constant Values](#4-enum--constant-values)
5. [ERD — Entity Relationship Diagram](#5-erd--entity-relationship-diagram)
6. [Đặc tả bảng dữ liệu](#6-đặc-tả-bảng-dữ-liệu)
   - 6.1 [KC_CATEGORY — Danh mục tài liệu](#61-kc_category--danh-mục-tài-liệu)
   - 6.2 [KC_ITEM — Tài liệu / Tri thức](#62-kc_item--tài-liệu--tri-thức)
   - 6.3 [KC_ITEM_ATTACHMENT — Tệp đính kèm](#63-kc_item_attachment--tệp-đính-kèm)
   - 6.4 [KC_TAG — Nhãn tag](#64-kc_tag--nhãn-tag)
   - 6.5 [KC_ITEM_TAG — Quan hệ Item–Tag](#65-kc_item_tag--quan-hệ-itemtag)
   - 6.6 [KC_VERSION_HISTORY — Lịch sử phiên bản](#66-kc_version_history--lịch-sử-phiên-bản)
   - 6.7 [KC_ACCESS_CONTROL — Phân quyền truy cập](#67-kc_access_control--phân-quyền-truy-cập)
   - 6.8 [KC_FEEDBACK — Phản hồi & đánh giá](#68-kc_feedback--phản-hồi--đánh-giá)
   - 6.9 [KC_VIEW_LOG — Nhật ký lượt xem](#69-kc_view_log--nhật-ký-lượt-xem)
7. [Luồng nghiệp vụ](#7-luồng-nghiệp-vụ)
   - 7.1 [Tạo & duyệt tài liệu](#71-tạo--duyệt-tài-liệu)
   - 7.2 [Versioning & rollback](#72-versioning--rollback)
   - 7.3 [Phân quyền truy cập](#73-phân-quyền-truy-cập)
   - 7.4 [Hết hiệu lực tự động](#74-hết-hiệu-lực-tự-động)
   - 7.5 [Tìm kiếm & lọc](#75-tìm-kiếm--lọc)
8. [API Endpoints (đề xuất)](#8-api-endpoints-đề-xuất)
9. [Business Rules & Ràng buộc](#9-business-rules--ràng-buộc)
10. [Indexes & Performance](#10-indexes--performance)
11. [Ghi chú triển khai cho SME](#11-ghi-chú-triển-khai-cho-sme)

---

## 1. Tổng quan module

**Knowledge Center** là module trung tâm quản lý toàn bộ tài sản tri thức của doanh nghiệp SME, bao gồm tài liệu nội bộ, quy trình vận hành, video hướng dẫn, biểu mẫu, FAQ, case study và chính sách/quy định.

### Mục đích chính

- Tập trung hóa tri thức doanh nghiệp vào một nơi duy nhất, dễ tìm kiếm
- Chuẩn hóa quy trình tạo — duyệt — phát hành tài liệu
- Kiểm soát phiên bản và lịch sử thay đổi nội dung
- Phân quyền truy cập linh hoạt theo user / vai trò / phòng ban
- Đo lường mức độ sử dụng và chất lượng tri thức

### Người dùng liên quan

| Vai trò | Quyền mặc định |
|---|---|
| **Admin** | Toàn quyền quản lý danh mục, tag, phân quyền, xem analytics |
| **Content Manager** | Tạo, chỉnh sửa, gửi duyệt, quản lý tài liệu trong phạm vi được phân |
| **Approver** | Duyệt / từ chối tài liệu được gửi lên |
| **Editor** | Tạo và sửa bản nháp, không tự duyệt |
| **Viewer** | Chỉ đọc tài liệu theo phạm vi visibility |

---

## 2. Phạm vi & mục tiêu

### Trong phạm vi (In Scope)

- Quản lý cây danh mục đa cấp (tối đa 3 cấp)
- Quản lý tài liệu với 7 loại (type): Tài liệu, SOP, Video, Biểu mẫu, FAQ, Case Study, Policy/Guideline
- Luồng duyệt: Draft → Pending Review → Approved / Rejected
- Versioning nội dung và rollback
- Đính kèm tệp (nhiều file/tài liệu), hỗ trợ S3 / GCS / local storage
- Gắn tag tự do
- Phân quyền xem/sửa theo user, role, department
- Tracking lượt xem và feedback
- Tìm kiếm full-text, lọc theo type, status, danh mục, tag
- Hết hiệu lực tự động (effective_date / expired_date)

### Ngoài phạm vi (Out of Scope)

- Tích hợp ký số điện tử
- Bình luận (comment thread) trên tài liệu — có thể mở rộng ở phiên bản sau
- Module học tập / LMS (nếu có, sẽ là module riêng tham chiếu KC_ITEM)

---

## 3. Các sub-module

### 3.1 Quản lý danh mục tài liệu (Category Management)

Cho phép tổ chức tài liệu thành cây phân cấp. Mỗi danh mục có thể có danh mục con, màu sắc và icon riêng để nhận diện trực quan.

**Chức năng:**

- Tạo / sửa / xóa mềm danh mục
- Kéo thả sắp xếp thứ tự (`sort_order`)
- Bật/tắt hiển thị danh mục (`is_active`)
- Xem số lượng tài liệu trong danh mục và các danh mục con

**Ràng buộc:**

- Không xóa danh mục khi còn tài liệu bên trong
- Cây danh mục tối đa 3 cấp
- `slug` là duy nhất trong toàn hệ thống (per org)

### 3.2 Quản lý tài liệu / tri thức (Knowledge Item Management)

Cho phép tạo, quản lý và tìm kiếm toàn bộ tài liệu trong kho tri thức.

**Chức năng:**

- Tạo tài liệu mới với editor soạn thảo (Markdown / Rich Text)
- Chọn loại (type), danh mục, tag, visibility
- Đính kèm nhiều file
- Gửi duyệt và theo dõi trạng thái
- Xem lịch sử phiên bản, so sánh diff, rollback
- Ghim tài liệu nổi bật (`is_featured`) hoặc đầu danh sách (`is_pinned`)
- Đặt ngày hiệu lực và ngày hết hạn
- Phân quyền chi tiết từng tài liệu (override visibility mặc định)

### 3.3 Tìm kiếm & Khám phá (Search & Discovery)

- Full-text search trên `title`, `summary`, `content`
- Lọc đồng thời theo: type, status, category, tag, language, date range
- Sắp xếp: mới nhất, xem nhiều nhất, đánh giá cao nhất, tiêu đề A-Z
- Gợi ý tài liệu liên quan (cùng danh mục / tag)
- Trang "Nổi bật" và "Mới nhất"

### 3.4 Analytics & Báo cáo

- Tài liệu được xem nhiều nhất (7 ngày / 30 ngày)
- Tài liệu chưa được xem nào
- Thống kê theo loại (type)
- Đánh giá trung bình (average rating) theo tài liệu và danh mục
- Tài liệu sắp hết hạn (trong 30 ngày tới)

---

## 4. Enum & Constant Values

### 4.1 KC_ITEM.type — Loại tài liệu

| Giá trị | Nhãn hiển thị | Mô tả | Icon gợi ý |
|---|---|---|---|
| `document` | Tài liệu | Văn bản, báo cáo, hướng dẫn thông thường | ti-file-text |
| `sop` | SOP | Standard Operating Procedure — Quy trình chuẩn vận hành | ti-list-check |
| `video` | Video | Video hướng dẫn, đào tạo — lưu URL embed hoặc file | ti-video |
| `form` | Biểu mẫu | Template biểu mẫu, form điền sẵn, checklist | ti-forms |
| `faq` | FAQ | Câu hỏi thường gặp theo dạng Q&A | ti-help-circle |
| `case_study` | Case Study | Bài học, kinh nghiệm thực tế, tình huống điển hình | ti-bulb |
| `policy` | Policy/Guideline | Chính sách, quy định, hướng dẫn nội bộ | ti-scale |

### 4.2 KC_ITEM.status — Trạng thái luồng duyệt

| Giá trị | Nhãn | Mô tả | Màu gợi ý |
|---|---|---|---|
| `draft` | Bản nháp | Đang soạn, chưa gửi duyệt | Gray |
| `pending_review` | Chờ duyệt | Đã gửi, chờ approver xử lý | Amber |
| `approved` | Đã duyệt | Tài liệu chính thức, hiển thị theo visibility | Green |
| `rejected` | Bị từ chối | Approver từ chối, cần chỉnh sửa và gửi lại | Red |
| `archived` | Lưu trữ | Không còn hiệu lực, ẩn khỏi tìm kiếm nhưng vẫn truy cập được | Gray |

**Sơ đồ chuyển trạng thái:**

```
draft ──► pending_review ──► approved ──► archived
           │                    │
           ▼                    ▼
        rejected             archived
           │
           ▼
          draft (chỉnh sửa lại)
```

### 4.3 KC_ITEM.visibility — Phạm vi hiển thị

| Giá trị | Mô tả |
|---|---|
| `public` | Tất cả user đã đăng nhập trong org đều thấy |
| `internal` | Chỉ nhân viên chính thức (lọc theo role) |
| `restricted` | Chỉ những user / role / dept được cấp trong KC_ACCESS_CONTROL |
| `private` | Chỉ owner và admin hệ thống |

### 4.4 KC_ACCESS_CONTROL.permission — Mức phân quyền

| Giá trị | Mô tả |
|---|---|
| `view` | Chỉ xem nội dung |
| `edit` | Xem + chỉnh sửa, gửi duyệt |
| `manage` | Toàn quyền: xem, sửa, duyệt, xóa, phân quyền |

### 4.5 KC_ACCESS_CONTROL.target_type — Đối tượng phân quyền

| Giá trị | Mô tả |
|---|---|
| `user` | Một user cụ thể |
| `role` | Tất cả user có role này |
| `dept` | Tất cả user thuộc phòng ban này |

---

## 5. ERD — Entity Relationship Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         KNOWLEDGE CENTER ERD                                │
└─────────────────────────────────────────────────────────────────────────────┘

KC_CATEGORY
┌──────────────────────────┐
│ id (PK)                  │
│ parent_id (FK → self) ───┼─── tự tham chiếu (cây đa cấp)
│ name                     │
│ slug (UNIQUE)            │
│ description              │
│ icon                     │
│ color_hex                │
│ sort_order               │
│ is_active                │
│ created_by (FK → users)  │
│ updated_by (FK → users)  │
│ created_at               │
│ updated_at               │
└──────────┬───────────────┘
           │ 1
           │ has many
           │ N
┌──────────▼───────────────────────────────┐
│                KC_ITEM                   │
│──────────────────────────────────────────│
│ id (PK)                                  │
│ category_id (FK → KC_CATEGORY)           │
│ org_id (FK → organizations)              │
│ title                                    │
│ slug (UNIQUE per org)                    │
│ summary                                  │
│ content (LONGTEXT)                       │
│ type (ENUM)                              │◄── document|sop|video|form|
│ status (ENUM)                            │    faq|case_study|policy
│ visibility (ENUM)                        │
│ language                                 │◄── draft|pending_review|
│ view_count                               │    approved|rejected|archived
│ download_count                           │
│ is_featured                              │
│ is_pinned                                │
│ owner_id (FK → users)                    │
│ approved_by (FK → users)                 │
│ approved_at                              │
│ version                                  │
│ effective_date                           │
│ expired_date                             │
│ created_by (FK → users)                  │
│ updated_by (FK → users)                  │
│ created_at                               │
│ updated_at                               │
└────┬──────┬──────┬──────┬──────┬─────────┘
     │      │      │      │      │
     │1     │1     │1     │1     │1
     │      │      │      │      │
     ▼N     ▼N     ▼N     ▼N     ▼N
┌────────┐ ┌──────┐ ┌──────────┐ ┌──────────────┐ ┌──────────┐
│KC_ITEM │ │KC_   │ │KC_       │ │KC_ACCESS_    │ │KC_VIEW_  │
│_ATTACH │ │ITEM  │ │VERSION_  │ │CONTROL       │ │LOG       │
│MENT    │ │_TAG  │ │HISTORY   │ │              │ │          │
│────────│ │──────│ │──────────│ │──────────────│ │──────────│
│id (PK) │ │item  │ │id (PK)   │ │id (PK)       │ │id (PK)   │
│item_id │ │_id   │ │item_id   │ │item_id       │ │item_id   │
│file_   │ │(FK)  │ │version_  │ │target_type   │ │user_id   │
│name    │ │tag_id│ │number    │ │target_id     │ │ip_addr   │
│file_url│ │(FK)  │ │content_  │ │permission    │ │user_agent│
│file_   │ └──┬───┘ │snapshot  │ │granted_at    │ │viewed_at │
│type    │    │     │change_   │ │granted_by    │ └──────────┘
│file_   │    │N    │summary   │ └──────────────┘
│size_kb │    │     │changed_by│
│storage │    │     │changed_at│
│_provid │    │     └──────────┘
│sort_   │    │
│order   │  ┌─▼──────────┐
│upload  │  │  KC_TAG    │
│_by     │  │────────────│
│upload  │  │id (PK)     │
│_at     │  │org_id (FK) │
└────────┘  │name        │
            │slug        │
            │color_hex   │
            └────────────┘

KC_FEEDBACK (1 item → N feedbacks)
┌──────────────────────┐
│ id (PK)              │
│ item_id (FK)         │
│ user_id (FK)         │
│ rating (1–5)         │
│ comment              │
│ is_helpful           │
│ created_at           │
└──────────────────────┘
```

### Quan hệ tổng hợp

| Bảng A | Quan hệ | Bảng B | Ghi chú |
|---|---|---|---|
| KC_CATEGORY | 1 → N | KC_CATEGORY | Tự tham chiếu — cây danh mục |
| KC_CATEGORY | 1 → N | KC_ITEM | Mỗi tài liệu thuộc 1 danh mục |
| KC_ITEM | 1 → N | KC_ITEM_ATTACHMENT | Nhiều tệp đính kèm |
| KC_ITEM | N → M | KC_TAG | Qua bảng KC_ITEM_TAG |
| KC_ITEM | 1 → N | KC_VERSION_HISTORY | Lịch sử phiên bản |
| KC_ITEM | 1 → N | KC_ACCESS_CONTROL | Phân quyền chi tiết |
| KC_ITEM | 1 → N | KC_FEEDBACK | Đánh giá từ người dùng |
| KC_ITEM | 1 → N | KC_VIEW_LOG | Tracking lượt xem |

---

## 6. Đặc tả bảng dữ liệu

### 6.1 KC_CATEGORY — Danh mục tài liệu

**Mục đích:** Tổ chức tài liệu thành cấu trúc cây phân cấp (tối đa 3 cấp). Hỗ trợ icon và màu sắc nhận diện.

| Trường | Kiểu dữ liệu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | Khóa chính |
| `parent_id` | UUID | NULL | FK (self) | NULL | Danh mục cha — NULL nếu là cấp gốc |
| `name` | VARCHAR(150) | NOT NULL | | | Tên danh mục hiển thị |
| `slug` | VARCHAR(160) | NOT NULL | UNIQUE | | Định danh URL-friendly, tự sinh từ name |
| `description` | TEXT | NULL | | NULL | Mô tả ngắn về nội dung danh mục |
| `icon` | VARCHAR(80) | NULL | | NULL | Tên Tabler Icon, vd: `ti-folder`, `ti-book` |
| `color_hex` | CHAR(7) | NULL | | NULL | Mã màu hex, vd: `#534AB7` |
| `sort_order` | INT | NOT NULL | | 0 | Thứ tự hiển thị trong cùng cấp cha |
| `is_active` | BOOLEAN | NOT NULL | | TRUE | FALSE = ẩn danh mục (soft disable) |
| `created_by` | UUID | NOT NULL | FK (users) | | Người tạo |
| `updated_by` | UUID | NULL | FK (users) | NULL | Người cập nhật lần cuối |
| `created_at` | TIMESTAMP | NOT NULL | | NOW() | Thời điểm tạo |
| `updated_at` | TIMESTAMP | NOT NULL | | NOW() | Thời điểm cập nhật |

**Indexes:**

```sql
CREATE UNIQUE INDEX idx_kc_category_slug ON KC_CATEGORY(slug);
CREATE INDEX idx_kc_category_parent ON KC_CATEGORY(parent_id);
CREATE INDEX idx_kc_category_sort ON KC_CATEGORY(parent_id, sort_order);
```

**Ràng buộc:**

- `slug` phải là lowercase, alphanumeric và dấu gạch ngang (`-`), không chứa ký tự đặc biệt
- Không được đặt `parent_id` trỏ về chính mình (`id <> parent_id`)
- Trước khi xóa: kiểm tra không còn `KC_ITEM` nào có `category_id` trỏ vào
- Trước khi xóa: kiểm tra không có danh mục con (`parent_id`) trỏ vào

---

### 6.2 KC_ITEM — Tài liệu / Tri thức

**Mục đích:** Bảng trung tâm lưu trữ toàn bộ nội dung tri thức với đầy đủ metadata, trạng thái duyệt và kiểm soát vòng đời.

| Trường | Kiểu dữ liệu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | Khóa chính |
| `category_id` | UUID | NOT NULL | FK (KC_CATEGORY) | | Danh mục chứa tài liệu |
| `org_id` | UUID | NOT NULL | FK (organizations) | | Tổ chức sở hữu — multi-tenant isolation |
| `title` | VARCHAR(300) | NOT NULL | | | Tiêu đề tài liệu |
| `slug` | VARCHAR(320) | NOT NULL | UNIQUE(org_id, slug) | | Định danh URL, unique trong org |
| `summary` | TEXT | NULL | | NULL | Tóm tắt ngắn — dùng cho danh sách, preview |
| `content` | LONGTEXT | NULL | FULLTEXT | NULL | Nội dung chính — Markdown hoặc HTML |
| `type` | ENUM | NOT NULL | INDEX | | Xem mục 4.1 |
| `status` | ENUM | NOT NULL | INDEX | `draft` | Xem mục 4.2 |
| `visibility` | ENUM | NOT NULL | | `internal` | Xem mục 4.3 |
| `language` | CHAR(5) | NULL | | `vi` | Ngôn ngữ nội dung theo BCP 47: `vi`, `en`, `ja` |
| `view_count` | INT | NOT NULL | | 0 | Tổng lượt xem (denormalized từ KC_VIEW_LOG) |
| `download_count` | INT | NOT NULL | | 0 | Tổng lượt tải về |
| `is_featured` | BOOLEAN | NOT NULL | | FALSE | Hiển thị nổi bật trên trang chủ KC |
| `is_pinned` | BOOLEAN | NOT NULL | | FALSE | Ghim lên đầu trong danh mục |
| `owner_id` | UUID | NOT NULL | FK (users) | | Người chịu trách nhiệm nội dung |
| `approved_by` | UUID | NULL | FK (users) | NULL | Người đã duyệt tài liệu |
| `approved_at` | TIMESTAMP | NULL | | NULL | Thời điểm được duyệt |
| `version` | INT | NOT NULL | | 1 | Số phiên bản hiện tại, tăng dần khi approve |
| `effective_date` | TIMESTAMP | NULL | | NULL | Ngày tài liệu bắt đầu có hiệu lực |
| `expired_date` | TIMESTAMP | NULL | INDEX | NULL | Ngày hết hiệu lực — cron tự chuyển archived |
| `created_by` | UUID | NOT NULL | FK (users) | | Người tạo |
| `updated_by` | UUID | NULL | FK (users) | NULL | Người cập nhật lần cuối |
| `created_at` | TIMESTAMP | NOT NULL | | NOW() | Thời điểm tạo |
| `updated_at` | TIMESTAMP | NOT NULL | | NOW() | Thời điểm cập nhật |

**Indexes:**

```sql
CREATE INDEX idx_kc_item_org_category ON KC_ITEM(org_id, category_id);
CREATE INDEX idx_kc_item_type ON KC_ITEM(type);
CREATE INDEX idx_kc_item_status ON KC_ITEM(status);
CREATE INDEX idx_kc_item_owner ON KC_ITEM(owner_id);
CREATE INDEX idx_kc_item_expired ON KC_ITEM(expired_date) WHERE expired_date IS NOT NULL;
CREATE INDEX idx_kc_item_featured ON KC_ITEM(org_id, is_featured) WHERE is_featured = TRUE;
CREATE UNIQUE INDEX idx_kc_item_slug_org ON KC_ITEM(org_id, slug);
FULLTEXT INDEX idx_kc_item_search ON KC_ITEM(title, summary, content);
```

**Ghi chú đặc biệt:**

- Trường `type = 'video'`: `content` lưu embed URL hoặc video ID; file thực tế đặt trong `KC_ITEM_ATTACHMENT`
- Trường `type = 'faq'`: `content` nên theo cấu trúc JSON array `[{ "q": "...", "a": "..." }]` để render accordion
- Trường `type = 'form'`: `content` chứa template biểu mẫu; file `.docx`/`.xlsx` đặt trong `KC_ITEM_ATTACHMENT`
- `view_count` là denormalized counter — cập nhật async từ `KC_VIEW_LOG` để tránh lock tranh chấp

---

### 6.3 KC_ITEM_ATTACHMENT — Tệp đính kèm

**Mục đích:** Lưu trữ metadata của tất cả tệp đính kèm liên quan đến một tài liệu. File thực tế lưu trên object storage.

| Trường | Kiểu dữ liệu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | Khóa chính |
| `item_id` | UUID | NOT NULL | FK (KC_ITEM) | | Tài liệu chứa file này |
| `file_name` | VARCHAR(255) | NOT NULL | | | Tên file gốc khi upload |
| `file_url` | TEXT | NOT NULL | | | URL đầy đủ để tải/xem file |
| `file_type` | VARCHAR(50) | NOT NULL | | | MIME type: `application/pdf`, `video/mp4`, ... |
| `file_size_kb` | INT | NOT NULL | | | Kích thước file tính bằng KB |
| `storage_provider` | VARCHAR(20) | NOT NULL | | `s3` | Nơi lưu: `s3`, `gcs`, `local` |
| `storage_key` | VARCHAR(500) | NOT NULL | | | Object key / đường dẫn nội bộ trên storage |
| `sort_order` | INT | NOT NULL | | 0 | Thứ tự hiển thị file trong danh sách đính kèm |
| `uploaded_by` | UUID | NOT NULL | FK (users) | | Người upload |
| `uploaded_at` | TIMESTAMP | NOT NULL | | NOW() | Thời điểm upload |

**Indexes:**

```sql
CREATE INDEX idx_kc_attachment_item ON KC_ITEM_ATTACHMENT(item_id);
CREATE INDEX idx_kc_attachment_sort ON KC_ITEM_ATTACHMENT(item_id, sort_order);
```

---

### 6.4 KC_TAG — Nhãn tag

**Mục đích:** Danh sách tag/nhãn tự do của tổ chức, dùng để gắn vào tài liệu nhằm tăng khả năng tìm kiếm chéo danh mục.

| Trường | Kiểu dữ liệu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | Khóa chính |
| `org_id` | UUID | NOT NULL | FK (organizations) | | Tổ chức sở hữu tag |
| `name` | VARCHAR(80) | NOT NULL | | | Tên tag hiển thị, vd: "ISO 9001", "Onboarding" |
| `slug` | VARCHAR(90) | NOT NULL | UNIQUE(org_id) | | Định danh, vd: `iso-9001` |
| `color_hex` | CHAR(7) | NULL | | NULL | Màu hiển thị badge tag |
| `created_at` | TIMESTAMP | NOT NULL | | NOW() | Thời điểm tạo |

**Indexes:**

```sql
CREATE UNIQUE INDEX idx_kc_tag_slug_org ON KC_TAG(org_id, slug);
CREATE INDEX idx_kc_tag_org ON KC_TAG(org_id);
```

---

### 6.5 KC_ITEM_TAG — Quan hệ Item–Tag

**Mục đích:** Bảng pivot nhiều–nhiều giữa `KC_ITEM` và `KC_TAG`.

| Trường | Kiểu dữ liệu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `item_id` | UUID | NOT NULL | PK, FK (KC_ITEM) | | Tài liệu |
| `tag_id` | UUID | NOT NULL | PK, FK (KC_TAG) | | Tag được gắn |

```sql
ALTER TABLE KC_ITEM_TAG ADD PRIMARY KEY (item_id, tag_id);
CREATE INDEX idx_kc_item_tag_tag ON KC_ITEM_TAG(tag_id);
```

---

### 6.6 KC_VERSION_HISTORY — Lịch sử phiên bản

**Mục đích:** Lưu snapshot nội dung mỗi lần tài liệu được duyệt, cho phép xem lại và rollback.

| Trường | Kiểu dữ liệu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | Khóa chính |
| `item_id` | UUID | NOT NULL | FK (KC_ITEM) | | Tài liệu |
| `version_number` | INT | NOT NULL | | | Số phiên bản (bằng KC_ITEM.version tại thời điểm snapshot) |
| `title_snapshot` | VARCHAR(300) | NOT NULL | | | Tiêu đề tại thời điểm snapshot |
| `content_snapshot` | LONGTEXT | NOT NULL | | | Toàn bộ nội dung tại thời điểm snapshot |
| `change_summary` | TEXT | NULL | | NULL | Ghi chú thay đổi so với version trước |
| `changed_by` | UUID | NOT NULL | FK (users) | | Người thực hiện thay đổi |
| `changed_at` | TIMESTAMP | NOT NULL | | NOW() | Thời điểm snapshot được tạo |

**Indexes:**

```sql
CREATE INDEX idx_kc_version_item ON KC_VERSION_HISTORY(item_id);
CREATE UNIQUE INDEX idx_kc_version_unique ON KC_VERSION_HISTORY(item_id, version_number);
```

**Ghi chú:**

- Snapshot được tạo tự động mỗi khi tài liệu chuyển sang trạng thái `approved`
- Với SME, nên giới hạn giữ lại tối đa **20 version gần nhất** / tài liệu để kiểm soát storage
- Rollback: tạo version mới với `content_snapshot` từ version cũ, không xóa lịch sử

---

### 6.7 KC_ACCESS_CONTROL — Phân quyền truy cập

**Mục đích:** Quản lý quyền truy cập chi tiết từng tài liệu khi `visibility = 'restricted'`. Cho phép cấp quyền cho user cụ thể, vai trò hoặc phòng ban.

| Trường | Kiểu dữ liệu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | Khóa chính |
| `item_id` | UUID | NOT NULL | FK (KC_ITEM) | | Tài liệu được phân quyền |
| `target_type` | ENUM | NOT NULL | | | `user` / `role` / `dept` |
| `target_id` | UUID | NOT NULL | | | ID của user / role / dept tương ứng |
| `permission` | ENUM | NOT NULL | | `view` | `view` / `edit` / `manage` |
| `granted_at` | TIMESTAMP | NOT NULL | | NOW() | Thời điểm cấp quyền |
| `granted_by` | UUID | NOT NULL | FK (users) | | Người cấp quyền |
| `expired_at` | TIMESTAMP | NULL | | NULL | Quyền hết hạn tự động (nếu có) |

**Indexes:**

```sql
CREATE INDEX idx_kc_access_item ON KC_ACCESS_CONTROL(item_id);
CREATE INDEX idx_kc_access_target ON KC_ACCESS_CONTROL(target_type, target_id);
CREATE UNIQUE INDEX idx_kc_access_unique ON KC_ACCESS_CONTROL(item_id, target_type, target_id);
```

**Logic kiểm tra quyền (Permission Resolution):**

```
Khi user U truy cập item I:
1. Nếu U là admin → ALLOW (manage)
2. Nếu I.visibility = 'public' → ALLOW (view)
3. Nếu I.visibility = 'private' → chỉ owner hoặc admin
4. Nếu I.visibility = 'internal' → kiểm tra U có role nhân viên không
5. Nếu I.visibility = 'restricted':
   a. Tìm record trong KC_ACCESS_CONTROL với:
      - target_type='user' AND target_id=U.id, OR
      - target_type='role' AND target_id IN (U.roles), OR
      - target_type='dept' AND target_id = U.dept_id
   b. Lấy permission cao nhất trong kết quả trả về
   c. Kiểm tra expired_at chưa quá hạn
6. Nếu không khớp → DENY
```

---

### 6.8 KC_FEEDBACK — Phản hồi & đánh giá

**Mục đích:** Thu thập đánh giá chất lượng tài liệu từ người dùng. Mỗi user chỉ được đánh giá 1 lần / tài liệu (có thể cập nhật).

| Trường | Kiểu dữ liệu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | Khóa chính |
| `item_id` | UUID | NOT NULL | FK (KC_ITEM) | | Tài liệu được đánh giá |
| `user_id` | UUID | NOT NULL | FK (users) | | Người đánh giá |
| `rating` | SMALLINT | NULL | | NULL | Điểm 1–5 sao (nullable nếu chỉ vote helpful) |
| `comment` | TEXT | NULL | | NULL | Nhận xét chi tiết |
| `is_helpful` | BOOLEAN | NULL | | NULL | Nhanh: "Tài liệu này có hữu ích không?" |
| `created_at` | TIMESTAMP | NOT NULL | | NOW() | Thời điểm tạo |
| `updated_at` | TIMESTAMP | NOT NULL | | NOW() | Thời điểm cập nhật |

**Indexes:**

```sql
CREATE UNIQUE INDEX idx_kc_feedback_unique ON KC_FEEDBACK(item_id, user_id);
CREATE INDEX idx_kc_feedback_item ON KC_FEEDBACK(item_id);
```

---

### 6.9 KC_VIEW_LOG — Nhật ký lượt xem

**Mục đích:** Ghi lại từng lượt xem tài liệu để phân tích mức độ sử dụng. Bảng này tăng trưởng nhanh — cần partition theo thời gian.

| Trường | Kiểu dữ liệu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | Khóa chính |
| `item_id` | UUID | NOT NULL | FK (KC_ITEM) | | Tài liệu được xem |
| `user_id` | UUID | NULL | FK (users) | NULL | User xem (NULL nếu anonymous/public) |
| `session_id` | VARCHAR(100) | NULL | | NULL | Session ID để dedup lượt xem |
| `ip_address` | VARCHAR(45) | NULL | | NULL | IP (IPv4 hoặc IPv6) |
| `user_agent` | TEXT | NULL | | NULL | Browser / device info |
| `viewed_at` | TIMESTAMP | NOT NULL | INDEX | NOW() | Thời điểm xem |

**Partitioning (khuyến nghị):**

```sql
-- Partition by month để quản lý size
PARTITION BY RANGE (YEAR(viewed_at) * 100 + MONTH(viewed_at)) (
  PARTITION p202601 VALUES LESS THAN (202602),
  PARTITION p202602 VALUES LESS THAN (202603),
  ...
);
```

**Indexes:**

```sql
CREATE INDEX idx_kc_viewlog_item ON KC_VIEW_LOG(item_id, viewed_at);
CREATE INDEX idx_kc_viewlog_user ON KC_VIEW_LOG(user_id, viewed_at);
```

---

## 7. Luồng nghiệp vụ

### 7.1 Tạo & duyệt tài liệu

```
[Editor/Content Manager]
       │
       ▼
  Tạo tài liệu mới
  (status = 'draft')
       │
       ├─► Lưu nháp (tiếp tục chỉnh sửa)
       │
       ▼
  Gửi duyệt
  (status → 'pending_review')
       │
       ▼
[Approver nhận notification]
       │
       ├─► Từ chối (status → 'rejected')
       │        └─► Gửi lý do → Editor chỉnh sửa lại → Gửi duyệt lại
       │
       └─► Duyệt (status → 'approved')
                 ├─► version += 1
                 ├─► approved_by = approver.id
                 ├─► approved_at = NOW()
                 └─► Tạo bản ghi KC_VERSION_HISTORY
                           └─► Tài liệu hiển thị theo visibility
```

**Điều kiện gửi duyệt:**

- `title` không được để trống
- `category_id` phải hợp lệ
- `type` phải được chọn
- Nếu `type = 'sop'` hoặc `type = 'policy'`: yêu cầu có `summary`

### 7.2 Versioning & rollback

```
Khi Approve:
  1. Tăng KC_ITEM.version += 1
  2. INSERT INTO KC_VERSION_HISTORY:
     - version_number = KC_ITEM.version (mới)
     - content_snapshot = KC_ITEM.content (bản được duyệt)
     - title_snapshot = KC_ITEM.title
     - changed_by = approver.id
  3. Cập nhật KC_ITEM (approved_by, approved_at, status)

Khi Rollback về version V:
  1. Tìm bản ghi KC_VERSION_HISTORY với version_number = V
  2. Tạo version mới từ snapshot:
     KC_ITEM.content = snapshot.content_snapshot
     KC_ITEM.title = snapshot.title_snapshot
     KC_ITEM.version += 1 (không đặt lại về V)
     KC_ITEM.status = 'draft' (cần duyệt lại)
  3. Ghi lại change_summary: "Rolled back to version V"
```

### 7.3 Phân quyền truy cập

Xem chi tiết tại mục **6.7 KC_ACCESS_CONTROL — Logic kiểm tra quyền**.

**Kịch bản thường gặp cho SME:**

| Tình huống | Cấu hình |
|---|---|
| Tài liệu dùng chung toàn công ty | `visibility = 'internal'` |
| Quy trình chỉ dành cho phòng Kỹ thuật | `visibility = 'restricted'` + cấp quyền `dept = Engineering` |
| Tài liệu nhạy cảm HR | `visibility = 'restricted'` + cấp quyền cho 2–3 user cụ thể |
| SOP cho team mới onboard | `visibility = 'public'` (trong org) |
| Bản nháp chưa muốn ai thấy | `visibility = 'private'` |

### 7.4 Hết hiệu lực tự động

```
[Cron Job — chạy mỗi ngày lúc 01:00]
  1. SELECT * FROM KC_ITEM
     WHERE status = 'approved'
       AND expired_date IS NOT NULL
       AND expired_date <= NOW()

  2. Với mỗi tài liệu tìm thấy:
     a. UPDATE status = 'archived'
     b. INSERT notification → owner_id: "Tài liệu X đã hết hiệu lực"
     c. Nếu có người theo dõi: gửi thêm notification

  3. Với tài liệu SOP/Policy sắp hết hạn trong 30 ngày:
     a. Gửi cảnh báo trước cho owner_id
     b. INSERT reminder notification
```

### 7.5 Tìm kiếm & lọc

**Query logic đề xuất:**

```sql
SELECT i.*
FROM KC_ITEM i
JOIN KC_CATEGORY c ON i.category_id = c.id
WHERE
  i.org_id = :org_id
  AND i.status = 'approved'
  -- Visibility check
  AND (
    i.visibility = 'public'
    OR i.visibility = 'internal'
    OR (i.visibility = 'restricted' AND EXISTS (
      SELECT 1 FROM KC_ACCESS_CONTROL ac
      WHERE ac.item_id = i.id
        AND (
          (ac.target_type = 'user' AND ac.target_id = :user_id)
          OR (ac.target_type = 'role' AND ac.target_id IN (:user_roles))
          OR (ac.target_type = 'dept' AND ac.target_id = :user_dept_id)
        )
        AND (ac.expired_at IS NULL OR ac.expired_at > NOW())
    ))
  )
  -- Full-text search (nếu có từ khóa)
  AND (
    :keyword IS NULL
    OR MATCH(i.title, i.summary, i.content) AGAINST(:keyword IN BOOLEAN MODE)
  )
  -- Filter
  AND (:type IS NULL OR i.type = :type)
  AND (:category_id IS NULL OR i.category_id = :category_id)
ORDER BY
  CASE :sort
    WHEN 'newest' THEN i.created_at
    WHEN 'views'  THEN i.view_count
  END DESC;
```

---

## 8. API Endpoints (đề xuất)

### Danh mục (Category)

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/kc/categories` | Lấy toàn bộ cây danh mục |
| GET | `/api/kc/categories/:id` | Lấy chi tiết 1 danh mục + danh mục con |
| POST | `/api/kc/categories` | Tạo danh mục mới |
| PUT | `/api/kc/categories/:id` | Cập nhật danh mục |
| DELETE | `/api/kc/categories/:id` | Xóa danh mục (có kiểm tra ràng buộc) |
| PUT | `/api/kc/categories/reorder` | Cập nhật thứ tự sắp xếp |

### Tài liệu (Items)

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/kc/items` | Danh sách tài liệu (hỗ trợ filter, search, sort, paging) |
| GET | `/api/kc/items/:id` | Chi tiết tài liệu + track view |
| GET | `/api/kc/items/:id/versions` | Lịch sử phiên bản |
| GET | `/api/kc/items/:id/versions/:v` | Nội dung tại version V |
| POST | `/api/kc/items` | Tạo tài liệu mới (status = draft) |
| PUT | `/api/kc/items/:id` | Cập nhật tài liệu |
| POST | `/api/kc/items/:id/submit` | Gửi duyệt |
| POST | `/api/kc/items/:id/approve` | Duyệt tài liệu |
| POST | `/api/kc/items/:id/reject` | Từ chối duyệt (kèm lý do) |
| POST | `/api/kc/items/:id/archive` | Lưu trữ tài liệu |
| POST | `/api/kc/items/:id/rollback/:version` | Rollback về phiên bản cũ |
| DELETE | `/api/kc/items/:id` | Xóa mềm tài liệu |

### Tệp đính kèm (Attachments)

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/kc/items/:id/attachments` | Danh sách file đính kèm |
| POST | `/api/kc/items/:id/attachments` | Upload file đính kèm |
| DELETE | `/api/kc/items/:id/attachments/:aid` | Xóa file đính kèm |

### Tags

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/kc/tags` | Danh sách tag của org |
| POST | `/api/kc/tags` | Tạo tag mới |
| PUT | `/api/kc/tags/:id` | Cập nhật tag |
| DELETE | `/api/kc/tags/:id` | Xóa tag |

### Phân quyền

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/kc/items/:id/permissions` | Danh sách phân quyền của tài liệu |
| POST | `/api/kc/items/:id/permissions` | Cấp quyền cho user/role/dept |
| DELETE | `/api/kc/items/:id/permissions/:pid` | Thu hồi quyền |

### Feedback

| Method | Endpoint | Mô tả |
|---|---|---|
| POST | `/api/kc/items/:id/feedback` | Gửi đánh giá |
| PUT | `/api/kc/items/:id/feedback` | Cập nhật đánh giá |
| GET | `/api/kc/items/:id/feedback/summary` | Tổng hợp rating của tài liệu |

### Analytics

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/kc/analytics/top-viewed` | Tài liệu xem nhiều nhất |
| GET | `/api/kc/analytics/by-type` | Thống kê theo loại |
| GET | `/api/kc/analytics/expiring-soon` | Tài liệu sắp hết hạn |
| GET | `/api/kc/analytics/unread` | Tài liệu chưa được xem |

---

## 9. Business Rules & Ràng buộc

### BR-KC-001: Phân cấp danh mục

- Cây danh mục tối đa **3 cấp** (root → level 1 → level 2)
- Không cho phép di chuyển danh mục tạo ra chu kỳ (`parent_id` không được trỏ về con cháu của chính nó)
- Xóa danh mục chỉ được phép khi: không có tài liệu và không có danh mục con

### BR-KC-002: Luồng duyệt

- Chỉ những user có role `approver` hoặc `admin` mới được thực hiện approve/reject
- Tác giả tài liệu không được tự duyệt tài liệu của mình (trừ admin)
- Khi reject: bắt buộc nhập lý do (trường `comment` trong response body)
- Tài liệu `approved` muốn chỉnh sửa: tạo `draft` mới từ version hiện tại, version cũ vẫn còn hiệu lực cho đến khi có version `approved` mới

### BR-KC-003: Versioning

- Mỗi lần `approved`: bắt buộc tạo snapshot vào `KC_VERSION_HISTORY`
- Giữ tối đa **20 versions** gần nhất / tài liệu; version cũ hơn xóa tự động (cron)
- Rollback chỉ tạo version mới từ snapshot cũ, không xóa lịch sử

### BR-KC-004: Tệp đính kèm

- Giới hạn kích thước mỗi file: **50MB** (configurable per org plan)
- Tổng kích thước tệp đính kèm / tài liệu: **200MB**
- File type được phép (configurable): `.pdf`, `.docx`, `.xlsx`, `.pptx`, `.png`, `.jpg`, `.mp4`, `.zip`

### BR-KC-005: Feedback

- Mỗi user chỉ có **1 feedback record / tài liệu** (upsert)
- Không cho phép feedback tài liệu ở trạng thái `draft` hoặc `rejected`

### BR-KC-006: Counting & Analytics

- `view_count` chỉ tăng **1 lần / user / session / 24h** / tài liệu (dedup bằng `session_id` hoặc `user_id + item_id + ngày`)
- Cập nhật `view_count` bất đồng bộ (queue-based) để không block request đọc tài liệu
- `download_count` tăng mỗi khi user click download file từ `KC_ITEM_ATTACHMENT`

### BR-KC-007: Multi-tenancy

- Tất cả query đều phải có điều kiện `org_id = :current_org_id`
- Tag và danh mục cũng là per-org (không chia sẻ giữa các org)
- Slug unique trong phạm vi từng org, không cần global unique

---

## 10. Indexes & Performance

### Index tổng hợp quan trọng

```sql
-- Trang chủ Knowledge Center: lấy tài liệu nổi bật, mới nhất
CREATE INDEX idx_kc_item_homepage
  ON KC_ITEM(org_id, status, is_featured, created_at DESC)
  WHERE status = 'approved';

-- Lọc theo danh mục + type (thường dùng nhất)
CREATE INDEX idx_kc_item_category_type
  ON KC_ITEM(org_id, category_id, type, status);

-- Tìm tài liệu sắp hết hạn (cron + alert dashboard)
CREATE INDEX idx_kc_item_expiry
  ON KC_ITEM(org_id, expired_date, status)
  WHERE expired_date IS NOT NULL AND status = 'approved';

-- Phân quyền: lookup nhanh theo target
CREATE INDEX idx_kc_access_lookup
  ON KC_ACCESS_CONTROL(item_id, target_type, target_id, permission)
  WHERE expired_at IS NULL OR expired_at > NOW();
```

### Lưu ý hiệu năng

| Vấn đề | Giải pháp đề xuất |
|---|---|
| `KC_VIEW_LOG` tăng trưởng vô hạn | Partition by month; chỉ giữ raw log 6 tháng, aggregate hàng ngày vào bảng stats riêng |
| Full-text search chậm khi data lớn | Kết hợp Elasticsearch / OpenSearch cho search; MySQL FULLTEXT dùng cho SME nhỏ |
| Visibility check phức tạp trong mỗi query | Cache danh sách `item_id` user có quyền xem vào Redis (TTL 5 phút) |
| `view_count` update gây lock | Dùng message queue (Redis + worker) để async update |
| Snapshot `content_snapshot` tốn storage | Nén bằng gzip trước khi lưu vào LONGTEXT; hoặc lưu chỉ diff |

---

## 11. Ghi chú triển khai cho SME

### Giai đoạn 1 — MVP (1–2 tháng)

Triển khai đủ để đưa vào sử dụng cơ bản:

- [ ] `KC_CATEGORY` + `KC_ITEM` + `KC_ITEM_ATTACHMENT`
- [ ] `KC_TAG` + `KC_ITEM_TAG`
- [ ] Luồng Draft → Approved (không cần Pending Review nếu org nhỏ)
- [ ] Tìm kiếm full-text cơ bản
- [ ] `visibility`: chỉ `public` và `internal`

### Giai đoạn 2 — Mở rộng (tháng 3–4)

- [ ] `KC_VERSION_HISTORY` + Rollback
- [ ] `KC_ACCESS_CONTROL` + `visibility = 'restricted'`
- [ ] `KC_FEEDBACK` + Rating
- [ ] Luồng duyệt đầy đủ (Pending Review → Approve/Reject)
- [ ] Cron job hết hiệu lực tự động

### Giai đoạn 3 — Analytics & tối ưu (tháng 5+)

- [ ] `KC_VIEW_LOG` + Dashboard analytics
- [ ] Notification (sắp hết hạn, tài liệu mới, bị từ chối)
- [ ] Tích hợp Elasticsearch nếu data lớn
- [ ] Export tài liệu ra PDF/Word

### Cấu hình khuyến nghị cho SME quy mô nhỏ (< 50 nhân viên)

- Cây danh mục: 2 cấp là đủ, tránh phức tạp cho người dùng
- Luồng duyệt: có thể bỏ qua Pending Review, owner tự approve
- Giữ lại 10 version / tài liệu
- Log lượt xem 3 tháng, sau đó aggregate và xóa raw log
- Upload storage: bắt đầu với local storage / MinIO trước khi migrate lên S3

---

*Tài liệu này được tạo bởi AI Assistant và cần được review bởi Technical Lead / Product Owner trước khi đưa vào triển khai chính thức.*

*Version 1.0.0 — Knowledge Center Module Specification*