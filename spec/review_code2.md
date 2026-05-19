## DATA SEEDING (Thiết lập dữ liệu mặc định)

**Mục tiêu:**  
Toàn bộ dữ liệu mặc định (default / initial data) của hệ thống phải được quản lý **tập trung, có thứ tự rõ ràng, idempotent** và chỉ cần chạy **một lệnh duy nhất**.

**Vấn đề hiện tại:**  
Hiện tại phải chạy nhiều lệnh seeder thủ công riêng lẻ theo thứ tự thủ công, không scalable khi phát triển thêm module mới.

**QUY TẮC BẮT BUỢC (phải tuân thủ 100%):**

1. **Chỉ được phép chạy tối đa 1 lệnh** để seed toàn bộ dữ liệu mặc định của hệ thống.
2. Tất cả dữ liệu mặc định **PHẢI** được seed thông qua **Master Seeder**.
3. Không được seed dữ liệu trực tiếp trong file migration.
4. Seeder phải **idempotent** (chạy nhiều lần không tạo dữ liệu trùng lặp). Ưu tiên dùng `firstOrCreate`, `updateOrCreate` hoặc kiểm tra tồn tại trước khi insert.

**Dữ liệu mặc định bắt buộc phải có:**
- **IAM (Role & Permission):** ~8 roles + ~40 permissions (dựa trên `RoleEnum` và `PermissionEnum`).
- **Auth:** 2 tài khoản Administrator / Super Admin có full quyền trong hệ thống.
- **Organization:** Organization demo / mặc định (nếu áp dụng).
- **Users test:** 8 user test (mỗi role 1 user) dùng cho mục đích phát triển và testing.

**Quy tắc với module mới (Task, Lead, Project, …):**
- Khi phát triển module mới, **có thể có hoặc không có** data seed tùy theo yêu cầu.
- Nếu module **cần** dữ liệu mặc định → bắt buộc tạo seeder riêng cho module đó và đăng ký vào Master Seeder.
- Nếu module **không cần** data seed → không phải tạo seeder.
- Mọi seeder của module mới đều phải được đăng ký theo đúng thứ tự dependency trong Master Seeder.

**Cấu trúc & Giải pháp khuyến nghị:**

- **Master Seeder** chính: `Database\Seeders\SystemDataSeeder.php`.
- Trong Master Seeder sẽ gọi các seeder con theo **thứ tự chính xác**:
  1. Role & Permission Seeder
  2. Auth / Administrator Accounts Seeder
  3. Organization Seeder
  4. User Test Seeder
  5. Các module seeder khác (theo dependency)

**Lệnh sử dụng mong muốn:**

```bash
# Seed dữ liệu mặc định
php artisan db:seed --class=Database\Seeders\SystemDataSeeder

# Hoặc lệnh tiện lợi hơn (nên tạo)
php artisan system:seed
```

