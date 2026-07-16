# BCOS — TRẠNG THÁI TRIỂN KHAI & VIỆC CÒN LẠI

> Cập nhật: 2026-07-17 (Template Library — **Phase 2, mảng 5/5 — TOÀN BỘ PHASE 2 ĐÃ HOÀN THÀNH**).
> 8/8 workspace đã triển khai hết, không còn tab disabled. Đối chiếu với
> `spec/nghiencuu/bcos_master_flow.md` (Phần 9 — Lộ trình) và plan đã duyệt
> `/home/hacom/.claude1/plans/declarative-discovering-garden.md`.
> Mục đích: để biết chính xác đã làm gì, còn gì, ưu tiên gì cho phiên làm việc tiếp theo.

---

## ✅ ĐÃ HOÀN THÀNH — Diagnosis Workspace + Approval R3 (Phase 2, mảng 1/5), cùng ngày 2026-07-16

Theo `bcos_master_flow.md` Giai đoạn 3 + Handbook 4.4-4.10 (THUCHOCVN Root Cause Framework, Diagnosis
Matrix, Impact–Effort Matrix). Đây là mảng đầu tiên của Phase 2 — bật thật feature flag
`businessproject.stage_gates.diagnosis.enforced` (Phase 1 để `false` = bypass, giờ `true`).

- **Diagnosis Report** — `DeliverableType::DiagnosisReport`, singleton như các Report khác, content
  JSON = `{overview, findings: [...]}`. `findings` là mảng cấu trúc (KHÔNG tách bảng riêng — đây
  đúng là "1 tài liệu duy nhất", khác Milestone/Issue/Risk cần bảng riêng vì có escalate/cross-ref),
  mỗi dòng: Vấn đề/Nhóm (People-Process-Data-Digital-Management)/Nguyên nhân gốc/Impact/Effort.
  **Priority KHÔNG nhập tay** — luôn tính từ Impact+Effort qua `DiagnosisPriority::fromImpactAndEffort()`
  đúng 4 quadrant Impact–Effort Matrix (Handbook 4.7): Quick Win/Strategic/Fill-in/Low Priority.
- **Evidence linking** — dùng lại NGUYÊN `AttachEvidenceAction` + `deliverable_evidence_links` +
  `evidenceFor()` relation đã tạo sẵn từ Vertical Slice 1 (Phần 6.2), không phải xây lại quan hệ
  dữ liệu — evidence trích dẫn ở CẤP Diagnosis Report (không phải per-finding, đúng ví dụ Handbook
  "Diagnosis Matrix cần evidence từ Interview #4"), candidate lấy từ Deliverable của Discovery
  Workspace (Interview/Observation/Document Review/Data Review/Process Map/Discovery Report).
- **Approval R3** — tái dùng nguyên `SubmitDeliverableForApprovalAction`/`ApproveDeliverableAction`/
  `RejectDeliverableAction` (cùng flow Ringlesoft "Deliverable Approval" như Context/Proposal/SOW/
  Final Report). Duyệt: Founder hoặc Lead Consultant (Consultant/BA/PM/CS đều "—" — matrix Phần
  7.2 hàng "Duyệt Diagnosis (R3)"), dùng nguyên `DeliverablePolicy::approve()` sẵn có, KHÔNG cần
  sửa gì thêm (Ringlesoft canBeApprovedBy() + CEO bypass đã đúng ngay từ đầu).
- **Gate R3** (Diagnosis → Transformation): Diagnosis Report `approved` — 1 điều kiện duy nhất
  đúng spec, không thêm điều kiện phụ (VD không yêu cầu số lượng findings tối thiểu, spec không
  đòi hỏi).
- **"Tách xem trước và kích hoạt" (spec Giai đoạn 3)** — Consultant vẫn soạn nháp Proposal/SOW ở
  Transformation Workspace bình thường (không chặn `saveProposal`/`saveSow`), nhưng "Gửi phê duyệt
  nội bộ" (= publish chính thức) bị chặn nếu Diagnosis Report chưa approved — thêm
  `TransformationController::assertDiagnosisApprovedForPublish()`, chỉ áp dụng khi flag enforced
  bật. Đây là guard CROSS-WORKSPACE đầu tiên trong BCOS (Transformation phụ thuộc trạng thái
  Diagnosis) — đặt ở tầng Controller, KHÔNG nhét vào `SubmitDeliverableForApprovalAction` chung
  (action đó vẫn generic cho mọi workspace, không nên biết về Diagnosis).
- Permission mới `business_diagnosis.manage` — CEO, Lead Consultant, Consultant (BA/PM/CS đều
  "—" — matrix hàng "Gửi phê duyệt Diagnosis"). `BusinessProjectPolicy::manageDiagnosis` +
  `DeliverablePolicy::manage()` thêm case Diagnosis.
- `DiagnosisController` (8 routes) + views (`diagnosis/show.blade.php` + `_report`, `_matrix`,
  `_evidence`). Tab Diagnosis ở project-header giờ là link thật — **cả 7/8 tab đã xong**, chỉ
  Knowledge/Customer Success còn disabled (Phase 2, mảng 2 & 3).

**Verify**: tinker full flow Context→Discovery→Diagnosis(thật, không bypass): gate chặn khi chưa
có Diagnosis Report → thêm 3 finding (impact/effort khác nhau, verify đúng priority quadrant tính
tự động: quick_win/strategic/low_priority) → xóa 1 finding, re-index đúng → đính evidence từ
Discovery Interview thật → gate vẫn chặn (chưa approved) → **verify cross-workspace guard chặn
đúng** khi Diagnosis chưa duyệt, **verify guard mở đúng** sau khi duyệt → submit→approve Diagnosis
Report → gate mở → advance sang Transformation → chạy tiếp toàn bộ chuỗi
Transformation→Delivery→Closing để xác nhận **không có regression** khi bật gate thật (trước đó
toàn bộ chuỗi này chỉ được test với Diagnosis bypass). 21/21 check PASS + full-chain PASS. Render
HTTP thật (curl+cookie jar, login CEO) cho Diagnosis page (matrix + priority badge + evidence hiện
đúng) + regression toàn bộ 5 workspace còn lại. Dữ liệu test đã dọn sạch.

---

## ✅ ĐÃ HOÀN THÀNH — Closing Workspace (Rule R6/R7), cùng ngày 2026-07-16 — **Phase 1 MVP xong 6/6 bước**

Theo `bcos_master_flow.md` Giai đoạn 6. Bước cuối cùng của roadmap Phần 9 Phase 1.

- **Final Project Report** — `DeliverableType::FinalReport`, singleton (giống Discovery Report),
  đi qua draft → submit → approve (tái dùng nguyên `SubmitDeliverableForApprovalAction`/
  `ApproveDeliverableAction`/`RejectDeliverableAction` — generic trên Deliverable, không viết
  lại). R6 chỉ cần "đã approved", KHÔNG cần Confirm (khác Proposal/SOW).
