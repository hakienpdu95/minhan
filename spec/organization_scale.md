**Đường dẫn dự án:** `/var/www/html/minhan`

## REVIEW MODULE ORGANIZATION + BỔ SUNG CHỨC NĂNG CHỌN TỈNH THÀNH - PHƯỜNG XÃ

Trước khi thực hiện bất kỳ thay đổi nào, bạn **hãy scan và review toàn bộ code** trong thư mục module sau:

- **Module Organization:** `/var/www/html/minhan/Modules/Organization/`

### Yêu cầu review chi tiết:
- Kiểm tra xem module đã tuân thủ **đúng và đầy đủ** cấu trúc **Advanced Vertical Slice (AVSA)** đã định nghĩa chưa (Actions, Data/Requests, Models, Traits, Providers, Routes, Middleware…).
- Đánh giá việc áp dụng **CQRS-lite + Laravel Actions** có linh hoạt, sạch sẽ và đúng chuẩn không (mọi logic business có đi qua Action không).
- Kiểm tra tính nhất quán, tối ưu hiệu suất, single responsibility và tuân thủ các Key Rules đã nêu trong Organization Module Specification.
- Liệt kê rõ những điểm đã làm tốt và những điểm còn thiếu / cần cải tiến (nếu có).

### Yêu cầu bổ sung chức năng mới (quan trọng):
Bổ sung thông tin **địa chỉ** cho Organization với tính năng chọn **Tỉnh/Thành phố – Phường/Xã động**:

- Sử dụng dữ liệu đã import từ command `/var/www/html/minhan/app/Console/Commands/ImportProvincesAndWards.php` và 3 bảng: `regions`, `provinces`, `wards`.
- Trong Model `Organization.php` cần bổ sung các trường sau (nếu chưa có):
  - `province_code` (char 2) → FK tới `provinces.province_code`
  - `ward_code` (char 5) → FK tới `wards.ward_code`
  - `full_address` (text) → địa chỉ chi tiết
  - `country` (string 2, default 'VN')

- Trong form tạo/sửa Organization (CreateOrganizationData, OrganizationSettingsData, các Action liên quan…):
  - Có dropdown chọn **Tỉnh/Thành phố** (`province`).
  - Khi chọn tỉnh/thành phố → load động danh sách **Phường/Xã** tương ứng theo `province_code`.

- Thực hiện theo đúng **Advanced Vertical Slice (AVSA)**:
  - Tạo Action mới nếu cần (ví dụ: `GetWardsByProvinceAction.php`, `UpdateOrganizationAddressAction.php`…).
  - Sử dụng Data class trong `Data/Requests/`.
  - Nếu cần API endpoint để load wards động thì tạo trong `Routes/api.php` và Action tương ứng.

**Quy tắc bắt buộc:**
- Claude **chỉ được tạo/sửa file bên trong** `Modules/Organization/`.
- Tất cả thay đổi schema (nếu có) phải tuân thủ quy tắc qua `render_migration_file.json` và `render_extension_file.json`.
- Giữ nguyên phong cách code và cấu trúc AVSA đã áp dụng trong module.

Sau khi review xong, bạn hãy:
1. Báo cáo kết quả review ngắn gọn (điểm mạnh – điểm cần cải tiến).
2. Đề xuất cách triển khai chức năng chọn Tỉnh/Thành – Phường/Xã.
3. Bắt đầu thực hiện các thay đổi (nếu cần) theo thứ tự hợp lý.

Bạn đã hiểu rõ toàn bộ nhiệm vụ review module Organization và bổ sung chức năng tỉnh thành - phường xã chưa?