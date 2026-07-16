<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum as P;
use App\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->createAllPermissions();
        $this->createRolesWithPermissions();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    // ── Tạo toàn bộ permissions từ PermissionEnum ─────────────────────

    private function createAllPermissions(): void
    {
        foreach (P::cases() as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission->value,
                'guard_name' => 'web',
            ]);
        }
    }

    // ── Map role → danh sách permission values ────────────────────────

    private function createRolesWithPermissions(): void
    {
        foreach ($this->rolePermissionMap() as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($permissions);
        }
    }

    // ── Bảng phân quyền đầy đủ theo File 03 User Role Analysis ────────

    private function rolePermissionMap(): array
    {
        return [

            // ─────────────────────────────────────────────────────────
            // CEO / Founder — Full visibility, approve SOP, manage leads
            // ─────────────────────────────────────────────────────────
            RoleEnum::CEO->value => [
                P::CEO_DASH_FULL->value,

                P::LEADS_VIEW_ALL->value,
                P::LEADS_CREATE->value,
                P::LEADS_EDIT->value,
                P::LEADS_DELETE->value,
                P::LEADS_ASSIGN->value,
                P::LEADS_EXPORT->value,

                P::CUSTOMERS_VIEW_ALL->value,
                P::CUSTOMERS_CREATE->value,
                P::CUSTOMERS_EDIT->value,
                P::CUSTOMERS_DELETE->value,
                P::CUSTOMERS_EXPORT->value,

                P::SALES_AI_VIEW->value,

                P::TASKS_VIEW_ALL->value,
                P::TASKS_CREATE->value,
                P::TASKS_EDIT->value,
                P::TASKS_ASSIGN->value,
                P::TASKS_CLOSE->value,

                P::SOP_VIEW->value,
                P::SOP_APPROVE->value,

                P::WORKFLOW_MONITOR->value,

                P::PROMPT_VIEW->value,

                P::AI_LOGS_VIEW->value,

                P::USERS_VIEW->value,

                P::REPORTS_FULL->value,

                P::AUDIT_VIEW->value,

                // Assessment: view + results
                P::ASSESSMENT_VIEW->value,
                P::ASSESSMENT_RESULTS->value,

                // Job Posting: full
                P::JOB_POSTING_VIEW->value,
                P::JOB_POSTING_CREATE->value,
                P::JOB_POSTING_EDIT->value,
                P::JOB_POSTING_DELETE->value,
                P::JOB_POSTING_PUBLISH->value,

                // Recruitment: view
                P::RECRUITMENT_VIEW->value,

                // Marketplace: view
                P::MARKETPLACE_VIEW->value,

                // Business Project (BCOS): Full — Founder duyệt mọi Deliverable (Phần 7.2 spec)
                P::BUSINESS_PROJECT_VIEW->value,
                P::BUSINESS_PROJECT_CREATE->value,
                P::BUSINESS_CONTEXT_MANAGE->value,
                P::BUSINESS_CONTEXT_APPROVE->value,
                P::BUSINESS_DIAGNOSIS_MANAGE->value,
                P::BUSINESS_DISCOVERY_MANAGE->value,
                P::BUSINESS_TRANSFORMATION_MANAGE->value,
                P::BUSINESS_DELIVERY_MANAGE->value,
                P::BUSINESS_CLOSING_MANAGE->value,
                P::BUSINESS_KNOWLEDGE_MANAGE->value,
                P::BUSINESS_CUSTOMER_SUCCESS_MANAGE->value,
            ],

            // ─────────────────────────────────────────────────────────
            // Sales Team — Assigned leads, own tasks, AI assist
            // ─────────────────────────────────────────────────────────
            RoleEnum::SALES->value => [
                P::LEADS_VIEW_ASSIGNED->value,
                P::LEADS_CREATE->value,
                P::LEADS_EDIT->value,

                P::CUSTOMERS_VIEW_ASSIGNED->value,
                P::CUSTOMERS_CREATE->value,
                P::CUSTOMERS_EDIT->value,

                P::SALES_AI_USE->value,

                P::TASKS_VIEW_ASSIGNED->value,
                P::TASKS_CREATE->value,

                P::SOP_VIEW_RELATED->value,

                P::WORKFLOW_VIEW_LIMITED->value,

                P::REPORTS_PERSONAL->value,
                P::REPORTS_TEAM->value,
            ],

            // ─────────────────────────────────────────────────────────
            // Operations — Full task control, SOP authoring, workflow monitoring
            // ─────────────────────────────────────────────────────────
            RoleEnum::OPS->value => [
                P::CEO_DASH_VIEW->value,

                P::LEADS_VIEW_ALL->value,
                P::LEADS_EXPORT->value,
                P::LEADS_MANAGE_TAGS->value,

                P::CUSTOMERS_VIEW_ALL->value,
                P::CUSTOMERS_CREATE->value,
                P::CUSTOMERS_EDIT->value,
                P::CUSTOMERS_EXPORT->value,

                P::TASKS_VIEW_ALL->value,
                P::TASKS_CREATE->value,
                P::TASKS_EDIT->value,
                P::TASKS_ASSIGN->value,
                P::TASKS_CLOSE->value,

                P::SOP_VIEW->value,
                P::SOP_CREATE->value,
                P::SOP_EDIT->value,

                P::WORKFLOW_MONITOR->value,
                P::WORKFLOW_EDIT->value,

                P::AI_LOGS_VIEW->value,

                P::REPORTS_OPS->value,

                // Assessment: view + results
                P::ASSESSMENT_VIEW->value,
                P::ASSESSMENT_RESULTS->value,
            ],

            // ─────────────────────────────────────────────────────────
            // Marketing — Source-view leads, campaign tracking, limited tasks
            // ─────────────────────────────────────────────────────────
            RoleEnum::MARKETING->value => [
                P::LEADS_VIEW_SOURCE->value,

                P::CUSTOMERS_VIEW_ALL->value,

                P::SALES_AI_VIEW->value,

                P::TASKS_VIEW_LIMITED->value,
                P::TASKS_CREATE->value,

                P::SOP_VIEW_RELATED->value,

                P::WORKFLOW_VIEW_LIMITED->value,

                P::REPORTS_MARKETING->value,

                // Marketplace: view (monitor public listings)
                P::MARKETPLACE_VIEW->value,
            ],

            // ─────────────────────────────────────────────────────────
            // HR / Admin Staff — Onboarding, HR SOP, HR tasks
            // ─────────────────────────────────────────────────────────
            RoleEnum::HR->value => [
                P::TASKS_VIEW_DEPT->value,
                P::TASKS_CREATE->value,

                P::SOP_VIEW->value,
                P::SOP_CREATE_HR->value,

                P::WORKFLOW_VIEW_LIMITED->value,

                P::USERS_HR->value,

                P::REPORTS_HR->value,

                // Job Posting: full
                P::JOB_POSTING_VIEW->value,
                P::JOB_POSTING_CREATE->value,
                P::JOB_POSTING_EDIT->value,
                P::JOB_POSTING_PUBLISH->value,

                // Recruitment: full
                P::RECRUITMENT_VIEW->value,
                P::RECRUITMENT_CREATE->value,
                P::RECRUITMENT_EDIT->value,
                P::RECRUITMENT_MANAGE->value,

                // Marketplace: full (manage listings + review applicants)
                P::MARKETPLACE_VIEW->value,
                P::MARKETPLACE_CREATE->value,
                P::MARKETPLACE_EDIT->value,
            ],

            // ─────────────────────────────────────────────────────────
            // AI Operator — Prompt management, AI logs, workflow AI config
            // ─────────────────────────────────────────────────────────
            RoleEnum::AI_OP->value => [
                P::CEO_DASH_VIEW->value,

                P::LEADS_VIEW_ALL->value,

                P::CUSTOMERS_VIEW_ALL->value,

                P::TASKS_VIEW_LIMITED->value,

                P::SOP_VIEW->value,
                P::SOP_AI_CONFIG->value,

                P::WORKFLOW_MONITOR->value,
                P::WORKFLOW_AI_CONFIG->value,

                P::PROMPT_FULL->value,

                P::AI_LOGS_FULL->value,

                P::REPORTS_AI_USAGE->value,

                // Assessment: full (config + reprocess)
                P::ASSESSMENT_VIEW->value,
                P::ASSESSMENT_CONFIG->value,
                P::ASSESSMENT_RESULTS->value,
                P::ASSESSMENT_REPROCESS->value,
            ],

            // ─────────────────────────────────────────────────────────
            // System Admin — Full config access, no business data access
            // ─────────────────────────────────────────────────────────
            RoleEnum::ADMIN->value => [
                P::CEO_DASH_VIEW->value,
                P::CEO_DASH_CONFIG->value,

                P::LEADS_CONFIG->value,
                P::LEADS_VIEW_ALL->value,
                P::LEADS_MANAGE_PIPELINE->value,
                P::LEADS_MANAGE_SOURCES->value,
                P::LEADS_MANAGE_TAGS->value,

                P::CUSTOMERS_CONFIG->value,
                P::CUSTOMERS_VIEW_ALL->value,

                P::SALES_AI_CONFIG->value,

                P::TASKS_CONFIG->value,

                P::SOP_CONFIG->value,

                P::WORKFLOW_MONITOR->value,
                P::WORKFLOW_EDIT->value,
                P::WORKFLOW_FULL_CONFIG->value,

                P::PROMPT_ADMIN_CONFIG->value,

                P::AI_LOGS_FULL->value,

                P::USERS_MANAGE->value,
                P::ROLES_MANAGE->value,

                P::REPORTS_FULL->value,

                P::INTEGRATION_MANAGE->value,
                P::AUDIT_VIEW->value,
                P::SYSTEM_CONFIG->value,

                // Assessment: full
                P::ASSESSMENT_VIEW->value,
                P::ASSESSMENT_CONFIG->value,
                P::ASSESSMENT_RESULTS->value,
                P::ASSESSMENT_REPROCESS->value,

                // Job Posting: full manage
                P::JOB_POSTING_VIEW->value,
                P::JOB_POSTING_CREATE->value,
                P::JOB_POSTING_EDIT->value,
                P::JOB_POSTING_DELETE->value,
                P::JOB_POSTING_PUBLISH->value,
                P::JOB_POSTING_MANAGE->value,

                // Recruitment: full manage
                P::RECRUITMENT_VIEW->value,
                P::RECRUITMENT_CREATE->value,
                P::RECRUITMENT_EDIT->value,
                P::RECRUITMENT_MANAGE->value,

                // Marketplace: full manage (approve orgs, global view)
                P::MARKETPLACE_VIEW->value,
                P::MARKETPLACE_CREATE->value,
                P::MARKETPLACE_EDIT->value,
                P::MARKETPLACE_MANAGE->value,

                // Vertical templates: full manage (dashboard/vertical-templates — thư viện mẫu)
                P::VERTICAL_TEMPLATES_MANAGE->value,

                // Business Project (BCOS): Config — System Admin hỗ trợ, không thay Founder/Lead Consultant
                P::BUSINESS_PROJECT_VIEW->value,
                P::BUSINESS_PROJECT_MANAGE->value,
            ],

            // ─────────────────────────────────────────────────────────
            // Viewer / Partner — Read-only, shared reports
            // ─────────────────────────────────────────────────────────
            RoleEnum::VIEWER->value => [
                P::CEO_DASH_VIEW->value,
                P::TASKS_VIEW_LIMITED->value,
                P::SOP_VIEW->value,
                P::REPORTS_SHARED->value,
            ],

            // ─────────────────────────────────────────────────────────
            // Business Consulting OS (BCOS) — vai trò trong Business Project.
            // Global role để Ringlesoft laravel-process-approval route theo Spatie Role;
            // giới hạn "chỉ project được phân công" nằm ở Policy (business_project_members),
            // KHÔNG nằm ở permission toàn cục này (đúng 2-lớp phân quyền, Phần 7.1 spec).
            // ─────────────────────────────────────────────────────────
            RoleEnum::LEAD_CONSULTANT->value => [
                P::BUSINESS_PROJECT_VIEW->value,
                P::BUSINESS_PROJECT_CREATE->value,
                P::BUSINESS_CONTEXT_MANAGE->value,
                P::BUSINESS_CONTEXT_APPROVE->value,
                P::BUSINESS_DIAGNOSIS_MANAGE->value,
                P::BUSINESS_DISCOVERY_MANAGE->value,
                P::BUSINESS_TRANSFORMATION_MANAGE->value,
                P::BUSINESS_DELIVERY_MANAGE->value,
                P::BUSINESS_CLOSING_MANAGE->value,
                P::BUSINESS_KNOWLEDGE_MANAGE->value,
            ],

            RoleEnum::CONSULTANT->value => [
                P::BUSINESS_PROJECT_VIEW->value,
                P::BUSINESS_CONTEXT_MANAGE->value,
                P::BUSINESS_DIAGNOSIS_MANAGE->value,
                P::BUSINESS_DISCOVERY_MANAGE->value,
                P::BUSINESS_TRANSFORMATION_MANAGE->value,
                P::BUSINESS_DELIVERY_MANAGE->value,
            ],

            // BA không quản lý Diagnosis (Ma trận Phần 7.2: hàng "Gửi phê duyệt Diagnosis" là "—"
            // cho BA, chỉ Founder/Lead Consultant/Consultant).
            RoleEnum::BUSINESS_ANALYST->value => [
                P::BUSINESS_PROJECT_VIEW->value,
                P::BUSINESS_CONTEXT_MANAGE->value,
                P::BUSINESS_DISCOVERY_MANAGE->value,
            ],

            // PM không quản lý Context/Discovery (Ma trận Phần 7.2: hàng "Nhập Context/Discovery"
            // là "—" cho PM) — chỉ tham gia từ Transformation trở đi (ghi nhận + tick Confirmed
            // R4; Weekly Report/Issue/Risk ở Delivery; Đóng dự án R6/R7 — matrix "✅" cho PM ở cả
            // 3 hàng này, khác Consultant/BA/Customer Success đều "—" ở hàng Đóng dự án).
            RoleEnum::PROJECT_MANAGER->value => [
                P::BUSINESS_PROJECT_VIEW->value,
                P::BUSINESS_TRANSFORMATION_MANAGE->value,
                P::BUSINESS_DELIVERY_MANAGE->value,
                P::BUSINESS_CLOSING_MANAGE->value,
                P::BUSINESS_KNOWLEDGE_MANAGE->value,
            ],

            // Ma trận Phần 7.2 "CSAT/NPS, Renewal, tạo Lead mới": chỉ Founder + Customer Success
            // (Lead Consultant/Consultant/BA/PM đều "—") — role Customer Success chỉ cần
            // đúng 1 quyền workspace này, không cần các quyền workspace khác.
            RoleEnum::CUSTOMER_SUCCESS->value => [
                P::BUSINESS_PROJECT_VIEW->value,
                P::BUSINESS_CUSTOMER_SUCCESS_MANAGE->value,
            ],
        ];
    }
}
