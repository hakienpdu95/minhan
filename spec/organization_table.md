**Đường dẫn dự án:** `/var/www/html/minhan`
**Module**: `Modules/Organization`

## CHỨC NĂNG DANH SÁCH TỔ CHỨC (LIST ORGANIZATIONS) - TỐI ƯU HIỆU SUẤT CAO

Bây giờ chúng ta tập trung sâu vào **chức năng danh sách tổ chức** (List Organizations).

Package đã cài:
- `"tabulator-tables": "^6.4.0"`
- `"alpinejs": "^3.15.12"`

### Yêu cầu chính:
- Xây dựng danh sách tổ chức **tối ưu hiệu suất cao**, hỗ trợ tốt dữ liệu **trên 500.000 rows**.
- Sử dụng **Tabulator Tables** làm thư viện chính cho bảng (không dùng DataTable hoặc các thư viện khác).
- Áp dụng **server-side processing** (AJAX) để phân trang, lọc, sắp xếp trên server.
- Hỗ trợ lọc linh hoạt theo:
  - Tên tổ chức (name)
  - Tỉnh/Thành phố (`province_code`)
  - Phường/Xã (`ward_code`)
- Trong tương lai phải dễ dàng bổ sung thêm điều kiện lọc khác (ví dụ: status, region, created_at, subscription status…).

### Yêu cầu kỹ thuật chi tiết:

1. **Backend (Laravel)**
   - Sử dụng Query class theo CQRS-lite (`ListOrganizationsQuery`).
   - Hỗ trợ đầy đủ: pagination, sorting, filtering (name, province_code, ward_code).
   - Tối ưu query (indexing, eager loading, tránh N+1).
   - Trả về JSON theo format Tabulator mong đợi (`data`, `last_page`, `total`…).

2. **Frontend**
   - Sử dụng **Tabulator** + **AlpineJS** để render bảng.
   - Bật các tính năng: server-side pagination, filter, sort, search global.
   - Thiết kế giao diện sạch, responsive, có thể resize cột, freeze cột nếu cần.
   - Code JavaScript phải sạch sẽ, tái sử dụng (có thể dùng cho danh sách của các module khác sau này).

3. **Yêu cầu chung**
   - Hiệu suất phải mượt mà ngay cả khi có >500k records.
   - Lọc phải nhanh và chính xác (kết hợp nhiều điều kiện cùng lúc).
   - Code phải theo đúng kiến trúc **Advanced Vertical Slice (AVSA)**.
   - Dễ dàng mở rộng thêm filter trong tương lai.

Bạn hãy:
- Review code danh sách hiện tại (nếu có).
- Đề xuất và triển khai giải pháp tối ưu nhất với Tabulator + AlpineJS.
- Ưu tiên server-side processing để đảm bảo hiệu suất.

Bạn đã hiểu rõ yêu cầu xây dựng chức năng danh sách tổ chức với Tabulator hiệu suất cao chưa? Hãy xác nhận và tóm tắt cách bạn sẽ thực hiện trước khi bắt đầu.