- **Knowledge Asset (Rule R7)** — thêm `business_project_id` nullable vào `kc_items` (cùng pattern
  `tasks`). KcItem module bắt buộc `category_id` (Modules/KcCategory, khái niệm khác BusinessProject)
  nên KHÔNG tạo KcItem rút gọn trong Closing Workspace — dùng đúng pattern Task integration ở
  Delivery: (a) gắn KcItem có sẵn (`AttachKnowledgeAssetAction`), (b) link mở
  `backend.kc-items.create?business_project_id=X` (prefill query string, patch nhỏ
  `KcItemController::create()`/`StoreKcItemData`/`StoreKcItemAction`, y hệt cách đã patch Task).
- Gate R6+R7 (Closing → Knowledge): Final Report approved VÀ ≥1 KcItem gắn `business_project_id`.
  Rời khỏi Closing (qua gate) **chính là hành động "Đóng dự án"** — không có action/nút riêng:
  `AdvanceBusinessProjectStageAction` tự set `business_projects.status = 'closed'` + bắn event
  `BusinessProjectClosed` khi stage rời khỏi `closing`. Event tồn tại sẵn, CHƯA có Listener (Phase 2:
  tự động tạo Project Retrospective + kích hoạt Customer Success Workspace, theo đúng spec).
- Permission mới `business_closing.manage` — CEO, Lead Consultant, PM (đúng Ma trận Phần 7.2 hàng
  "Đóng dự án": Consultant/BA/Customer Success đều "—"). Patch `KcItemPolicy` thêm
  `lead_consultant`/`consultant`/`pm`/`ceo` vào `viewAny`/`view`/`create` (như đã làm với
  `TaskPolicy` ở Delivery Workspace) để link "Tạo Knowledge Asset mới" dùng được.
- `ClosingController` (6 routes) + views (`closing/show.blade.php` + `_final_report`,
  `_knowledge_assets`). Tab Closing ở project-header giờ là link thật (6/8 tab đã xong: Context,
  Discovery, Transformation, Delivery, Closing — chỉ Diagnosis/Knowledge/Customer Success còn
  disabled). Nút advance-stage đổi label thành "Đóng dự án" khi đang ở stage Closing.

### 🐛 Bug thật tự phát hiện khi verify — đã fix

**Delivery stage chưa từng có nhánh gate riêng** — `CheckStageGateEligibilityHandler` thiếu case
`BusinessProjectStage::Delivery`, rơi vào `default => notImplementedConditions()` (luôn `met: false`).
Nghĩa là project **không thể advance ra khỏi Delivery** dù R5 không phải stage gate (chỉ là rule
toàn vẹn dữ liệu) — bug tồn tại từ khi build Delivery Workspace nhưng chưa bị phát hiện vì phiên đó
chỉ verify TỚI Delivery, chưa verify ADVANCE RA KHỎI Delivery. Phát hiện ngay khi test fast-track
tới Closing. **Đã fix**: thêm `deliveryConditions()` trả về 1 condition luôn `met: true` (không có
điều kiện gate bắt buộc, đúng bản chất R5).

**Verify**: tinker full flow Context→...→Delivery→Closing: gate chặn đúng khi chưa có gì → Final
Report draft (gate vẫn chặn) → submit→approve (gate vẫn chặn vì thiếu Knowledge Asset) → tạo KcItem
độc lập + gắn vào project → gate mở đủ cả 2 điều kiện → advance ra khỏi Closing → verify qua
`Event::fake()` xác nhận `BusinessProjectClosed` bắn đúng + `business_projects.status` chuyển
`closed` + `current_stage` chuyển `knowledge`. 18/18 PASS. Render HTTP thật (curl+cookie jar,
login CEO) cho Closing page (hiện đúng nút "Đóng dự án" + cả 2 điều kiện gate) + regression
Discovery/Transformation/Delivery + link tạo KcItem prefill — tất cả 200 OK. Dữ liệu test đã dọn
sạch, không orphan row.

---

## ✅ ĐÃ HOÀN THÀNH — Delivery Workspace (Rule R5), cùng ngày 2026-07-16

Theo `bcos_master_flow.md` Giai đoạn 5. **Không có gate R5** (khác R1-R4) — R5 là rule toàn vẹn dữ
liệu ("Weekly Report luôn gắn Project"), không phải điều kiện chuyển giai đoạn; Gate Delivery ->
Closing thuộc task tiếp theo (R6/R7 — "Đóng dự án tối thiểu"), `CheckStageGateEligibilityHandler`
nhánh `delivery` vẫn `notImplementedConditions()` có chủ đích.

- **Task integration** — thêm `business_project_id` nullable vào bảng `tasks` (module Task hiện
  có, KHÔNG xây task tracker thứ hai). Task module bắt buộc `project_id` (Modules/Project — khái
  niệm khác BusinessProject, NOT NULL) nên KHÔNG tạo Task rút gọn trong Delivery Workspace — chỉ
  (a) gắn Task có sẵn (`AttachTaskToProjectAction`), hoặc (b) link mở `backend.tasks.create` với
  `business_project_id`/`title` prefill qua query string (`TaskController::create()` đọc query,
  `StoreTaskData`/`StoreTaskAction` lưu field mới — patch nhỏ, không đụng phần còn lại của module).
- **Meeting** — bảng `meetings` mới (type/title/held_at + `deliverable_id` 1-1, giống pattern
  `business_contexts.deliverable_id`). Minutes qua `SaveMeetingMinutesAction` riêng (KHÔNG dùng
  `UpsertSingletonDeliverableAction` — bảng đó khoá theo (project,type) chỉ đúng cho 1 bản/project,
  ở đây nhiều Meeting cùng type/project). Action items lưu trong content JSON, hiển thị kèm link
  "Tạo Task" prefill title — không tự động sinh Task (cùng lý do Task integration ở trên).
- **Weekly Report** — `DeliverableType::WeeklyReport`, KHÔNG singleton (mỗi lần bấm là 1 bản ghi
  MỚI, không phải sửa report cũ). `CreateWeeklyReportAction` tính prefill snapshot (task done/
  pending, issue mới từ report trước, issue/risk đang mở) tại thời điểm tạo — verify số đúng qua
  2 report liên tiếp.
- **Issue / Risk** — bảng riêng `issues`/`risks` (severity, likelihood/impact, status), escalate
  sang **Change Request** (`change_requests` — source_type + issue_id/risk_id, đúng 1 trong 2).
- **Change Request** — duyệt qua Approval Service **flow RIÊNG** "Change Request Approval" (khác
  "Deliverable Approval" — Ringlesoft chỉ cho 1 Model = 1 flow, đăng ký ở
  `BusinessProjectPermissionSeeder::seedChangeRequestApprovalFlow()`). Nếu `impacts_scope=true` và
  được duyệt, SOW đang `confirmed` tự mở khóa về `draft` (`ChangeRequest::onApprovalCompleted()`)
  để Consultant sửa lại qua đúng luồng `SaveSowAction` hiện có — **không tự chế nội dung version
  thay Consultant**.
- Permission mới `business_delivery.manage` — CEO, Lead Consultant, Consultant, PM (không BA/CS,
  đúng Ma trận Phần 7.2 — cùng role set với Transformation). `BusinessProjectPolicy::manageDelivery`
  + `DeliverablePolicy::manage()` thêm case Delivery. `ChangeRequestPolicy` mới (view/manage/approve).
