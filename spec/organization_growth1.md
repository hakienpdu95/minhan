**Đường dẫn dự án:** `/var/www/html/minhan`
**Module**: `Modules/Organization`

## REFACTOR MODULE ORGANIZATION - TẬP TRUNG QUẢN LÝ THÔNG TIN TỔ CHỨC

**Quyết định quan trọng:**
Chức năng **Invitation User** (mời người dùng vào organization) **không cần thiết** và không phù hợp với mục tiêu dự án hiện tại.

→ Bạn hãy **loại bỏ hoàn toàn** chức năng Invitation User ra khỏi module Organization, bao gồm:
- Model `OrganizationInvitation`
- Tất cả Action liên quan (InviteUserToOrganizationAction, AcceptInvitationAction…)
- Route, Controller, View (nếu có)
- Migration liên quan (nếu chỉ dùng cho invitation)
- Bất kỳ code nào liên quan đến invitation

**Mục tiêu mới của module:**
`Modules/Organization` chỉ tập trung quản lý **thông tin tổ chức/doanh nghiệp** (Organization CRUD + Settings).

### Các cải tiến còn lại cần thực hiện:

- **[M2] Backend Actions**: Thiết kế lại `StoreOrganizationAction` và `UpdateOrganizationAction` để sử dụng đúng `CreateOrganizationData` (không bypass DTO).
- **CQRS-lite**: Tạo Query classes cho `ListOrganizations` và `GetOrganization`.
- **Events**: Thay vì gọi `activity()->log()` trực tiếp trong Action, hãy bắn Event (`OrganizationCreated`, `OrganizationUpdated`, `MemberJoined`…).

**Quy tắc thực hiện:**
- Tuân thủ nghiêm ngặt kiến trúc **Advanced Vertical Slice (AVSA)** + Laravel Actions.
- Khi xóa code phải dọn dẹp sạch sẽ, không để lại file thừa hoặc reference lỗi.
- Sau khi refactor xong, module phải gọn nhẹ, rõ ràng và dễ mở rộng.

Bạn đã hiểu rõ toàn bộ yêu cầu refactor module Organization chưa? Hãy xác nhận và tóm tắt lại những gì bạn sẽ thực hiện trước khi bắt đầu.