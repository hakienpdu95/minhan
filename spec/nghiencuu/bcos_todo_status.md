# BCOS — TRẠNG THÁI TRIỂN KHAI & VIỆC CÒN LẠI

> Cập nhật: 2026-07-16. Đối chiếu với `spec/nghiencuu/bcos_master_flow.md` (Phần 9 — Lộ trình)
> và plan đã duyệt `/home/hacom/.claude1/plans/declarative-discovering-garden.md`.
> Mục đích: để biết chính xác đã làm gì, còn gì, ưu tiên gì cho phiên làm việc tiếp theo.

---

## ✅ BLOCKER ĐÃ XỬ LÝ — Migration ledger (2026-07-16, cùng ngày)

**Đã fix** — người dùng tự xử lý DB, xác nhận "đầy đủ bảng rồi, vào được bình thường". Đã tự kiểm tra lại và xác nhận:

- `employees`, `departments`, `branches`, `job_titles`, `kc_items`, `vertical_templates`, `business_projects`, `business_contexts`, `deliverables` — tất cả `Schema::hasTable()` = OK.
- `layouts.backend` và `layouts.partials.sidebar` render sạch (trước đó lỗi do `SidebarComposer` query `vertical_templates`) — **đã verify qua render trực tiếp, không chỉ tin lời báo**.
- **Lưu ý phụ**: DB có vẻ đã được reseed/import mới hoàn toàn — Organization/User ID đã đổi (Demo Organization giờ là `id=2`, không còn `id=8`; CEO User giờ `id=3`). Dữ liệu test cũ (Lead/Customer/BusinessProject tạo lúc verify Vertical Slice 1) đã mất theo, đã tạo lại bộ test mới ở id=1 để verify lại toàn bộ luồng — **kết quả: PASS 100%** (R1 block, Gate check, Submit/Approve qua Ringlesoft, Advance stage — y hệt lần trước).
- Sau khi DB đầy đủ, đã render **toàn bộ 3 trang qua layout thật** (`index`, `show` của BusinessProject, `leads/show`) — phát hiện thêm 1 bug thật (xem mục Ghi chú kỹ thuật, đã sửa).

Migration ledger vẫn còn ~203 migration "Pending" (không phải 0) nhưng không còn là blocker — các bảng cốt lõi đã đủ và app chạy được. Không cần động tiếp vào bảng `migrations` trừ khi gặp lỗi cụ thể mới.

---

## ✅ ĐÃ HOÀN THÀNH — Vertical Slice 1 (Nền tảng + Lead Convert + Context Workspace)

Module `Modules/BusinessProject` mới, đã verify end-to-end qua tinker **và render toàn bộ view qua layout thật** (sau khi DB fix — xem mục trên):

- Migrations: `business_projects`, `business_project_members`, `business_contexts`, `deliverables`, `deliverable_versions`, `deliverable_evidence_links`, + ALTER `leads` (cột `converted_business_project_id`).
- Models + Enums (`BusinessProjectStage`, `DeliverableStatus`, `ProjectMemberRole`, `DeliverableType`).
- Data objects (Spatie Laravel-Data), Queries+Handlers (`CheckStageGateEligibilityQuery/Handler` — đủ 8 stage, `GetEvidenceForDeliverableQuery/Handler`).
- Actions: `AdvanceBusinessProjectStageAction`, `CreateBusinessContextAction`, `UpdateBusinessContextAction`, `SubmitDeliverableForApprovalAction`, `ApproveDeliverableAction`, `RejectDeliverableAction`, `AttachEvidenceAction`, `ConvertLeadToBusinessProjectAction`.
- Đấu nối **thật** `ringlesoft/laravel-process-approval` vào `Deliverable` (lần đầu tiên trong codebase) — flow "Deliverable Approval", step role `lead_consultant`.
- RBAC: 5 role mới (`lead_consultant`, `consultant`, `ba`, `pm`, `customer_success`) + 5 permission `business_project.*`/`business_context.*` — đã sửa đúng **nguồn thật thi hành** là `database/seeders/RolePermissionSeeder.php` (không phải `config/permissions.php`, file đó là code chết — xem mục "Ghi chú kỹ thuật" dưới).
- Policies (`BusinessProjectPolicy`, `DeliverablePolicy`), Controllers, Routes, Views (Project Header + Tabs + Right Sidebar Deliverables theo Phần 5B spec).
- Lead module: nút "Convert to Business Project" + card hiển thị sau khi convert.
- Sidebar entry mới.
- Test end-to-end qua tinker: Lead → Customer (tự convert) → Business Project → Business Context → chặn đúng R1 (tạo Context lần 2) → Gate chặn đúng khi chưa duyệt → Submit → Approve (CEO, qua Ringlesoft thật) → Gate mở → Advance sang Discovery → Gate ở Discovery đúng hiện placeholder. **Chạy lại 100% PASS sau khi DB fix + reseed.**
- Render qua layout đầy đủ (`layouts.backend`): trang `index`, `show` của Business Project, và `leads/show` (card Convert) — cả 3 sạch, không lỗi.