- `DeliveryController` (12 routes) + views (`delivery/show.blade.php` + 5 partials: `_tasks`,
  `_meetings`, `_weekly_reports`, `_issues_risks`, `_change_requests`). Tab Delivery ở
  project-header giờ là link thật.

### 🐛 3 bug thật tự phát hiện khi verify (KHÔNG do Delivery Workspace trực tiếp, đều đã fix)

1. **Ringlesoft không hỗ trợ resubmit lần 2 trên cùng 1 record** — `ProcessApprovalStatus` (approval
   status của Ringlesoft) là vòng đời 1 CHIỀU (`Created→Submitted→Approved/Rejected`, không tự
   reset). Set lại cột `deliverables.status` về `draft` (như `UpdateBusinessContextAction` đã làm
   từ Vertical Slice 1) KHÔNG đủ — `submit()` vẫn ném `RequestAlreadySubmittedException` vì
   `isSubmitted()` check `approvalStatus` riêng của Ringlesoft, không phải cột `status` của app.
   Bug này đã tồn tại từ Context Workspace (Vertical Slice 1) nhưng chưa ai test qua chu kỳ sửa
   sau khi approved → gửi duyệt lại — chỉ phát hiện khi verify Change Request mở khóa SOW.
   **Đã fix**: thêm `Deliverable::resetApprovalCycle()` (tái tạo lại `approvalStatus` giống hệt
   logic `bootApprovable()` lúc tạo record — không có API reset sẵn từ vendor), gọi ở
   `UpdateBusinessContextAction` và `ChangeRequest::onApprovalCompleted()`. Verify: cả 2 chu kỳ
   Context và SOW (sửa sau approved → gửi lại → duyệt lại) đều PASS.
2. **`Modules\Task\Policies\TaskPolicy` dùng role name viết hoa** (`'CEO'`, `'System_Admin'`...)
   không khớp role thật viết thường được seed (`RolePermissionSeeder`) — khóa **toàn bộ module
   Task cho MỌI user**, không riêng BCOS, từ trước khi có Delivery Workspace. Phát hiện khi test
   live link "Tạo Task mới" (403). **Đã fix**: sửa toàn bộ role name về lowercase + thêm
   `lead_consultant`/`consultant`/`pm` vào `view`/`viewAny`/`create`/`update` để BCOS dùng được.

   ⚠️ **PHÁT HIỆN QUAN TRỌNG — cùng bug lặp lại ở ÍT NHẤT 24 file khác**, ngoài phạm vi BCOS,
   **CHƯA fix** (chỉ fix đúng `Modules\Task\Policies\TaskPolicy` theo yêu cầu): `grep -rln
   "hasAnyRole(\['[A-Z]\|hasRole('[A-Z]" app Modules --include="*.php"` ra `KpiGoalPolicy`,
   `EmployeePolicy`, `KcItemPolicy`, `KcTagPolicy`, `BranchPolicy`, `JobTitlePolicy`,
   `LeavePolicyPolicy`, `LeaveRequestPolicy`, `PerformanceReviewPolicy`, `ProjectPolicy`,
   `DepartmentPolicy`, `DeploymentIssuePolicy`, `DeploymentTargetPolicy`, `OrgChartConfigPolicy`,
   `UserRoleScopePolicy`, `KcCategoryPolicy`, và vài file Recruitment/controller khác — TẤT CẢ
   dùng role name viết hoa (`'System_Admin'`, `'CEO'`, `'HR'`...) trong khi role thật trong DB
   viết thường. Nghĩa là **hầu hết Policy trong toàn app đang trả `false` cho mọi user, ở mọi
   module** — mức độ nghiêm trọng cao hơn nhiều so với phạm vi BCOS. Đây là việc CẦN LÀM RIÊNG,
   khẩn cấp, KHÔNG nằm trong phạm vi Delivery Workspace — cần 1 phiên audit riêng để rà soát +
   sửa toàn bộ (có thể kèm test để tránh lặp lại), không nên sửa vội trong lúc làm việc khác.
3. **`ChangeRequest` model thiếu cast `source_type` → `ChangeRequestSourceType`** — gây lỗi 500
   "Call to a member function label() on string" khi render `_change_requests.blade.php`. Bug
   của chính session này (không phải pre-existing), phát hiện + fix ngay khi render live.

**Verify**: tinker full flow Context(sửa sau approved+resubmit)→Discovery→Diagnosis(bypass)→
Transformation(Proposal/SOW confirmed)→Delivery: task attach + generic Project tách biệt, Meeting
+ Minutes + action items, 2 Weekly Report liên tiếp (prefill đúng số ở cả 2), Issue+Risk tạo/escalate,
Change Request impacts_scope=true reopens SOW → sửa → resubmit → approve → confirm lại (chu kỳ 2,
đúng cái vừa fix), Change Request impacts_scope=false không đụng SOW đang confirmed. 26/26 PASS.
Render HTTP thật (curl+cookie jar, login CEO) cho Delivery + regression Context/Discovery/
Transformation, và link Task create prefill (sau khi fix TaskPolicy) — tất cả 200 OK. Dữ liệu
test (business project, generic Project, Task rời) đã dọn sạch, không orphan row.

---

## ✅ ĐÃ HOÀN THÀNH — Diagnosis bypass + Transformation Workspace (Rule R4), cùng ngày 2026-07-16

Theo đúng ghi chú bắt buộc Phần 9 ("Bypass Diagnosis ở Phase 1"): thêm nhánh `diagnosis` thật vào
`CheckStageGateEligibilityHandler` (không còn rơi vào `notImplementedConditions()` placeholder)
— auto-pass qua feature flag `businessproject.stage_gates.diagnosis.enforced` (config mới ở
`Modules/BusinessProject/config/config.php`, mặc định `false`). State machine đi ĐÚNG qua state
`diagnosis` (không code cứng nhảy cóc) — Phase 2 chỉ cần đổi flag thành `true` + thay nội dung
nhánh bằng check Diagnosis Report approved (R3), không refactor lại cấu trúc/dữ liệu.

**Transformation Workspace (Rule R4)** — theo Handbook 5.5 + master_flow.md Giai đoạn 4:

- Transformation Design Canvas ⭐ — đúng 8 mục Handbook: Business Goal, Priority Problems,
  Transformation Objectives, Key Initiatives, Quick Wins, Resources, Risks, Success Metrics.
  Singleton deliverable, không có Approval/Confirm (chỉ soạn thảo tự do).
- Transformation Roadmap — bản tổng quan (singleton, versioned) + **bảng `milestones` mới**
  (migration riêng, KHÔNG phải deliverable — spec Phần 6.1 liệt kê milestones là bảng riêng biệt)
  với 4 category `quick_win/day_30/day_90/day_365` (`MilestoneCategory` enum).
- Proposal (solution + collaboration_plan) và SOW (scope + deliverables + responsibilities) —
  singleton deliverable, đi qua **đúng luồng Rule R4**: draft → "Gửi phê duyệt nội bộ" (tái dùng
  `SubmitDeliverableForApprovalAction`/`ApproveDeliverableAction`/`RejectDeliverableAction` sẵn có
  từ Context, KHÔNG viết lại) → approved → Consultant/PM tick **Confirmed** (`ConfirmDeliverableAction`
  mới, bắt buộc `status=approved` trước, ném lỗi 422 nếu tick tắt) → `confirmed_at`/`confirmed_by`
  lưu làm bằng chứng audit. Sau khi confirmed, sửa lại bị chặn (422) — thêm guard trong
  `UpsertSingletonDeliverableAction`, giống rule cũ của `UpdateBusinessContextAction`.
