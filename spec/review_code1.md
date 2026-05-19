# HƯỚNG DẪN LÀM VIỆC VỚI DỰ ÁN LARAVEL

**Đường dẫn dự án:** `/var/www/html/minhan`

## QUY TẮC BẮT BUỘC (Luôn phải tuân thủ 100%)

Trước khi trả lời **bất kỳ yêu cầu công việc nào** liên quan đến migration, schema, bảng, cột, bạn **PHẢI** đọc và nắm rõ code của 2 file sau:

### 1. File GenerateMigration.php
- Đường dẫn: `/var/www/html/minhan/app/Console/Commands/GenerateMigration.php`
- Chức năng: Xử lý logic sinh migration tự động.
- Nó sẽ scan file `/var/www/html/minhan/render_migration_file.json` để tạo các file migration định nghĩa bảng và cột.

### 2. File GenerateExtension.php
- Đường dẫn: `/var/www/html/minhan/app/Console/Commands/GenerateExtension.php`
- Chức năng: Xử lý logic sinh migration để **thêm mới hoặc modify cột** vào bảng đã tồn tại.
- Nó sẽ scan file `/var/www/html/minhan/render_extension_file.json`.

## FLOW CHẠY MIGRATION (RẤT QUAN TRỌNG)
Khi chạy lệnh:
```bash
php artisan migration:generate --fresh

```

→ Thứ tự phải luôn là:

Xử lý render_migration_file.json trước (tạo bảng mới)
Xử lý render_extension_file.json sau (thêm/modify cột)

Bạn hãy review code để đảm bảo khi phát triển thêm một module nào đó, thì phạm vi tạo bảng và cột phải thao tác tuân thủ ở trên 100% cho tối ưu, đồng nhất, dễ phát triển và tối ưu hiệu suất hệ thống, và gợi ý cải tiến luôn nhé.