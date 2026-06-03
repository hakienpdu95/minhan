# Đặc Tả Module: SOP Center (Quản lý Quy trình Vận hành Tiêu chuẩn)

> **Hệ thống:** SaaS SME  
> **Module:** SOP Center  
> **Phiên bản đặc tả:** 1.0.0  
> **Ngày cập nhật:** 2026-06-03  
> **Quy mô mục tiêu:** Small SME (20–100 người)  
> **Trạng thái:** Draft

---

## Mục lục

1. [Tổng quan module](#1-tổng-quan-module)
2. [Phân biệt SOP Center vs Knowledge Center](#2-phân-biệt-sop-center-vs-knowledge-center)
3. [Phạm vi & mục tiêu](#3-phạm-vi--mục-tiêu)
4. [Các sub-module](#4-các-sub-module)
5. [Enum & Constant Values](#5-enum--constant-values)
6. [ERD — Entity Relationship Diagram](#6-erd--entity-relationship-diagram)
7. [Đặc tả bảng dữ liệu](#7-đặc-tả-bảng-dữ-liệu)
   - 7.1 [SOP_CATEGORY — Danh mục quy trình](#71-sop_category--danh-mục-quy-trình)
   - 7.2 [SOP_PROCESS — Quy trình vận hành](#72-sop_process--quy-trình-vận-hành)
   - 7.3 [SOP_STEP — Các bước thực hiện](#73-sop_step--các-bước-thực-hiện)
   - 7.4 [SOP_STEP_RACI — Phân công vai trò từng bước](#74-sop_step_raci--phân-công-vai-trò-từng-bước)
   - 7.5 [SOP_STEP_ATTACHMENT — Tệp đính kèm từng bước](#75-sop_step_attachment--tệp-đính-kèm-từng-bước)
   - 7.6 [SOP_APPROVAL_FLOW — Luồng duyệt đa cấp](#76-sop_approval_flow--luồng-duyệt-đa-cấp)
   - 7.7 [SOP_VERSION_HISTORY — Lịch sử phiên bản](#77-sop_version_history--lịch-sử-phiên-bản)
   - 7.8 [SOP_RELATION — Liên kết giữa các SOP](#78-sop_relation--liên-kết-giữa-các-sop)
   - 7.9 [SOP_TAG — Nhãn tag](#79-sop_tag--nhãn-tag)
   - 7.10 [SOP_PROCESS_TAG — Quan hệ SOP–Tag](#710-sop_process_tag--quan-hệ-soptag)
8. [Luồng nghiệp vụ](#8-luồng-nghiệp-vụ)
   - 8.1 [Tạo & xây dựng SOP](#81-tạo--xây-dựng-sop)
   - 8.2 [Luồng duyệt đa cấp](#82-luồng-duyệt-đa-cấp)
   - 8.3 [Versioning & rollback](#83-versioning--rollback)
   - 8.4 [Liên kết SOP cha–con và SOP liên quan](#84-liên-kết-sop-chacon-và-sop-liên-quan)
   - 8.5 [Review định kỳ & hết hiệu lực](#85-review-định-kỳ--hết-hiệu-lực)
   - 8.6 [RACI Matrix — logic phân công](#86-raci-matrix--logic-phân-công)
9. [Sơ đồ chuyển trạng thái (State Machine)](#9-sơ-đồ-chuyển-trạng-thái-state-machine)
10. [API Endpoints (đề xuất)](#10-api-endpoints-đề-xuất)
11. [Business Rules & Ràng buộc](#11-business-rules--ràng-buộc)
12. [Indexes & Performance](#12-indexes--performance)
13. [Ghi chú triển khai cho SME](#13-ghi-chú-triển-khai-cho-sme)

---

## 1. Tổng quan module

**SOP Center** là module chuyên biệt để **xây dựng, chuẩn hóa và quản lý vòng đời** các Quy trình Vận hành Tiêu chuẩn (Standard Operating Procedures) trong doanh nghiệp SME. Khác với Knowledge Center (lưu trữ tài liệu tĩnh), SOP Center tập trung vào **cấu trúc quy trình có thứ tự bước rõ ràng, phân công trách nhiệm theo mô hình RACI, và vòng đời duyệt chặt chẽ với versioning toàn diện**.

### Mục đích chính

- Chuẩn hóa cách thực hiện công việc trong toàn tổ chức — mọi nhân viên đều làm đúng một cách
- Xây dựng quy trình dạng step-by-step có cấu trúc, không phải tài liệu tự do
- Phân công rõ ràng ai làm gì (Responsible), ai chịu trách nhiệm (Accountable), ai tư vấn (Consulted), ai được thông báo (Informed) ở từng bước
- Kiểm soát phiên bản nghiêm ngặt — rollback hoàn chỉnh cả quy trình lẫn từng bước
- Liên kết SOP với nhau để mô tả quy trình phức tạp dạng cha–con hoặc trigger
- Đảm bảo SOP luôn được review và cập nhật theo chu kỳ

### Người dùng liên quan

| Vai trò | Quyền mặc định |
|---|---|
| **Admin** | Toàn quyền: quản lý danh mục, cấu hình luồng duyệt, xem analytics, xóa |
| **Process Owner** | Tạo, sửa, quản lý SOP mình sở hữu; gửi duyệt; xem lịch sử |
| **Editor / Contributor** | Tạo bản nháp, sửa SOP được phân quyền, không tự duyệt |
| **Approver** | Duyệt hoặc từ chối SOP được giao trong luồng duyệt |
| **Reviewer** | Xem và nhận xét SOP (Consulted trong RACI) |
| **Viewer / Staff** | Chỉ đọc SOP theo phạm vi visibility được cấp |

---

## 2. Phân biệt SOP Center vs Knowledge Center

Hai module này độc lập nhau về thiết kế nhưng bổ sung cho nhau về chức năng.

| Khía cạnh | Knowledge Center | SOP Center |
|---|---|---|
| **Đơn vị cơ bản** | Tài liệu (document) — nội dung tự do | Quy trình (process) — cấu trúc bước có thứ tự |
| **Cấu trúc nội dung** | Free-form Markdown / Rich Text | Step Builder có `step_number`, `step_type`, điều kiện rẽ nhánh |
| **Phân công** | Không có | RACI từng bước (R/A/C/I per step, per role) |
| **Liên kết nội dung** | Tag, danh mục | SOP cha–con, prerequisite, triggers, replaces |
| **Sơ đồ trực quan** | Không | Flowchart/BPMN tự sinh từ steps và step_type |
| **Versioning** | Snapshot text nội dung | Snapshot JSON toàn bộ: process + steps + RACI |
| **Mục tiêu sử dụng** | Đọc & tham khảo | Thực thi & tuân thủ quy trình |
| **Vòng đời** | effective_date / expired_date | Thêm review_date — nhắc nhở review định kỳ |
| **Loại nội dung** | 7 loại (doc, video, FAQ, form...) | 4 loại (standard, emergency, checklist, guideline) |

---

## 3. Phạm vi & mục tiêu

### Trong phạm vi (In Scope)

- Quản lý cây danh mục quy trình đa cấp (tối đa 3 cấp)
- Xây dựng SOP theo step-by-step builder với 5 loại bước: action, decision, sub_sop, notification, wait
- Phân công RACI cho từng bước — hỗ trợ gán theo user hoặc role
- Luồng duyệt đa cấp (sequential approval): Draft → In Review → Approved / Rejected
- Versioning toàn diện: snapshot JSON cả process + steps + RACI, rollback về bất kỳ version nào
- Liên kết SOP: cha–con (parent_sop_id), quan hệ chéo (SOP_RELATION) với 5 kiểu liên kết
- File đính kèm từng bước (ảnh minh họa, video clip, PDF checklist)
- Tag tự do (SOP_TAG / SOP_PROCESS_TAG)
- Mã định danh SOP (`code`): SOP-HR-001, SOP-OPS-012
- Trường `review_date` + cron nhắc nhở review định kỳ
- Tìm kiếm full-text trên title, objective, scope, step descriptions

### Ngoài phạm vi (Out of Scope)

- Thực thi SOP (task execution / workflow engine) — nếu có, là module riêng
- Ký số điện tử trên SOP
- Tích hợp BPMN engine (Camunda, Activiti) — sơ đồ chỉ là render phía frontend từ dữ liệu step
- Đào tạo / kiểm tra nhân viên đã đọc SOP — có thể mở rộng ở phiên bản sau
- Analytics vi phạm SOP — phụ thuộc module Task/Execution (ngoài scope)

---

## 4. Các sub-module

### 4.1 Quản lý danh mục quy trình (Category Management)

Tổ chức SOP thành cây phân cấp. Ví dụ: Vận hành → Sản xuất → QC Đầu vào.

**Chức năng:**
- Tạo / sửa / xóa mềm danh mục
- Kéo thả sắp xếp thứ tự (`sort_order`)
- Gán icon và màu sắc nhận diện
- Xem tổng số SOP trong danh mục và con

**Ràng buộc:**
- Tối đa 3 cấp để tránh phức tạp UI
- Không xóa danh mục khi còn SOP bên trong hoặc có danh mục con
- `slug` unique per org

### 4.2 Step Builder — Xây dựng quy trình

Core của module. Cho phép xây dựng SOP bằng cách thêm, sắp xếp, chỉnh sửa từng bước.

**Chức năng:**
- Thêm / sửa / xóa bước
- Kéo thả sắp xếp lại thứ tự (cập nhật `step_number` hàng loạt)
- Chọn `step_type`: action, decision, sub_sop, notification, wait
- Với `step_type = 'decision'`: định nghĩa 2 nhánh (Yes → step X, No → step Y)
- Với `step_type = 'sub_sop'`: chọn SOP con từ `ref_sop_id`
- Đặt `duration_minutes` cho từng bước → tự tính tổng thời gian SOP
- Đánh dấu bước bắt buộc / tùy chọn (`is_mandatory`)
- Đính kèm file minh họa từng bước (`SOP_STEP_ATTACHMENT`)

### 4.3 RACI Matrix — Phân công vai trò

Phân công trách nhiệm theo mô hình RACI cho từng bước của quy trình.

**Chức năng:**
- Gán R / A / C / I cho từng bước — có thể gán theo user cụ thể hoặc role
- Xem ma trận RACI tổng hợp toàn SOP (bước × vai trò)
- Validation: mỗi bước bắt buộc có ít nhất 1 R (Responsible) và 1 A (Accountable)
- Export RACI matrix ra bảng

**Nguyên tắc RACI:**

| Ký hiệu | Tên | Ý nghĩa |
|---|---|---|
| **R** | Responsible | Người trực tiếp thực hiện bước này |
| **A** | Accountable | Người chịu trách nhiệm cuối cùng về kết quả — chỉ 1 người |
| **C** | Consulted | Người được hỏi ý kiến trước/trong khi thực hiện |
| **I** | Informed | Người được thông báo khi bước hoàn thành |

### 4.4 Approval Flow — Luồng duyệt đa cấp

Cấu hình và thực hiện luồng phê duyệt SOP trước khi phát hành.

**Chức năng:**
- Cấu hình chuỗi approver theo `sequence` (duyệt tuần tự)
- Approver có thể là user cụ thể hoặc role (ai trong role đó đều có thể duyệt)
- Ghi lại comment khi duyệt hoặc từ chối
- Notification tự động đến approver tiếp theo khi approver trước hoàn tất
- Khi bất kỳ approver nào từ chối → toàn bộ luồng dừng, SOP chuyển về `rejected`

### 4.5 Version Control — Quản lý phiên bản

**Chức năng:**
- Snapshot tự động khi SOP được approve: lưu JSON đầy đủ gồm process metadata + toàn bộ steps + RACI
- Xem danh sách lịch sử phiên bản với change_summary
- So sánh diff giữa 2 phiên bản (render ở frontend từ JSON)
- Rollback về phiên bản cụ thể → tạo draft mới từ snapshot, cần duyệt lại

### 4.6 SOP Relations — Liên kết quy trình

**Chức năng:**
- Liên kết SOP với nhau qua 5 kiểu: prerequisite, triggers, references, replaces, related
- Hiển thị sơ đồ liên kết (dependency graph) tại trang chi tiết SOP
- Cảnh báo khi SOP liên kết bị archived hoặc thay đổi version
- SOP con: dùng `parent_sop_id` trên bảng `SOP_PROCESS` — quan hệ cha–con trực tiếp

---

## 5. Enum & Constant Values

### 5.1 SOP_PROCESS.type — Loại quy trình

| Giá trị | Nhãn | Mô tả | Icon gợi ý |
|---|---|---|---|
| `standard` | Quy trình tiêu chuẩn | Quy trình vận hành thông thường, steps tuần tự | ti-list-check |
| `emergency` | Quy trình khẩn cấp | Ưu tiên hiển thị nổi bật, cần thực hiện ngay khi sự cố | ti-alert-triangle |
| `checklist` | Checklist | Danh sách kiểm tra tick-off, không cần thứ tự cứng | ti-checkbox |
| `guideline` | Hướng dẫn linh hoạt | Không bắt buộc từng bước, mang tính tham khảo | ti-book |

### 5.2 SOP_PROCESS.status — Trạng thái vòng đời

| Giá trị | Nhãn | Mô tả | Màu gợi ý |
|---|---|---|---|
| `draft` | Bản nháp | Đang xây dựng, chưa gửi duyệt | Gray |
| `in_review` | Đang duyệt | Đã gửi, đang trong luồng phê duyệt | Amber |
| `approved` | Đang hiệu lực | SOP chính thức, hiển thị theo visibility | Teal/Green |
| `rejected` | Bị từ chối | Approver từ chối, cần chỉnh sửa và gửi lại | Red |
| `archived` | Lưu trữ | Hết hiệu lực, ẩn khỏi danh sách nhưng vẫn truy cập được | Gray |

### 5.3 SOP_PROCESS.priority — Mức ưu tiên

| Giá trị | Nhãn | Mô tả |
|---|---|---|
| `critical` | Nghiêm trọng | Bắt buộc tuân thủ tuyệt đối, vi phạm gây rủi ro lớn |
| `high` | Cao | Quan trọng, cần tuân thủ chặt chẽ |
| `medium` | Trung bình | Nên tuân thủ, có thể linh hoạt trong trường hợp đặc biệt |
| `low` | Thấp | Hướng dẫn tham khảo |

### 5.4 SOP_PROCESS.visibility — Phạm vi hiển thị

| Giá trị | Mô tả |
|---|---|
| `public` | Tất cả user đã đăng nhập trong org |
| `internal` | Chỉ nhân viên chính thức |
| `restricted` | Chỉ user/role/dept được cấp quyền riêng |
| `private` | Chỉ owner và admin |

### 5.5 SOP_STEP.step_type — Loại bước

| Giá trị | Nhãn | Mô tả | Render trên flowchart |
|---|---|---|---|
| `action` | Thực thi | Bước thực hiện thông thường | Hình chữ nhật |
| `decision` | Quyết định | Rẽ nhánh Yes/No — cần thêm trường `branch_yes_step` và `branch_no_step` | Hình thoi |
| `sub_sop` | Gọi SOP con | Thực hiện toàn bộ SOP khác tại đây (`ref_sop_id`) | Hình chữ nhật viền đôi |
| `notification` | Thông báo | Gửi thông báo/email đến người liên quan, không cần chờ phản hồi | Hình bình hành |
| `wait` | Chờ | Chờ điều kiện hoặc khoảng thời gian trước khi tiếp tục | Đồng hồ cát |

### 5.6 SOP_STEP_RACI.raci_type — Loại RACI

| Giá trị | Tên đầy đủ | Ý nghĩa | Ràng buộc |
|---|---|---|---|
| `R` | Responsible | Người trực tiếp làm | Ít nhất 1 per step |
| `A` | Accountable | Người chịu trách nhiệm kết quả | Đúng 1 người per step |
| `C` | Consulted | Người được hỏi ý kiến | Không giới hạn |
| `I` | Informed | Người được thông báo kết quả | Không giới hạn |

### 5.7 SOP_RELATION.relation_type — Kiểu liên kết

| Giá trị | Nhãn | Ý nghĩa |
|---|---|---|
| `prerequisite` | Điều kiện tiên quyết | SOP kia phải hoàn thành trước khi thực hiện SOP này |
| `triggers` | Kích hoạt | Hoàn thành SOP này sẽ kích hoạt SOP kia |
| `references` | Tham chiếu | Tham khảo chéo, không có quan hệ thứ tự |
| `replaces` | Thay thế | SOP này thay thế SOP cũ (SOP cũ đã archived) |
| `related` | Liên quan | Cùng chủ đề / lĩnh vực, không có quan hệ thứ tự |

### 5.8 SOP_APPROVAL_FLOW.status — Trạng thái từng bước duyệt

| Giá trị | Mô tả |
|---|---|
| `pending` | Chờ approver này hành động |
| `approved` | Approver đã duyệt |
| `rejected` | Approver đã từ chối (kết thúc toàn luồng) |
| `skipped` | Bị bỏ qua (approver không có ai nhận, hoặc auto-approve theo rule) |

---

## 6. ERD — Entity Relationship Diagram

```
┌──────────────────────────────────────────────────────────────────────────────────┐
│                              SOP CENTER ERD                                      │
└──────────────────────────────────────────────────────────────────────────────────┘

SOP_CATEGORY
┌─────────────────────────┐
│ id (PK)                 │
│ parent_id (FK → self) ──┼── tự tham chiếu (cây đa cấp, tối đa 3 cấp)
│ name                    │
│ slug (UNIQUE per org)   │
│ icon                    │
│ color_hex               │
│ sort_order              │
│ is_active               │
│ org_id (FK)             │
│ created_by (FK)         │
│ created_at              │
└──────────┬──────────────┘
           │ 1
           │ contains
           │ N
┌──────────▼──────────────────────────────────────────────────────┐
│                       SOP_PROCESS                               │
│─────────────────────────────────────────────────────────────────│
│ id (PK)                                                         │
│ category_id (FK → SOP_CATEGORY)                                 │
│ org_id (FK → organizations)                                     │
│ code (UNIQUE per org)       ◄── SOP-HR-001, SOP-OPS-012         │
│ title                                                           │
│ slug (UNIQUE per org)                                           │
│ objective          ◄── Mục tiêu: tại sao có SOP này            │
│ scope              ◄── Phạm vi áp dụng                         │
│ content_overview   ◄── Tổng quan bổ sung                       │
│ type (ENUM)        ◄── standard|emergency|checklist|guideline   │
│ status (ENUM)      ◄── draft|in_review|approved|rejected|       │
│                         archived                                │
│ priority (ENUM)    ◄── critical|high|medium|low                 │
│ visibility (ENUM)  ◄── public|internal|restricted|private       │
│ version (INT)      ◄── tăng mỗi lần approve                    │
│ owner_id (FK)                                                   │
│ approved_by (FK)                                                │
│ approved_at                                                     │
│ effective_date     ◄── ngày bắt đầu hiệu lực                   │
│ review_date        ◄── ngày cần review định kỳ                  │
│ expired_date       ◄── ngày hết hiệu lực (auto archived)       │
│ parent_sop_id (FK → self) ◄── SOP con/sub-process              │
│ created_by (FK)                                                 │
│ updated_by (FK)                                                 │
│ created_at                                                      │
│ updated_at                                                      │
└──┬────┬────┬────┬────┬────────────────────────────────────────-─┘
   │    │    │    │    │
   │1   │1   │1   │1   │1
   │    │    │    │    │
   ▼N   ▼N   ▼N   ▼N   ▼N
┌──────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌───────────┐
│SOP_  │ │SOP_      │ │SOP_      │ │SOP_      │ │SOP_       │
│STEP  │ │APPROVAL_ │ │VERSION_  │ │RELATION  │ │PROCESS_   │
│      │ │FLOW      │ │HISTORY   │ │          │ │TAG        │
│──────│ │──────────│ │──────────│ │──────────│ │───────────│
│id(PK)│ │id (PK)   │ │id (PK)   │ │id (PK)   │ │sop_id(FK) │
│sop_id│ │sop_id(FK)│ │sop_id(FK)│ │sop_id(FK)│ │tag_id(FK) │
│step_ │ │sequence  │ │version_  │ │related_  │ └──┬────────┘
│number│ │approver  │ │number    │ │sop_id(FK)│    │N
│title │ │_id (FK)  │ │snapshot_ │ │relation_ │    │
│descr │ │approver  │ │json      │ │type      │    │1
│expec │ │_type     │ │change_   │ │notes     │ ┌──▼────────┐
│_outp │ │status    │ │summary   │ │created_by│ │ SOP_TAG   │
│warni │ │comment   │ │changed_by│ │created_at│ │───────────│
│ng    │ │actioned  │ │changed_at│ └──────────┘ │id (PK)    │
│step_ │ │_at       │ └──────────┘              │org_id(FK) │
│type  │ └──────────┘                           │name       │
│ref_  │                                        │slug       │
│sop_id│                                        │color_hex  │
│(FK)  │                                        └───────────┘
│dur_  │
│min   │
│is_   │
│mand  │
└──┬───┘
   │ 1
   │
   ├──────────────────────────┐
   │N                         │N
   ▼                          ▼
┌─────────────────┐  ┌──────────────────────┐
│ SOP_STEP_RACI   │  │ SOP_STEP_ATTACHMENT  │
│─────────────────│  │──────────────────────│
│ id (PK)         │  │ id (PK)              │
│ step_id (FK)    │  │ step_id (FK)         │
│ role_id (FK)    │  │ file_name            │
│ raci_type (ENUM)│  │ file_url             │
│ notes           │  │ file_type            │
└─────────────────┘  │ file_size_kb         │
 R|A|C|I per step    │ storage_provider     │
                     │ sort_order           │
                     │ uploaded_by (FK)     │
                     │ uploaded_at          │
                     └──────────────────────┘
```

### Quan hệ tổng hợp

| Bảng A | Quan hệ | Bảng B | Ghi chú |
|---|---|---|---|
| SOP_CATEGORY | 1 → N | SOP_CATEGORY | Tự tham chiếu — cây danh mục đa cấp |
| SOP_CATEGORY | 1 → N | SOP_PROCESS | Mỗi SOP thuộc 1 danh mục |
| SOP_PROCESS | 1 → N | SOP_PROCESS | Self-ref: parent_sop_id — SOP cha–con |
| SOP_PROCESS | 1 → N | SOP_STEP | Nhiều bước trong một quy trình |
| SOP_STEP | 1 → N | SOP_STEP_RACI | RACI assignments cho bước |
| SOP_STEP | 1 → N | SOP_STEP_ATTACHMENT | File đính kèm minh họa bước |
| SOP_PROCESS | 1 → N | SOP_APPROVAL_FLOW | Chuỗi người duyệt |
| SOP_PROCESS | 1 → N | SOP_VERSION_HISTORY | Lịch sử snapshot phiên bản |
| SOP_PROCESS | 1 → N | SOP_RELATION | Liên kết với SOP khác (nguồn) |
| SOP_PROCESS | N → M | SOP_TAG | Qua bảng SOP_PROCESS_TAG |

---

## 7. Đặc tả bảng dữ liệu

### 7.1 SOP_CATEGORY — Danh mục quy trình

**Mục đích:** Tổ chức SOP thành cấu trúc cây (tối đa 3 cấp). Ví dụ: Nhân sự → Tuyển dụng → Onboarding.

| Trường | Kiểu dữ liệu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | Khóa chính |
| `parent_id` | UUID | NULL | FK (self) | NULL | Danh mục cha — NULL nếu là cấp gốc |
| `org_id` | UUID | NOT NULL | FK (organizations) | | Tổ chức sở hữu |
| `name` | VARCHAR(150) | NOT NULL | | | Tên danh mục hiển thị |
| `slug` | VARCHAR(160) | NOT NULL | UNIQUE(org_id) | | Định danh URL-friendly, tự sinh |
| `description` | TEXT | NULL | | NULL | Mô tả ngắn về loại quy trình trong danh mục |
| `icon` | VARCHAR(80) | NULL | | NULL | Tên Tabler Icon, vd: `ti-settings`, `ti-users` |
| `color_hex` | CHAR(7) | NULL | | NULL | Mã màu nhận diện, vd: `#1D9E75` |
| `sort_order` | INT | NOT NULL | | 0 | Thứ tự hiển thị trong cùng cấp |
| `is_active` | BOOLEAN | NOT NULL | | TRUE | FALSE = ẩn danh mục (soft disable) |
| `created_by` | UUID | NOT NULL | FK (users) | | Người tạo |
| `updated_by` | UUID | NULL | FK (users) | NULL | Người cập nhật lần cuối |
| `created_at` | TIMESTAMP | NOT NULL | | NOW() | |
| `updated_at` | TIMESTAMP | NOT NULL | | NOW() | |

**Indexes:**

```sql
CREATE UNIQUE INDEX idx_sop_cat_slug_org ON SOP_CATEGORY(org_id, slug);
CREATE INDEX idx_sop_cat_parent ON SOP_CATEGORY(parent_id);
CREATE INDEX idx_sop_cat_sort ON SOP_CATEGORY(parent_id, sort_order);
```

**Ràng buộc:**
- Không xóa khi còn `SOP_PROCESS` trỏ vào hoặc có danh mục con
- `parent_id` không được tạo chu kỳ (không trỏ về con cháu của chính nó)
- Tối đa 3 cấp — kiểm tra tại application layer

---

### 7.2 SOP_PROCESS — Quy trình vận hành

**Mục đích:** Bảng trung tâm lưu trữ metadata của quy trình, trạng thái vòng đời, và các thông tin điều phối (owner, approver, effective/review/expired date). Nội dung chi tiết được tổ chức trong `SOP_STEP`.

| Trường | Kiểu dữ liệu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | Khóa chính |
| `category_id` | UUID | NOT NULL | FK (SOP_CATEGORY) | | Danh mục chứa SOP |
| `org_id` | UUID | NOT NULL | FK (organizations) | | Tổ chức sở hữu — multi-tenant |
| `code` | VARCHAR(30) | NOT NULL | UNIQUE(org_id) | | Mã định danh: `SOP-HR-001`, `SOP-OPS-012` |
| `title` | VARCHAR(300) | NOT NULL | | | Tiêu đề đầy đủ của quy trình |
| `slug` | VARCHAR(320) | NOT NULL | UNIQUE(org_id) | | URL-friendly, tự sinh từ title |
| `objective` | TEXT | NULL | | NULL | Mục tiêu — trả lời "tại sao có SOP này" |
| `scope` | TEXT | NULL | | NULL | Phạm vi áp dụng: đối tượng, bộ phận, tình huống |
| `content_overview` | TEXT | NULL | | NULL | Mô tả tổng quan hoặc ghi chú bổ sung |
| `type` | ENUM | NOT NULL | INDEX | `standard` | standard \| emergency \| checklist \| guideline |
| `status` | ENUM | NOT NULL | INDEX | `draft` | draft \| in_review \| approved \| rejected \| archived |
| `priority` | ENUM | NOT NULL | | `medium` | critical \| high \| medium \| low |
| `visibility` | ENUM | NOT NULL | | `internal` | public \| internal \| restricted \| private |
| `version` | INT | NOT NULL | | 1 | Số phiên bản hiện tại — tăng khi approve |
| `owner_id` | UUID | NOT NULL | FK (users) | | Người chịu trách nhiệm sở hữu SOP |
| `approved_by` | UUID | NULL | FK (users) | NULL | Người duyệt cuối cùng |
| `approved_at` | TIMESTAMP | NULL | | NULL | Thời điểm được duyệt |
| `effective_date` | TIMESTAMP | NULL | | NULL | Ngày SOP bắt đầu có hiệu lực |
| `review_date` | TIMESTAMP | NULL | INDEX | NULL | Ngày cần review định kỳ — cron nhắc owner |
| `expired_date` | TIMESTAMP | NULL | INDEX | NULL | Ngày hết hiệu lực — cron tự chuyển archived |
| `parent_sop_id` | UUID | NULL | FK (self) | NULL | SOP cha nếu đây là sub-process / SOP con |
| `created_by` | UUID | NOT NULL | FK (users) | | Người tạo |
| `updated_by` | UUID | NULL | FK (users) | NULL | Người cập nhật lần cuối |
| `created_at` | TIMESTAMP | NOT NULL | | NOW() | |
| `updated_at` | TIMESTAMP | NOT NULL | | NOW() | |

**Indexes:**

```sql
CREATE UNIQUE INDEX idx_sop_code_org ON SOP_PROCESS(org_id, code);
CREATE UNIQUE INDEX idx_sop_slug_org ON SOP_PROCESS(org_id, slug);
CREATE INDEX idx_sop_category ON SOP_PROCESS(category_id);
CREATE INDEX idx_sop_status ON SOP_PROCESS(status);
CREATE INDEX idx_sop_type ON SOP_PROCESS(type);
CREATE INDEX idx_sop_owner ON SOP_PROCESS(owner_id);
CREATE INDEX idx_sop_parent ON SOP_PROCESS(parent_sop_id);
CREATE INDEX idx_sop_review ON SOP_PROCESS(review_date) WHERE review_date IS NOT NULL;
CREATE INDEX idx_sop_expired ON SOP_PROCESS(expired_date) WHERE expired_date IS NOT NULL;
CREATE FULLTEXT INDEX idx_sop_search ON SOP_PROCESS(title, objective, scope, content_overview);
```

**Ghi chú thiết kế:**
- `code` được sinh tự động theo pattern `SOP-{CATEGORY_CODE}-{SEQUENCE}`, ví dụ `SOP-HR-001`
- Khi SOP `approved` được sửa đổi, tạo draft mới — SOP cũ vẫn ở trạng thái `approved` cho đến khi bản sửa được approve xong
- `parent_sop_id` dùng cho quan hệ cha–con trực tiếp (sub-process). Quan hệ chéo phức tạp hơn dùng bảng `SOP_RELATION`

---

### 7.3 SOP_STEP — Các bước thực hiện

**Mục đích:** Lưu từng bước của quy trình theo thứ tự. Đây là nội dung chính của SOP — mỗi bước mô tả một hành động cụ thể, kết quả mong đợi và cảnh báo nếu có.

| Trường | Kiểu dữ liệu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | Khóa chính |
| `sop_id` | UUID | NOT NULL | FK (SOP_PROCESS) | | SOP chứa bước này |
| `step_number` | INT | NOT NULL | INDEX | | Số thứ tự — hỗ trợ sắp xếp lại (update hàng loạt khi drag & drop) |
| `title` | VARCHAR(200) | NOT NULL | | | Tên bước ngắn gọn, vd: "Kiểm tra tài liệu đầu vào" |
| `description` | TEXT | NULL | | NULL | Hướng dẫn chi tiết từng thao tác cần thực hiện |
| `expected_output` | TEXT | NULL | | NULL | Kết quả / đầu ra mong đợi sau khi hoàn thành bước |
| `warning_note` | TEXT | NULL | | NULL | Cảnh báo, lưu ý đặc biệt — hiển thị nổi bật màu vàng/đỏ |
| `step_type` | ENUM | NOT NULL | | `action` | action \| decision \| sub_sop \| notification \| wait |
| `branch_yes_step` | INT | NULL | | NULL | Nếu step_type=decision: số bước tiếp theo nếu Yes |
| `branch_no_step` | INT | NULL | | NULL | Nếu step_type=decision: số bước tiếp theo nếu No |
| `ref_sop_id` | UUID | NULL | FK (SOP_PROCESS) | NULL | Nếu step_type=sub_sop: ID của SOP con cần thực hiện |
| `duration_minutes` | INT | NULL | | NULL | Thời gian ước tính (phút) — dùng tính tổng thời gian SOP |
| `is_mandatory` | BOOLEAN | NOT NULL | | TRUE | FALSE = bước tùy chọn, có thể bỏ qua |
| `created_by` | UUID | NOT NULL | FK (users) | | Người tạo bước |
| `updated_by` | UUID | NULL | FK (users) | NULL | Người cập nhật bước |
| `created_at` | TIMESTAMP | NOT NULL | | NOW() | |
| `updated_at` | TIMESTAMP | NOT NULL | | NOW() | |

**Indexes:**

```sql
CREATE INDEX idx_sop_step_sop ON SOP_STEP(sop_id);
CREATE UNIQUE INDEX idx_sop_step_order ON SOP_STEP(sop_id, step_number);
```

**Tính năng tổng thời gian:**

```sql
-- Tổng thời gian ước tính của một SOP
SELECT SUM(duration_minutes) AS total_duration_minutes
FROM SOP_STEP
WHERE sop_id = :sop_id
  AND is_mandatory = TRUE;
```

**Ghi chú thiết kế:**
- Khi kéo thả sắp xếp lại: update hàng loạt `step_number` của tất cả bước trong cùng SOP
- `branch_yes_step` / `branch_no_step` lưu giá trị `step_number` (không phải UUID) để dễ render flowchart
- `step_type = 'sub_sop'` + `ref_sop_id`: khi hiển thị, render như một bước đặc biệt với link đến SOP con

---

### 7.4 SOP_STEP_RACI — Phân công vai trò từng bước

**Mục đích:** Lưu phân công RACI (Responsible / Accountable / Consulted / Informed) cho từng bước của quy trình. Thiết kế per-step để đủ linh hoạt — mỗi bước có thể do bộ phận khác nhau thực hiện.

| Trường | Kiểu dữ liệu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | Khóa chính |
| `step_id` | UUID | NOT NULL | FK (SOP_STEP) | | Bước được phân công |
| `assignee_type` | ENUM | NOT NULL | | `role` | `user` hoặc `role` — xác định assignee_id là gì |
| `assignee_id` | UUID | NOT NULL | | | FK tới `users.id` hoặc `roles.id` tùy `assignee_type` |
| `raci_type` | ENUM | NOT NULL | | | R \| A \| C \| I |
| `notes` | TEXT | NULL | | NULL | Ghi chú bổ sung cho vai trò này trong bước này |

**Indexes:**

```sql
CREATE INDEX idx_raci_step ON SOP_STEP_RACI(step_id);
CREATE INDEX idx_raci_assignee ON SOP_STEP_RACI(assignee_type, assignee_id);
CREATE UNIQUE INDEX idx_raci_unique ON SOP_STEP_RACI(step_id, assignee_type, assignee_id, raci_type);
```

**Business rules RACI (validation tại application layer):**

```
Trước khi lưu / submit duyệt, kiểm tra với mỗi bước IS MANDATORY:
1. Phải có ít nhất 1 record raci_type = 'R' (Responsible)
2. Phải có đúng 1 record raci_type = 'A' (Accountable)
   → Nếu > 1 record A: báo lỗi "Một bước chỉ được có 1 Accountable"
3. Số lượng C và I không giới hạn
```

**Ví dụ RACI matrix cho SOP Onboarding nhân viên mới:**

```
Bước                     | HR Manager | IT Support | Direct Manager | Admin
─────────────────────────┼────────────┼────────────┼────────────────┼──────
1. Chuẩn bị hợp đồng     |    R, A    |     I      |       I        |   C
2. Tạo tài khoản hệ thống|     I      |    R, A    |       I        |   I
3. Cung cấp thiết bị     |     C      |    R, A    |       I        |   I
4. Giới thiệu team       |     I      |     I      |      R, A      |   I
5. Đào tạo nội quy       |    R, A    |     I      |       C        |   I
```

---

### 7.5 SOP_STEP_ATTACHMENT — Tệp đính kèm từng bước

**Mục đích:** Lưu file minh họa cho từng bước — ảnh chụp màn hình, hình ảnh thao tác, video clip ngắn, PDF checklist con. File thực tế lưu trên object storage.

| Trường | Kiểu dữ liệu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | Khóa chính |
| `step_id` | UUID | NOT NULL | FK (SOP_STEP) | | Bước chứa file này |
| `file_name` | VARCHAR(255) | NOT NULL | | | Tên file gốc khi upload |
| `file_url` | TEXT | NOT NULL | | | URL đầy đủ để xem/tải |
| `file_type` | VARCHAR(50) | NOT NULL | | | MIME type: `image/png`, `video/mp4`, `application/pdf` |
| `file_size_kb` | INT | NOT NULL | | | Kích thước tính bằng KB |
| `storage_provider` | VARCHAR(20) | NOT NULL | | `s3` | `s3`, `gcs`, `local` |
| `storage_key` | VARCHAR(500) | NOT NULL | | | Object key nội bộ trên storage |
| `alt_text` | VARCHAR(300) | NULL | | NULL | Mô tả ngắn file (accessibility + tooltip) |
| `sort_order` | INT | NOT NULL | | 0 | Thứ tự hiển thị file trong bước |
| `uploaded_by` | UUID | NOT NULL | FK (users) | | Người upload |
| `uploaded_at` | TIMESTAMP | NOT NULL | | NOW() | |

**Indexes:**

```sql
CREATE INDEX idx_sop_attach_step ON SOP_STEP_ATTACHMENT(step_id);
CREATE INDEX idx_sop_attach_sort ON SOP_STEP_ATTACHMENT(step_id, sort_order);
```

---

### 7.6 SOP_APPROVAL_FLOW — Luồng duyệt đa cấp

**Mục đích:** Lưu chuỗi người duyệt theo thứ tự (`sequence`) cho một SOP. Khi SOP được gửi duyệt, hệ thống tuần tự gửi thông báo cho từng approver theo `sequence` tăng dần.

| Trường | Kiểu dữ liệu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | Khóa chính |
| `sop_id` | UUID | NOT NULL | FK (SOP_PROCESS) | | SOP trong luồng duyệt |
| `sequence` | INT | NOT NULL | | | Thứ tự duyệt: 1 = duyệt trước, 2 = duyệt sau, ... |
| `approver_id` | UUID | NOT NULL | | | FK tới `users.id` hoặc `roles.id` |
| `approver_type` | ENUM | NOT NULL | | `user` | `user` \| `role` |
| `status` | ENUM | NOT NULL | | `pending` | pending \| approved \| rejected \| skipped |
| `comment` | TEXT | NULL | | NULL | Nhận xét khi duyệt hoặc lý do từ chối |
| `actioned_at` | TIMESTAMP | NULL | | NULL | Thời điểm approver hành động |
| `notified_at` | TIMESTAMP | NULL | | NULL | Thời điểm gửi notification đến approver này |

**Indexes:**

```sql
CREATE INDEX idx_sop_approval_sop ON SOP_APPROVAL_FLOW(sop_id);
CREATE INDEX idx_sop_approval_approver ON SOP_APPROVAL_FLOW(approver_type, approver_id);
CREATE UNIQUE INDEX idx_sop_approval_seq ON SOP_APPROVAL_FLOW(sop_id, sequence);
```

**Logic luồng duyệt tuần tự:**

```
Khi SOP được submit (status → 'in_review'):
  1. Lấy tất cả SOP_APPROVAL_FLOW của sop_id, sắp xếp theo sequence ASC
  2. Set status = 'pending' cho record sequence = 1
  3. Gửi notification đến approver sequence = 1

Khi approver tại sequence N hành động:
  APPROVED:
    - Cập nhật record N: status = 'approved', actioned_at = NOW()
    - Kiểm tra có record sequence = N+1 không?
      + Có: set status = 'pending', gửi notification đến approver N+1
      + Không (đây là approver cuối): SOP.status → 'approved'
                                      tạo SOP_VERSION_HISTORY snapshot
                                      SOP.version += 1
                                      SOP.approved_by = approver cuối
                                      SOP.approved_at = NOW()
  REJECTED:
    - Cập nhật record N: status = 'rejected', comment = ..., actioned_at = NOW()
    - SOP.status → 'rejected'
    - Set tất cả record còn pending → 'skipped'
    - Gửi notification đến SOP.owner_id với lý do từ chối
```

---

### 7.7 SOP_VERSION_HISTORY — Lịch sử phiên bản

**Mục đích:** Lưu snapshot đầy đủ của SOP tại mỗi thời điểm được approve — bao gồm cả process metadata, toàn bộ steps và RACI assignments. Cho phép rollback hoàn chỉnh.

| Trường | Kiểu dữ liệu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | Khóa chính |
| `sop_id` | UUID | NOT NULL | FK (SOP_PROCESS) | | SOP được snapshot |
| `version_number` | INT | NOT NULL | | | Số phiên bản — bằng SOP_PROCESS.version tại thời điểm snapshot |
| `snapshot_json` | LONGTEXT | NOT NULL | | | JSON đầy đủ: `{process: {...}, steps: [...], raci: [...]}` |
| `change_summary` | TEXT | NULL | | NULL | Tóm tắt nội dung thay đổi so với version trước |
| `changed_by` | UUID | NOT NULL | FK (users) | | Người thực hiện approve (approver cuối trong luồng) |
| `changed_at` | TIMESTAMP | NOT NULL | | NOW() | Thời điểm snapshot được tạo |

**Indexes:**

```sql
CREATE INDEX idx_sop_version_sop ON SOP_VERSION_HISTORY(sop_id);
CREATE UNIQUE INDEX idx_sop_version_unique ON SOP_VERSION_HISTORY(sop_id, version_number);
```

**Cấu trúc `snapshot_json`:**

```json
{
  "process": {
    "id": "uuid",
    "code": "SOP-HR-001",
    "title": "Quy trình onboarding nhân viên mới",
    "objective": "...",
    "scope": "...",
    "type": "standard",
    "priority": "high",
    "version": 3
  },
  "steps": [
    {
      "step_number": 1,
      "title": "Chuẩn bị hợp đồng",
      "description": "...",
      "expected_output": "...",
      "step_type": "action",
      "duration_minutes": 30,
      "is_mandatory": true,
      "warning_note": null
    }
  ],
  "raci": [
    {
      "step_number": 1,
      "assignee_type": "role",
      "assignee_id": "uuid-hr-manager",
      "raci_type": "R"
    }
  ]
}
```

**Ghi chú:**
- Snapshot toàn bộ JSON (không chỉ text) để rollback có thể phục hồi cả cấu trúc steps và RACI
- Với SME 20–100 người, giới hạn giữ tối đa **20 version gần nhất** / SOP; version cũ hơn xóa bằng cron
- Rollback logic: tạo draft mới từ `snapshot_json`, không xóa lịch sử

---

### 7.8 SOP_RELATION — Liên kết giữa các SOP

**Mục đích:** Quản lý quan hệ chéo giữa các SOP với 5 kiểu liên kết. Dùng cho các quan hệ không phải cha–con trực tiếp (đã có `parent_sop_id` trong `SOP_PROCESS`).

| Trường | Kiểu dữ liệu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | Khóa chính |
| `sop_id` | UUID | NOT NULL | FK (SOP_PROCESS) | | SOP nguồn (đang liên kết đến) |
| `related_sop_id` | UUID | NOT NULL | FK (SOP_PROCESS) | | SOP đích (được liên kết tới) |
| `relation_type` | ENUM | NOT NULL | | | prerequisite \| triggers \| references \| replaces \| related |
| `notes` | TEXT | NULL | | NULL | Ghi chú mô tả quan hệ cụ thể |
| `created_by` | UUID | NOT NULL | FK (users) | | Người tạo liên kết |
| `created_at` | TIMESTAMP | NOT NULL | | NOW() | |

**Indexes:**

```sql
CREATE UNIQUE INDEX idx_sop_rel_unique ON SOP_RELATION(sop_id, related_sop_id, relation_type);
CREATE INDEX idx_sop_rel_source ON SOP_RELATION(sop_id);
CREATE INDEX idx_sop_rel_target ON SOP_RELATION(related_sop_id);
```

**Ràng buộc:**
- `sop_id` không được bằng `related_sop_id` (không tự liên kết)
- Khi SOP liên kết bị archived: cảnh báo owner SOP nguồn để cập nhật liên kết
- `relation_type = 'replaces'`: kiểm tra `related_sop_id` phải có status = `archived`

---

### 7.9 SOP_TAG — Nhãn tag

**Mục đích:** Tag tự do của tổ chức dùng để gắn vào SOP, tăng khả năng tìm kiếm và phân nhóm chéo danh mục.

| Trường | Kiểu dữ liệu | Null | Key | Default | Mô tả |
|---|---|---|---|---|---|
| `id` | UUID | NOT NULL | PK | gen_random_uuid() | Khóa chính |
| `org_id` | UUID | NOT NULL | FK (organizations) | | Tag thuộc org nào |
| `name` | VARCHAR(80) | NOT NULL | | | Tên tag hiển thị, vd: "ISO 9001", "An toàn lao động" |
| `slug` | VARCHAR(90) | NOT NULL | UNIQUE(org_id) | | Định danh URL-friendly |
| `color_hex` | CHAR(7) | NULL | | NULL | Màu badge hiển thị |
| `created_at` | TIMESTAMP | NOT NULL | | NOW() | |

```sql
CREATE UNIQUE INDEX idx_sop_tag_slug_org ON SOP_TAG(org_id, slug);
```

---

### 7.10 SOP_PROCESS_TAG — Quan hệ SOP–Tag

**Mục đích:** Bảng pivot nhiều–nhiều giữa `SOP_PROCESS` và `SOP_TAG`.

| Trường | Kiểu dữ liệu | Null | Key | Mô tả |
|---|---|---|---|---|
| `sop_id` | UUID | NOT NULL | PK, FK (SOP_PROCESS) | SOP |
| `tag_id` | UUID | NOT NULL | PK, FK (SOP_TAG) | Tag được gắn |

```sql
ALTER TABLE SOP_PROCESS_TAG ADD PRIMARY KEY (sop_id, tag_id);
CREATE INDEX idx_sop_ptag_tag ON SOP_PROCESS_TAG(tag_id);
```

---

## 8. Luồng nghiệp vụ

### 8.1 Tạo & xây dựng SOP

```
[Process Owner / Editor]
       │
       ▼
  Tạo SOP mới
  ├── Điền metadata: title, code, category, type, priority, visibility
  ├── Viết objective và scope
  └── status = 'draft'
       │
       ▼
  Xây dựng steps (Step Builder)
  ├── Thêm bước lần lượt (step_number tự tăng)
  ├── Với mỗi bước:
  │   ├── Đặt title, description, expected_output, warning_note
  │   ├── Chọn step_type (action/decision/sub_sop/notification/wait)
  │   ├── Nếu decision: đặt branch_yes_step và branch_no_step
  │   ├── Nếu sub_sop: chọn ref_sop_id
  │   ├── Đặt duration_minutes, is_mandatory
  │   └── Upload file minh họa → SOP_STEP_ATTACHMENT
  ├── Kéo thả sắp xếp lại thứ tự
  └── Lưu nháp bất kỳ lúc nào
       │
       ▼
  Phân công RACI (RACI Matrix)
  ├── Với mỗi bước is_mandatory:
  │   ├── Gán R: ai thực hiện (user hoặc role)
  │   ├── Gán A: ai chịu trách nhiệm (đúng 1 người)
  │   ├── Gán C: ai được hỏi ý kiến (tùy chọn)
  │   └── Gán I: ai được thông báo (tùy chọn)
  └── Validation: tất cả bước mandatory đều có R và A
       │
       ▼
  Gửi duyệt → Xem mục 8.2
```

**Điều kiện gửi duyệt (validation):**
- `title` không trống
- `category_id` hợp lệ
- Có ít nhất 1 bước (`SOP_STEP`)
- Tất cả bước `is_mandatory = TRUE` phải có ít nhất 1 RACI record (R) và đúng 1 (A)
- `code` không trùng trong org

---

### 8.2 Luồng duyệt đa cấp

```
[Process Owner]
       │
       ▼
  Submit duyệt
  ├── SOP.status → 'in_review'
  ├── Lấy danh sách SOP_APPROVAL_FLOW theo sequence ASC
  └── Set record sequence=1: status='pending', gửi notification

[Approver 1]
       │
       ├── APPROVE
       │   ├── Record 1: status='approved', actioned_at=NOW()
       │   └── Còn approver tiếp theo?
       │       ├── CÓ: Set sequence=2 → 'pending', gửi notification
       │       └── KHÔNG (approver cuối):
       │           ├── SOP.status → 'approved'
       │           ├── SOP.version += 1
       │           ├── SOP.approved_by = approver_id
       │           ├── SOP.approved_at = NOW()
       │           └── Tạo SOP_VERSION_HISTORY snapshot
       │
       └── REJECT (kèm comment)
           ├── Record 1: status='rejected'
           ├── Set tất cả record còn pending → 'skipped'
           ├── SOP.status → 'rejected'
           └── Gửi notification đến owner: "Bị từ chối bởi [approver] — [lý do]"

[Process Owner sau khi bị Reject]
       │
       ▼
  Chỉnh sửa SOP (SOP.status vẫn là 'rejected', có thể edit)
       │
       ▼
  Submit lại → Reset tất cả SOP_APPROVAL_FLOW về 'pending'
             → SOP.status → 'in_review'
             → Bắt đầu lại từ sequence = 1
```

---

### 8.3 Versioning & rollback

```
Khi SOP được Approve (approver cuối):
  1. SOP.version += 1 (ví dụ: 2 → 3)
  2. INSERT INTO SOP_VERSION_HISTORY:
     - version_number = SOP.version (mới)
     - snapshot_json = serialize({
         process: SOP_PROCESS record hiện tại,
         steps: tất cả SOP_STEP của SOP này,
         raci: tất cả SOP_STEP_RACI của các step này
       })
     - change_summary = [nhập khi submit hoặc tự động detect diff]
     - changed_by = approver cuối

Khi muốn Rollback về version V:
  1. Lấy SOP_VERSION_HISTORY với sop_id = X, version_number = V
  2. Parse snapshot_json
  3. Tạo draft mới:
     a. Cập nhật SOP_PROCESS fields từ snapshot.process (giữ nguyên id, org_id, owner_id)
     b. Xóa tất cả SOP_STEP hiện tại của SOP này
     c. INSERT lại từ snapshot.steps
     d. Xóa tất cả SOP_STEP_RACI liên quan
     e. INSERT lại từ snapshot.raci
     f. SOP.status = 'draft' (cần duyệt lại)
     g. SOP.version giữ nguyên (KHÔNG đặt lại về V)
     h. change_summary mới: "Rolled back to version V"
  4. Lịch sử không bị xóa — tất cả version vẫn tồn tại
```

---

### 8.4 Liên kết SOP cha–con và SOP liên quan

**SOP cha–con (parent_sop_id):**

```
SOP-OPS-001: Quy trình tiếp nhận khách hàng mới
  ├── SOP-OPS-001a: Kiểm tra thông tin KYC (parent_sop_id = SOP-OPS-001)
  ├── SOP-OPS-001b: Tạo hồ sơ hệ thống (parent_sop_id = SOP-OPS-001)
  └── SOP-OPS-001c: Gửi welcome package (parent_sop_id = SOP-OPS-001)
```

- SOP con được liên kết trong SOP_STEP qua `step_type = 'sub_sop'` + `ref_sop_id`
- Danh sách SOP con cũng có thể truy vấn trực tiếp qua `parent_sop_id`

**SOP liên quan (SOP_RELATION):**

```sql
-- Lấy tất cả SOP liên quan đến SOP X
SELECT r.relation_type, p.code, p.title, p.status
FROM SOP_RELATION r
JOIN SOP_PROCESS p ON p.id = r.related_sop_id
WHERE r.sop_id = :sop_id

UNION

SELECT r.relation_type, p.code, p.title, p.status
FROM SOP_RELATION r
JOIN SOP_PROCESS p ON p.id = r.sop_id
WHERE r.related_sop_id = :sop_id;
```

---

### 8.5 Review định kỳ & hết hiệu lực

```
[Cron Job — chạy mỗi ngày lúc 07:00]

NHẮC NHỞ REVIEW:
  SELECT * FROM SOP_PROCESS
  WHERE status = 'approved'
    AND review_date IS NOT NULL
    AND review_date <= NOW() + INTERVAL 30 DAY
    AND review_date > NOW()
  → Gửi notification đến owner_id:
    "SOP {code} — {title} sẽ đến hạn review vào {review_date}"

REVIEW QUÁ HẠN:
  SELECT * FROM SOP_PROCESS
  WHERE status = 'approved'
    AND review_date IS NOT NULL
    AND review_date < NOW()
  → Gửi notification nhắc nhở cấp 2 đến owner_id + admin:
    "SOP {code} — {title} đã quá hạn review từ {review_date}"

HẾT HIỆU LỰC:
  SELECT * FROM SOP_PROCESS
  WHERE status = 'approved'
    AND expired_date IS NOT NULL
    AND expired_date <= NOW()
  → UPDATE status = 'archived'
  → INSERT notification đến owner_id:
    "SOP {code} đã hết hiệu lực và được chuyển sang Archived"
```

---

### 8.6 RACI Matrix — logic phân công

**Tổng hợp ma trận cho toàn SOP:**

```sql
-- Lấy RACI matrix đầy đủ cho một SOP
SELECT
  s.step_number,
  s.title AS step_title,
  r.raci_type,
  r.assignee_type,
  CASE
    WHEN r.assignee_type = 'user' THEN u.full_name
    WHEN r.assignee_type = 'role' THEN ro.name
  END AS assignee_name
FROM SOP_STEP s
LEFT JOIN SOP_STEP_RACI r ON r.step_id = s.id
LEFT JOIN users u ON r.assignee_type = 'user' AND u.id = r.assignee_id
LEFT JOIN roles ro ON r.assignee_type = 'role' AND ro.id = r.assignee_id
WHERE s.sop_id = :sop_id
ORDER BY s.step_number, r.raci_type;
```

---

## 9. Sơ đồ chuyển trạng thái (State Machine)

```
                    ┌─────────┐
                    │  DRAFT  │◄──────────────────────────┐
                    └────┬────┘                           │
                         │ submit()                       │ sửa & gửi lại
                         ▼                                │
                  ┌─────────────┐    reject()    ┌────────┴──────┐
                  │  IN_REVIEW  │───────────────►│   REJECTED    │
                  └──────┬──────┘                └───────────────┘
                         │ approve() [approver cuối]
                         ▼
                  ┌─────────────┐
                  │  APPROVED   │
                  └──────┬──────┘
                         │
              ┌──────────┴───────────┐
              │ manual archive()     │ auto: expired_date <= NOW()
              │ hoặc có version mới  │ (cron job)
              ▼                      ▼
       ┌─────────────────────────────────┐
       │            ARCHIVED             │
       └─────────────────────────────────┘

Đặc biệt: APPROVED → [chỉnh sửa] → tạo DRAFT mới
          APPROVED cũ vẫn có hiệu lực cho đến khi DRAFT mới được APPROVED
```

**Các hành động hợp lệ theo trạng thái:**

| Từ trạng thái | Hành động | Đến trạng thái | Điều kiện |
|---|---|---|---|
| `draft` | `submit()` | `in_review` | Qua validation đầy đủ |
| `draft` | `delete()` | — | Chỉ owner hoặc admin |
| `in_review` | `approve()` | `in_review` hoặc `approved` | Approver trong luồng |
| `in_review` | `reject()` | `rejected` | Approver trong luồng, kèm comment |
| `in_review` | `withdraw()` | `draft` | Chỉ owner — rút lại trước khi ai duyệt |
| `rejected` | `submit()` | `in_review` | Sau khi chỉnh sửa |
| `approved` | `archive()` | `archived` | Admin hoặc owner |
| `approved` | `edit()` | `draft` (bản mới) | Tạo draft mới, bản cũ vẫn approved |
| `archived` | `restore()` | `draft` | Admin — tạo draft mới từ archived |

---

## 10. API Endpoints (đề xuất)

### Danh mục (Category)

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/sop/categories` | Lấy cây danh mục |
| GET | `/api/sop/categories/:id` | Chi tiết danh mục + số SOP |
| POST | `/api/sop/categories` | Tạo danh mục |
| PUT | `/api/sop/categories/:id` | Cập nhật |
| DELETE | `/api/sop/categories/:id` | Xóa (kiểm tra ràng buộc) |
| PUT | `/api/sop/categories/reorder` | Cập nhật sort_order hàng loạt |

### Quy trình SOP

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/sop/processes` | Danh sách SOP (filter: type, status, priority, category, tag) |
| GET | `/api/sop/processes/:id` | Chi tiết SOP đầy đủ (kèm steps, RACI, relations) |
| GET | `/api/sop/processes/:id/steps` | Danh sách bước của SOP |
| GET | `/api/sop/processes/:id/raci` | RACI matrix tổng hợp |
| GET | `/api/sop/processes/:id/versions` | Lịch sử phiên bản |
| GET | `/api/sop/processes/:id/versions/:v` | Nội dung tại version V |
| GET | `/api/sop/processes/:id/relations` | SOP liên quan |
| GET | `/api/sop/processes/:id/children` | Các SOP con |
| POST | `/api/sop/processes` | Tạo SOP mới (status = draft) |
| PUT | `/api/sop/processes/:id` | Cập nhật metadata SOP |
| DELETE | `/api/sop/processes/:id` | Xóa mềm (chỉ draft) |
| POST | `/api/sop/processes/:id/submit` | Gửi duyệt |
| POST | `/api/sop/processes/:id/withdraw` | Rút lại khỏi luồng duyệt |
| POST | `/api/sop/processes/:id/archive` | Lưu trữ |
| POST | `/api/sop/processes/:id/rollback/:version` | Rollback về version cụ thể |

### Bước (Steps)

| Method | Endpoint | Mô tả |
|---|---|---|
| POST | `/api/sop/processes/:id/steps` | Thêm bước mới |
| PUT | `/api/sop/processes/:id/steps/:stepId` | Cập nhật bước |
| DELETE | `/api/sop/processes/:id/steps/:stepId` | Xóa bước |
| PUT | `/api/sop/processes/:id/steps/reorder` | Sắp xếp lại thứ tự (bulk update step_number) |
| POST | `/api/sop/processes/:id/steps/:stepId/attachments` | Upload file đính kèm bước |
| DELETE | `/api/sop/processes/:id/steps/:stepId/attachments/:aid` | Xóa file |

### RACI

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/sop/processes/:id/raci` | RACI matrix toàn SOP |
| POST | `/api/sop/steps/:stepId/raci` | Thêm phân công RACI |
| PUT | `/api/sop/steps/:stepId/raci/:raciId` | Cập nhật |
| DELETE | `/api/sop/steps/:stepId/raci/:raciId` | Xóa |

### Luồng duyệt

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/sop/processes/:id/approval-flow` | Danh sách approver và trạng thái |
| POST | `/api/sop/processes/:id/approval-flow` | Cấu hình luồng duyệt |
| POST | `/api/sop/processes/:id/approve` | Duyệt (approver action) |
| POST | `/api/sop/processes/:id/reject` | Từ chối (kèm comment) |

### Liên kết SOP

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/sop/processes/:id/relations` | Lấy tất cả liên kết |
| POST | `/api/sop/processes/:id/relations` | Tạo liên kết mới |
| DELETE | `/api/sop/processes/:id/relations/:rid` | Xóa liên kết |

### Tag

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/api/sop/tags` | Danh sách tag của org |
| POST | `/api/sop/tags` | Tạo tag |
| PUT | `/api/sop/tags/:id` | Cập nhật |
| DELETE | `/api/sop/tags/:id` | Xóa |

---

## 11. Business Rules & Ràng buộc

### BR-SOP-001: Mã SOP (code)

- `code` unique trong phạm vi org, format gợi ý: `SOP-{DEPT}-{SEQ}` (vd: `SOP-HR-001`)
- Không cho phép thay đổi `code` sau khi SOP đã được approve lần đầu
- Sequence tự tăng theo category, do application layer quản lý

### BR-SOP-002: Cấu trúc bước

- Mỗi SOP phải có ít nhất 1 bước (`SOP_STEP`) trước khi gửi duyệt
- `step_number` phải liên tục từ 1, không có khoảng trống (đảm bảo bằng trigger hoặc application layer)
- Khi xóa một bước: tự động renumber các bước phía sau
- `step_type = 'decision'`: bắt buộc có `branch_yes_step` và `branch_no_step` hợp lệ
- `step_type = 'sub_sop'`: bắt buộc có `ref_sop_id` trỏ đến SOP có status = `approved`

### BR-SOP-003: RACI validation

- Mỗi bước `is_mandatory = TRUE` bắt buộc có ít nhất 1 R (Responsible)
- Mỗi bước `is_mandatory = TRUE` bắt buộc có đúng 1 A (Accountable) — nếu nhiều hơn 1: báo lỗi
- Một người/role có thể có nhiều vai trò trong cùng bước (vd: vừa R vừa A)
- Bước `is_mandatory = FALSE` không bắt buộc có RACI

### BR-SOP-004: Luồng duyệt

- Tác giả (owner) không được tự duyệt SOP của mình — trừ admin
- Mỗi SOP cần có ít nhất 1 approver trước khi submit
- Không cho phép thay đổi danh sách approver khi SOP đang `in_review`
- Khi rút lại (withdraw): chỉ được phép nếu chưa có approver nào hành động

### BR-SOP-005: Versioning

- Snapshot được tạo tự động khi SOP chuyển sang `approved` — không thể tắt
- Giữ tối đa **20 versions** gần nhất / SOP; xóa version cũ bằng cron hàng tuần
- Rollback luôn tạo `draft` mới — không bao giờ ghi đè trực tiếp version hiện tại
- `version` chỉ tăng — không bao giờ giảm dù rollback

### BR-SOP-006: Liên kết SOP

- Không tạo liên kết tự tham chiếu (`sop_id = related_sop_id`)
- `relation_type = 'prerequisite'`: kiểm tra không tạo chu kỳ (A prerequisite B, B prerequisite A)
- Khi archive SOP: kiểm tra SOP khác có `relation_type = 'prerequisite'` trỏ vào không, nếu có thì cảnh báo
- Một cặp SOP chỉ được có 1 relation của mỗi `relation_type`

### BR-SOP-007: Xóa dữ liệu

- Chỉ xóa cứng SOP ở trạng thái `draft` và `rejected` — các trạng thái còn lại chỉ archive
- Xóa SOP sẽ cascade: xóa tất cả SOP_STEP, SOP_STEP_RACI, SOP_STEP_ATTACHMENT, SOP_APPROVAL_FLOW
- Không xóa SOP_VERSION_HISTORY khi xóa SOP (giữ audit trail)
- Xóa danh mục chỉ khi không còn SOP nào (kể cả archived)

### BR-SOP-008: Review định kỳ

- Khi `review_date` đến (cron): gửi notification cho `owner_id`, không tự động thay đổi status
- Sau 30 ngày kể từ `review_date` mà chưa có action: gửi thêm notification cấp 2 cho admin
- Owner cập nhật `review_date` mới sau khi hoàn tất review (kể cả không thay đổi nội dung)

---

## 12. Indexes & Performance

### Index quan trọng

```sql
-- Trang danh sách SOP: lọc theo org + status + type
CREATE INDEX idx_sop_list
  ON SOP_PROCESS(org_id, status, type, priority, updated_at DESC);

-- SOP nổi bật / khẩn cấp
CREATE INDEX idx_sop_emergency
  ON SOP_PROCESS(org_id, type, status)
  WHERE type = 'emergency' AND status = 'approved';

-- Cây SOP cha–con
CREATE INDEX idx_sop_children
  ON SOP_PROCESS(parent_sop_id)
  WHERE parent_sop_id IS NOT NULL;

-- RACI lookup nhanh theo bước
CREATE INDEX idx_raci_step_type
  ON SOP_STEP_RACI(step_id, raci_type);

-- Tìm toàn bộ SOP có assignee là user/role X
CREATE INDEX idx_raci_assignee_lookup
  ON SOP_STEP_RACI(assignee_type, assignee_id, raci_type);

-- Approval flow: tìm approval pending của một approver
CREATE INDEX idx_approval_pending
  ON SOP_APPROVAL_FLOW(approver_type, approver_id, status)
  WHERE status = 'pending';

-- Version history: lấy version mới nhất
CREATE INDEX idx_version_latest
  ON SOP_VERSION_HISTORY(sop_id, version_number DESC);

-- Cron: SOP cần review hoặc sắp hết hạn
CREATE INDEX idx_sop_cron_review
  ON SOP_PROCESS(review_date, status)
  WHERE review_date IS NOT NULL AND status = 'approved';
```

### Ghi chú hiệu năng

| Vấn đề | Giải pháp đề xuất |
|---|---|
| `snapshot_json` có thể rất lớn nếu SOP nhiều bước | Nén gzip trước khi lưu; hoặc lưu vào S3 và chỉ lưu S3 URL trong bảng |
| Full-text search chậm khi data lớn | Elasticsearch / OpenSearch cho org có > 500 SOP |
| RACI matrix render nhiều record | Cache JSON aggregate per SOP, invalidate khi SOP_STEP_RACI thay đổi |
| Approval flow query N+1 | JOIN toàn bộ approval flow trong 1 query kèm thông tin approver |
| `step_number` reorder tốn nhiều UPDATE | Dùng floating point step_number (1.0, 2.0, 2.5 nếu chèn giữa) hoặc batch update trong transaction |

---

## 13. Ghi chú triển khai cho SME

### Lộ trình theo giai đoạn

**Giai đoạn 1 — MVP (tháng 1–2): Core SOP Builder**
- [ ] `SOP_CATEGORY` + `SOP_PROCESS` + `SOP_STEP`
- [ ] Step Builder cơ bản (action, decision) — chưa cần sub_sop, notification, wait
- [ ] `SOP_STEP_RACI` + validation R và A
- [ ] Luồng duyệt đơn giản (1 approver, không cần sequence)
- [ ] `SOP_STEP_ATTACHMENT` — upload ảnh minh họa
- [ ] Tìm kiếm full-text cơ bản

**Giai đoạn 2 — Mở rộng (tháng 3–4)**
- [ ] `SOP_VERSION_HISTORY` + Rollback
- [ ] Luồng duyệt đa cấp theo `sequence`
- [ ] `SOP_RELATION` — liên kết SOP
- [ ] Step type đầy đủ (sub_sop, notification, wait)
- [ ] `SOP_TAG` + tìm kiếm theo tag
- [ ] Render flowchart từ steps (frontend)

**Giai đoạn 3 — Hoàn thiện (tháng 5+)**
- [ ] Cron job: review nhắc nhở + hết hiệu lực tự động
- [ ] RACI matrix view dạng bảng export
- [ ] Dashboard analytics: SOP sắp hết hạn, phân bố theo type/priority
- [ ] Notification system hoàn chỉnh

### Khuyến nghị cấu hình cho SME 20–100 người

| Thông số | Khuyến nghị |
|---|---|
| Cấp danh mục | 2 cấp là đủ (Lĩnh vực → Nhóm quy trình) |
| Số approver | 1–2 cấp duyệt; tránh > 3 cấp gây chậm trễ |
| Giới hạn số bước / SOP | Gợi ý tối đa 20 bước; SOP phức tạp hơn → chia SOP con |
| File đính kèm / bước | Tối đa 5 file, 10MB/file |
| Giữ version history | 10–15 version gần nhất là đủ |
| Review cycle mặc định | 6 tháng cho standard SOP; 3 tháng cho emergency |
| Format code SOP | `SOP-{3 ký tự phòng ban}-{3 số}`: SOP-HR-001, SOP-IT-012 |

### Ví dụ danh mục cho SME phổ biến

```
SOP Center
├── Nhân sự (HR)
│   ├── Tuyển dụng
│   └── Onboarding & Offboarding
├── Vận hành (Operations)
│   ├── Sản xuất / Dịch vụ
│   └── Chất lượng (QC/QA)
├── Tài chính - Kế toán
│   ├── Thu - Chi
│   └── Báo cáo & Kiểm toán
├── Công nghệ thông tin (IT)
│   ├── Hạ tầng & Bảo mật
│   └── Hỗ trợ người dùng
└── Kinh doanh & Khách hàng
    ├── Bán hàng
    └── Chăm sóc khách hàng
```

---

*Tài liệu này được tạo bởi AI Assistant và cần được review bởi Technical Lead / Product Owner trước khi đưa vào triển khai chính thức.*

*Version 1.0.0 — SOP Center Module Specification — SME SaaS Platform*