- Gate R4 (Transformation → Delivery): cả Proposal VÀ SOW cùng `confirmed` mới cho advance.
- `TransformationController` (10 routes) + views (`transformation/show.blade.php` + 4 partials:
  `_canvas`, `_roadmap` (kèm form thêm milestone + danh sách theo category), `_proposal`, `_sow`).
  Tab Transformation ở project-header giờ là link thật; tab Diagnosis vẫn disabled (bypass chỉ ở
  gate, CHƯA có workspace/view riêng — đúng chủ đích Phase 1).

**Permission mới `business_transformation.manage`** — CEO, Lead Consultant, Consultant, PM (BA
và Customer Success KHÔNG có, đúng Ma trận Phần 7.2: PM chỉ tham gia từ Transformation trở đi).

### 🐛 Bug rò quyền tự phát hiện khi thêm PM — đã fix cùng lúc

Khi thêm role PM (permission set khác Context/Discovery — PM có `business_transformation.manage`
nhưng KHÔNG có `business_context.manage`/`business_discovery.manage`), phát hiện
`BusinessProjectPolicy::update()` cũ là 1 ability DÙNG CHUNG cho mọi Controller (OR toàn bộ
permission workspace) — nếu giữ nguyên và chỉ thêm `business_transformation.manage` vào OR-list,
PM sẽ **lọt qua** `authorize('update', ...)` ở `BusinessContextController`/`DiscoveryController`
dù matrix Phần 7.2 không cho phép PM thao tác 2 workspace đó. **Đã fix**: tách `update()` thành
3 ability riêng — `manageContext`/`manageDiscovery`/`manageTransformation` (mỗi Controller dùng
đúng ability của mình; `update()` giữ lại làm fallback cho `authorizeResource()`, không controller
nào gọi trực tiếp nữa). `DeliverablePolicy::manage()` cũng sửa thành dispatch theo
`$deliverable->workspace` thay vì hard-code permission Context. Verify: PM (đã thêm vào
`business_project_members`) pass `manageTransformation`, fail `manageContext`/`manageDiscovery`;
CEO vẫn pass cả 3 (regression OK).

**Verify**: tinker full flow Context→Discovery→Diagnosis(bypass)→Transformation: canvas, 2
milestone (quick_win + day_90), Proposal + SOW qua đủ draft→submit→approve→confirm, gate chặn
đúng ở từng bước (trước khi có gì / chỉ 1 trong 2 confirmed / cả 2 confirmed), confirm trước khi
approved bị chặn 422, sửa sau khi confirmed bị chặn 422, advance Transformation→Delivery thành
công. Render qua HTTP thật (curl + cookie jar, login CEO thật) cho cả trang Transformation VÀ
regression Context/Discovery — cả 3 đều 200 OK, nội dung đúng. Test thêm 1 milestone qua POST
thật (CSRF + session) — persist đúng, hiện trên trang ngay. Dữ liệu test + role/membership PM
tạm gán đã dọn sạch, không còn orphan row.

---

## ✅ ĐÃ HOÀN THÀNH — Discovery Workspace (Rule R2), cùng ngày 2026-07-16

Tiếp nối Vertical Slice 1 (Context Workspace), đã build xong Giai đoạn 2 theo `bcos_master_flow.md`
Phần 3 + `thaotac.md`. **Không cần migration mới** — tái dùng hoàn toàn bảng `deliverables` /
`deliverable_versions` sẵn có (đúng nguyên tắc Deliverable Engine "1 service dùng mọi nơi").

- `DeliverableType` enum: thêm 7 case mới — `interview`, `observation`, `document_review`,
  `data_review`, `process_map` (5 loại bản ghi khảo sát trực tiếp), `tps_canvas`,
  `business_discovery_report`. Helper `discoveryRecordTypes()` cho dropdown/validate.
- `UpsertSingletonDeliverableAction` (mới, dùng chung) — upsert "1 deliverable/project, nhiều
  version" cho mọi Canvas/Report dạng singleton (TPS Canvas, Business Discovery Report, và các
  Canvas tương tự ở Phase sau). Tách riêng khỏi `AddDiscoveryRecordAction` (deliverable CON,
  nhiều bản ghi, không phải singleton).
- `AddDiscoveryRecordAction` — mỗi bản ghi Interview/Observation/Document Review/Data Review/
  Process Map tự động là 1 Deliverable con (`parent_id`) của Business Discovery Report; report
  cha tự khởi tạo rỗng (`current_version=0`) nếu chưa có, không bắt tạo report trước.
- `SaveTpsCanvasAction`, `SaveBusinessDiscoveryReportAction` — dùng `UpsertSingletonDeliverableAction`.
- Gate R2 (`CheckStageGateEligibilityHandler::discoveryConditions()`): (a) có Business Discovery
  Report (`current_version >= 1`), (b) TPS Canvas đã điền đủ cả 3 trường Problem/Goal/Scope.
  Không yêu cầu approval (đúng Ma trận Phần 4 — Approval Service ở Discovery là "—").
- `DiscoveryController` (show/storeRecord/saveTpsCanvas/saveReport) + routes
  `/{businessProject}/discovery/*`.
- Views: `discovery/show.blade.php` + 3 partial (`_records`, `_tps_canvas`, `_report`), dùng lại
  `gate-checklist` + members card partials sẵn có (Phần 5B). `project-header.blade.php`: tab
  Context + Discovery giờ là link thật (active theo `request()->routeIs()`), 6 tab còn lại vẫn
  disabled/tooltip.
- Permission mới `business_discovery.manage` (PermissionEnum + RolePermissionSeeder: CEO, Lead
  Consultant, Consultant, BA — cùng nhóm role với `business_context.manage`, đúng Ma trận Phần
  7.2 dòng "Nhập Context/Discovery"). `BusinessProjectPolicy::update()` sửa để chấp nhận permission
  mới (cổng ghi dùng chung cho mọi workspace).
- **Verify**: tạo project test qua tinker (org 2), chạy trọn luồng Context (create→submit→approve→
  advance) → Discovery: gate chặn đúng khi chưa có gì → thêm 2 bản ghi (Interview + Observation,
  xác nhận cùng `parent_id` = Business Discovery Report container) → gate vẫn chặn → lưu TPS Canvas
  **thiếu trường** (gate vẫn chặn, đúng) → lưu đủ 3 trường (gate vẫn chặn vì thiếu Report) → lưu
  Business Discovery Report summary (deliverable trùng đúng container cũ, `current_version` tăng
  đúng, 2 con vẫn còn gắn) → gate mở → advance sang `diagnosis` thành công. **Render toàn bộ trang
  qua route + layout thật** (không chỉ tinker business logic) — Discovery page và Context page đều
  render sạch, đúng nội dung. Dữ liệu test đã dọn sạch sau khi verify (không để lại orphan row).