---

## 🟡 CÒN LẠI TRONG PHẠM VI ĐÃ LÀM — hạn chế biết trước (đã ghi chú trong code)

Không phải bug, nhưng cần biết để làm tiếp đúng hướng:

1. **`DeliverablePolicy::approve()` chưa siết theo `business_project_members`** — hiện tại bất kỳ user có role global `lead_consultant` đều duyệt được Context Report của **mọi** Business Project, chưa giới hạn "chỉ project được phân công". Ghi rõ trong code là việc của Phase 2 khi có nhiều Lead Consultant chạy song song nhiều project.
2. **Chưa đăng ký feature gate Subscription** (`feature:module.businessproject`) — route hiện không bị gate theo subscription plan, khác với Lead/Sop dùng `feature:module.X`. Quyết định có chủ đích (BCOS là công cụ nội bộ), nhưng cần xác nhận lại nếu sau này bán platform này cho tenant khác.
3. **Chưa có test tự động (PHPUnit/Pest)** cho module `BusinessProject` — `tests/Feature` và `tests/Unit` còn trống. Verify hiện tại chỉ qua script tinker thủ công (không lặp lại được tự động trong CI).
4. **Context Canvas UI còn đơn giản** — 3 textarea tự do (company_profile/stakeholders/strategic_goals dạng `{notes: "..."}`), chưa phải canvas có cấu trúc field đúng như Handbook mô tả. Đủ dùng cho MVP, cần polish ở Phase 2/3.
5. **Dữ liệu test còn trong DB** (Lead, Customer, Business Project id=1, tạo lại sau khi DB reseed) — có thể giữ làm demo hoặc xóa, tùy ý. Lưu ý: có 1 row `deliverable_versions` (id=1) đã được update thủ công qua tinker để fix `created_at` null (không phải qua Action) — nếu muốn dữ liệu "sạch" hoàn toàn thì xóa hết bộ test này và tạo lại qua UI thật.

---

## ⬜ CHƯA LÀM — phần còn lại của Phase 1 MVP (theo `bcos_master_flow.md` Phần 9)

Vertical Slice 1 chỉ là 3/6 bước của Phase 1 MVP đầy đủ. Còn thiếu:

- **Discovery Workspace (Rule R2)** — Interview/Observation/Document Review/Data Review nhập trực tiếp (tự động thành Deliverable con), TPS Canvas, Business Discovery Report, gate kiểm tra đủ điều kiện sang Diagnosis.
- **Transformation Workspace (Rule R4)** — Roadmap (Quick Wins/30/90/365), Proposal, SOW, xác nhận thủ công (`confirmed_at`/`confirmed_by`).
- **Delivery Workspace (Rule R5)** — Weekly Report (prefill từ Task/Issue), Issue/Risk, Change Request, Meeting, tích hợp module Task hiện có (thêm `business_project_id` vào bảng `tasks`).
- **Đóng dự án tối thiểu (Rule R6/R7)** — Final Report + ≥1 Knowledge Asset (KcItem) mới cho phép đóng, event `BusinessProjectClosed` → tạo Retrospective gợi ý.

**Lưu ý quan trọng khi làm tiếp**: `CheckStageGateEligibilityHandler` đã có sẵn cấu trúc `match()` đủ 8 nhánh — chỉ cần thay nội dung nhánh `discovery`/`transformation`/`delivery`/`closing` (hiện đang trả placeholder "chưa triển khai") bằng điều kiện thật, **không cần đổi cấu trúc** — đúng thiết kế đã chuẩn bị trước.

