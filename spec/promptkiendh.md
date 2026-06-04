Tôi có hệ thống SaaS SME Laravel 11 + Alpine.js với các bảng/module sẵn có sau:

**Bảng/module đã có trong hệ thống:**
- Modules/Organization
- Modules/Assessment
- Modules/Auth
- Modules/Branch
- Modules/Department
- Modules/Employee
- Modules/JobTitle
- Modules/Lead
- Modules/LeadPipelineStage
- Modules/OrgChart
- Modules/PerformanceReview
- Modules/Project
- Modules/Survey
- Modules/User
- Modules/WorkflowAutomation
- Modules/ActivityLog

**Yêu cầu:**
Đọc file spec/kc.md cho module Knowledge Center và spec/sop.md cho module SOP, sau đó:
1. Xác định các bảng/trường đang bị TRÙNG LẶP với hệ thống hiện có
2. Xác định FK nào cần điều chỉnh để reference đúng bảng sẵn có
3. Loại bỏ hoặc gộp bảng không cần thiết
4. Cập nhật lại ERD và đặc tả — giữ nguyên logic nghiệp vụ, chỉ tối ưu cấu trúc
5. Xuất file .md mới đã được cập nhật vào trong 2 file tương ứng spec/kc.md và spec/sop.md 

**Lưu ý**
Trong thiết kế bảng, tuyệt đối và hạn chế không sử dụng json để lưu data, vì hạn chế trong query, nữa là thiết kế bảng luôn có cột id bigtin và cột uuid nhé
$table->id();
$table->uuid()->nullable()->unique()->comment('Public UUID — expose ra ngoài, không phải PK');