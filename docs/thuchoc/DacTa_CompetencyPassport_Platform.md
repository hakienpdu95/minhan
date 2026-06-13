# Đặc tả Kỹ thuật: Competency Passport Platform
## Nền tảng Chứng nhận Năng lực Số Cá nhân — Xuyên Tổ chức

**Phiên bản:** 1.0  
**Ngày:** 13/06/2026  
**Trạng thái:** Bản đặc tả gốc (Baseline Specification)  
**Phân loại:** Tài liệu Kiến trúc & Sản phẩm Nội bộ

---

## Mục lục

1. [Tầm nhìn & Bối cảnh](#1-tầm-nhìn--bối-cảnh)
2. [Khái niệm cốt lõi](#2-khái-niệm-cốt-lõi)
3. [Kiến trúc tổng thể](#3-kiến-trúc-tổng-thể)
4. [Vòng đời tài khoản](#4-vòng-đời-tài-khoản)
5. [Phase 0 — Identity Foundation](#5-phase-0--identity-foundation)
6. [Phase 1 — Competency Passport](#6-phase-1--competency-passport)
7. [Phase 2 — Portability & Sharing](#7-phase-2--portability--sharing)
8. [Phase 3 — eKYC & Verified Identity](#8-phase-3--ekyc--verified-identity)
9. [Phase 4 — Open Assessment Marketplace](#9-phase-4--open-assessment-marketplace)
10. [Phase 5 — National Platform & API](#10-phase-5--national-platform--api)
11. [Schema dữ liệu chi tiết](#11-schema-dữ-liệu-chi-tiết)
12. [Nguyên tắc thiết kế & Ràng buộc](#12-nguyên-tắc-thiết-kế--ràng-buộc)
13. [Lộ trình tổng quan](#13-lộ-trình-tổng-quan)

---

## 1. Tầm nhìn & Bối cảnh

### 1.1 Vấn đề hiện tại

Hệ thống Workforce Digital Twin hiện tại hoạt động theo mô hình **org-centric** (tổ chức là trung tâm):

```
[Tổ chức A] ←owns→ [Hồ sơ năng lực của nhân viên X]
```

Khi nhân viên X rời tổ chức A:
- Tài khoản bị deactivate → mất quyền truy cập toàn bộ
- Dữ liệu TDWCF, Sandbox, Chứng nhận, AI Impact ở lại tổ chức A
- Tổ chức B tạo hồ sơ mới, trống hoàn toàn
- Không có cơ chế để nhân viên chứng minh năng lực tích lũy với nhà tuyển dụng mới

### 1.2 Tầm nhìn

Xây dựng lớp **Competency Passport** — biến hệ thống từ "phần mềm HR nội bộ" thành **nền tảng chứng nhận năng lực số quốc gia**:

```
[Cá nhân X] ←owns→ [Competency Passport]
                         ↑ được đóng góp bởi
              [Org A] + [Org B] + [Open Assessments]
```

Cá nhân làm chủ lịch sử năng lực của mình, được xác minh bởi các tổ chức thực tế đã từng gắn bó.

### 1.3 Định vị sản phẩm dài hạn

```
Giai đoạn 1 (hiện tại):  HRM nội bộ — quản lý năng lực nhân viên
Giai đoạn 2 (Phase 0-1): HRM + Personal Passport — hồ sơ cá nhân portable
Giai đoạn 3 (Phase 2-3): Verified Identity Platform — danh tính nghề nghiệp tin cậy
Giai đoạn 4 (Phase 4):   Talent Marketplace — kết nối ứng viên ↔ nhà tuyển dụng
Giai đoạn 5 (Phase 5):   National Digital Competency Index — chuẩn quốc gia
```

---

## 2. Khái niệm cốt lõi

### 2.1 Các loại tài khoản

| Loại | Mô tả | Quyền |
|---|---|---|
| `free` | Tài khoản cá nhân độc lập, chưa thuộc org nào | Xem Passport cá nhân, tham gia Open Assessment (sau Phase 3) |
| `org_member` | Đang thuộc một tổ chức cụ thể | Toàn bộ quyền trong org đó (theo role hiện tại) |
| `suspended` | Bị khóa tạm thời | Không truy cập được gì |

> Một người chỉ tồn tại **1 user record duy nhất** trên toàn hệ thống — định danh bằng email. Trạng thái `org_member` hay `free` là thuộc tính của chính user đó, không phải của tổ chức.

### 2.2 Competency Passport

Là tập hợp **các Snapshot hồ sơ năng lực** qua từng tổ chức mà cá nhân đã làm việc, cộng với các kết quả Open Assessment nếu có. Passport thuộc về cá nhân, không bị xóa khi rời org.

### 2.3 Workforce Profile Snapshot

Khi nhân viên rời tổ chức, hệ thống tự động **chụp lại (snapshot)** hồ sơ năng lực tại thời điểm đó và lưu vào namespace cá nhân. Snapshot là bản sao **read-only**, không đồng bộ ngược về org. Org giữ nguyên bản gốc của họ.

### 2.4 Mức độ xác minh danh tính (Identity Trust Level)

```
Level 0: Chưa xác minh gì
Level 1: Email đã xác minh                        ← Phase 0
Level 2: Email + Số điện thoại OTP                ← Phase 3
Level 3: Email + Phone + CCCD hash                ← Phase 3
Level 4: Email + Phone + CCCD + Face liveness     ← Phase 5 (tùy chọn)
```

Mỗi level mở ra thêm tính năng và uy tín của hồ sơ khi chia sẻ với nhà tuyển dụng.

### 2.5 Open Assessment Campaign

Tổ chức đăng một "chiến dịch đánh giá mở" — bất kỳ cá nhân nào có tài khoản (ở mức xác minh tối thiểu) đều có thể tham gia làm khảo sát / Sandbox mô phỏng vị trí đó. Kết quả được lưu vào Passport cá nhân và tổ chức có thể xem ranking để tuyển dụng.

---

## 3. Kiến trúc tổng thể

### 3.1 Mô hình 3 lớp

```
┌────────────────────────────────────────────────────────────────┐
│  LAYER 3: Open Marketplace (Phase 4-5)                        │
│                                                               │
│  [Org A posts campaign] ←→ [Ứng viên tự do tham gia]         │
│  [National Competency Index] [Third-party API integrations]   │
├────────────────────────────────────────────────────────────────┤
│  LAYER 2: Competency Passport (Phase 1-3)                     │
│                                                               │
│  [Personal Dashboard]  [Snapshot history]  [Verified badge]   │
│  [Shareable profile]   [PDF export]        [Public link]      │
├────────────────────────────────────────────────────────────────┤
│  LAYER 1: Org Workspace (hiện tại + Phase 0)                  │
│                                                               │
│  [HR Admin]  [Workforce Admin]  [Sandbox]  [Certifications]   │
│  [AI Impact] [Career Pathway]  [Reports]   [TDWCF Survey]     │
└────────────────────────────────────────────────────────────────┘

Tất cả các Layer chia sẻ chung: [Identity Layer] — 1 user = 1 identity
```

### 3.2 Quan hệ dữ liệu cốt lõi

```
users (1 record / người, tồn tại mãi mãi)
  │
  ├── org_memberships (lịch sử các tổ chức đã thuộc về)
  │         ├── joined_at, left_at, role_at_exit, job_title_at_exit
  │         └── status: active | inactive
  │
  ├── workforce_profile_snapshots (Passport — thuộc về cá nhân)
  │         ├── source_org_id, snapshot_at (ngày chụp)
  │         ├── Điểm TDWCF tại thời điểm rời
  │         ├── Certifications đã có
  │         └── Top AI Impact highlights
  │
  ├── identity_verifications (log các bước xác minh)
  │         └── method, verified_at, trust_level
  │
  └── campaign_participations (Open Assessment — Phase 4)
            └── campaign_id, result_snapshot, status
```

### 3.3 Nguyên tắc tách biệt dữ liệu

```
Org namespace:  workforce_profiles (organization_id NOT NULL)
                → TenantAwareModel, chỉ truy cập khi là org_member
                → Org sở hữu, org quyết định

Personal namespace: workforce_profile_snapshots (organization_id = null)
                    → Không qua TenantAware, filter theo user_id
                    → Cá nhân sở hữu, tồn tại mãi mãi
```

---

## 4. Vòng đời tài khoản

### 4.1 Happy path đầy đủ

```
[Người dùng mới]
  Đăng ký bằng email cá nhân
  Xác minh email → account_type = 'free', trust_level = 1
        │
        ▼
[Được mời / Tự apply vào Org A]
  HR tạo hoặc link tài khoản
  org_memberships.insert(user_id, org_A, joined_at, role='member')
  account_type = 'org_member', current_org_id = Org_A
        │
        ▼
[Làm việc tại Org A — N tháng]
  Thao tác bình thường trong workspace Org A
  workforce_profiles record tồn tại tại (org_A, user_id)
  Tích lũy: TDWCF, Sandbox, Cert, AI Impact
        │
        ▼
[Nghỉ việc / HR thu hồi quyền]
  Trigger: HR đánh dấu "Nhân viên đã rời tổ chức"
  
  Hệ thống tự động (trong 1 transaction):
  ① org_memberships.update(left_at = now, status = 'inactive')
  ② Chụp snapshot: workforce_profile_snapshots.insert(
       user_id, source_org_id = Org_A,
       snapshot_at = now, [toàn bộ điểm + cert + highlights]
     )
  ③ account_type = 'free', current_org_id = null
  ④ Gửi email: "Hồ sơ năng lực tại [Org A] đã được lưu vào Passport của bạn"
        │
        ▼
[Trạng thái Free]
  Đăng nhập → thấy Personal Dashboard
  Xem lịch sử tất cả org đã làm việc
  Tải PDF từng snapshot
  (Phase 2+) Chia sẻ link hồ sơ
  (Phase 4+) Tham gia Open Assessment
        │
        ▼
[Vào Org B]
  Lặp lại chu trình — snapshot Org A vẫn còn trong Passport
  Passport tích lũy qua từng org
```

### 4.2 Trạng thái tài khoản và quyền truy cập

```
account_type = 'free'
  ✓ Đăng nhập hệ thống
  ✓ Xem Personal Dashboard + tất cả snapshots
  ✓ Tải PDF từng snapshot
  ✗ Không thao tác được dữ liệu của bất kỳ org nào
  ✗ Không làm khảo sát / Sandbox / Cert của org (chỉ của Open Campaign)

account_type = 'org_member' (Org X)
  ✓ Tất cả quyền của 'free'
  ✓ Toàn bộ quyền trong workspace Org X (theo role: member/ops/manager/ceo)
  ✗ Không truy cập workspace org khác
```

---

## 5. Phase 0 — Identity Foundation

**Mục tiêu:** Tách bạch định danh cá nhân khỏi tổ chức. Mọi người dùng có thể tồn tại trên hệ thống dù không thuộc org nào.

**Thời gian ước tính:** 2–3 tuần

### 5.1 Thay đổi schema

```sql
-- Thêm vào bảng users:
ALTER TABLE users ADD COLUMN account_type VARCHAR(20) NOT NULL DEFAULT 'free'
    COMMENT 'free | org_member | suspended';
ALTER TABLE users ADD COLUMN current_org_id BIGINT NULL
    COMMENT 'NULL nếu free, org_id nếu đang là org_member';
ALTER TABLE users ADD COLUMN email_verified_at TIMESTAMP NULL;
ALTER TABLE users ADD COLUMN trust_level TINYINT NOT NULL DEFAULT 0
    COMMENT '0=unverified, 1=email, 2=phone, 3=cccd, 4=cccd+face';

-- Bảng lịch sử thành viên tổ chức:
CREATE TABLE org_memberships (
    id              BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id         BIGINT NOT NULL,
    organization_id BIGINT NOT NULL,
    role_at_entry   VARCHAR(50),
    role_at_exit    VARCHAR(50),
    job_title_at_exit VARCHAR(200),
    joined_at       TIMESTAMP NOT NULL,
    left_at         TIMESTAMP NULL,       -- NULL = đang hoạt động
    exit_reason     VARCHAR(100) NULL,    -- 'resigned', 'terminated', 'retired', 'contract_end'
    exit_initiated_by VARCHAR(20) NULL,   -- 'hr', 'self', 'system'
    status          VARCHAR(20) NOT NULL DEFAULT 'active',  -- active | inactive
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_active_membership (user_id, organization_id, status),
    INDEX idx_om_user (user_id),
    INDEX idx_om_org (organization_id)
);
```

### 5.2 Email verification flow

```
[Đăng ký]
  User nhập email + password
  Hệ thống gửi email chứa link xác minh (token, TTL 24h)
  trust_level = 0, email_verified_at = NULL
        ↓
[Click link xác minh]
  Token hợp lệ → email_verified_at = now()
  trust_level = 1
  Redirect về dashboard với banner "Email đã xác minh"
        ↓
[Xác minh lại nếu cần]
  User có thể yêu cầu gửi lại email (rate limit: 3 lần/giờ)
```

### 5.3 Logic khi HR thu hồi quyền nhân viên

```php
// Service: OrgMembershipService::deactivate(User $user, Organization $org, array $options)
DB::transaction(function () use ($user, $org, $options) {
    // 1. Cập nhật membership
    OrgMembership::where('user_id', $user->id)
        ->where('organization_id', $org->id)
        ->where('status', 'active')
        ->update([
            'left_at'          => now(),
            'status'           => 'inactive',
            'role_at_exit'     => $user->getRoleInOrg($org->id),
            'job_title_at_exit'=> $user->employee?->jobTitle?->name,
            'exit_reason'      => $options['reason'] ?? 'resigned',
            'exit_initiated_by'=> $options['initiated_by'] ?? 'hr',
        ]);

    // 2. Chụp snapshot (Phase 1 implement, Phase 0 chuẩn bị hook)
    SnapshotWorkforceProfileJob::dispatch($user->id, $org->id);

    // 3. Cập nhật trạng thái user
    $user->update([
        'account_type'    => 'free',
        'current_org_id'  => null,
    ]);

    // 4. Thu hồi role/permissions trong Spatie
    $user->removeRole($user->getRoleInOrg($org->id));

    // 5. Gửi email thông báo cho nhân viên
    Mail::to($user->email)->send(new OrgExitNotification($user, $org));
});
```

### 5.4 Màn hình sau Phase 0

**Khi user = 'free' đăng nhập:**
```
┌─────────────────────────────────────────────┐
│  Xin chào, Nguyễn Văn A                     │
│  Tài khoản cá nhân · trust_level: 1 ✓ Email │
│                                             │
│  Bạn chưa thuộc tổ chức nào.               │
│                                             │
│  [Xem Competency Passport của tôi]          │
│  [Cách tham gia một tổ chức]               │
└─────────────────────────────────────────────┘
```

### 5.5 Acceptance criteria Phase 0

- [ ] User có thể đăng ký tài khoản bằng email cá nhân, không cần được org mời trước
- [ ] Email xác minh hoạt động, trust_level cập nhật đúng
- [ ] Khi HR deactivate nhân viên: account_type chuyển 'free', không còn vào được workspace org
- [ ] User dạng 'free' đăng nhập được, thấy màn hình Personal Dashboard (dù chưa có data)
- [ ] Bảng `org_memberships` ghi nhận đầy đủ lịch sử vào/ra của user
- [ ] Không break bất kỳ luồng hiện tại nào của org workspace

---

## 6. Phase 1 — Competency Passport

**Mục tiêu:** Khi rời org, hồ sơ năng lực được snapshot và lưu vào Personal Passport. User có thể xem và tải PDF lịch sử của mình.

**Phụ thuộc:** Phase 0 hoàn thành  
**Thời gian ước tính:** 3–4 tuần

### 6.1 Schema bảng snapshot

```sql
CREATE TABLE workforce_profile_snapshots (
    id                      BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid                    CHAR(36) NOT NULL UNIQUE COMMENT 'Public ID — dùng cho URL chia sẻ',
    user_id                 BIGINT NOT NULL,
    source_org_id           BIGINT NOT NULL,
    source_org_name         VARCHAR(200) NOT NULL  COMMENT 'Lưu tên org tại thời điểm snapshot, tránh thay đổi sau',
    source_org_logo_url     VARCHAR(500) NULL,
    snapshot_at             TIMESTAMP NOT NULL,
    tenure_months           SMALLINT NULL          COMMENT 'Số tháng làm việc tại org',
    job_title_at_exit       VARCHAR(200) NULL,
    department_at_exit      VARCHAR(200) NULL,

    -- Điểm TDWCF tại thời điểm snapshot
    tdwcf_score             DECIMAL(5,2) NULL,
    tdwcf_maturity_level    VARCHAR(64)  NULL,
    score_d1                DECIMAL(5,2) NULL,
    score_d2                DECIMAL(5,2) NULL,
    score_d3                DECIMAL(5,2) NULL,
    score_d4                DECIMAL(5,2) NULL,
    score_d5                DECIMAL(5,2) NULL,
    score_d6                DECIMAL(5,2) NULL,
    workforce_trust_score   DECIMAL(5,2) NULL,
    ai_readiness_score      DECIMAL(5,2) NULL,
    sandbox_hours_total     SMALLINT DEFAULT 0,
    sandbox_score_avg       DECIMAL(5,2) NULL,

    -- Dữ liệu phong phú dưới dạng JSON (không cần join)
    certifications_json     JSON NULL COMMENT '[{code, name, level, issued_at, expires_at}]',
    top_impacts_json        JSON NULL COMMENT 'Top 5 AI Impact entries nổi bật nhất',
    career_goal_at_exit     VARCHAR(200) NULL,

    -- Quyền riêng tư
    visibility              VARCHAR(20) NOT NULL DEFAULT 'private'
                            COMMENT 'private | link_only | public',
    share_token             VARCHAR(64) NULL UNIQUE COMMENT 'Token bảo vệ khi visibility=link_only',
    share_token_expires_at  TIMESTAMP NULL,

    -- Xác nhận từ phía org (tùy chọn, Phase 3+)
    org_verified            BOOLEAN NOT NULL DEFAULT FALSE,
    org_verified_at         TIMESTAMP NULL,
    org_verified_by         BIGINT NULL COMMENT 'user_id của HR đã verify',

    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_wps_user (user_id),
    INDEX idx_wps_org  (source_org_id),
    INDEX idx_wps_visibility (visibility)
);
```

### 6.2 Job chụp snapshot

```php
// App\Jobs\SnapshotWorkforceProfileJob
class SnapshotWorkforceProfileJob implements ShouldQueue
{
    public function handle(): void
    {
        $profile = WorkforceProfile::where('organization_id', $this->orgId)
            ->where('user_id', $this->userId)
            ->first();

        if (!$profile) return; // Nhân viên chưa có profile thì không snapshot

        $membership = OrgMembership::where('user_id', $this->userId)
            ->where('organization_id', $this->orgId)
            ->latest('left_at')->first();

        $topImpacts = AiImpactSnapshot::where('organization_id', $this->orgId)
            ->where('employee_id', $profile->employee_id)
            ->orderByDesc('impact_score')
            ->limit(5)
            ->get(['title', 'impact_category', 'result_description', 'period_start'])
            ->toArray();

        $certs = WorkforceCertification::where('organization_id', $this->orgId)
            ->where('workforce_profile_id', $profile->id)
            ->where('status', 'active')
            ->get(['cert_code', 'cert_name', 'cert_level', 'issued_at', 'expires_at'])
            ->toArray();

        WorkforceProfileSnapshot::create([
            'uuid'                  => Str::uuid(),
            'user_id'               => $this->userId,
            'source_org_id'         => $this->orgId,
            'source_org_name'       => $profile->organization->name,
            'source_org_logo_url'   => $profile->organization->logo_url,
            'snapshot_at'           => now(),
            'tenure_months'         => $membership
                ? (int) $membership->joined_at->diffInMonths($membership->left_at)
                : null,
            'job_title_at_exit'     => $membership?->job_title_at_exit,
            'tdwcf_score'           => $profile->tdwcf_score,
            'tdwcf_maturity_level'  => $profile->tdwcf_maturity_level,
            'score_d1'              => $profile->score_d1_digital_literacy,
            'score_d2'              => $profile->score_d2_data_literacy,
            'score_d3'              => $profile->score_d3_ai_literacy,
            'score_d4'              => $profile->score_d4_workflow,
            'score_d5'              => $profile->score_d5_innovation,
            'score_d6'              => $profile->score_d6_performance,
            'workforce_trust_score' => $profile->workforce_trust_score,
            'ai_readiness_score'    => $profile->ai_readiness_score,
            'sandbox_hours_total'   => $profile->sandbox_hours_total,
            'certifications_json'   => $certs,
            'top_impacts_json'      => $topImpacts,
            'career_goal_at_exit'   => $profile->career_goal,
            'visibility'            => 'private',
        ]);
    }
}
```

### 6.3 Personal Dashboard UI

Route: `/passport` (hoặc `/my-passport`) — truy cập được khi account_type = 'free' hoặc 'org_member'

```
┌──────────────────────────────────────────────────────────────┐
│  COMPETENCY PASSPORT                                         │
│  Nguyễn Văn A  ·  ✓ Email đã xác minh  ·  trust_level: 1   │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  LỊCH SỬ NĂNG LỰC                          [+ Org hiện tại] │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐    │
│  │  [Logo Org A]  CÔNG TY A                            │    │
│  │  Chuyên viên Sales · 18 tháng · 01/2024 – 06/2026  │    │
│  │                                                     │    │
│  │  TDWCF 72.5 ●  Professional  ●  AI Score 68.0      │    │
│  │  ████████░░  D1:75  D2:68  D3:71  D4:74  D5:66  D6:80  │    │
│  │                                                     │    │
│  │  🏆 3 Chứng nhận  ·  ⚡ 24h Sandbox  ·  📊 12 Impact │    │
│  │                                                     │    │
│  │  [Xem chi tiết]  [Tải PDF]  [Cài đặt chia sẻ]     │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐    │
│  │  [Logo Org B]  CÔNG TY B                            │    │
│  │  Nhân viên · 12 tháng · 01/2023 – 12/2023          │    │
│  │  TDWCF 54.0 ●  Practitioner  ...                   │    │
│  │  [Xem chi tiết]  [Tải PDF]                         │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                              │
│  ĐANG LÀM VIỆC TẠI: [Org C] — [Xem Hồ sơ hiện tại →]      │
└──────────────────────────────────────────────────────────────┘
```

### 6.4 Quyền truy cập Passport

- **Chính chủ:** Xem toàn bộ, tải PDF, cài đặt visibility từng snapshot
- **Org hiện tại (HR/Manager):** Xem snapshot của org mình (đã có sẵn qua workforce_profiles), không thấy snapshots org khác
- **Bên ngoài:** Chỉ thấy khi có link + visibility đúng (Phase 2)

### 6.5 Tự export PDF từ trang cá nhân

Nhân viên đang làm việc tại bất kỳ org nào có thể vào `/passport` và tải PDF hồ sơ **hiện tại** (snapshot nháp từ dữ liệu org đang thuộc về) — không cần xin Manager.

```
PDF cá nhân gồm:
  - Header: Tên, Trust Level badge, Ngày xuất
  - Hồ sơ tại [Org đang làm] nếu đang là org_member
  - Danh sách snapshot các org cũ (tóm tắt)
  - Footer: "Hồ sơ được xác nhận bởi hệ thống [platform name]"
```

### 6.6 Acceptance criteria Phase 1

- [ ] Khi HR deactivate nhân viên, snapshot tự động được tạo (async job)
- [ ] Job thất bại phải retry và log lỗi — không được mất data
- [ ] User 'free' đăng nhập → thấy danh sách snapshots theo thứ tự thời gian
- [ ] Tải PDF từng snapshot hoạt động không cần org context
- [ ] PDF cá nhân có thể xuất ngay từ `/passport` dù đang là org_member
- [ ] Snapshot không bị thay đổi dù org xóa/sửa hồ sơ gốc sau đó

---

## 7. Phase 2 — Portability & Sharing

**Mục tiêu:** Cho phép chia sẻ hồ sơ với nhà tuyển dụng qua link hoặc public page.

**Phụ thuộc:** Phase 1 hoàn thành  
**Thời gian ước tính:** 2 tuần

### 7.1 Visibility modes

| Mode | Ai xem được | URL |
|---|---|---|
| `private` | Chỉ chính chủ | Không có public URL |
| `link_only` | Ai có link | `/p/{share_token}` — token 32 ký tự |
| `public` | Tất cả, index được | `/p/{uuid}` |

User có thể đặt visibility riêng cho từng snapshot. Mặc định: `private`.

### 7.2 Share token management

```php
// Tạo link chia sẻ:
$snapshot->update([
    'visibility'            => 'link_only',
    'share_token'           => Str::random(32),
    'share_token_expires_at'=> now()->addYear(),   // hết hạn sau 1 năm
]);
// URL: /p/{share_token}

// Thu hồi link:
$snapshot->update([
    'visibility'            => 'private',
    'share_token'           => null,
    'share_token_expires_at'=> null,
]);
```

### 7.3 Public profile page `/p/{token_or_uuid}`

Trang không yêu cầu đăng nhập. Hiển thị:
- Tên người dùng (hoặc ẩn họ nếu user cài privacy)
- Trust Level badge
- TDWCF Score + Maturity Level tại org X
- Radar chart 6 năng lực
- Danh sách chứng nhận (không hiện ngày hết hạn nếu đã hết)
- Top 3 AI Impact highlights
- Badge "Xác nhận bởi [Org Name]" nếu `org_verified = true`
- Nút "Tải PDF"

Không hiển thị: Thông tin cá nhân nhạy cảm (số điện thoại, CCCD, email chính xác).

### 7.4 Acceptance criteria Phase 2

- [ ] User có thể toggle visibility cho từng snapshot (3 mức)
- [ ] Link `link_only` không cho phép crawl (noindex meta tag)
- [ ] Link hết hạn sau thời gian đặt, trả về 410 Gone thay vì 404
- [ ] Trang public load được không cần login, mobile-responsive
- [ ] Nút "Tải PDF" trên trang public hoạt động, không cần account

---

## 8. Phase 3 — eKYC & Verified Identity

**Mục tiêu:** Xây dựng hệ thống xác minh danh tính nhiều lớp, tăng độ tin cậy của hồ sơ.

**Phụ thuộc:** Phase 0 hoàn thành  
**Thời gian ước tính:** 3–4 tuần

### 8.1 Schema identity_verifications

```sql
CREATE TABLE identity_verifications (
    id              BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id         BIGINT NOT NULL,
    method          VARCHAR(50) NOT NULL
                    COMMENT 'email | phone_otp | cccd_ocr | cccd_chip | vne_id | passport',
    status          VARCHAR(20) NOT NULL DEFAULT 'pending'
                    COMMENT 'pending | verified | rejected | expired',
    verified_at     TIMESTAMP NULL,
    expires_at      TIMESTAMP NULL,
    metadata_json   JSON NULL          COMMENT 'Thông tin phi nhạy cảm: issuing_province, doc_type, etc.',
    rejection_reason VARCHAR(200) NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_iv_user_method (user_id, method)
);

-- Thêm vào bảng users:
ALTER TABLE users ADD COLUMN national_id_hash VARCHAR(64) NULL UNIQUE
    COMMENT 'SHA-256(số_CCCD) — để check uniqueness, không lưu số thật';
ALTER TABLE users ADD COLUMN phone_number VARCHAR(20) NULL;
ALTER TABLE users ADD COLUMN phone_verified_at TIMESTAMP NULL;
ALTER TABLE users ADD COLUMN kyc_status VARCHAR(20) NOT NULL DEFAULT 'none'
    COMMENT 'none | email_only | phone_verified | id_verified';
```

### 8.2 Roadmap xác minh từng bước

#### Step 1: Email (Phase 0 — đã có)
```
trust_level: 0 → 1
Mở khoá: Đăng nhập, xem Passport cá nhân
```

#### Step 2: Số điện thoại OTP
```
User nhập số điện thoại
→ Hệ thống gửi OTP (6 số, TTL 5 phút)
→ User nhập OTP đúng
→ phone_verified_at = now(), trust_level = 2, kyc_status = 'phone_verified'

Rate limit: 3 OTP/số/ngày
Mở khoá: Tham gia Open Assessment (Phase 4)
```

#### Step 3: CCCD (Căn cước Công dân)
```
Option A — OCR tự động (MVP):
  User upload ảnh 2 mặt CCCD
  → OCR trích xuất: họ tên, ngày sinh, số CCCD, ngày cấp, nơi cấp
  → Hệ thống hash số CCCD: national_id_hash = SHA256(cccd_number)
  → Kiểm tra uniqueness: hash này đã tồn tại trên hệ thống chưa?
    - Nếu có → từ chối (mỗi CCCD chỉ liên kết 1 tài khoản)
    - Nếu không → verified
  → trust_level = 3, kyc_status = 'id_verified'
  → identity_verifications.insert(method='cccd_ocr', status='verified')

Option B — VNeID API (nâng cao, Phase 5):
  Tích hợp API định danh quốc gia
  → Xác minh real-time, không cần upload ảnh
  → Độ tin cậy cao nhất
```

### 8.3 Trust Level badges hiển thị trên hồ sơ

```
trust_level 1:  ✉ Email đã xác minh
trust_level 2:  📱 Điện thoại đã xác minh
trust_level 3:  🪪 Danh tính đã xác minh (CCCD)
trust_level 4:  ⭐ Xác minh sinh trắc học (Phase 5)
```

Badges xuất hiện trên:
- Public profile page
- PDF xuất ra
- Trong profile khi nhà tuyển dụng xem (Phase 4)

### 8.4 Nguyên tắc bảo vệ dữ liệu nhạy cảm

```
KHÔNG lưu:  Số CCCD, ảnh CCCD, số điện thoại đầy đủ trong profile
LƯU:        SHA-256 hash của số CCCD (để check uniqueness)
            Số điện thoại đã mask: 09*****789
            Tỉnh/thành cấp CCCD (phi nhạy cảm, dùng để phân tích địa lý)

HIỂN THỊ:   Trust level badge, không hiển thị thông tin định danh cụ thể
XÓA:        Khi user yêu cầu xóa tài khoản, xóa toàn bộ PII trong 30 ngày
```

### 8.5 Acceptance criteria Phase 3

- [ ] Luồng xác minh phone OTP hoạt động, có rate limit
- [ ] Upload CCCD → OCR → hash → check uniqueness hoạt động
- [ ] Một số CCCD không thể liên kết với 2 tài khoản khác nhau
- [ ] Trust badges hiển thị đúng trên profile và PDF
- [ ] Không lưu số CCCD plain text, chỉ lưu hash
- [ ] User có thể xem lịch sử xác minh của mình (identity_verifications)

---

## 9. Phase 4 — Open Assessment Marketplace

**Mục tiêu:** Tổ chức đăng chiến dịch đánh giá mở, cá nhân tự do tham gia, kết quả vào Passport.

**Phụ thuộc:** Phase 1 + Phase 3 (trust_level ≥ 2 để tham gia)  
**Thời gian ước tính:** 6–8 tuần

### 9.1 Schema

```sql
CREATE TABLE open_assessment_campaigns (
    id                      BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid                    CHAR(36) NOT NULL UNIQUE,
    organization_id         BIGINT NOT NULL,
    title                   VARCHAR(200) NOT NULL,
    description             TEXT NULL,
    target_job_title        VARCHAR(200) NULL,
    target_department       VARCHAR(200) NULL,

    -- Yêu cầu đầu vào (để lọc ứng viên phù hợp)
    min_trust_level         TINYINT NOT NULL DEFAULT 2,
    required_domains_json   JSON NULL   COMMENT '[{"domain":"D3","min_score":40}]',
    required_tdwcf_min      DECIMAL(5,2) NULL,

    -- Nội dung đánh giá
    sandbox_env_ids_json    JSON NULL   COMMENT 'Danh sách sandbox_environment_id',
    custom_questions_json   JSON NULL   COMMENT 'Câu hỏi tự luận tùy chỉnh của org',

    -- Cấu hình campaign
    status                  VARCHAR(20) NOT NULL DEFAULT 'draft'
                            COMMENT 'draft | open | closed | archived',
    open_from               TIMESTAMP NULL,
    open_until              TIMESTAMP NULL,
    max_participants        SMALLINT NULL,
    is_anonymous_to_org     BOOLEAN DEFAULT TRUE
                            COMMENT 'Org không thấy tên ứng viên cho đến khi invite',

    -- Kết quả
    participants_count      SMALLINT DEFAULT 0,
    avg_tdwcf_result        DECIMAL(5,2) NULL,

    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_oac_org (organization_id),
    INDEX idx_oac_status (status)
);

CREATE TABLE campaign_participations (
    id                      BIGINT PRIMARY KEY AUTO_INCREMENT,
    campaign_id             BIGINT NOT NULL,
    user_id                 BIGINT NOT NULL,
    joined_at               TIMESTAMP NOT NULL,
    completed_at            TIMESTAMP NULL,
    status                  VARCHAR(20) NOT NULL DEFAULT 'in_progress'
                            COMMENT 'in_progress | completed | abandoned | invited | hired',

    -- Kết quả riêng của campaign này (không ghi vào workforce_profiles của org)
    result_tdwcf_score      DECIMAL(5,2) NULL,
    result_domains_json     JSON NULL,
    result_sandbox_json     JSON NULL,

    -- Từ phía org
    org_rating              TINYINT NULL   COMMENT '1-5 sao',
    org_note                VARCHAR(500) NULL,
    org_action              VARCHAR(50) NULL   COMMENT 'invited | rejected | hired | shortlisted',
    org_action_at           TIMESTAMP NULL,

    UNIQUE KEY uq_cp (campaign_id, user_id),
    INDEX idx_cp_user (user_id)
);
```

### 9.2 Luồng ứng viên tham gia campaign

```
[Ứng viên]
  Vào /campaigns → duyệt danh sách campaign đang mở
  Xem chi tiết campaign: yêu cầu, nội dung, hạn nộp
        ↓
  Nhấn "Tham gia" (yêu cầu trust_level ≥ 2)
  campaign_participations.insert(status='in_progress')
        ↓
  Làm các Sandbox được chỉ định (trong môi trường sandbox của campaign, không phải của org)
  Trả lời câu hỏi tự luận nếu có
        ↓
  Nộp bài → status = 'completed'
  Kết quả được lưu vào:
    ① campaign_participations.result_*  (org đọc)
    ② workforce_profile_snapshots (type='campaign') (cá nhân đọc, vào Passport)
        ↓
[Org HR nhìn thấy]
  Bảng ranking ứng viên (ẩn danh mặc định: "Ứng viên #1", "#2",...)
  Xem điểm, kết quả sandbox
  Nhấn "Mời phỏng vấn" → org_action = 'invited'
  → Hệ thống reveal danh tính ứng viên đó cho org
  → Gửi email mời ứng viên
```

### 9.3 Màn hình campaigns cho ứng viên tự do

```
/campaigns — Khám phá chiến dịch đánh giá

  🔍 [Tìm kiếm theo ngành / vị trí / yêu cầu]

  ┌──────────────────────────────────────────────┐
  │  [Logo Org X]  CÔNG TY X                    │
  │  Tuyển: AI Sales Lead                       │
  │  Yêu cầu: TDWCF ≥ 50 · D3 ≥ 40 · Trust ≥ 2│
  │  Nội dung: Sandbox AI Sales + 3 câu hỏi    │
  │  Hạn: 30/06/2026 · 12 người đã tham gia    │
  │                     [Xem chi tiết] [Tham gia]│
  └──────────────────────────────────────────────┘

  ┌──────────────────────────────────────────────┐
  │  [Logo Org Y]  CÔNG TY Y                    │
  │  Tuyển: Digital Transformation Manager     │
  │  Yêu cầu: TDWCF ≥ 70 · Trust ≥ 3          │
  │  ...                                        │
  └──────────────────────────────────────────────┘
```

### 9.4 Acceptance criteria Phase 4

- [ ] Org tạo được campaign, cấu hình sandbox và câu hỏi
- [ ] Chỉ user có trust_level ≥ yêu cầu campaign mới tham gia được
- [ ] Sandbox trong campaign chạy độc lập, không ảnh hưởng workspace org
- [ ] Kết quả campaign xuất hiện trong Passport cá nhân (dạng snapshot type='campaign')
- [ ] Org xem ranking ẩn danh, chỉ reveal danh tính khi org chủ động invite
- [ ] Ứng viên nhận email thông báo khi được mời phỏng vấn

---

## 10. Phase 5 — National Platform & API

**Mục tiêu:** Mở rộng thành nền tảng chứng nhận năng lực số quốc gia, tích hợp third-party.

**Phụ thuộc:** Phase 1–4 hoàn thành  
**Thời gian ước tính:** 3–6 tháng

### 10.1 National Digital Competency Index

Tổng hợp dữ liệu ẩn danh từ toàn bộ người dùng để tạo **chỉ số năng lực số quốc gia**:

```
Ví dụ báo cáo công bố định kỳ:
  "Chỉ số Năng lực Số Việt Nam — Q2/2026"
  - Điểm TDWCF trung bình toàn quốc: 52.3
  - D3 AI Literacy: 38.1 (thấp nhất, cần đầu tư)
  - Tỉ lệ đạt Practitioner+: 31%
  - Top 5 ngành có TDWCF cao nhất: IT, Finance, ...
  - Top 5 tỉnh/thành: ...
```

Dữ liệu hoàn toàn ẩn danh, chỉ dùng cho nghiên cứu và chính sách.

### 10.2 Public API

Mở API cho tổ chức tích hợp vào ATS (Applicant Tracking System) hoặc HRMS hiện có:

```
GET  /api/v1/passport/{uuid}/summary
     → Trả về tóm tắt hồ sơ (trust_level, tdwcf, maturity, certs count)
     → Yêu cầu: user đã set visibility='public' + API key của org

POST /api/v1/campaigns/{id}/verify-candidate
     → Org kiểm tra ứng viên có đủ điều kiện tham gia campaign không

GET  /api/v1/national/competency-index
     → Public endpoint, dữ liệu ẩn danh tổng hợp
```

### 10.3 VNeID Integration (eKYC cấp cao nhất)

Tích hợp với Cổng định danh quốc gia VNeID:
- Xác minh CCCD chip thông qua app VNeID
- trust_level = 4
- Badge: "Đã xác minh VNeID — Bộ Công an Việt Nam"
- Giá trị pháp lý cao nhất, phù hợp cho tuyển dụng vị trí nhạy cảm

### 10.4 Employer Dashboard

Trang dành riêng cho nhà tuyển dụng (không cần setup org đầy đủ):
```
/hire — Tìm kiếm ứng viên theo competency
  Lọc: TDWCF ≥ X · Domain D3 ≥ Y · Cert level ≥ Z · Trust level ≥ 2
  Kết quả: Danh sách ứng viên anonymous với competency summary
  Trả phí để "mở khoá" danh tính và contact ứng viên
```

---

## 11. Schema dữ liệu chi tiết

### 11.1 Tóm tắt các bảng mới qua từng phase

| Bảng | Phase | Mô tả |
|---|---|---|
| `org_memberships` | 0 | Lịch sử vào/ra của user tại các org |
| `identity_verifications` | 0/3 | Log các bước xác minh danh tính |
| `workforce_profile_snapshots` | 1 | Passport — snapshot hồ sơ khi rời org |
| `open_assessment_campaigns` | 4 | Chiến dịch đánh giá mở từ các org |
| `campaign_participations` | 4 | Ứng viên tham gia campaign |

### 11.2 Thay đổi bảng hiện có

| Bảng | Cột thêm vào | Phase |
|---|---|---|
| `users` | `account_type`, `current_org_id`, `email_verified_at`, `trust_level` | 0 |
| `users` | `phone_number`, `phone_verified_at`, `national_id_hash`, `kyc_status` | 3 |

### 11.3 Không thay đổi

Toàn bộ namespace org hiện tại (`workforce_profiles`, `workforce_certifications`, `sandbox_sessions`, `ai_impact_snapshots`, v.v.) **không thay đổi schema**. Phase 0–5 chỉ thêm lớp mới bên trên, không sửa lớp org đang chạy.

---

## 12. Nguyên tắc thiết kế & Ràng buộc

### 12.1 Non-breaking by design

> Mỗi phase phải deploy được mà không break bất kỳ luồng org hiện tại nào.

- Tất cả thay đổi là **additive** (thêm cột, thêm bảng, thêm route) không phải destructive
- Feature flag từng phase qua `organization_feature_overrides` nếu cần rollout dần

### 12.2 Data ownership rõ ràng

| Dữ liệu | Chủ sở hữu | Có thể xóa bởi |
|---|---|---|
| `workforce_profiles` | Org | Org admin |
| `workforce_profile_snapshots` | Cá nhân | Cá nhân (hoặc GDPR request) |
| `org_memberships` | Hệ thống | Không xóa, chỉ archive |
| `identity_verifications` | Hệ thống | Ẩn danh hóa sau X năm |
| `campaign_participations` | Hệ thống | Cá nhân có thể yêu cầu xóa sau 1 năm |

### 12.3 Privacy by default

- Snapshot mặc định `private` — user phải chủ động bật chia sẻ
- Public profile không bao giờ hiển thị email, số điện thoại, số CCCD
- Trust badge chỉ hiển thị mức độ, không hiển thị thông tin xác minh
- Org chỉ thấy tên ứng viên trong campaign khi đã chủ động "mời"

### 12.4 Scalability

- `workforce_profile_snapshots` được thiết kế **append-only** (chỉ insert, không update sau khi tạo) để đảm bảo tính bất biến của lịch sử
- JSON columns cho certifications/impacts tránh over-normalization ở giai đoạn sớm — có thể refactor sang bảng riêng ở Phase 5 nếu cần query phức tạp
- UUID trên snapshots từ đầu để public URL không đoán được
- Tách `campaign_participations` sandbox results ra khỏi `sandbox_sessions` chính để không làm phức tạp tenant logic

### 12.5 Idempotency của snapshot job

```php
// Job phải idempotent — chạy nhiều lần không tạo ra nhiều snapshots
WorkforceProfileSnapshot::firstOrCreate(
    ['user_id' => $userId, 'source_org_id' => $orgId, 'snapshot_at' => $snapshotDate],
    [...data]
);
```

---

## 13. Lộ trình tổng quan

```
2026 Q3         2026 Q4         2027 Q1         2027 Q2         2027 Q3+
  │               │               │               │               │
  ▼               ▼               ▼               ▼               ▼
Phase 0         Phase 1         Phase 2         Phase 3         Phase 4
Identity        Passport        Portability     eKYC            Marketplace
Foundation      Snapshot        & Sharing       Verified        Open Assessment
                                                Identity
  2–3 tuần       3–4 tuần        2 tuần          3–4 tuần        6–8 tuần
```

### Milestone quyết định

| Milestone | Điều kiện tiến lên phase tiếp |
|---|---|
| Phase 0 → 1 | org_memberships hoạt động, snapshot job không mất data qua 100 test cases |
| Phase 1 → 2 | 50+ nhân viên thực tế đã có snapshot, PDF export ổn định |
| Phase 2 → 3 | Public link hoạt động, rate limit vững, không có data leak |
| Phase 3 → 4 | eKYC phone đạt 80% success rate, zero CCCD uniqueness violation |
| Phase 4 → 5 | ≥ 3 tổ chức đã đăng campaign thực tế, ≥ 20 ứng viên hoàn thành |

---

## Phụ lục: Câu hỏi mở cần quyết định

> Các quyết định dưới đây cần alignment từ product/business trước khi implement.

1. **Khi org xóa hồ sơ nhân viên (soft delete):** Snapshot đã chụp có bị ảnh hưởng không? → Đề xuất: Không, snapshot hoàn toàn độc lập.

2. **Nhân viên có thể chỉnh sửa snapshot không?** → Đề xuất: Không cho phép — tính bất biến là giá trị cốt lõi của Passport. Nếu muốn thêm context, cho phép thêm "ghi chú cá nhân" đính kèm snapshot.

3. **Org có thể "thu hồi" xác nhận snapshot không?** → Đề xuất: Có (org_verified = false), nhưng không xóa snapshot. Badge "Đã xác nhận bởi Org X" sẽ biến mất nhưng dữ liệu vẫn còn với badge "Chưa xác nhận".

4. **Khi user bị deactivate vì lý do kỷ luật:** Snapshot có nên bị ẩn không? → Cần policy riêng cho trường hợp này.

5. **Mô hình kinh doanh Phase 4:** Tính phí org khi "mở khoá" danh tính ứng viên? Hay freemium? → Cần business decision.

6. **Tên miền riêng cho Passport:** `passport.platform.vn` hay dưới `/passport`? → Ảnh hưởng đến SEO và brand của Phase 5.

---

*Tài liệu này là đặc tả sống (living specification) — cập nhật theo từng phase khi có quyết định mới.*

*Workforce Digital Twin — Competency Passport Platform — v1.0 — 13/06/2026*