### 🐛 Bug thật phát hiện khi verify live (KHÔNG phải do Discovery Workspace, lỗi toàn app) — đã fix

Khi verify qua browser thật (không phải tinker) với tài khoản `ceo@demo.test`, mục "Business
Consulting OS" KHÔNG hiện ở sidebar dù permission đã seed đúng. Nguyên nhân: Spatie Permission
dùng Teams (`team_foreign_key = organization_id`) — cần gọi `setPermissionsTeamId($orgId)` ở MỌI
request, nhưng middleware DUY NHẤT được wire global vào `web` group
(`App\Http\Middleware\IdentifyOrganization`, đăng ký ở `bootstrap/app.php`) chỉ set
`TenantContext`, KHÔNG gọi `setPermissionsTeamId()`. Có 1 middleware khác
(`Modules\Organization\Http\Middleware\SetCurrentOrganization`) có gọi đúng nhưng chỉ đăng ký
alias `current_organization`, KHÔNG được gắn vào route group nào — code chết, tương tự bài học cũ
về `config/permissions.php`. Hậu quả: MỌI check `hasRole()`/`can()` team-scoped ở request thật
đều resolve rỗng cho MỌI user không phải super-admin — lỗi ảnh hưởng toàn app, không riêng BCOS,
chỉ chưa bị phát hiện vì các phiên verify trước tự set team id thủ công trong tinker.

**Đã fix**: thêm `setPermissionsTeamId($organization->id)` (và `setPermissionsTeamId(null)` ở
nhánh không resolve được org) vào `App\Http\Middleware\IdentifyOrganization::handle()`, ngay sau
`TenantContext::set()`. Verify lại qua HTTP thật (curl + cookie jar, login POST /login, KHÔNG
qua tinker) trên `php artisan serve` cổng riêng: sidebar "Business Consulting OS" hiện đúng, trang
Discovery Workspace render đầy đủ (tab active, TPS Canvas, Business Discovery Report, gate
checklist) cho session CEO thật. Server tạm + project test đã dọn sạch sau khi verify.

**Hạn chế biết trước** (nhất quán với Vertical Slice 1, chưa cần fix ngay):
- Discovery Workspace luôn hiển thị nội dung theo tab được click, KHÔNG theo `current_stage` —
  Gate checklist ở sidebar vẫn phản ánh `current_stage` của project (không phải tab đang xem).
  Đây là hành vi có chủ đích ở mức Phase 1 (đơn giản hoá), CHƯA implement đầy đủ nguyên tắc Phần
  5B "xem trước khi qua gate, chỉ khoá hành động ghi" cho các workspace tương lai (Diagnosis...).
- `DeliverablePolicy::approve()` / phạm vi `business_project_members` vẫn là hạn chế cũ (xem mục
  dưới) — Discovery không có approval nên không bị ảnh hưởng.
- TPS Canvas / Business Discovery Report chưa có ràng buộc DB "1/project" (không cần, vì
  `UpsertSingletonDeliverableAction` luôn tìm bản ghi cũ trước khi tạo mới, không có đường tạo
  trùng qua UI).

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

## ✅ PHASE 1 MVP HOÀN THÀNH — 6/6 bước (theo `bcos_master_flow.md` Phần 9)

- ~~**Nền + Context Workspace (Rule R1)**~~ — ✅ Đã xong (Vertical Slice 1).
- ~~**Discovery Workspace (Rule R2)**~~ — ✅ Đã xong.
- ~~**Transformation Workspace (Rule R4)**~~ — ✅ Đã xong.
- ~~**Delivery Workspace (Rule R5)**~~ — ✅ Đã xong.
- ~~**Đóng dự án tối thiểu (Rule R6/R7)**~~ — ✅ Đã xong (xem mục "ĐÃ HOÀN THÀNH — Closing Workspace" ở trên).

**Tiêu chí hoàn thành Phase 1** (spec Phần 9): "1 dự án thật chạy trọn Lead → Closed trong hệ
thống; 100% deliverable có version; không bản ghi mồ côi; `StageGateService` đã có đủ 8 stage".
Đã verify end-to-end qua tinker (Context→Discovery→Diagnosis(bypass)→Transformation→Delivery→
Closing→status=closed) nhiều lần xuyên suốt các phiên — CheckStageGateEligibilityHandler đủ
8 nhánh match() thật (Context/Discovery/Diagnosis-bypass/Transformation/Delivery/Closing đã có
điều kiện thật, Knowledge/CustomerSuccess còn `notImplementedConditions()` — đúng, vì Phase 2).

**Việc tiếp theo tự nhiên là Phase 2** (xem mục dưới) — không còn "phần thiếu của Phase 1".

---

## ✅ ĐÃ HOÀN THÀNH — Knowledge Workspace mở rộng (Phase 2, mảng 2/5), cùng ngày 2026-07-16

Theo `bcos_master_flow.md` Giai đoạn 7 — "khép vòng tri thức": Consultant ở dự án sau tra cứu lại
Case Study/Lessons Learned/Best Practice/Industry Knowledge từ các dự án trước cùng Industry, ngay
lúc làm Discovery.

- **Mở rộng `kc_items`** — thêm 3 type mới vào MySQL enum thật (`type` column):
  `lessons_learned`, `best_practice`, `industry_knowledge` (giữ nguyên `case_study` đã có sẵn từ
  Vertical Slice 1) + cột `industry` (string 100, nullable, indexed) — migration
  `2026_07_16_100015_add_industry_and_widen_type_on_kc_items_table.php`, dùng raw
  `ALTER TABLE ... MODIFY COLUMN` (không cần Doctrine/DBAL). `KcItemType::projectKnowledgeTypes()`
  helper mới gom đúng 4 type "tri thức dự án" (case_study/lessons_learned/best_practice/
  industry_knowledge) để dùng lại ở cả Knowledge Workspace lẫn Discovery widget, không hard-code
  lại danh sách ở nhiều nơi.
- **KcItem module** — `industry` field đi xuyên suốt: model fillable, Store/Update Data+Action,
  create/edit blade (input text), index filter bar (free-text, giống pattern `search`, URL key
  `ind`) + `ListKcItemsQuery`/`ListKcItemsHandler`/`KcItemApiController` (LIKE match, cùng cách
  `ListKcItemsHandler` xử lý `search`). Không xây search engine riêng trong BCOS — mọi tra cứu
  Knowledge Asset đều link-out sang chính trang KcItem index đã mở rộng này.
