<?php
// Thêm permission mới: chỉ sửa file này + chạy php artisan permissions:sync
use App\Enums\RoleEnum as R;
use App\Enums\PermissionEnum as P;

return [

    R::CEO->value => [
        // CEO Dashboard: Full
        P::CEO_DASH_FULL->value,
        // CRM Leads: Full (view all + CRUD + assign)
        P::LEADS_VIEW_ALL->value,
        P::LEADS_CREATE->value,
        P::LEADS_EDIT->value,
        P::LEADS_DELETE->value,
        P::LEADS_ASSIGN->value,
        // Sales AI: Full (view + use)
        P::SALES_AI_VIEW->value,
        P::SALES_AI_USE->value,
        // Tasks: Full
        P::TASKS_VIEW_ALL->value,
        P::TASKS_CREATE->value,
        P::TASKS_EDIT->value,
        P::TASKS_ASSIGN->value,
        P::TASKS_CLOSE->value,
        // SOP: Approve/View — KHÔNG có create/edit
        P::SOP_VIEW->value,
        P::SOP_APPROVE->value,
        // Workflow: Monitor only — KHÔNG có edit
        P::WORKFLOW_MONITOR->value,
        // Prompt: View only
        P::PROMPT_VIEW->value,
        // AI Logs: View summary
        P::AI_LOGS_VIEW->value,
        // Users: View
        P::USERS_VIEW->value,
        // Reports: Full
        P::REPORTS_FULL->value,
    ],

    R::SALES->value => [
        // CRM Leads: Assigned (view + edit chỉ của mình — Policy xử lý)
        P::LEADS_VIEW_ASSIGNED->value,
        P::LEADS_CREATE->value,
        P::LEADS_EDIT->value,       // Policy: chỉ edit lead assigned_to === user->id
        // Sales AI: Use (dùng output, không config)
        P::SALES_AI_USE->value,
        // Tasks: Assigned
        P::TASKS_VIEW_ASSIGNED->value,
        P::TASKS_CREATE->value,
        // SOP: View related (dept liên quan)
        P::SOP_VIEW_RELATED->value,
        // Workflow: Limited (visibility=public)
        P::WORKFLOW_VIEW_LIMITED->value,
        // Reports: Personal + Team
        P::REPORTS_PERSONAL->value,
        P::REPORTS_TEAM->value,
    ],

    R::OPS->value => [
        // CEO Dashboard: Limited (view, không có AI brief, không có approve)
        P::CEO_DASH_VIEW->value,
        // CRM Leads: Limited — view_all nhưng KHÔNG có edit/delete/assign
        P::LEADS_VIEW_ALL->value,
        // Tasks: Full team (view all + full CRUD + assign)
        P::TASKS_VIEW_ALL->value,
        P::TASKS_CREATE->value,
        P::TASKS_EDIT->value,
        P::TASKS_ASSIGN->value,
        P::TASKS_CLOSE->value,
        // SOP: Create/Edit
        P::SOP_VIEW->value,
        P::SOP_CREATE->value,
        P::SOP_EDIT->value,
        // Workflow: Monitor/Edit
        P::WORKFLOW_MONITOR->value,
        P::WORKFLOW_EDIT->value,
        // AI Logs: Limited (view, không full)
        P::AI_LOGS_VIEW->value,
        // Reports: Operations scope
        P::REPORTS_OPS->value,
    ],

    R::MARKETING->value => [
        // CRM Leads: SOURCE VIEW — KHÔNG phải Limited
        // Chỉ xem lead có lead_source, ẩn phone/email
        P::LEADS_VIEW_SOURCE->value,
        // Sales AI: Limited (view, không use/config)
        P::SALES_AI_VIEW->value,
        // Tasks: Limited (visibility=public only)
        P::TASKS_VIEW_LIMITED->value,
        // SOP: View related
        P::SOP_VIEW_RELATED->value,
        // Workflow: Limited
        P::WORKFLOW_VIEW_LIMITED->value,
        // Reports: Marketing scope
        P::REPORTS_MARKETING->value,
    ],

    R::HR->value => [
        // Tasks: HR tasks (dept=hr only)
        P::TASKS_VIEW_DEPT->value,
        P::TASKS_CREATE->value,
        // SOP: Create HR SOP (dept=hr only)
        P::SOP_VIEW->value,
        P::SOP_CREATE_HR->value,
        // Workflow: Limited
        P::WORKFLOW_VIEW_LIMITED->value,
        // Users: Limited (tạo user nội bộ, onboarding)
        P::USERS_HR->value,
        // Reports: HR scope
        P::REPORTS_HR->value,
    ],

    R::AI_OP->value => [
        // CEO Dashboard: Limited
        P::CEO_DASH_VIEW->value,
        // CRM Leads: Limited (view, không edit)
        P::LEADS_VIEW_ALL->value,
        // Sales AI: Config prompt
        P::SALES_AI_CONFIG_PROMPT->value,
        // Tasks: Limited
        P::TASKS_VIEW_LIMITED->value,
        // SOP: AI config (cấu hình AI flow trong SOP)
        P::SOP_AI_CONFIG->value,
        // Workflow: AI config
        P::WORKFLOW_AI_CONFIG->value,
        // Prompt Management: Full
        P::PROMPT_FULL->value,
        // AI Logs: Full
        P::AI_LOGS_FULL->value,
        // Reports: AI usage scope
        P::REPORTS_AI_USAGE->value,
    ],

    R::ADMIN->value => [
        // Config trên tất cả module (độc lập với data)
        P::CEO_DASH_CONFIG->value,
        P::LEADS_CONFIG->value,
        P::LEADS_VIEW_ALL->value,   // Để support — activity log ghi lại
        P::SALES_AI_CONFIG->value,
        P::TASKS_CONFIG->value,
        P::SOP_CONFIG->value,
        P::WORKFLOW_FULL_CONFIG->value,
        P::PROMPT_ADMIN_CONFIG->value,
        P::AI_LOGS_FULL->value,
        // User & roles management
        P::USERS_MANAGE->value,
        P::ROLES_MANAGE->value,
        // System
        P::INTEGRATION_MANAGE->value,
        P::AUDIT_VIEW->value,
        P::SYSTEM_CONFIG->value,
        P::REPORTS_FULL->value,
    ],

    R::VIEWER->value => [
        // CEO Dashboard: View limited
        P::CEO_DASH_VIEW->value,
        // Tasks: View limited (public only)
        P::TASKS_VIEW_LIMITED->value,
        // SOP: View limited
        P::SOP_VIEW->value,
        // Reports: Shared only
        P::REPORTS_SHARED->value,
        // CRM Leads = No, Workflow = No, Prompt = No
    ],
];