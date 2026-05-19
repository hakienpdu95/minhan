**Đường dẫn dự án:** `/var/www/html/minhan`

## IMPORT DỮ LIỆU TỈNH THÀNH - PHƯỜNG XÃ

Trước khi thực hiện bất kỳ yêu cầu nào liên quan đến import dữ liệu tỉnh/thành phố/phường/xã, bạn **hãy đọc kỹ** code của file sau:

- **File Command:** `/var/www/html/minhan/app/Console/Commands/ImportProvincesAndWards.php`  
  → File này xử lý logic import dữ liệu đã có vào 3 bảng: `regions`, `provinces`, `wards`.

- **File dữ liệu:** `/var/www/html/minhan/datafiles/provinces.json`  
  → Đây là file JSON chứa thông tin sẵn về vùng, tỉnh/thành phố và phường/xã.

### Yêu cầu cụ thể:
- Review toàn bộ code của `ImportProvincesAndWards.php`.
- Kiểm tra và tạo model nếu chưa tồn tại:
  - `Region` (bảng regions)
  - `Province` (bảng provinces)
  - `Ward` (bảng wards)
- Chỉnh sửa / tối ưu command `ImportProvincesAndWards.php` để command có thể chạy và chèn dữ liệu **đúng, đầy đủ, hiệu suất cao** vào đúng 3 bảng: `regions`, `provinces`, `wards`.
- Tối ưu hiệu suất: sử dụng transaction, chunk insert, disable foreign key check tạm thời nếu cần, tránh query N+1, sử dụng bulk insert khi có thể.
- Đảm bảo dữ liệu được import idempotent (chạy lại nhiều lần không bị trùng lặp).
- Giữ nguyên phong cách code và cấu trúc project hiện tại.

### Thông tin 3 bảng được định nghĩa trong `render_migration_file.json`:

```json
[
  "regions///__///__///Bảng lưu thông tin vùng",
  "name///string///255///NOT_NULL///__///->index()///Tên vùng",
  "created_at///timestamp///__///_NULL///NULL///__///Thời gian tạo",
  "updated_at///timestamp///__///_NULL///NULL///__///Thời gian cập nhật",
  "deleted_at///timestamp///__///_NULL///NULL///__///Thời gian xóa mềm"
],

[
  "provinces///__///__///Bảng lưu thông tin tỉnh/thành phố",
  "name///string///255///NOT_NULL///__///->index()///Tên tỉnh/thành phố",
  "short_name///string///255///NOT_NULL///__///__///Tên ngắn gọn của tỉnh/thành phố",
  "logo///string///255///_NULL///NULL///__///Logo tỉnh",
  "province_code///char///2///NOT_NULL///__///->unique()->index()///Mã tỉnh/thành phố",
  "place_type///enum///[thanh-pho,tinh]///NOT_NULL///'tinh'///->index()///Loại: Thành phố Trung Ương hoặc Tỉnh",
  "region_id///unsignedBigInteger///__///NOT_NULL///__///->constrained('regions')->onDelete('cascade')///Thuộc vùng — FK tới regions.id",
  "country///string///2///NOT_NULL///'VN'///->index()///Mã quốc gia",
  "is_active///boolean///__///NOT_NULL///true///->index()///Trạng thái hoạt động",
  "created_at///timestamp///__///_NULL///NULL///__///Thời gian tạo",
  "updated_at///timestamp///__///_NULL///NULL///__///Thời gian cập nhật",
  "deleted_at///timestamp///__///_NULL///NULL///__///Thời gian xóa mềm",
  "__index///index///__///__///__///region_id///Index cho FK region_id"
],

[
  "wards///__///__///Bảng lưu thông tin phường/xã",
  "name///string///255///NOT_NULL///__///->index()///Tên phường/xã",
  "ward_code///char///5///NOT_NULL///__///->unique()->index()///Mã phường/xã",
  "place_type///enum///[phuong,xa,dac-khu]///NOT_NULL///'xa'///->index()///Loại: phường, xã, đặc khu",
  "province_code///char///2///NOT_NULL///__///->references('province_code')->constrained('provinces')->onDelete('cascade')///Tỉnh/thành phố liên kết — FK tới provinces.province_code",
  "is_active///boolean///__///NOT_NULL///true///->index()///Trạng thái hoạt động",
  "created_at///timestamp///__///_NULL///NULL///__///Thời gian tạo",
  "updated_at///timestamp///__///_NULL///NULL///__///Thời gian cập nhật",
  "deleted_at///timestamp///__///_NULL///NULL///__///Thời gian xóa mềm",
  "__index///index///__///__///__///province_code///Index cho FK province_code"
]