- **BCOS Knowledge Workspace tab** (workspace thứ 7/8, KHÔNG có Deliverable/DeliverableType riêng —
  Rule R7 dùng thẳng `KcItem` + quan hệ `BusinessProject::kcItems()`/`KcItem::businessProject()` đã
  có sẵn từ Closing Workspace) — `KnowledgeController` (`show`, `attach`) + views
  `knowledge/show.blade.php` + `_knowledge_assets.blade.php`, dùng LẠI nguyên
  `AttachKnowledgeAssetAction` của Closing (generic, chỉ gắn `business_project_id` lên KcItem đã
  tồn tại — không tạo KcItem rút gọn, cùng lý do Rule R7 gốc: KcItem bắt buộc `category_id` khác
  khái niệm BusinessProject). Danh sách "gắn KcItem có sẵn" lọc riêng theo 4 type tri thức dự án
  (khác Closing's danh sách generic mọi type). "Tạo Knowledge Asset mới" = 4 link-out tới
  `backend.kc-items.create` prefill `business_project_id` + `type` + `industry` (industry lấy mặc
  định từ `$businessProject->customer->industry`) — cùng pattern query-string-prefill với
  Task/KcItem integration ở Delivery/Closing.
  Permission mới `business_knowledge.manage` — gán giống hệt `business_closing.manage` (CEO, Lead
  Consultant, PM; Consultant/BA/CS đều "—", theo đúng lý do Closing: Knowledge Workspace gắn liền
  R7/đóng dự án). Project-header tab Knowledge giờ là link thật — **7/8 tab đã xong**, chỉ Customer
  Success còn disabled (Phase 2, mảng 3/5).
- **Industry Search widget ở Discovery Workspace** — card nhỏ trong sidebar
  (`discovery/_industry_knowledge.blade.php`): hiện Industry của customer + đếm nhanh số Knowledge
  Asset cùng ngành (LIKE match, đúng cách `ListKcItemsHandler` xử lý industry filter) + nút link-out
  sang KcItem index với `?ind=<industry>` — KHÔNG xây lại search UI trong BCOS. Nếu customer chưa
  khai báo Industry, widget hiện fallback message thay vì ẩn hẳn/lỗi.

**Verify**: tinker (tạo KcItem type mới + industry, filter LIKE đúng — match/không match/partial
match) + HTTP thật (curl+cookie jar, port 8123): create/edit form hiển thị field `industry` + 3 type
mới, round-trip tạo→filter tìm thấy→edit đổi type/industry→filter phản ánh đúng; Knowledge Workspace
tab hiển thị đúng danh sách gắn với project (`kcItems()` relation), gắn KcItem có sẵn redirect đúng
về Knowledge (không lạc sang Closing), tạo mới qua link-out prefill đúng cả 3 trường, xuất hiện ngay
trong danh sách; Discovery widget đếm đúng (1 item cùng ngành, loại trừ đúng item khác ngành), link
ra KcItem index tìm đúng item, fallback đúng khi industry rỗng. Regression: cả 7 tab workspace +
kc-items index/create/edit đều 200, không log lỗi mới trong `storage/logs/laravel.log`. Toàn bộ dữ
liệu test đã forceDelete, mật khẩu tạm của user test đã restore về hash gốc, cache đã clear.

---

## ✅ ĐÃ HOÀN THÀNH — Customer Success Workspace (Phase 2, mảng 3/5), cùng ngày 2026-07-16

Theo `bcos_master_flow.md` Giai đoạn 8 — "vòng đời không kết thúc ở Closed": workspace thứ 8/8,
KHÔNG còn tab disabled trên project-header.

- **`success_reviews` table** (migration `2026_07_16_100016_create_success_reviews_table.php`) —
  mỗi hàng là 1 touchpoint CS (không ép 1 project = 1 hàng): `survey_response_id` + `csat_score`/
  `nps_score` denormalized, `follow_up_at`/`follow_up_note`/`followed_up_at`, `renewal_status`/
  `renewal_note`, `new_lead_id`. Model `SuccessReview` (TenantAwareModel) +
  `BusinessProject::successReviews()` (HasMany).
- **CSAT/NPS qua Survey engine hiện có (KHÔNG xây form khảo sát mới)** —
  `EnsureCsatNpsSurveyAction` tạo 1 Survey DÙNG CHUNG/tổ chức (idempotent theo title, không tạo
  Survey riêng mỗi project), `allow_multiple_responses=true` — CS staff điền hộ khách hàng qua
  đúng trang "take" chuẩn của Survey engine, sau đó `AttachSuccessReviewSurveyAction` gắn
  SurveyResponse đã điền vào đúng Business Project (đọc điểm qua SurveyAnswer theo `field_type`,
  không hard-code field_key vì key sinh ngẫu nhiên). **2 bug thật của Survey engine phát hiện +
  sửa khi tích hợp** (xem "Ghi chú kỹ thuật" bên dưới) — không phải lỗi mới do BCOS gây ra, nhưng
  chặn hoàn toàn tính năng nếu không sửa.
- **Follow-up định kỳ + Renewal** — `StoreSuccessReviewNoteAction` (ghi 1 touchpoint mới, các
  trường độc lập, không ép đủ mọi trường) + `MarkFollowUpDoneAction`. Notification
  `FollowUpDueNotification` + command `notifications:success-followup-due` (dailyAt 08:15,
  `routes/console.php`) — mirror đúng pattern `TaskOverdueNotification`/`SendTaskOverdueNotifications`
  đã có sẵn (KHÔNG xây cơ chế Calendar/reminder riêng — Meeting entity của Delivery Workspace
  không tái dùng được vì nó là bản ghi quá khứ, không phải lịch nhắc tương lai).
- **New Opportunity → Tạo Lead mới** — `CreateLeadFromOpportunityAction`, chiều NGƯỢC của
  `ConvertLeadToBusinessProjectAction` (Context Workspace) — tái dùng NGUYÊN `CreateLeadAction`
  của module Lead (dedup contact, stage history, event, scoring), không tự chế logic tạo Lead.
  Thêm `LeadSource` code mới `business_project` (`LeadSourcesSeeder`, `LeadSourceCode` enum) —
  source_detail ghi rõ code dự án cũ. Khép vòng lặp toàn hệ thống (spec Phần 3 "Vòng thương mại").
- **`CustomerSuccessController`** (`show`, `attachSurvey`, `storeNote`, `markFollowUpDone`,
  `createLead`) + views (`customer-success/show.blade.php` + 4 partials). Permission mới
  `business_customer_success.manage` — CHỈ CEO + role `customer_success` (Ma trận Phần 7.2 hàng
  "CSAT/NPS, Renewal, tạo Lead mới": Lead Consultant/Consultant/BA/PM đều "—", khác Knowledge/
  Closing dùng chung set role — đây là workspace ĐẦU TIÊN có set quyền hẹp hơn, do có dòng ma
  trận riêng, không suy luận từ workspace khác). `BusinessProjectPolicy::manageCustomerSuccess`.
  Gate `Knowledge -> CustomerSuccess` + `CustomerSuccess` (terminal, không gate) thêm vào
  `CheckStageGateEligibilityHandler` (trước đó rơi vào `notImplementedConditions()`, chặn advance).
  Project-header: xóa nốt tab disabled cuối cùng — **8/8 workspace giờ là link thật**.

**2 bug thật của module Survey phát hiện + sửa (ngoài phạm vi BCOS nhưng chặn tính năng CSAT/NPS)**:
1. `CreateFieldAction::generateKey()` thiếu match arm cho `FieldType::Nps`/`Matrix`/`Ranking` —
   `UnhandledMatchError` khi tạo field NPS (chưa từng có field NPS nào được tạo trong toàn bộ
   codebase trước đây, kể cả qua seeder). Đã thêm 3 arm còn thiếu.
2. `SurveyTakeController::submit()` gọi `SurveyAnswerData::collect($array)` KHÔNG truyền `$into`,
   trả về `array` thường trong khi `SurveyResponseData::$answers` yêu cầu kiểu `DataCollection`
   — **mọi lần submit khảo sát qua UI (bất kỳ survey nào, không riêng CSAT/NPS) đều lỗi 500** trước
   khi sửa. Đã thêm `DataCollection::class` làm tham số `$into`.

**Verify**: tinker (tạo Survey CSAT/NPS + field NPS thật, submit response, attach vào project, đọc
đúng csat/nps score) + HTTP thật đầy đủ (curl+cookie jar port 8123): login CEO → mở CS Workspace →
mở trang "take" khảo sát thật → submit thật qua HTTP (phát hiện bug #2 ở bước này) → gắn response
vào project qua UI → ghi follow-up+renewal → đánh dấu follow-up hoàn thành → tạo Lead mới từ New
Opportunity → tất cả hiển thị đúng trên Workspace. Notification command chạy đúng (queue job đúng
channels `database+broadcast`, đã xoá job test khỏi hàng đợi để không tồn lưu). Regression: cả
8/8 tab workspace + kc-items index đều 200, `business_customer_success.manage` đúng CHỈ gán cho
ceo/customer_success (không lead_consultant/pm/consultant/ba). Toàn bộ dữ liệu test đã forceDelete
(BusinessProject, Customer, SuccessReview, SurveyResponse+SurveyAnswer, Lead+LeadContact+
LeadStageHistory+LeadActivity), mật khẩu tạm đã restore, cache đã clear. **Lưu ý phát hiện ngoài
lề (KHÔNG sửa, ngoài phạm vi)**: bảng `jobs` có ~63 job pending tồn đọng từ trước (một số đã FAIL,
VD `DeliverableAwaitingApprovalNotification`) — không có `queue:work` chạy nền liên tục trong môi
trường dev này; cần lưu ý nếu sau này bật queue worker thật, có thể cần dọn/kiểm tra queue backlog
trước.

## ✅ ĐÃ HOÀN THÀNH — BCOS Admin Dashboard + KPI (Phase 2, mảng 4/5), 2026-07-17

Theo `bcos_master_flow.md` Phần 10 — "BCOS tự đo chính nó". Màn hình mới, cross-project, **chỉ
Founder/Admin truy cập** (`BusinessProjectPolicy::viewBcosDashboard()`, route
`backend.business-projects.bcos-dashboard.show`, link từ trang Business Projects index).

- **`business_project_stage_history`** (migration `2026_07_17_100017_...`) — bảng MỚI duy nhất
  của mảng này, nhưng đúng nguyên tắc spec tự nêu ("nếu 1 KPI cần bảng riêng, nghĩa là mô hình dữ
  liệu đang thiếu, bổ sung Ở NGUỒN") — trước đây KHÔNG có cách nào biết 1 project vào/rời 1 stage
  lúc nào (chỉ có `current_stage` hiện tại), giờ `AdvanceBusinessProjectStageAction` ghi 1 hàng mỗi
  lần advance. Cycle Time chỉ tính được từ dữ liệu MỚI (không backfill được lịch sử advance trước
  khi bảng này tồn tại).
- **6 KPI, tất cả tính trực tiếp từ dữ liệu đã có (`BcosDashboardController`)**:
  1. Gate Compliance Rate — % project active KHÔNG bị "trễ" (đủ điều kiện qua gate ≥7 ngày mà
     chưa advance).
  2. Knowledge Reuse Rate — **ƯỚC TÍNH** (không sửa được chính xác vì `deliverable_evidence_links`
     là Deliverable↔Deliverable, KHÔNG phải Deliverable↔KcItem — phát hiện khi research; và
     `kc_view_logs` không có cột `business_project_id` nên không biết chính xác người xem đang ở
     project nào) — join `kc_view_logs.user_id` qua `business_project_members` của project KHÁC
     project gốc, ghi rõ "ước tính" trên UI, không giả vờ là số chính xác.
  3. Average Cycle Time theo giai đoạn — từ `business_project_stage_history` (mới).
  4. Deliverable Version Discipline — % deliverable `confirmed` có ≥2 version. Sub-metric "% dùng
     Template chuẩn" **BỎ QUA** — cột `deliverables.template_id` KHÔNG tồn tại trong schema (khác
     ghi nhớ trước đây tưởng "có cột nhưng chưa dùng" — thực tế chưa từng migrate), chờ Template
     Library (mảng 5/5).
  5. CSAT/NPS trung bình + Renewal Rate — trực tiếp từ `success_reviews` (Giai đoạn 8).
  6. R7 Fulfillment Rate — % project đã `closed` có ≥1 Knowledge Asset + trung bình/project.
- **Export CSV** — spec nói "tái dụng cơ chế export ở Report module" nhưng research xác nhận
  KHÔNG có service export dùng chung nào để gọi — mọi export trong codebase (Report/Lead/
  ActivityLog/Deployment) đều theo đúng 1 convention lặp lại: build `Collection` mảng
  associative (key = tên cột) rồi `(new FastExcel($rows))->download(...)`. Áp dụng ĐÚNG convention
  đó (6 endpoint export riêng, 1/KPI, đuôi `.csv` thay vì `.xlsx` mặc định của các nơi khác) —
  không phải xây engine export mới, cũng không có gì "dùng chung" theo nghĩa gọi 1 class có sẵn.

**Verify**: tinker seed dữ liệu thật (1 project closed có Knowledge Asset + deliverable confirmed
2-version + success_review CSAT=5/NPS=10/renewal=renewed, 1 project active "cũ" để test không bị
đếm nhầm là stuck khi gate chưa đủ điều kiện, 1 KcViewLog mô phỏng reuse cross-project) + HTTP thật
đầy đủ: dashboard hiển thị đúng cả 6 KPI (100% ở các KPI có 1/1 mẫu đạt, CSAT=5/NPS=10/Renewal=100%
đúng dữ liệu, Cycle Time hiện đúng 1 giai đoạn có dữ liệu + "Chưa đủ dữ liệu" cho các giai đoạn
khác), cả 6 export CSV đều 200 + `text/csv` + nội dung UTF-8 đúng tiếng Việt, quyền truy cập đúng
(CEO xem được, Sales bị 403 + link ẩn đúng trên trang index qua `@can`). Regression cả 8/8 tab
workspace vẫn 200. **1 lỗi phát hiện lúc verify hoá ra là lỗi TỰ TẠO của chính test data** (tinker
tạo `DeliverableVersion` trực tiếp, quên set `created_at` — model này `$timestamps=false` nên
không tự set, khác `UpsertSingletonDeliverableAction` thật luôn set tường minh) — không phải bug
thật của code, đã xác nhận bằng cách so sánh với action thật rồi sửa lại data test. Toàn bộ dữ liệu
test đã forceDelete, mật khẩu tạm (`ceo@demo.test`, `sales@demo.test`) đã khôi phục đúng (restore
theo hash gốc đã lưu, hoặc theo password mặc định `password` ghi trong `UserSeeder.php` khi không
kịp lưu hash gốc trước khi đổi — bài học: LUÔN lưu hash gốc TRƯỚC khi đổi password test, kể cả với
user "phụ" chỉ dùng để test 403).

## ✅ ĐÃ HOÀN THÀNH — Template Library (Phase 2, mảng 5/5 — HẾT PHASE 2), 2026-07-17

Theo `bcos_master_flow.md` — "Template Service" (nhắc tên ở Phần 1/4/6/9 nhưng KHÔNG có 1 mục
Giai đoạn riêng như Giai đoạn 1-8 — mảng ít chi tiết nhất trong toàn bộ spec).

- **`deliverable_templates`** (bảng MỚI, migration `2026_07_17_100018_...`) — `organization_id`
  NULLABLE (mirror ĐÚNG pattern `lead_sources`/`lead_pipeline_stages`: null = template dùng chung
  mọi tổ chức, có giá trị = riêng 1 org), `type` (string khớp `DeliverableType`, không FK cứng —
  cùng lý do `deliverables.type`), `content` JSON cùng shape với `DeliverableVersion.content` của
  type đó (prefill thẳng vào form, không cần transform). Model `DeliverableTemplate` — Model
  THƯỜNG (không phải TenantAwareModel, vì cần hỗ trợ org=null).
- **`deliverables.template_id`** (cột MỚI, cùng migration) — cột này TRƯỚC ĐÂY hoàn toàn không tồn
  tại (đã xác nhận qua `Schema::getColumnListing` ở mảng Dashboard/KPI, sửa lại ghi nhớ sai trước
  đó "có cột nhưng chưa dùng"). `Deliverable::template()` relation. `UpsertSingletonDeliverableAction`
  nhận thêm tham số optional `?int $templateId` — set MỘT LẦN lúc tạo deliverable mới (không đổi
  lại khi update sau đó, đây là "nguồn gốc" không phải "lần áp dụng cuối"). Đây LÀ hành động đóng
  gap sub-metric "% dùng Template chuẩn" của KPI Deliverable Version Discipline (Dashboard mảng
  4/5) — lần đầu tiên cột này có dữ liệu thật.
- **`TemplateLibraryController`** — CRUD đầy đủ (index nhóm theo type/create/edit/update/destroy
  soft-delete), permission mới `business_template.manage` (CEO + Lead Consultant + System Admin —
  Ma trận 7.2 hàng "Duyệt Knowledge/SOP/Framework" cho tinh thần tương tự: Founder full, Lead
  Consultant chuẩn hóa). `DeliverableTemplatePolicy` riêng (KHÔNG theo pattern `manageWorkspace()`
  của `BusinessProjectPolicy` — template không gắn 1 Business Project cụ thể, không cần check
  `isMember()`). Link từ trang Business Projects index (cùng vị trí link "BCOS Dashboard").
- **Chứng minh vòng lặp đầy đủ ở Proposal + SOW** (2 loại DUY NHẤT spec gọi tên cụ thể — Phần 4:
  "Template Service (mẫu Proposal/SOW chuẩn)") — dropdown "Bắt đầu từ Template" trên cả 2 form
  (Alpine.js `x-ref` prefill textarea từ `content` JSON của template chọn, KHÔNG cần round-trip
  server), `template_id` submit kèm form, ghi vào Deliverable khi tạo mới. KHÔNG wiring vào toàn
  bộ 10 loại Deliverable khác (Context/TPS Canvas/Diagnosis Matrix/Weekly Report/Final Report...)
  — quyết định phạm vi có chủ đích: `UpsertSingletonDeliverableAction` (action DÙNG CHUNG cho ~8
  loại singleton deliverable) đã có sẵn tham số `templateId`, nên các workspace khác có thể bật
  UI selector bất cứ lúc nào sau này (chỉ cần thêm dropdown + Alpine prefill giống Proposal/SOW),
  không cần sửa lại tầng Action/DB.

**2 lỗi nhỏ tự tạo, tự sửa lúc verify (không phải bug thiết kế)**: (1) namespace sai của trait
`LogsActivity` (`Spatie\Activitylog\Traits\LogsActivity` không tồn tại, đúng phải là
`Spatie\Activitylog\Models\Concerns\LogsActivity`) — lỗi gõ nhầm lúc viết `DeliverableTemplate`
model, phát hiện ngay ở lần tinker đầu tiên. (2) `TemplateLibraryController` giả định key
`description` luôn có trong mảng `validated()` dù field optional không gửi lên — sửa bằng
`?? null`. Cả 2 sửa ngay tại chỗ, verify lại xác nhận không còn lỗi.

**Verify**: tinker (tạo `DeliverableTemplate` global, deliverableType() resolve đúng) + HTTP thật
đầy đủ: CRUD Template qua UI (create → list đúng nhóm theo type → edit prefill đúng JSON → xóa
soft-delete đúng, không còn hiện trong list); mở Transformation Workspace của project thật thấy
đúng dropdown "Bắt đầu từ Template" liệt kê template vừa tạo; submit Proposal kèm `template_id`
→ Deliverable tạo ra có `template_id` đúng + relation `template()` resolve đúng tên. Quyền hạn
đúng (CEO/Lead Consultant/System Admin qua được, `ops` role bị 403). Regression cả 8/8 tab
workspace + BCOS Dashboard + KcItem index đều 200. Toàn bộ dữ liệu test đã forceDelete, mật khẩu
tạm (`ceo@demo.test`, `ops@demo.test`) đã khôi phục đúng, cache đã clear.

---

## 🎉 PHASE 2 HOÀN THÀNH TOÀN BỘ (5/5 mảng, 2026-07-17)

Diagnosis Workspace+Approval R3 → Knowledge Workspace mở rộng → Customer Success Workspace →
BCOS Admin Dashboard+KPI → Template Library — cả 5 mảng đã xong, **8/8 workspace BCOS đều là
link thật, không còn tab disabled nào**. Việc tiếp theo tự nhiên là Phase 3/4 (xem mục dưới) —
không còn "phần thiếu của Phase 2".

**Lưu ý tổng kết khi bắt đầu Phase 3/4**: pattern permission đã lặp lại NHIỀU lần qua toàn bộ
Phase 2 (Context/Discovery/Diagnosis/Transformation/Delivery/Closing/Knowledge/Customer Success =
8 workspace, cộng Template Library là 1 resource cấp tổ chức riêng) — mỗi workspace/resource mới
luôn 3 chỗ: `PermissionEnum`, `RolePermissionSeeder`, ability riêng (`manageX` trong
`BusinessProjectPolicy`, hoặc Policy riêng nếu KHÔNG gắn với 1 Business Project cụ thể như
`DeliverableTemplatePolicy`) — KHÔNG gộp vào 1 OR-list chung mà bỏ qua ability riêng. Nếu 2
workspace phụ thuộc lẫn nhau, dùng đúng pattern cross-workspace guard đã làm ở Diagnosis→
Transformation: đặt check ở tầng Controller của workspace PHỤ THUỘC, không nhét vào action
generic. Nếu 1 KPI/tính năng cần dữ liệu mà bảng hiện có không đủ, đọc kỹ nguyên tắc Phần 10 tự
nêu: bổ sung Ở NGUỒN (bảng/cột mới phục vụ toàn hệ thống), không phải bảng thống kê song song
riêng cho 1 con số.

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
