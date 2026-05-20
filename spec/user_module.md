**Đường dẫn dự án:** `/var/www/html/minhan`

## ÁP DỤNG MODULE LIST PATTERN CHO MODULE USER (DANH SÁCH USER)

Module Organization đã triển khai hoàn chỉnh danh sách theo **Module List Pattern** (file `/var/www/html/minhan/docs/module-list-pattern.md`).

Bây giờ hãy **áp dụng y hệt mô hình, kỹ thuật và cấu trúc** đó cho **module User** — chức năng danh sách người dùng.

### Yêu cầu cụ thể:

- Áp dụng **đầy đủ và chính xác** Module List Pattern đã định nghĩa:
  - Backend: `ListUsersQuery`, `ListUsersHandler` (CQRS-lite), ApiController, formatRow, sort whitelist, withoutTenant / organization scope.
  - Frontend: Tabulator + AlpineJS + Tom Select + Flatpickr, server-side processing, persist filter state (URL), localStorage cho UI preference, active chips, esc() helper, ward cascade (nếu cần), date preset…
  - Tất cả select trong filter **bắt buộc dùng Tom Select**.
  - Hỗ trợ advanced filtering: search string, province_code, ward_code, role, status, created_at (hôm nay, tuần này, tháng này, năm nay, date range).

- Danh sách User phải tuân thủ **multi-tenant** (filter theo organization hiện tại, admin có thể xem tất cả).

- Code phải sạch, tái sử dụng cao, theo đúng AVSA + CQRS-lite.

- Sau khi hoàn thành, danh sách User phải có chất lượng ngang bằng (hoặc tốt hơn) danh sách Organization.

Bạn đã hiểu rõ yêu cầu áp dụng toàn bộ Module List Pattern cho chức năng danh sách User chưa? Hãy xác nhận và tóm tắt những file chính bạn sẽ tạo/sửa trước khi bắt đầu.