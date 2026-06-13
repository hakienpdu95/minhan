# Hướng Dẫn Sử Dụng Hệ Thống Hồ Sơ Năng Lực Số
## Workforce Digital Twin — Tài liệu Đào tạo Nội bộ

**Phiên bản:** 2.0 · **Ngày cập nhật:** 13/06/2026  
**Áp dụng cho:** Toàn bộ nhân viên tổ chức  
**Tác giả:** Phòng HR & Phòng IT

---

## Mục tiêu tài liệu

Sau khi đọc tài liệu này, người dùng có thể:

- **Nhân viên:** Tự tạo và quản lý hồ sơ năng lực số, hiểu điểm TDWCF của mình, thực hành Sandbox và ghi nhận AI Impact
- **Quản lý:** Đọc hiểu báo cáo Workforce, nhận diện nhân viên cần đào tạo, sử dụng AI Gợi ý để lên kế hoạch IDP
- **CEO/BGĐ:** Đọc dashboard toàn tổ chức, ra quyết định chiến lược đào tạo dựa trên dữ liệu thực
- **IT Admin:** Triển khai, cấu hình và vận hành hệ thống đúng quy trình

---

## Mục lục

1. [Tổng quan hệ thống](#1-tổng-quan-hệ-thống)
2. [Phân quyền và vai trò](#2-phân-quyền-và-vai-trò)
3. [Khởi tạo hệ thống — Dành cho Admin](#3-khởi-tạo-hệ-thống--dành-cho-admin)
4. [Hướng dẫn dành cho CEO / Ban Giám đốc](#4-hướng-dẫn-dành-cho-ceo--ban-giám-đốc)
5. [Hướng dẫn dành cho Quản lý / Trưởng phòng](#5-hướng-dẫn-dành-cho-quản-lý--trưởng-phòng)
6. [Hướng dẫn dành cho Nhân viên](#6-hướng-dẫn-dành-cho-nhân-viên)
7. [Khảo sát TDWCF — Hướng dẫn chi tiết](#7-khảo-sát-tdwcf--hướng-dẫn-chi-tiết)
8. [Khung đánh giá TDWCF — 6 Năng lực số](#8-khung-đánh-giá-tdwcf--6-năng-lực-số)
9. [Cấp độ trưởng thành số](#9-cấp-độ-trưởng-thành-số)
10. [Lộ trình phát triển nghề nghiệp](#10-lộ-trình-phát-triển-nghề-nghiệp)
11. [Hệ thống chứng nhận AI](#11-hệ-thống-chứng-nhận-ai)
12. [AI Sandbox — Thực hành thực tế](#12-ai-sandbox--thực-hành-thực-tế)
13. [AI Impact Tracker](#13-ai-impact-tracker)
14. [Kế hoạch Phát triển Cá nhân (IDP)](#14-kế-hoạch-phát-triển-cá-nhân-idp)
15. [Xuất báo cáo Excel và PDF](#15-xuất-báo-cáo-excel-và-pdf)
16. [Quy trình vận hành định kỳ](#16-quy-trình-vận-hành-định-kỳ)
17. [Kế hoạch 30-60-90 ngày cho nhân viên mới](#17-kế-hoạch-30-60-90-ngày-cho-nhân-viên-mới)
18. [Câu hỏi thường gặp (FAQ)](#18-câu-hỏi-thường-gặp-faq)
19. [Phụ lục A: Bảng chỉ số tham chiếu nhanh](#19-phụ-lục-a-bảng-chỉ-số-tham-chiếu-nhanh)
20. [Phụ lục B: Bảng thuật ngữ (Glossary)](#20-phụ-lục-b-bảng-thuật-ngữ-glossary)

---

## 1. Tổng quan hệ thống

### Hệ thống Workforce Digital Twin là gì?

Workforce Digital Twin là nền tảng quản lý và phát triển **năng lực số (Digital Competency)** của toàn tổ chức. Hệ thống tạo ra một "hồ sơ kỹ thuật số" cho mỗi nhân viên — phản ánh trung thực mức độ thành thạo công nghệ AI, dữ liệu và chuyển đổi số của họ.

Tên "Digital Twin" (Bản sao số) xuất phát từ ngành công nghiệp sản xuất: cũng như một nhà máy có bản sao số để theo dõi và tối ưu máy móc — tổ chức của chúng ta có bản sao số để theo dõi và phát triển con người.

### Tại sao cần dùng hệ thống này?

| Vấn đề trước đây | Giải pháp với Digital Twin |
|---|---|
| Không biết nhân viên đang ở đâu về năng lực số | Dashboard cá nhân + điểm TDWCF cập nhật thời gian thực |
| Không có cơ sở để lên kế hoạch đào tạo | AI tự động phân tích khoảng cách kỹ năng và đề xuất kế hoạch |
| Khó đo lường ROI của chương trình đào tạo | Leaderboard + AI Impact Tracker đo lường hiệu quả thực tế |
| Thiếu bằng chứng năng lực khi đề bạt | Portfolio + chứng nhận AI được xác thực bởi hệ thống |
| Đào tạo đại trà, không cá nhân hóa | Gợi ý AI phân tích từng hồ sơ, từng chức danh |
| Manager không biết nhân viên đang học gì | Báo cáo tiến độ thời gian thực, xuất PDF cho review 1-1 |

### Luồng tổng quan

```
Nhân viên hoàn thành khảo sát TDWCF (30-45 phút, 1 lần/quý)
        ↓
Hệ thống tạo Hồ sơ Digital Twin (điểm D1–D6, cấp độ, trust score)
        ↓
AI phân tích khoảng cách so với yêu cầu vị trí việc làm
        ↓
AI đề xuất 5 hành động ưu tiên (gợi ý phát triển cá nhân)
        ↓
Nhân viên thực hành Sandbox + đăng ký Chứng nhận AI
        ↓
Ghi nhận tác động AI vào công việc thực tế (AI Impact)
        ↓
Quản lý xem báo cáo Workforce + xuất Excel/PDF → review 1-1
        ↓
CEO ra quyết định chiến lược về đào tạo và phân công
```

### Mối liên hệ giữa các tính năng

```
TDWCF Survey ──→ [TDWCF Score] ──→ Cấp độ trưởng thành
                       ↓
                  Skill Gap Analysis ←── Job Title Requirements
                       ↓
              AI Gợi ý phát triển ──→ Career Pathway
                       ↓
              Sandbox ──→ [Sandbox Score] ──→ Trust Score
              Cert    ──→ [Cert Count]    ──→ Trust Score
              KPI     ──→ [KPI %]         ──→ Trust Score
              Impact  ──→ [Impact Score]  ──→ Trust Score
```

---

## 2. Phân quyền và vai trò

Hệ thống phân quyền theo 5 nhóm chức năng chính:

| Vai trò | Tên hiển thị | Quyền truy cập |
|---|---|---|
| `member` | Nhân viên | Xem hồ sơ cá nhân, AI Sandbox, Chứng nhận, Lộ trình, AI Impact của mình |
| `ops` | Chuyên viên / Giám sát | + Xem hồ sơ tất cả nhân viên, xuất báo cáo Excel/PDF |
| `manager` | Quản lý / Trưởng phòng | + Xem trang Workforce Admin đầy đủ |
| `ceo` | CEO / Ban Giám đốc | + Báo cáo tổng hợp toàn tổ chức |
| `system_admin` | Quản trị hệ thống | + Cấu hình Sandbox, Pathway, Chứng nhận, cấp chứng nhận |

### URL truy cập các tính năng

| Tính năng | URL | Ai truy cập được |
|---|---|---|
| Hồ sơ cá nhân (Digital Twin) | `/dashboard/workforce/me` | Tất cả |
| AI Sandbox (cá nhân) | `/dashboard/sandbox` | Tất cả |
| Chứng nhận AI | `/dashboard/certifications` | Tất cả |
| Lộ trình nghề nghiệp | `/dashboard/career-pathway` | Tất cả |
| AI Impact Tracker | `/dashboard/ai-impact` | Tất cả |
| Workforce Admin (tổng hợp) | `/dashboard/workforce` | ops, manager, ceo |
| Sandbox Admin | `/dashboard/sandbox-admin` | system_admin |
| Pathway Admin | `/dashboard/career-pathway-admin` | system_admin |
| Certs Admin | `/dashboard/certs-admin` | system_admin |

### Nguyên tắc quyền riêng tư dữ liệu

- **Nhân viên** chỉ thấy dữ liệu của bản thân — không thấy điểm của đồng nghiệp
- **Manager/Ops** thấy toàn bộ hồ sơ nhân viên trong tổ chức
- Khi xuất file PDF/Excel, tài liệu có ghi tên người xuất + timestamp để kiểm soát
- Hồ sơ Digital Twin không được chia sẻ ra ngoài tổ chức mà không có sự đồng ý của nhân viên

---

## 3. Khởi tạo hệ thống — Dành cho Admin

> **Dành cho:** System Admin, IT Admin  
> **Thực hiện một lần** khi triển khai hệ thống lần đầu

### Bước 1: Đảm bảo dữ liệu nền tảng đã được seed

Hệ thống cần các dữ liệu nền sau đây (đã được seed sẵn khi deploy):

```bash
# Chạy lệnh này trên server để seed toàn bộ dữ liệu cần thiết
php artisan db:seed --class="Modules\Assessment\Database\Seeders\AssessmentDatabaseSeeder"
```

Lệnh này tạo:
- **6 môi trường Sandbox** (AI Office, AI Data, AI Sales, AI HR, AI Workflow, AI Leadership)
- **28 chứng nhận AI** theo 4 cấp độ × 7 lĩnh vực
- **5 bước lộ trình** Career Pathway
- **114 yêu cầu năng lực** theo chức danh (19 job titles × 6 domains)
- **12 hồ sơ demo** nhân viên mẫu để kiểm thử

### Bước 2: Kích hoạt module cho tổ chức

Module Năng lực số (`module.assessment`) cần được bật theo từng tổ chức:

1. Đăng nhập với tài khoản `system_admin`
2. Vào **Cài đặt → Tổ chức → Tính năng**
3. Bật toggle **"Năng lực số — Workforce Digital Twin"**

Hoặc qua database:
```sql
INSERT INTO organization_feature_overrides 
  (organization_id, feature_slug, value, override_reason, created_at, updated_at)
VALUES 
  (1, 'module.assessment', '1', 'Kích hoạt Workforce module', NOW(), NOW());
```

### Bước 3: Gán chức danh cho nhân viên

Hệ thống **tự động phân tích khoảng cách kỹ năng** dựa trên chức danh (Job Title) của nhân viên. Cần đảm bảo:

1. Vào **HR → Nhân viên → [Chọn nhân viên] → Chỉnh sửa**
2. Chọn đúng **Chức danh (Job Title)** trong dropdown
3. Lưu lại

> **Lưu ý:** Nếu nhân viên chưa có chức danh, hệ thống vẫn tạo hồ sơ nhưng phần "Skill Gap theo vị trí" sẽ hiển thị "N/A". Cần gán chức danh trước khi nhân viên làm khảo sát TDWCF.

### Bước 4: Phân quyền người dùng

Khi nhân viên mới vào hệ thống, cần gán role phù hợp:

1. Vào **HR → Nhân viên → [Chọn nhân viên] → Phân quyền**
2. Gán role theo cấp bậc:
   - **Nhân viên thông thường:** `member`
   - **Chuyên viên / Giám sát:** `ops`
   - **Trưởng nhóm / Trưởng phòng:** `manager`
   - **Giám đốc / CEO:** `ceo`

### Bước 5: Cấu hình Sandbox Admin (tùy chọn)

Mỗi môi trường Sandbox có nhiệm vụ thực hành. Để thêm/sửa nhiệm vụ:

1. Đăng nhập `system_admin` → **Năng lực số → Sandbox Admin**
2. Chọn môi trường (VD: "AI Office — Foundation")
3. Nhấn **"Xem nhiệm vụ"** → **"Thêm nhiệm vụ"**
4. Điền: Tiêu đề nhiệm vụ, Mô tả, Thời gian dự kiến (phút), Điểm tối đa

### Bước 6: Kiểm tra hệ thống hoạt động

Sau khi setup, thực hiện kiểm tra với tài khoản thử nghiệm:

```
[ ] Đăng nhập với role member → thấy mục "Năng lực số" trong sidebar
[ ] Vào /dashboard/workforce/me → thấy trang hồ sơ (dù chưa có dữ liệu)
[ ] Đăng nhập với role manager → thấy Workforce Admin
[ ] Xuất thử 1 file Excel → file tải về thành công
[ ] Kiểm tra seed data: 6 Sandbox environments, 28 certifications
```

### Bước 7: Hướng dẫn nhân viên bắt đầu

Sau khi hệ thống sẵn sàng, gửi thông báo cho toàn bộ nhân viên:

1. Đường dẫn đăng nhập hệ thống
2. Yêu cầu hoàn thành khảo sát TDWCF trong **vòng 7 ngày**
3. Tài liệu này (hoặc bản tóm tắt cho nhân viên)

---

## 4. Hướng dẫn dành cho CEO / Ban Giám đốc

> **Dành cho:** CEO, Giám đốc, Phó Giám đốc (role: `ceo`)

**Mục tiêu sau khi đọc phần này:** Biết cách đọc dashboard tổng quan, nhận diện điểm mạnh/yếu toàn tổ chức và xuất báo cáo cho cuộc họp.

### 4.1 Xem tổng quan năng lực số toàn tổ chức

1. Đăng nhập → Sidebar **"Năng lực số"** → nhấn **"Workforce Admin"**
2. Trang hiển thị:
   - **6 chỉ số KPI** ở trên cùng: Tổng hồ sơ + số lượng từng cấp độ
   - **Biểu đồ phân bổ cấp độ trưởng thành** (Khởi đầu → Dẫn dắt)
   - **Điểm trung bình 6 năng lực** toàn tổ chức (D1–D6)
   - **Bảng danh sách toàn bộ nhân viên** có điểm TDWCF + cấp độ

### 4.2 Đọc hiểu Dashboard chính

```
┌─────────────────────────────────────────────────────────┐
│  Tổng hồ sơ │ Khởi đầu │ Nhận thức │ Thực hành │ ...   │
│      12     │    1      │     2      │    3       │ ...  │
├─────────────────────────────────────────────────────────┤
│  Phân bổ cấp độ          │  Điểm TB 6 năng lực          │
│  ████░░ 3 Thực hành 25%  │  D1 Số cơ bản    ████  64.0  │
│  ████░░ 3 Chuyên nghiệp  │  D2 Dữ liệu      ███░  58.2  │
│  ...                     │  D3 AI           ██░░  47.5  │
└─────────────────────────────────────────────────────────┘
```

**Cách đọc chỉ số:**
- **TDWCF Score 0–100:** Điểm năng lực số tổng hợp. Dưới 40 = cần đào tạo gấp
- **Cấp độ Khởi đầu/Nhận thức:** Nhân viên chưa thành thạo công cụ AI cơ bản
- **D3 AI thấp hơn D1, D2:** Tổ chức mạnh về tin học văn phòng nhưng yếu về AI — đây là cơ hội đầu tư
- **Phân bổ lý tưởng:** > 50% nhân viên ở cấp Thực hành trở lên sau 12 tháng triển khai

**Các câu hỏi nên hỏi từ dashboard:**
1. "Bao nhiêu % nhân viên ở cấp Khởi đầu?" → Nếu > 30%, cần chương trình đào tạo đại trà
2. "Domain nào có điểm thấp nhất?" → Đó là ưu tiên đầu tư đào tạo
3. "Ai đang ở cấp Dẫn dắt?" → Đây là nhân tố nòng cốt có thể mentor người khác

### 4.3 Lọc và tìm nhân viên cần chú ý

1. Dùng **bộ lọc "Cấp độ"** → Chọn "Khởi đầu" để xem nhân viên cần đào tạo ngay
2. Dùng **ô tìm kiếm** để tìm theo tên
3. Nhấn vào **tên nhân viên** để xem chi tiết hồ sơ

### 4.4 Xem hồ sơ chi tiết một nhân viên

Từ bảng danh sách → nhấn tên nhân viên → trang hiển thị:

- **Radar chart 6 năng lực** → thấy điểm mạnh/yếu theo từng domain
- **Bảng Skill Gap** → so sánh điểm hiện tại vs yêu cầu của chức danh
- **AI Gợi ý phát triển** → kế hoạch cụ thể do AI đề xuất
- **Lịch sử điểm TDWCF** → xu hướng tiến bộ theo thời gian
- **Chứng nhận AI đã đạt** → bằng chứng năng lực xác thực

### 4.5 Xuất báo cáo cho cuộc họp

**Báo cáo toàn tổ chức:**
1. Vào **Workforce Admin** → nút **"Xuất Excel"** (góc trên phải)
   - File Excel 4 sheets: Tổng quan, Danh sách nhân viên, Phân tích Skill Gap, Leaderboard
2. Hoặc nút **"Xuất PDF"** → file A4 2 trang, phù hợp in để họp

**Báo cáo cá nhân một nhân viên:**
1. Vào trang chi tiết nhân viên → nút **"Xuất Excel"** hoặc **"Xuất PDF"**
   - Excel: Hồ sơ năng lực + Gợi ý phát triển
   - PDF: Hồ sơ đầy đủ, in đẹp kèm biểu đồ radar

### 4.6 Xem Leaderboard (Bảng xếp hạng)

Leaderboard trong file Excel xuất ra sắp xếp nhân viên theo **Workforce Trust Score** — chỉ số tổng hợp gồm:

| Thành phần | Tỉ trọng | Cách nâng cao |
|---|---|---|
| TDWCF Score (điểm 6 năng lực) | 30% | Làm lại khảo sát sau khi đào tạo |
| Chứng nhận AI đã đạt | 25% | Admin cấp chứng nhận cho đủ điều kiện |
| KPI Achievement | 20% | Cập nhật KPI định kỳ |
| Điểm Sandbox | 15% | Thực hành Sandbox thường xuyên |
| Portfolio | 10% | Ghi nhận AI Impact, case study |

### 4.7 Sử dụng dữ liệu để ra quyết định

**Quyết định đào tạo:** Nếu > 40% nhân viên có D3 AI < 40 → ưu tiên chương trình "AI Fundamentals" cho toàn tổ chức trước khi cá nhân hóa.

**Quyết định thăng chức:** Dùng Trust Score + Skill Gap làm một trong các tiêu chí khách quan bên cạnh KPI và đánh giá năng lực truyền thống.

**Quyết định phân công dự án AI:** Chọn nhân viên có D3 ≥ 60 và Sandbox Hours ≥ 10h cho các dự án ứng dụng AI.

---

## 5. Hướng dẫn dành cho Quản lý / Trưởng phòng

> **Dành cho:** Trưởng nhóm, Trưởng phòng, Phó Giám đốc (role: `manager`, `ops`)

**Mục tiêu sau khi đọc phần này:** Biết cách xem hồ sơ nhân viên, đọc Skill Gap, sử dụng AI Gợi ý để lên kế hoạch IDP và xuất PDF cho review 1-1.

### 5.1 Xem hồ sơ nhân viên trong nhóm

1. Sidebar → **"Workforce Admin"**
2. Tìm tên nhân viên trong bảng hoặc lọc theo cấp độ
3. Nhấn tên để xem chi tiết

**Gợi ý:** Lọc ngay theo "Khởi đầu" để xác định ai cần ưu tiên hỗ trợ nhất.

### 5.2 Đọc biểu đồ Skill Gap

Trang chi tiết nhân viên có phần **"Skill Gap theo Vị trí"**:

```
                      Hiện tại  Yêu cầu  Gap
D1 — Năng lực số cơ bản  ████░░  45.2    60.0   -14.8  ← Cần nâng cao
D2 — Năng lực dữ liệu    ███░░░  38.5    50.0   -11.5  ← Cần nâng cao
D3 — Năng lực AI         ██░░░░  28.0    45.0   -17.0  ← Ưu tiên cao (critical)
D4 — Quy trình & TĐH     ████░░  52.3    45.0   +7.3   ✓ Đạt
D5 — Đổi mới & Sáng kiến ███░░░  41.0    40.0   +1.0   ✓ Đạt
D6 — Hiệu suất & KPI      ████░░  60.2    55.0   +5.2   ✓ Đạt
```

- **Màu đỏ / "critical":** Domain này là bắt buộc cho chức danh, cần ưu tiên đào tạo ngay
- **Màu cam:** Có khoảng cách nhưng chưa critical
- **Màu xanh / ✓:** Đã đáp ứng hoặc vượt yêu cầu

**Cách ưu tiên gap:** Tập trung vào domain vừa có gap lớn vừa được đánh dấu "critical" trước, sau đó mới đến các domain gap lớn khác.

### 5.3 Đọc AI Gợi ý phát triển

Phần **"AI Gợi ý phát triển"** trên trang chi tiết hiển thị tối đa 5 gợi ý, ưu tiên theo gap lớn nhất:

Mỗi gợi ý bao gồm:
- **Ưu tiên** (P1 = cao nhất, P5 = thấp nhất)
- **Domain cần cải thiện** (VD: D3)
- **Hành động cụ thể** (VD: "Hoàn thành khoá học AI Fundamentals")
- **Tài nguyên đề xuất** (khoá học, sandbox, chứng nhận)
- **Loại tài nguyên:** Khoá học / Sandbox / Chứng nhận / Thực hành

**Cách Manager sử dụng AI Gợi ý:** Dùng 5 gợi ý này làm điểm khởi đầu cho buổi thảo luận IDP, không nên giao thêm việc mà không thảo luận trước với nhân viên.

### 5.4 Tạo mới gợi ý AI cho nhân viên

Khi hồ sơ nhân viên có thay đổi (điểm mới, chức danh mới), có thể yêu cầu AI phân tích lại:

1. Vào trang chi tiết nhân viên
2. Trong phần "AI Gợi ý phát triển" → nhấn **"Tạo gợi ý mới"**
3. Chờ ~5–10 giây để AI phân tích
4. Gợi ý mới được lưu vào hồ sơ

### 5.5 Xuất báo cáo cá nhân để review 1-1

Khi chuẩn bị buổi review định kỳ với nhân viên:

1. Vào trang chi tiết nhân viên
2. Nhấn **"Xuất PDF"** → tải file hồ sơ đầy đủ
3. In hoặc chia sẻ file với nhân viên **trước** buổi gặp (để nhân viên đọc trước)
4. Dùng phần "Gợi ý phát triển" làm cơ sở thảo luận kế hoạch IDP

**Kịch bản buổi review 1-1 (30 phút):**
- 5 phút: Nhân viên chia sẻ cảm nhận về điểm số
- 10 phút: Cùng xem Skill Gap, thảo luận domain nào quan trọng nhất
- 10 phút: Xem AI Gợi ý, thống nhất 1-2 hành động cụ thể cho quý tới
- 5 phút: Ghi lại cam kết vào IDP

### 5.6 Theo dõi tiến độ theo thời gian

Trên trang chi tiết nhân viên, phần **"Lịch sử điểm TDWCF"** hiển thị:
- Ngày đánh giá
- Điểm TDWCF tại từng thời điểm
- Loại sự kiện (assessment, certification, manual)

Nếu điểm không tăng sau 1 quý → cần có buổi trao đổi và giao nhiệm vụ thực hành cụ thể.

**Dấu hiệu cần chú ý:**
- TDWCF không tăng sau 2 lần đánh giá liên tiếp → kiểm tra nhân viên có thực sự thực hành Sandbox không
- Điểm giảm → có thể nhân viên đổi chức danh hoặc yêu cầu mới cao hơn
- Điểm tăng mạnh một domain → nhân viên đang học tốt, cân nhắc giao task AI thực tế

---

## 6. Hướng dẫn dành cho Nhân viên

> **Dành cho:** Toàn bộ nhân viên (role: `member` trở lên)

**Mục tiêu sau khi đọc phần này:** Biết cách đọc hồ sơ cá nhân, làm khảo sát TDWCF, thực hành Sandbox và ghi nhận AI Impact.

### 6.1 Lần đầu đăng nhập — Kiểm tra hồ sơ Digital Twin

1. Đăng nhập → Sidebar trái → mục **"Năng lực số"**
2. Nhấn **"Hồ sơ Digital Twin"**

**Nếu chưa có hồ sơ** (màn hình hiển thị "Chưa có hồ sơ năng lực"):
→ Bạn cần hoàn thành **khảo sát TDWCF**. Nhấn "Làm khảo sát ngay" hoặc xem Mục 7 trong tài liệu này.

**Nếu đã có hồ sơ**, bạn sẽ thấy:

```
┌────────────────────────────────────────────────────────────┐
│  Độ hoàn thiện hồ sơ: 40%  ■■■■░░░░░░                     │
│  TDWCF ✓  Cert ✓  Sandbox ░  Impact ░  KPI ░               │
├─────────────┬──────────┬──────────┬──────────┬─────────────┤
│ TDWCF: 52.3 │Trust: 38 │AI Score:42│Impact: 0 │ KPI: 75.5% │
├────────────────────────────────────────────────────────────┤
│          Radar Chart — 6 Năng lực số                       │
│                   D1                                       │
│              D6      D2                                    │
│              D5      D3                                    │
│                   D4                                       │
└────────────────────────────────────────────────────────────┘
```

**Thanh "Độ hoàn thiện hồ sơ" lên 100% khi:**
- TDWCF: Đã làm khảo sát ít nhất 1 lần
- Cert: Đã có ít nhất 1 chứng nhận AI
- Sandbox: Đã hoàn thành ít nhất 1 session Sandbox
- Impact: Đã ghi nhận ít nhất 1 AI Impact
- KPI: Đã nhập chỉ số KPI

### 6.2 Hiểu các chỉ số trên hồ sơ cá nhân

| Chỉ số | Ý nghĩa | Tốt khi | Cách nâng cao |
|---|---|---|---|
| **TDWCF Score** | Điểm năng lực số tổng hợp 6 domain | ≥ 70 | Thực hành, đào tạo, làm lại khảo sát |
| **Workforce Trust Score** | Độ tin cậy tổng hợp | ≥ 60 | Tất cả các hoạt động trong hệ thống |
| **AI Readiness Score** | Mức độ sẵn sàng làm việc với AI | ≥ 60 | Tập trung D3, D4, Sandbox AI |
| **Impact Score** | Mức độ áp dụng AI vào công việc thực tế | ≥ 50 | Ghi nhận AI Impact hàng tuần |
| **KPI Achievement** | % hoàn thành KPI | ≥ 80% | Cập nhật KPI, làm việc hiệu quả |
| **Sandbox Hours** | Tổng giờ thực hành AI | ≥ 10h | Thực hành Sandbox đều đặn |

### 6.3 Xem điểm 6 năng lực (D1–D6)

Phần **"Đánh giá năng lực TDWCF"** hiển thị điểm từng domain:

| Domain | Mô tả | Điểm của bạn | Yêu cầu vị trí |
|---|---|---|---|
| D1 — Số cơ bản | Công cụ văn phòng, bảo mật số, thiết bị | (xem hồ sơ) | (theo chức danh) |
| D2 — Dữ liệu | Đọc, phân tích và trình bày dữ liệu | (xem hồ sơ) | (theo chức danh) |
| D3 — AI | Hiểu và ứng dụng AI vào công việc | (xem hồ sơ) | (theo chức danh) |
| D4 — Quy trình | Tự động hoá task, tối ưu quy trình | (xem hồ sơ) | (theo chức danh) |
| D5 — Đổi mới | Tư duy sáng tạo, đề xuất cải tiến | (xem hồ sơ) | (theo chức danh) |
| D6 — Hiệu suất | Đạt mục tiêu, đo lường kết quả | (xem hồ sơ) | (theo chức danh) |

> **Gợi ý:** Tập trung cải thiện domain có màu đỏ hoặc gap âm lớn nhất trước.

### 6.4 Xem Skill Gap so với vị trí việc làm

Phần **"Skill Gap theo Vị trí"** cho thấy khoảng cách giữa điểm hiện tại và yêu cầu của chức danh bạn đang giữ.

- **Đường đỏ trên thanh bar:** Mức điểm yêu cầu tối thiểu
- **Thanh màu:** Điểm hiện tại của bạn
- **Gap âm (VD: -15.2):** Bạn đang thiếu 15.2 điểm so với yêu cầu
- **"critical" (chữ đỏ):** Domain này bắt buộc phải đạt cho vị trí của bạn

### 6.5 Xem và thực hiện AI Gợi ý phát triển

1. Trên trang Hồ sơ Digital Twin → cuộn xuống phần **"AI Gợi ý phát triển"**
2. Nếu chưa có gợi ý → nhấn **"Tạo gợi ý mới"**
3. AI sẽ phân tích hồ sơ và đề xuất **5 hành động ưu tiên**

Mỗi gợi ý có:
- **Hành động cụ thể** cần làm
- **Tài nguyên đề xuất** (khoá học online, bài thực hành, chứng nhận)
- **Loại hoạt động:** Khoá học / Sandbox / Chứng nhận / Thực hành

**Cách thực hiện gợi ý:**
- Nếu gợi ý "Sandbox" → vào **AI Sandbox** và chọn môi trường tương ứng
- Nếu gợi ý "Khoá học" → tìm khoá học đó trên Coursera/YouTube/nền tảng nội bộ
- Nếu gợi ý "Chứng nhận" → báo Manager/HR để được xem xét cấp chứng nhận
- Nếu gợi ý "Thực hành" → áp dụng thực tế rồi ghi nhận vào AI Impact Tracker

### 6.6 Đặt mục tiêu nghề nghiệp

Phần **"Mục tiêu & Lộ trình"** cho phép bạn tự ghi mục tiêu:

1. Nhấn **"Chỉnh sửa"**
2. **Mục tiêu nghề nghiệp:** VD: "Trở thành AI Practitioner trong 6 tháng"
3. **Lộ trình học tập:** VD: "AI Fundamentals → Sandbox Foundation × 3 → Chứng nhận Foundation"
4. Nhấn **"Lưu mục tiêu"**

> Quản lý của bạn có thể xem mục tiêu này khi review hồ sơ. Hãy viết cụ thể và thực tế.

---

## 7. Khảo sát TDWCF — Hướng dẫn chi tiết

> **Dành cho:** Tất cả nhân viên  
> **Quan trọng:** Đây là bước đầu tiên và bắt buộc để tạo hồ sơ Digital Twin

### 7.1 Khảo sát TDWCF là gì?

Khảo sát TDWCF là bộ câu hỏi tự đánh giá giúp hệ thống xác định điểm số của bạn trên **6 domain năng lực số**. Không có câu trả lời đúng/sai — hệ thống chỉ cần biết thực trạng của bạn để đưa ra gợi ý phù hợp.

**Thời gian:** 30–45 phút  
**Tần suất:** 3–6 tháng/lần (hoặc sau khi hoàn thành đào tạo quan trọng)  
**Ai cần làm:** Toàn bộ nhân viên, lần đầu trong tuần đầu tiên

### 7.2 Cách truy cập và bắt đầu khảo sát

1. Đăng nhập hệ thống
2. Sidebar → **"Năng lực số"** → **"Hồ sơ Digital Twin"**
3. Nếu chưa có hồ sơ → nhấn **"Làm khảo sát ngay"**
4. Nếu đã có hồ sơ → cuộn xuống → nhấn **"Làm lại khảo sát"**

### 7.3 Cấu trúc bộ câu hỏi

Bộ câu hỏi gồm **6 phần**, mỗi phần tương ứng 1 domain năng lực:

| Phần | Domain | Số câu hỏi | Ví dụ câu hỏi |
|---|---|---|---|
| Phần 1 | D1 — Số cơ bản | ~8 câu | "Bạn sử dụng thành thạo Google Docs / Word ở mức nào?" |
| Phần 2 | D2 — Dữ liệu | ~8 câu | "Bạn có thể tạo biểu đồ từ dữ liệu thô không?" |
| Phần 3 | D3 — AI | ~8 câu | "Bạn đã từng dùng ChatGPT / Copilot trong công việc chưa?" |
| Phần 4 | D4 — Quy trình | ~8 câu | "Bạn đã từng tự động hoá 1 tác vụ lặp lại nào chưa?" |
| Phần 5 | D5 — Đổi mới | ~6 câu | "Bạn có từng đề xuất cải tiến quy trình và được triển khai không?" |
| Phần 6 | D6 — Hiệu suất | ~6 câu | "Bạn có theo dõi KPI cá nhân bằng công cụ số không?" |

### 7.4 Thang điểm câu hỏi

Mỗi câu hỏi có thang điểm từ **1 đến 5**:

| Mức | Mô tả |
|---|---|
| 1 — Chưa bao giờ | Chưa từng nghe / biết đến |
| 2 — Biết nhưng chưa làm | Đã nghe / đọc nhưng chưa thực hành |
| 3 — Đã thử / Thỉnh thoảng | Đã làm 1–2 lần hoặc thỉnh thoảng |
| 4 — Thường xuyên | Dùng thường xuyên trong công việc |
| 5 — Thành thạo / Có thể dạy | Rất thành thạo, có thể hướng dẫn người khác |

### 7.5 Cách tính điểm từ khảo sát

Công thức tính điểm từng domain:

```
Điểm domain = (Tổng điểm các câu trong domain / Số câu × 5) × 100

Ví dụ D1 — 8 câu, tổng điểm 28/40:
Điểm D1 = (28 / 40) × 100 = 70.0
```

Điểm TDWCF tổng hợp = trung bình có trọng số 6 domain:

```
TDWCF = D1×0.15 + D2×0.15 + D3×0.25 + D4×0.20 + D5×0.15 + D6×0.10
```

> D3 (AI Literacy) có trọng số cao nhất (25%) vì đây là năng lực cốt lõi của chuyển đổi số.

### 7.6 Lời khuyên khi làm khảo sát

**Hãy trả lời trung thực:**
- Khảo sát không phải bài kiểm tra — không có điểm thưởng cho câu trả lời "hay hơn thực tế"
- Điểm quá cao sẽ khiến AI gợi ý sai hướng và Skill Gap không chính xác
- Quản lý không dùng điểm này để đánh giá hiệu suất trực tiếp — đây là công cụ phát triển

**Dành đủ thời gian:**
- Không nên làm vội trong 10–15 phút — độ chính xác sẽ thấp
- Có thể tạm dừng và tiếp tục sau (hệ thống lưu nháp tự động)

**Bối cảnh đánh giá:**
- Đánh giá dựa trên **công việc thực tế**, không phải kiến thức lý thuyết
- "Tôi biết khái niệm" ≠ "Tôi làm được trong công việc" → chọn điểm thấp hơn

### 7.7 Sau khi hoàn thành khảo sát

Hệ thống tự động:
1. Tính điểm 6 domain và TDWCF tổng hợp
2. Xác định cấp độ trưởng thành số
3. Phân tích Skill Gap so với chức danh
4. Sẵn sàng để AI tạo gợi ý phát triển (nhấn "Tạo gợi ý mới")

Bạn sẽ thấy hồ sơ Digital Twin xuất hiện sau 1-2 phút.

---

## 8. Khung đánh giá TDWCF — 6 Năng lực số

TDWCF (Total Digital Workforce Competency Framework) là khung đánh giá 6 năng lực số cốt lõi:

### D1 — Năng lực số cơ bản (Digital Literacy)

**Mô tả:** Thành thạo công cụ văn phòng số, bảo mật thông tin, sử dụng thiết bị.

**Ví dụ năng lực cụ thể:**
- Dùng Google Workspace (Docs, Sheets, Slides, Drive) hoặc Microsoft 365 thành thạo
- Quản lý file trên cloud, đặt quyền chia sẻ đúng cách
- Nhận diện email phishing, link độc hại
- Dùng phần mềm họp trực tuyến (Zoom, Teams, Google Meet) chuyên nghiệp
- Cài đặt và quản lý thiết bị cơ bản (laptop, điện thoại công việc)

**Cách nâng cao điểm D1:**
- Thực hành Sandbox "AI Office — Foundation"
- Khoá học Google Workspace Certification (miễn phí trên Google)
- Tập sử dụng phím tắt và tính năng nâng cao của công cụ đang dùng hàng ngày

### D2 — Năng lực dữ liệu (Data Literacy)

**Mô tả:** Đọc hiểu, phân tích và trình bày dữ liệu bằng công cụ số.

**Ví dụ năng lực cụ thể:**
- Tạo bảng pivot trong Excel/Sheets để phân tích dữ liệu nhanh
- Đọc và giải thích biểu đồ, dashboard báo cáo
- Viết công thức tính toán cơ bản (SUM, IF, VLOOKUP, v.v.)
- Làm báo cáo số liệu có hình ảnh trực quan rõ ràng
- Nhận ra khi dữ liệu bất thường hoặc sai

**Cách nâng cao điểm D2:**
- Thực hành Sandbox "AI Data — Foundation"
- Khoá học "Data Analysis with Excel" (Coursera/LinkedIn Learning)
- Tập phân tích một tập dữ liệu thực tế từ công việc

### D3 — Năng lực AI (AI Literacy)

**Mô tả:** Hiểu AI là gì, ứng dụng AI vào công việc hàng ngày.

**Ví dụ năng lực cụ thể:**
- Dùng ChatGPT/Copilot/Gemini để soạn thảo email, báo cáo nhanh hơn
- Tạo prompt hiệu quả (prompt engineering cơ bản)
- Hiểu AI có thể sai → biết cách kiểm tra kết quả
- Dùng AI để tóm tắt tài liệu dài, phân tích ý kiến khách hàng
- Biết khi nào nên / không nên dùng AI

**Cách nâng cao điểm D3:**
- Khoá học "AI for Everyone" (Andrew Ng, Coursera — miễn phí)
- Thực hành prompt engineering 15 phút/ngày với ChatGPT
- Ghi lại 1 lần dùng AI vào AI Impact Tracker mỗi tuần

### D4 — Quy trình & Tự động hoá (Workflow Automation)

**Mô tả:** Tự động hoá task lặp lại, tối ưu hoá quy trình bằng công cụ số.

**Ví dụ năng lực cụ thể:**
- Dùng Zapier/Make để tự động gửi thông báo khi có email mới
- Xây dựng form tự động nhập liệu vào bảng tính
- Thiết lập lịch tự động gửi báo cáo định kỳ
- Tạo template chuẩn hoá cho quy trình lặp lại
- Dùng AI để viết script tự động hoá đơn giản

**Cách nâng cao điểm D4:**
- Thực hành Sandbox "AI Workflow — Foundation"
- Xác định 1 task lặp lại trong công việc và thử tự động hoá
- Khoá học "No-Code Automation" (Make.com Academy — miễn phí)

### D5 — Đổi mới & Sáng kiến (Digital Innovation)

**Mô tả:** Tư duy sáng tạo trong môi trường số, đề xuất cải tiến quy trình.

**Ví dụ năng lực cụ thể:**
- Đề xuất ứng dụng AI mới vào bộ phận và có kế hoạch thực hiện cụ thể
- Dẫn dắt 1 sáng kiến cải tiến quy trình bằng công nghệ
- Tìm và thử nghiệm công cụ mới, chia sẻ kết quả với team
- Tư duy "first principles" khi gặp vấn đề trong môi trường số
- Xây dựng case study thực tế từ trải nghiệm của mình

**Cách nâng cao điểm D5:**
- Tham gia chương trình đổi mới sáng tạo nội bộ
- Đề xuất ít nhất 1 sáng kiến AI mỗi quý cho quản lý
- Thực hành Design Thinking online (IDEO Design Thinking — miễn phí)

### D6 — Hiệu suất & KPI (Digital Performance)

**Mô tả:** Đặt mục tiêu, đo lường và cải thiện hiệu suất bằng công cụ số.

**Ví dụ năng lực cụ thể:**
- Theo dõi KPI cá nhân trên dashboard hoặc bảng tính cập nhật tuần
- Dùng AI để phân tích xu hướng hiệu suất cá nhân/nhóm
- Đặt mục tiêu SMART và theo dõi tiến độ bằng số liệu
- Báo cáo hiệu suất rõ ràng, có số liệu cụ thể
- Điều chỉnh phương pháp làm việc dựa trên dữ liệu đo lường được

**Cách nâng cao điểm D6:**
- Thiết lập dashboard KPI cá nhân (Google Sheets hoặc Notion)
- Ghi nhận AI Impact thường xuyên (mỗi lần dùng AI có kết quả đo được)
- Khoá học "OKR and Goal Setting" (LinkedIn Learning)

---

## 9. Cấp độ trưởng thành số

Hệ thống phân chia 5 cấp độ trưởng thành số dựa trên điểm TDWCF:

| Cấp độ | Tên | Điểm TDWCF | Mô tả | Biểu tượng |
|---|---|---|---|---|
| 1 | **Khởi đầu** (Digital Beginner) | 0–34 | Biết dùng máy tính cơ bản, chưa tiếp cận AI | ⬜ Xám |
| 2 | **Nhận thức** (Digital Aware) | 35–54 | Biết AI tồn tại, đã thử dùng 1–2 công cụ AI | 🔵 Xanh dương |
| 3 | **Thực hành** (Digital Practitioner) | 55–69 | Dùng AI thường xuyên trong công việc hàng ngày | 🟡 Vàng |
| 4 | **Chuyên nghiệp** (Digital Professional) | 70–84 | Thành thạo nhiều công cụ AI, có thể hướng dẫn người khác | 🟢 Xanh lá |
| 5 | **Dẫn dắt** (Digital Leader) | 85–100 | Dẫn dắt chuyển đổi số, xây dựng chiến lược AI cho tổ chức | 🟣 Tím |

### Ngưỡng điểm để lên cấp

| Từ cấp | Lên cấp | Yêu cầu chính |
|---|---|---|
| Khởi đầu → Nhận thức | TDWCF ≥ 35 | Hoàn thành khảo sát + bắt đầu dùng Sandbox |
| Nhận thức → Thực hành | TDWCF ≥ 55 | ≥ 2 Sandbox sessions, đạt Chứng nhận Foundation |
| Thực hành → Chuyên nghiệp | TDWCF ≥ 70 | KPI ≥ 70%, ≥ 1 portfolio/case study, Chứng nhận Practitioner |
| Chuyên nghiệp → Dẫn dắt | TDWCF ≥ 85 | Sandbox ≥ 20h, Impact Score > 0, Chứng nhận Professional |

### Ý nghĩa của từng cấp độ với tổ chức

- **Khởi đầu:** Nhân viên này cần đào tạo nền tảng ngay — không nên giao task AI
- **Nhận thức:** Có thể bắt đầu thử dùng AI với hướng dẫn cụ thể
- **Thực hành:** Có thể làm việc độc lập với AI, giao được task thực tế
- **Chuyên nghiệp:** Có thể dẫn nhóm nhỏ áp dụng AI, mentor đồng nghiệp
- **Dẫn dắt:** Có thể xây dựng chiến lược AI cho bộ phận, đại diện trong dự án chuyển đổi số

---

## 10. Lộ trình phát triển nghề nghiệp

### Truy cập Lộ trình

Sidebar → **"Lộ trình nghề nghiệp"** (`/dashboard/career-pathway`)

Trang hiển thị:
- **Bước hiện tại** của bạn được highlight
- **Điều kiện để tiến lên bước tiếp theo**
- **Tiến độ các điều kiện** (% hoàn thành)

### 5 Bước lộ trình

```
Bước 1 ──► Bước 2 ──► Bước 3 ──► Bước 4 ──► Bước 5
Nền tảng   Foundation  Practitioner Professional  Leader
  4 tuần    8 tuần      12 tuần     16 tuần       24 tuần
```

| Bước | Tiêu đề | Điều kiện chính | Thời gian ước tính |
|---|---|---|---|
| 1 | Xây dựng nền tảng số cơ bản | Hoàn thành khảo sát TDWCF lần đầu, thực hành Sandbox Foundation | 4 tuần |
| 2 | Thực hành và đạt Chứng nhận Foundation | TDWCF ≥ 41, ≥ 2 Sandbox, đạt Chứng nhận AI Foundation | 8 tuần |
| 3 | Nâng cao và đạt Chứng nhận Practitioner | TDWCF ≥ 61, KPI ≥ 70%, ≥ 1 case study portfolio | 12 tuần |
| 4 | Trở thành chuyên gia — Chứng nhận Professional | Sandbox ≥ 20h, Impact Score > 0, TDWCF ≥ 76 | 16 tuần |
| 5 | Dẫn dắt chuyển đổi số — Chứng nhận Leader | Portfolio được duyệt, TDWCF ≥ 91, dẫn dắt ≥ 1 sáng kiến AI | 24 tuần |

### Kiểm tra điều kiện tiến lên

Trang Lộ trình có nút **"Kiểm tra điều kiện"** — hệ thống tự động so sánh hồ sơ của bạn với yêu cầu bước tiếp theo và hiển thị:
- ✅ Điều kiện đã đạt
- ⬜ Điều kiện chưa đạt + gợi ý cách đạt

### Ví dụ thực tế: Nhân viên Sales đi qua Lộ trình

| Tuần | Hoạt động | Kết quả |
|---|---|---|
| 1–2 | Làm khảo sát TDWCF, điểm 38 (Nhận thức) | Bước 1 hoàn thành |
| 3–4 | Thực hành Sandbox "AI Sales — Foundation" | +5 điểm Sandbox |
| 5–8 | Hoàn thành 2 Sandbox, đạt Cert Foundation (AI_SALES) | Bước 2 hoàn thành |
| 9–12 | KPI 75%, ghi 1 case study AI giúp chốt đơn nhanh hơn | Bước 3 đang tiến hành |
| 13–16 | Sandbox 20h, Impact Score > 0, TDWCF 78 | Bước 4 hoàn thành |
| Tháng 6+ | Dẫn dắt team Sales áp dụng AI, đạt Cert Professional | Bước 5 đang tiến hành |

---

## 11. Hệ thống chứng nhận AI

### Truy cập Chứng nhận

Sidebar → **"Chứng nhận AI"** (`/dashboard/certifications`)

### Cấu trúc chứng nhận

Hệ thống có **28 chứng nhận** theo **4 cấp độ × 7 lĩnh vực**:

| Cấp độ | Tên cấp | Mô tả | TDWCF yêu cầu |
|---|---|---|---|
| Foundation | Nền tảng | Kiến thức cơ bản về AI trong lĩnh vực | ≥ 35 |
| Practitioner | Thực hành | Ứng dụng AI thành thạo | ≥ 55 |
| Professional | Chuyên nghiệp | Chuyên sâu + có khả năng hướng dẫn | ≥ 70 |
| Leader | Dẫn dắt | Chiến lược + dẫn dắt chuyển đổi | ≥ 85 |

**7 lĩnh vực chứng nhận:**

| Mã | Lĩnh vực | Phù hợp với bộ phận | Ưu tiên học |
|---|---|---|---|
| AI_ADMIN | AI Administrative Officer | Hành chính, Văn phòng | D1, D4 |
| AI_HR | AI HR Practitioner | Nhân sự | D2, D5 |
| AI_SALES | AI Sales Practitioner | Kinh doanh, Sales | D3, D6 |
| AI_FINANCE | AI Finance Practitioner | Kế toán, Tài chính | D2, D6 |
| AI_DATA | AI Data Operator | Phân tích dữ liệu, IT | D2, D3 |
| AI_MANAGER | AI Workforce Manager | Quản lý | D5, D6 |
| AI_LEADER | AI Transformation Leader | CEO, Giám đốc | D3, D5 |

### Chứng nhận nên đăng ký theo vị trí

| Vị trí | Chứng nhận đề xuất | Thứ tự ưu tiên |
|---|---|---|
| Nhân viên văn phòng | AI_ADMIN Foundation → Practitioner | 1 → 2 |
| Nhân viên Sales | AI_SALES Foundation → Practitioner | 1 → 2 |
| HR Generalist | AI_HR Foundation → Practitioner | 1 → 2 |
| Kế toán / Finance | AI_FINANCE Foundation | 1 |
| Data Analyst | AI_DATA Foundation → Practitioner | 1 → 2 |
| Trưởng phòng | AI_MANAGER Foundation + chứng nhận ngành | 1 |
| CEO / BGĐ | AI_LEADER Foundation → Leader | 1 → 2 |

### Quy trình đăng ký và nhận chứng nhận

> **Lưu ý hiện tại:** Việc cấp chứng nhận được thực hiện bởi Admin/HR sau khi nhân viên hoàn thành các điều kiện. Nhân viên không tự đăng ký được.

**Nhân viên cần làm:**
1. Đảm bảo TDWCF Score đạt ngưỡng yêu cầu của cấp độ muốn đạt
2. Hoàn thành các Sandbox session liên quan đến lĩnh vực chứng nhận
3. Báo Manager hoặc HR để được xem xét

**Admin/HR thực hiện cấp chứng nhận:**
1. Vào **Certs Admin** → **"Thêm chứng nhận mới"**
2. Chọn nhân viên, loại chứng nhận, ngày cấp
3. Nhập ngày hết hạn nếu có (thường 2 năm)
4. Lưu → chứng nhận xuất hiện trong hồ sơ nhân viên

### Xem chứng nhận của mình

Trang Chứng nhận AI hiển thị:
- **Chứng nhận đang hoạt động** (active): còn hiệu lực
- **Chứng nhận đã hết hạn** (expired): cần gia hạn (làm lại khảo sát + Sandbox để gia hạn)
- Ngày cấp, ngày hết hạn, tổ chức cấp

### Ý nghĩa chứng nhận trong tổ chức

Chứng nhận là **bằng chứng năng lực được xác thực bởi hệ thống**, có giá trị trong:
- Đánh giá thăng chức / tăng lương (1 trong các tiêu chí)
- Phân công dự án AI yêu cầu chứng nhận tương ứng
- Portfolio cá nhân khi ứng tuyển vị trí mới (bên trong hoặc bên ngoài)

---

## 12. AI Sandbox — Thực hành thực tế

### AI Sandbox là gì?

Sandbox là các **bài tập thực hành AI** được thiết kế theo từng lĩnh vực công việc. Mỗi Sandbox session giúp bạn:
- Luyện kỹ năng sử dụng AI trong tình huống thực tế (không phải lý thuyết)
- Tích lũy điểm Sandbox để nâng Trust Score (tỉ trọng 15%)
- Hoàn thành điều kiện trên Lộ trình nghề nghiệp và cho Chứng nhận AI

### 6 môi trường Sandbox

| Tên | Mô tả | Phù hợp với | Thời gian hoàn thành |
|---|---|---|---|
| AI Office — Foundation | Thực hành AI trong công việc văn phòng (email, tài liệu, lịch) | Tất cả nhân viên | 2–4 giờ |
| AI Data — Foundation | Phân tích dữ liệu, đọc báo cáo với sự hỗ trợ của AI | Data, Kế toán | 3–5 giờ |
| AI Sales — Foundation | Ứng dụng AI trong bán hàng, chăm sóc khách hàng | Sales, Marketing | 2–4 giờ |
| AI HR — Foundation | AI trong tuyển dụng, onboarding, đánh giá năng lực | HR | 2–4 giờ |
| AI Workflow — Foundation | Tự động hoá quy trình, xây dựng workflow số | Tất cả bộ phận | 3–5 giờ |
| AI Leadership — Foundation | Chiến lược AI, ra quyết định dựa trên dữ liệu | Quản lý, CEO | 2–3 giờ |

### Ví dụ nhiệm vụ Sandbox thực tế

**AI Office — Foundation:**
- Nhiệm vụ 1: Dùng AI soạn email chuyên nghiệp từ ghi chú thô (30 phút)
- Nhiệm vụ 2: Dùng AI tóm tắt tài liệu dài 10 trang thành 1 trang (30 phút)
- Nhiệm vụ 3: Tạo template báo cáo tuần tự động điền bằng AI (45 phút)

**AI Sales — Foundation:**
- Nhiệm vụ 1: Dùng AI phân tích phản hồi khách hàng, tìm điểm chung (45 phút)
- Nhiệm vụ 2: Tạo script chăm sóc khách hàng cá nhân hoá với AI (30 phút)
- Nhiệm vụ 3: Dùng AI lên kế hoạch follow-up sau cuộc họp (30 phút)

### Quy trình thực hiện Sandbox

```
1. Chọn môi trường Sandbox phù hợp với bộ phận
        ↓
2. Xem danh sách nhiệm vụ trong môi trường
        ↓
3. Nhấn "Bắt đầu" một nhiệm vụ cụ thể
        ↓
4. Đọc hướng dẫn → mở công cụ AI song song → thực hiện
        ↓
5. Điền kết quả / đính kèm file → nhấn "Nộp bài"
        ↓
6. Hệ thống chấm điểm và cập nhật hồ sơ tự động
```

### Hướng dẫn từng bước

**Bước 1:** Sidebar → **"AI Sandbox"** → trang danh sách môi trường

**Bước 2:** Chọn môi trường phù hợp → nhấn vào tên môi trường

**Bước 3:** Xem danh sách nhiệm vụ → chọn nhiệm vụ → nhấn **"Bắt đầu nhiệm vụ"**

**Bước 4:** Đọc mô tả nhiệm vụ → thực hiện (có thể mở công cụ AI song song trên trình duyệt)

**Bước 5:** Điền kết quả / đính kèm file → nhấn **"Nộp bài"**

**Bước 6:** Xem điểm → hồ sơ Digital Twin tự động cập nhật Sandbox Hours và điểm

**Mẹo:** Bắt đầu với "AI Office — Foundation" nếu bạn mới. Mỗi Sandbox session mất 30–60 phút. Không cần làm liên tục — có thể chia nhỏ mỗi ngày 1 nhiệm vụ.

---

## 13. AI Impact Tracker

### AI Impact Tracker là gì?

Đây là công cụ để bạn **ghi lại các lần sử dụng AI mang lại kết quả thực tế** trong công việc. Mục đích:
- Chứng minh AI có giá trị thực tế (không chỉ là buzzword)
- Tăng Impact Score → nâng Trust Score → nâng thứ hạng Leaderboard
- Xây dựng portfolio case study thực tế từ chính công việc của bạn

**Ví dụ impact đáng ghi nhận:**
- Dùng AI giúp rút ngắn 2 tiếng soạn báo cáo hàng tuần
- Tự động hoá quy trình nhập liệu → tiết kiệm 3 ngày/tháng
- Dùng AI phân tích dữ liệu khách hàng → tăng tỉ lệ chốt đơn 15%
- Dùng AI dịch tài liệu kỹ thuật → không cần thuê dịch vụ ngoài (tiết kiệm 2 triệu/tháng)
- Dùng AI tóm tắt 50 email hàng ngày xuống còn 10 phút đọc

### Cách ghi nhận AI Impact

1. Sidebar → **"AI Impact Tracker"** → nhấn **"Ghi nhận Impact mới"**
2. Điền thông tin:
   - **Danh mục:** Năng suất / Tiết kiệm chi phí / Tăng doanh thu / Cải thiện chất lượng / Giảm rủi ro
   - **Tiêu đề:** Ngắn gọn, mô tả được kết quả (VD: "AI giúp soạn báo cáo tuần nhanh 2× ")
   - **Mô tả chi tiết:** Bạn đã làm gì với AI? Context là gì? Kết quả cụ thể ra sao?
   - **Công cụ AI sử dụng:** ChatGPT, Copilot, Gemini, v.v.
   - **Kết quả đo được:** VD: "Tiết kiệm 3 giờ/tuần", "Giảm lỗi 20%", "Tăng output 30%"
   - **Kỳ ghi nhận:** Tháng nào
3. Nhấn **"Lưu"**

### Bí quyết ghi nhận Impact hiệu quả

**Có số liệu cụ thể > mô tả chung chung:**
- ❌ Kém: "Dùng AI để làm việc nhanh hơn"
- ✅ Tốt: "Dùng ChatGPT để soạn 5 email marketing → giảm từ 90 phút xuống 20 phút"

**Ghi ngay khi còn nhớ:**
- Nên ghi trong vòng 24h sau khi có kết quả
- Đặt nhắc nhở hàng tuần để không bỏ sót

**Đủ loại impact:**
- Không chỉ ghi "tiết kiệm thời gian" — hãy thử cả "tăng chất lượng", "giảm rủi ro", "tăng doanh thu"

### Quản lý xem Impact của nhân viên

Ops/Manager có thể:
1. Vào **AI Impact Tracker** → xem tất cả records của tổ chức
2. Lọc theo **nhân viên**, **tháng**, **danh mục**
3. Xem tổng Impact Score của từng nhân viên trên trang Workforce Admin

**Impact Score** được tính dựa trên tần suất và chất lượng các lần ghi nhận. Đây là thành phần đóng góp vào Workforce Trust Score và là bằng chứng ROI của chương trình đào tạo AI.

---

## 14. Kế hoạch Phát triển Cá nhân (IDP)

### IDP là gì trong ngữ cảnh Digital Twin?

IDP (Individual Development Plan) là kế hoạch phát triển được thống nhất giữa nhân viên và manager, dựa trên dữ liệu từ hệ thống Workforce Digital Twin. IDP không được lưu trực tiếp trong hệ thống này nhưng có thể được xây dựng dựa trên dữ liệu xuất ra.

### Quy trình xây dựng IDP

**Bước 1: Thu thập dữ liệu (Manager)**
1. Vào trang chi tiết nhân viên → xuất PDF
2. Nhận diện: Domain gap lớn nhất, cấp độ hiện tại, chứng nhận còn thiếu

**Bước 2: Tạo AI Gợi ý mới**
1. Nhấn "Tạo gợi ý mới" trên hồ sơ nhân viên
2. Đọc 5 gợi ý AI → chọn 2-3 hành động phù hợp nhất với bối cảnh thực tế

**Bước 3: Họp review 1-1 (Manager + Nhân viên)**
1. Chia sẻ PDF hồ sơ cho nhân viên trước buổi họp
2. Thảo luận: "AI gợi ý điều này — bạn nghĩ sao? Có khả thi trong quý này không?"
3. Thống nhất 2-3 mục tiêu cụ thể cho quý tới

**Bước 4: Ghi lại cam kết**
Nhân viên vào hồ sơ của mình → phần "Mục tiêu & Lộ trình" → ghi lại:
- Mục tiêu TDWCF mong muốn đạt cuối quý (VD: TDWCF ≥ 60)
- Sandbox cần hoàn thành (VD: AI Sales Foundation hoàn thành)
- Chứng nhận đang hướng tới (VD: AI_SALES Foundation)

**Bước 5: Theo dõi tiến độ (Manager)**
- Cuối tháng: Kiểm tra Sandbox Hours, Impact entries
- Cuối quý: So sánh TDWCF mới với mục tiêu đã đặt

### Template IDP đơn giản

```
IDP - [Tên nhân viên] - Quý [Q/Năm]

HIỆN TRẠNG:
- TDWCF Score: [điểm] — Cấp độ: [Nhận thức/Thực hành/...]
- Domain yếu nhất: [D3 AI — 28 điểm, gap -17]
- Chứng nhận đang có: [AI_ADMIN Foundation]

MỤC TIÊU CUỐI QUÝ:
- TDWCF mục tiêu: [≥ 60]
- Domain cần cải thiện: [D3 AI lên ≥ 40]
- Hoàn thành Sandbox: [AI Sales Foundation]
- Đăng ký chứng nhận: [AI_SALES Foundation]

HÀNH ĐỘNG CỤ THỂ:
1. [Tuần 1-2] Khoá học "AI for Everyone" trên Coursera
2. [Tuần 3-4] Sandbox "AI Sales — Foundation" nhiệm vụ 1-2
3. [Tháng 2] Ghi nhận 4 AI Impact entries có số liệu cụ thể
4. [Tháng 3] Hoàn thành toàn bộ Sandbox, báo HR xem xét chứng nhận

CAM KẾT:
Nhân viên: [ký tên]  
Manager: [ký tên]  
Ngày: [DD/MM/YYYY]
```

---

## 15. Xuất báo cáo Excel và PDF

### Báo cáo toàn tổ chức (dành cho Manager/CEO)

**Truy cập:** Workforce Admin → nút "Xuất Excel" / "Xuất PDF"

**Excel — 4 sheets:**
| Sheet | Nội dung |
|---|---|
| Tổng quan | KPI tổng hợp, phân bổ cấp độ, điểm TB 6 domain |
| Danh sách nhân viên | Bảng đầy đủ: tên, chức danh, D1–D6, TDWCF, Trust Score, cấp độ |
| Phân tích Skill Gap | Gap từng domain so với yêu cầu chức danh, tổng gap |
| Leaderboard | Xếp hạng theo Trust Score, TDWCF, AI Readiness |

**PDF — 2 trang A4:**
- Trang 1: Header tổ chức + KPI cards + biểu đồ phân bổ + domain averages
- Trang 2: Leaderboard Top 5 + bảng toàn bộ nhân viên + ma trận Skill Gap

### Báo cáo cá nhân (dành cho nhân viên và Manager)

**Truy cập:** Trang chi tiết nhân viên → nút "Xuất Excel" / "Xuất PDF"

**Excel — 2 sheets:**
| Sheet | Nội dung |
|---|---|
| Hồ sơ năng lực | Thông tin cá nhân, điểm 6 domain + gap, chứng nhận, mục tiêu |
| Gợi ý phát triển | 5 gợi ý AI: ưu tiên, domain, hành động, tài nguyên |

**PDF — 1 trang A4:**
- Biểu đồ radar 6 năng lực
- Cards từng domain với thanh tiến độ + dấu yêu cầu
- Bảng Trust Score breakdown
- Danh sách chứng nhận
- AI Gợi ý phát triển

### Lịch xuất báo cáo đề xuất

| Tần suất | Loại báo cáo | Người thực hiện | Người nhận |
|---|---|---|---|
| Hàng tuần | AI Impact summary | Manager tự xem | Trưởng nhóm |
| Hàng tháng | Cá nhân từng nhân viên (PDF) | Manager | Review 1-1 |
| Hàng quý | Toàn tổ chức (Excel + PDF) | CEO/HR | CEO, Ban Giám đốc |
| Hàng năm | Báo cáo tổng kết năng lực + xu hướng | HR | CEO, toàn bộ Manager |

---

## 16. Quy trình vận hành định kỳ

### Hàng tuần (Nhân viên)

- [ ] Ghi nhận ít nhất 1 lần sử dụng AI có kết quả vào **AI Impact Tracker**
- [ ] Dành 30–60 phút thực hành ít nhất 1 nhiệm vụ **Sandbox**
- [ ] Kiểm tra **AI Gợi ý phát triển** xem có gợi ý mới không

### Hàng tháng (Manager)

- [ ] Xem trang **Workforce Admin** → kiểm tra tiến độ team
- [ ] Xác định nhân viên có điểm thấp / không tiến bộ
- [ ] Xuất **PDF cá nhân** cho mỗi nhân viên trước buổi review
- [ ] Họp 1-1: thảo luận AI Gợi ý, cập nhật mục tiêu nghề nghiệp
- [ ] Tạo gợi ý AI mới cho nhân viên có thay đổi (điểm mới, chức danh mới)

### Hàng quý (CEO/HR)

- [ ] Xuất **báo cáo Excel toàn tổ chức** → phân tích xu hướng theo quý
- [ ] So sánh điểm TDWCF trung bình quý này vs quý trước (có tăng không?)
- [ ] Đánh giá nhân viên sẵn sàng nhận **chứng nhận** mới → cấp chứng nhận
- [ ] Cập nhật **yêu cầu năng lực** theo chức danh nếu có thay đổi chiến lược
- [ ] Lên kế hoạch đào tạo cho nhóm có điểm yếu
- [ ] Review lại IDP của từng nhân viên cùng Manager

### Khi có nhân viên mới

1. HR tạo tài khoản → gán role `member`
2. Gán đúng **Chức danh (Job Title)** trong hồ sơ nhân viên (quan trọng — làm trước)
3. Chia sẻ tài liệu này (hoặc bản tóm tắt) cho nhân viên mới
4. Nhân viên mới hoàn thành **khảo sát TDWCF** trong tuần đầu tiên
5. Hệ thống tự tạo **Hồ sơ Digital Twin** và sẵn sàng cho AI Gợi ý
6. Manager nhấn "Tạo gợi ý mới" → xem gợi ý → lên kế hoạch IDP

### Khi nhân viên thăng chức

1. HR cập nhật **Chức danh mới** trong hồ sơ nhân viên
2. Hệ thống tự động tính lại **Skill Gap** theo chức danh mới
3. Manager yêu cầu **"Tạo gợi ý mới"** để AI cập nhật kế hoạch phát triển
4. Xem xét nâng role từ `member` → `ops` / `manager` nếu phù hợp
5. Xem xét cấp chứng nhận mới phù hợp với vị trí mới

### Khi có chương trình đào tạo tập trung

Sau khi tổ chức triển khai chương trình đào tạo AI (VD: workshop 2 ngày):
1. Yêu cầu toàn bộ nhân viên tham gia làm lại **khảo sát TDWCF** trong vòng 1 tuần
2. So sánh điểm trước và sau để đo hiệu quả chương trình
3. Xuất Excel toàn tổ chức → so sánh bảng "Danh sách nhân viên" 2 thời điểm
4. Báo cáo lên CEO: "Chương trình X nâng TDWCF trung bình từ Y lên Z"

---

## 17. Kế hoạch 30-60-90 ngày cho nhân viên mới

> Dành cho Manager sử dụng khi onboard nhân viên mới

### Ngày 1–30: Làm quen và tạo baseline

| Tuần | Hoạt động | Kết quả mong đợi |
|---|---|---|
| Tuần 1 | Gán chức danh + role, tạo tài khoản hệ thống | Nhân viên có thể đăng nhập |
| Tuần 1 | Nhân viên đọc tài liệu này + làm khảo sát TDWCF | Hồ sơ Digital Twin được tạo |
| Tuần 2 | Manager review hồ sơ + nhấn "Tạo gợi ý mới" | Có 5 AI Gợi ý phát triển |
| Tuần 2–3 | Họp IDP lần đầu: xem Skill Gap + thống nhất mục tiêu | IDP Quý 1 hoàn thành |
| Tuần 3–4 | Bắt đầu Sandbox đầu tiên (AI Office Foundation) | Sandbox Hours ≥ 2h |

**Mục tiêu cuối tháng 1:**
- TDWCF Score đã có (baseline đo được)
- Biết điểm yếu nhất của mình và domain cần ưu tiên
- Đã có IDP Quý 1 bằng văn bản

### Ngày 31–60: Thực hành và xây dựng thói quen

| Tuần | Hoạt động | Kết quả mong đợi |
|---|---|---|
| Tuần 5–6 | Hoàn thành Sandbox Foundation đầu tiên | Sandbox Hours ≥ 5h |
| Tuần 5–6 | Ghi nhận 4 AI Impact entries | Impact Score bắt đầu tăng |
| Tuần 7–8 | Bắt đầu Sandbox thứ 2 phù hợp với bộ phận | Sandbox Hours ≥ 8h |
| Tuần 8 | Check-in với Manager: tiến độ IDP | Điều chỉnh kế hoạch nếu cần |

**Mục tiêu cuối tháng 2:**
- Đã hoàn thành ít nhất 1 Sandbox Foundation
- Có ít nhất 4 AI Impact entries
- Thói quen ghi Impact và thực hành Sandbox đã hình thành

### Ngày 61–90: Đánh giá và hướng tới chứng nhận

| Tuần | Hoạt động | Kết quả mong đợi |
|---|---|---|
| Tuần 9–10 | Làm lại khảo sát TDWCF | Thấy điểm tăng so với baseline |
| Tuần 10–11 | Hoàn thành Sandbox thứ 2 | Sandbox Hours ≥ 12h |
| Tuần 11–12 | Review IDP với Manager + đề xuất chứng nhận | Đủ điều kiện Foundation Cert |
| Tuần 12 | Admin/HR cấp Chứng nhận Foundation (nếu đủ điều kiện) | Cert đầu tiên trong hồ sơ |

**Mục tiêu cuối tháng 3:**
- TDWCF tăng ≥ 10 điểm so với baseline
- Có ít nhất 2 Sandbox Foundation hoàn thành
- Được cấp hoặc đang hướng tới Chứng nhận Foundation đầu tiên

---

## 18. Câu hỏi thường gặp (FAQ)

**Q: Điểm TDWCF của tôi từ đâu mà ra?**  
A: Điểm TDWCF được tính từ kết quả khảo sát TDWCF (6 domain × điểm từng câu hỏi). Xem Mục 7 để hiểu chi tiết cách tính. Khi bạn làm thêm Sandbox, đạt chứng nhận hoặc ghi nhận Impact, điểm cũng được cập nhật tự động (ngoài điểm khảo sát).

**Q: Tôi không thấy mục "Năng lực số" trong sidebar?**  
A: Kiểm tra 2 điều: (1) Tài khoản đã được gán role chưa? (2) Module "Năng lực số" đã được bật cho tổ chức chưa? Liên hệ IT Admin nếu cả 2 đều ổn mà vẫn không thấy.

**Q: Bao lâu thì nên làm lại khảo sát TDWCF?**  
A: Đề xuất **3–6 tháng/lần** hoặc sau khi hoàn thành đào tạo/chứng nhận quan trọng. Điểm mới sẽ phản ánh tiến bộ thực sự. Không nên làm lại quá sớm (dưới 1 tháng) vì điểm sẽ không thay đổi nhiều.

**Q: Tôi có thể xem hồ sơ của đồng nghiệp không?**  
A: Không, tài khoản `member` chỉ xem được hồ sơ cá nhân. Manager/Ops mới có thể xem hồ sơ toàn đội.

**Q: AI Gợi ý phát triển có chính xác không?**  
A: AI phân tích dựa trên điểm 6 domain + yêu cầu chức danh của bạn. Gợi ý mang tính định hướng, không phải bắt buộc. Bạn nên thảo luận với Manager để điều chỉnh phù hợp với bối cảnh thực tế. Gợi ý chính xác hơn khi: (1) điểm TDWCF phản ánh đúng thực trạng, (2) chức danh được gán đúng.

**Q: Chứng nhận AI có giá trị ra ngoài công ty không?**  
A: Chứng nhận hiện tại do tổ chức cấp nội bộ. Tuy nhiên, kỹ năng và portfolio thực tế (Sandbox, case study, AI Impact) là bằng chứng năng lực có giá trị khi ứng tuyển vị trí mới. Đây là nền tảng để trong tương lai liên kết với chứng nhận quốc tế.

**Q: Sandbox mất bao lâu để hoàn thành?**  
A: Mỗi nhiệm vụ Sandbox ước tính 30–90 phút. Một môi trường Foundation thường có 3–5 nhiệm vụ = khoảng 2–5 giờ hoàn thành toàn bộ. Bạn có thể làm từng nhiệm vụ một, không cần làm liên tục.

**Q: Nếu điểm Sandbox thấp, có bị ảnh hưởng gì không?**  
A: Điểm Sandbox ảnh hưởng đến Workforce Trust Score (15% tỉ trọng). Không có hình phạt, nhưng điểm thấp sẽ phản ánh vào Trust Score và ảnh hưởng đến thứ hạng Leaderboard. Hệ thống khuyến khích làm lại để cải thiện.

**Q: Làm sao để tăng Impact Score nhanh nhất?**  
A: Ghi nhận **thường xuyên** và **có số liệu cụ thể**. Mỗi tuần ghi 1–2 lần sử dụng AI có kết quả đo được sẽ giúp Impact Score tăng dần theo tháng. Chất lượng mô tả (có số liệu, công cụ cụ thể) ảnh hưởng đến trọng số của từng entry.

**Q: Tôi có thể export dữ liệu hồ sơ của mình không?**  
A: Có. Trên trang Hồ sơ Digital Twin, bạn có thể yêu cầu Quản lý xuất PDF cá nhân để lưu. (Tự xuất chỉ khả dụng từ role ops trở lên.) Trong tương lai, nhân viên có thể tự xuất PDF từ trang hồ sơ cá nhân.

**Q: Hồ sơ Digital Twin có bị xóa khi tôi nghỉ việc không?**  
A: Hồ sơ được lưu trữ theo quy định của tổ chức. Khi nghỉ việc, bạn có thể yêu cầu HR xuất PDF hồ sơ trước khi tài khoản bị vô hiệu hóa.

**Q: Tôi vừa đổi vị trí, điểm Skill Gap thay đổi như thế nào?**  
A: Ngay sau khi HR cập nhật chức danh mới, hệ thống tự động tính lại Skill Gap theo yêu cầu chức danh mới. Điểm TDWCF của bạn không thay đổi, nhưng gap có thể tăng lên nếu chức danh mới có yêu cầu cao hơn. Đây là tín hiệu để cập nhật kế hoạch phát triển.

**Q: Tôi không đồng ý với điểm AI phân tích — phải làm gì?**  
A: Điểm TDWCF đến từ khảo sát tự đánh giá, nên phản ánh cảm nhận của bạn. Nếu bạn thấy gợi ý AI không phù hợp với thực tế công việc → thảo luận với Manager trong buổi 1-1 để điều chỉnh IDP thủ công. Bạn cũng có thể làm lại khảo sát nếu nghĩ lần trước trả lời chưa chính xác.

---

## 19. Phụ lục A: Bảng chỉ số tham chiếu nhanh

### Điểm TDWCF theo cấp chức danh (system default)

| Chức danh | Cấp | D3 AI yêu cầu | Tổng gap target |
|---|---|---|---|
| Thực tập sinh | 1–2 | 15 | Thấp |
| Nhân viên | 3–5 | 25 | Trung bình |
| Chuyên viên | 6–7 | 45 (critical) | Cao |
| Chuyên viên cấp cao | 8–9 | 55 (critical) | Cao |
| Tư vấn viên | 10–11 | 60 | Cao |
| Giám sát / Trưởng nhóm | 12–13 | 68 | Cao |
| Phó phòng / Trưởng phòng | 14–15 | 72 (critical) | Rất cao |
| Phó Giám đốc / Giám đốc | 16–17 | 78 | Rất cao |
| Tổng Giám đốc / C-Level | 18–20 | 83 | Rất cao |

### Màu sắc cấp độ trên giao diện

| Màu | Cấp độ | Điểm TDWCF | Ý nghĩa |
|---|---|---|---|
| ⬜ Xám | Digital Beginner | 0–34 | Chưa bắt đầu |
| 🔵 Xanh dương | Digital Aware | 35–54 | Đang tìm hiểu |
| 🟡 Vàng | Digital Practitioner | 55–69 | Đang thực hành |
| 🟢 Xanh lá | Digital Professional | 70–84 | Thành thạo |
| 🟣 Tím | Digital Leader | 85–100 | Dẫn dắt |

### Bảng tóm tắt quyền theo role

| Chức năng | member | ops | manager | ceo | system_admin |
|---|---|---|---|---|---|
| Xem hồ sơ cá nhân | ✓ | ✓ | ✓ | ✓ | ✓ |
| Làm Sandbox | ✓ | ✓ | ✓ | ✓ | ✓ |
| Ghi AI Impact | ✓ | ✓ | ✓ | ✓ | ✓ |
| Xem hồ sơ tất cả NV | — | ✓ | ✓ | ✓ | ✓ |
| Xuất Excel/PDF NV | — | ✓ | ✓ | ✓ | ✓ |
| Workforce Admin | — | ✓ | ✓ | ✓ | ✓ |
| Cấp chứng nhận | — | — | — | — | ✓ |
| Cấu hình Sandbox | — | — | — | — | ✓ |
| Cấu hình Pathway | — | — | — | — | ✓ |

### Checklist khởi động theo vai trò

**Nhân viên mới (tuần 1):**
- [ ] Đăng nhập thành công, thấy "Năng lực số" trong sidebar
- [ ] Làm khảo sát TDWCF hoàn chỉnh (không bỏ qua câu hỏi nào)
- [ ] Xem hồ sơ Digital Twin của mình
- [ ] Đọc Skill Gap và biết domain mình yếu nhất
- [ ] Nhấn "Tạo gợi ý mới" để có AI Gợi ý phát triển đầu tiên

**Manager mới tiếp nhận team:**
- [ ] Vào Workforce Admin, xem toàn bộ hồ sơ team
- [ ] Lọc Cấp độ "Khởi đầu" → biết ai cần ưu tiên
- [ ] Xuất Excel toàn tổ chức → lưu baseline
- [ ] Xem hồ sơ từng nhân viên → tạo gợi ý AI cho từng người
- [ ] Lên lịch review 1-1 với từng nhân viên trong 2 tuần đầu

---

## 20. Phụ lục B: Bảng thuật ngữ (Glossary)

| Thuật ngữ | Viết tắt | Định nghĩa |
|---|---|---|
| **Workforce Digital Twin** | WDT | Hồ sơ kỹ thuật số đại diện cho năng lực số của một nhân viên |
| **Total Digital Workforce Competency Framework** | TDWCF | Khung đánh giá 6 năng lực số cốt lõi; cũng là điểm tổng hợp từ 6 domain |
| **TDWCF Score** | — | Điểm tổng hợp năng lực số từ 0–100, tính từ kết quả khảo sát |
| **Workforce Trust Score** | Trust Score | Điểm tổng hợp độ tin cậy: TDWCF(30%) + Cert(25%) + KPI(20%) + Sandbox(15%) + Portfolio(10%) |
| **AI Readiness Score** | AI Score | Chỉ số mức độ sẵn sàng làm việc với AI, tập trung vào D3, D4 và Sandbox |
| **Impact Score** | — | Điểm đo lường mức độ ứng dụng AI thực tế, tính từ tần suất và chất lượng AI Impact entries |
| **Digital Literacy** | D1 | Năng lực số cơ bản: công cụ văn phòng, bảo mật, thiết bị |
| **Data Literacy** | D2 | Năng lực dữ liệu: đọc, phân tích, trình bày dữ liệu |
| **AI Literacy** | D3 | Năng lực AI: hiểu và ứng dụng AI vào công việc |
| **Workflow Automation** | D4 | Quy trình & Tự động hoá: tự động hoá task, tối ưu quy trình |
| **Digital Innovation** | D5 | Đổi mới & Sáng kiến: tư duy sáng tạo, đề xuất cải tiến |
| **Digital Performance** | D6 | Hiệu suất & KPI: đặt mục tiêu, đo lường và cải thiện bằng công cụ số |
| **Skill Gap** | — | Khoảng cách giữa điểm hiện tại và yêu cầu của chức danh đang giữ |
| **Critical domain** | — | Domain bắt buộc phải đạt ngưỡng yêu cầu cho chức danh đang giữ |
| **Sandbox** | — | Bài tập thực hành AI theo tình huống thực tế, được thiết kế theo từng lĩnh vực |
| **AI Impact** | — | Ghi nhận một lần sử dụng AI có kết quả đo được trong công việc thực tế |
| **Certification** | Cert | Chứng nhận AI được cấp sau khi đủ điều kiện, theo 4 cấp × 7 lĩnh vực |
| **Career Pathway** | — | Lộ trình phát triển nghề nghiệp số gồm 5 bước, từ Nền tảng đến Dẫn dắt |
| **IDP** | IDP | Individual Development Plan — Kế hoạch Phát triển Cá nhân, xây dựng từ dữ liệu hồ sơ |
| **Leaderboard** | — | Bảng xếp hạng nhân viên theo Workforce Trust Score |
| **Digital Beginner** | — | Cấp độ 1: TDWCF 0–34, chưa tiếp cận AI |
| **Digital Aware** | — | Cấp độ 2: TDWCF 35–54, đã biết AI nhưng chưa dùng thành thạo |
| **Digital Practitioner** | — | Cấp độ 3: TDWCF 55–69, dùng AI thường xuyên trong công việc |
| **Digital Professional** | — | Cấp độ 4: TDWCF 70–84, thành thạo và có thể hướng dẫn người khác |
| **Digital Leader** | — | Cấp độ 5: TDWCF 85–100, dẫn dắt chiến lược AI cho tổ chức |
| **Job Title** | — | Chức danh được gán cho nhân viên trong hệ thống HR, dùng để tính Skill Gap |
| **Baseline** | — | Điểm TDWCF đo lần đầu, dùng để so sánh tiến bộ về sau |

---

*Tài liệu này được soạn thảo để phục vụ đào tạo nội bộ. Mọi thắc mắc về hệ thống, liên hệ phòng IT hoặc HR.*

*Phiên bản 2.0 — Cập nhật 13/06/2026 — Hệ thống: Workforce Digital Twin*
