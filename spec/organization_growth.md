**Đường dẫn dự án:** `/var/www/html/minhan`

**Module**: `Modules/Organization`

## REVIEW NÂNG CAO: FORM VALIDATION UX VÀ JODIT EDITOR

**Yêu cầu review sâu và chi tiết:**

Bạn hãy scan và review toàn bộ code trong module Organization liên quan đến **Form Validation** và **Jodit Editor** trong project

### 1. Form Validation & UX Error Handling
- Kiểm tra cơ chế validate form hiện tại.
- Đánh giá xem đã có **thông báo lỗi ngay dưới input field** (inline validation) chưa.
- Thông báo lỗi có rõ ràng, thân thiện với người dùng không (ví dụ: “Tên tổ chức không được để trống”, “Mã số thuế không hợp lệ”…).
- Cơ chế validate có được thiết kế **tái sử dụng linh hoạt** chưa? Có thể áp dụng dễ dàng cho mọi form trong các module khác (Lead, Task, SOP, Proposal…) không?
- Đề xuất cách cải tiến để validation trở thành **component/form helper** chung, hỗ trợ required fields, custom messages, real-time validation (nếu có).

### 2. Jodit Editor Configuration & Optimization
- Review cấu hình Jodit Editor hiện tại.
- Đánh giá xem Jodit đã được tối ưu và sử dụng **linh hoạt** chưa, đặc biệt khi một form có **3–5 field** cần editor (ví dụ: mô tả, nội dung, ghi chú…).
- Code JavaScript thiết lập và khởi tạo Jodit có sạch sẽ, tối ưu, tái sử dụng chưa? Hay đang bị lặp code ở nhiều nơi?
- Cấu hình Jodit (toolbar, plugins, image upload, height, language, security…) đã phù hợp và nhất quán chưa?
- Đề xuất cách refactor để Jodit trở thành **reusable component** (có thể dễ dàng gọi với các config khác nhau).

### Yêu cầu chung khi review:
- Đánh giá tổng thể về **UX/UI friendliness**, tính **reusability**, **maintainability** và **performance**.
- Chỉ ra những điểm mạnh và những điểm còn hạn chế / tiềm ẩn rủi ro.
- Đề xuất giải pháp cải tiến cụ thể, code mẫu (nếu cần) theo đúng kiến trúc AVSA + Laravel Actions.
- Ưu tiên các giải pháp có thể áp dụng chung cho toàn bộ project.

Bạn đã hiểu rõ nhiệm vụ review nâng cao về Form Validation UX và Jodit Editor chưa?