---

## ⬜ CHƯA LÀM — Phase 2 (Chuẩn hóa, theo Phần 9 spec)

- Diagnosis Workspace đầy đủ + Approval R3 (đã có infra Ringlesoft, cần thêm flow/step cho Diagnosis).
- Knowledge Workspace hoàn chỉnh: mở rộng `kc_items` (types `case_study`/`lessons_learned`/`best_practice`/`industry_knowledge`, cột `industry`, cột `business_project_id` liên kết 2 chiều) — **lưu ý: bảng `kc_items` hiện chưa tồn tại trong DB (thuộc blocker migration ledger ở trên), phải xử lý blocker trước**.
- Customer Success: tích hợp Survey engine cho CSAT/NPS, follow-up, New Opportunity → Lead mới (khép vòng lặp).
- BCOS Dashboard + KPI (theo Phần 10 spec: Gate Compliance Rate, Knowledge Reuse Rate, Cycle Time, Deliverable Version Discipline, CSAT/NPS, R7 Fulfillment Rate) — export CSV.
- Template Library chuẩn (Template Service — hiện chưa xây, `deliverables.template_id` chưa dùng).

## ⬜ CHƯA LÀM — Phase 3/4 (Tự động hóa / AI Ready)

Workflow Engine, Template Engine nâng cao, Digital Signature, Import/Export, full-text search (Knowledge), AI Discovery/Diagnosis/Proposal/Weekly Summary/Knowledge Search Assistant — chưa cần làm sớm theo đúng roadmap, chỉ ghi lại để không quên.

---

## Ghi chú kỹ thuật quan trọng (đọc trước khi code tiếp)

- `config/permissions.php` là **code chết** — comment nói dùng lệnh `permissions:sync` nhưng lệnh đó không tồn tại. Nguồn thật thi hành khi seed là `database/seeders/RolePermissionSeeder.php`. Mọi permission/role mới phải sửa ở đó mới có hiệu lực thật.
- Model `extends TenantAwareModel` **bắt buộc** có cột `organization_id` và `deleted_at` trong migration (bundle cứng `BelongsToOrganization` + `SoftDeletes` + `LogsActivity`).
- `$table->uuid()` không tự sinh giá trị — luôn phải tự set `'uuid' => Str::uuid()` khi tạo record.
- Spatie Permission dùng Teams (`organization_id`) — test qua tinker phải tự gọi `app(PermissionRegistrar::class)->setPermissionsTeamId($orgId)` trước khi check role/permission, nếu không luôn trả rỗng.
- `ringlesoft/laravel-process-approval`: 1 Model class chỉ được có đúng 1 flow (`makeApprovable()` ném exception nếu gọi lại) — luôn check tồn tại trước.
- **[Bug đã fix]** Model với `$timestamps = false` + cột DB `->useCurrent()` (như `DeliverableVersion`, giống `sop_versions`): nếu `Model::create([...])` KHÔNG tự set `'created_at' => now()`, Eloquent KHÔNG tự refetch từ DB để lấy giá trị default sau khi insert — object trong PHP có `created_at = null` ngay sau `create()`, dù DB đã lưu đúng giờ. Gây lỗi `Call to a member function format() on null` khi hiển thị version trong Blade. **Luôn tự set `created_at` trong PHP khi tạo record `$timestamps=false`**, không dựa vào DB default — đã sửa ở `CreateBusinessContextAction`/`UpdateBusinessContextAction`.
- **Kiểm thử qua tinker chỉ render 1 view riêng lẻ KHÔNG đủ để bắt bug này** — phải render toàn bộ trang qua đúng luồng dữ liệu thật (tạo record qua Action, sau đó load lại và render list/versions) mới phát hiện được. Bài học: verify Blade cần render đúng route/luồng controller thật, không chỉ view đơn lẻ với dữ liệu giả lập.

Toàn bộ chi tiết implementation đã lưu trong memory (`bcos-vertical-slice-1-implementation.md`, `bcos-spec-gap-analysis.md`) để phiên sau tự động nhớ lại.
