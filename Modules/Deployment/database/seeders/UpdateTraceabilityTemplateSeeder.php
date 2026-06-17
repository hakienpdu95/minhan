<?php

namespace Modules\Deployment\Database\Seeders;

use App\Foundation\Vertical\VerticalTemplate;
use Illuminate\Database\Seeder;

/**
 * Sprint 4 patch — cập nhật template `traceability` theo hướng Survey-based:
 *   - bỏ production_* (khu/lô/cây) → has_physical_assets = false
 *   - data_collection_template_slug = data_collection_v1
 *   - phase surveying → collecting → standardizing → exporting → training → handover
 *   - checklist và sidebar phản ánh luồng thu thập dữ liệu qua survey
 */
class UpdateTraceabilityTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $template = VerticalTemplate::where('code', 'traceability')->first();

        if (! $template) {
            $this->command?->warn('[UpdateTraceabilityTemplate] Template traceability không tìm thấy — chạy TraceabilityTemplateSeeder trước.');
            return;
        }

        $template->update([
            'has_physical_assets'            => false,
            'data_collection_template_slug'  => 'data_collection_v1',

            'phases' => [
                'draft', 'surveying', 'collecting',
                'standardizing', 'exporting',
                'training', 'handover', 'completed',
            ],

            'default_checklist' => [
                'surveying'     => [
                    ['key' => 'entity_profile_verified',    'label' => 'Xác nhận thông tin tổ chức (Section A)',     'required' => true],
                    ['key' => 'data_collection_assigned',   'label' => 'Đã giao form thu thập dữ liệu',              'required' => true],
                    ['key' => 'field_survey_done',          'label' => 'Hoàn thành khảo sát thực địa',               'required' => true],
                    ['key' => 'gps_captured',               'label' => 'Thu thập GPS trung tâm vùng sản xuất',       'required' => true],
                    ['key' => 'photos_uploaded',            'label' => 'Upload ảnh thực địa',                        'required' => false],
                ],
                'collecting'    => [
                    ['key' => 'legal_docs_uploaded',        'label' => 'Upload hồ sơ pháp lý (ĐKKD, CCCD...)',      'required' => true],
                    ['key' => 'product_list_confirmed',     'label' => 'Danh mục sản phẩm đầy đủ (Section C)',      'required' => true],
                    ['key' => 'history_files_uploaded',     'label' => 'Upload file lịch sử hoạt động (nếu có)',    'required' => false],
                    ['key' => 'donvi_files_uploaded',       'label' => 'Upload file đơn vị chi tiết (nếu có)',      'required' => false],
                ],
                'standardizing' => [
                    ['key' => 'org_data_standardized',      'label' => 'Chuẩn hóa dữ liệu tổ chức vào org profile', 'required' => true],
                    ['key' => 'product_data_standardized',  'label' => 'Chuẩn hóa danh mục sản phẩm',              'required' => true],
                    ['key' => 'ai_validator_passed',        'label' => 'AI Validator đạt ≥ 90%',                    'required' => true],
                ],
                'exporting'     => [
                    ['key' => 'export_package_generated',   'label' => 'Tạo gói export CheckVN',                    'required' => true],
                    ['key' => 'export_reviewed',            'label' => 'Đã review file export',                     'required' => true],
                    ['key' => 'partner_import_confirmed',   'label' => 'Xác nhận đã nhập vào hệ thống đối tác',     'required' => true],
                ],
                'training'      => [
                    ['key' => 'login_trained',              'label' => 'Đào tạo đăng nhập hệ thống',                'required' => true],
                    ['key' => 'data_view_trained',          'label' => 'Đào tạo xem và kiểm tra dữ liệu',           'required' => true],
                    ['key' => 'activity_log_trained',       'label' => 'Đào tạo nhập nhật ký',                      'required' => true],
                    ['key' => 'photo_upload_trained',       'label' => 'Đào tạo upload ảnh',                        'required' => true],
                ],
                'handover'      => [
                    ['key' => 'documents_handedover',       'label' => 'Bàn giao hồ sơ pháp lý',                   'required' => true],
                    ['key' => 'data_handover_confirmed',    'label' => 'Xác nhận dữ liệu đã bàn giao',              'required' => true],
                    ['key' => 'user_guide_provided',        'label' => 'Cung cấp tài liệu hướng dẫn',              'required' => true],
                    ['key' => 'handover_minutes_signed',    'label' => 'Ký biên bản bàn giao',                      'required' => true],
                    ['key' => 'account_transfer_done',      'label' => 'Chuyển giao tài khoản hệ thống',            'required' => true],
                ],
            ],

            'sidebar_config' => [
                'TRIỂN KHAI' => [
                    ['label' => 'Dashboard',         'route' => '{vertical}.dashboard'],
                    ['label' => 'Dự án',             'route' => '{vertical}.projects.index'],
                    ['label' => '{target}',          'route' => '{vertical}.targets.index'],
                    ['label' => 'Khảo sát năng lực', 'route' => '{vertical}.readiness.index'],
                ],
                'THU THẬP DỮ LIỆU' => [
                    ['label' => 'Thu thập dữ liệu', 'route' => '{vertical}.data-collection.index'],
                    ['label' => 'Hồ sơ pháp lý',    'route' => '{vertical}.org-docs.index'],
                    ['label' => 'Export',            'route' => '{vertical}.export.index'],
                ],
                'CÔNG VIỆC' => [
                    ['label' => 'Công việc', 'route' => '{vertical}.tasks.index'],
                    ['label' => 'Tiến độ',   'route' => '{vertical}.progress.index'],
                    ['label' => 'Issues',    'route' => '{vertical}.issues.index'],
                    ['label' => 'Bàn giao',  'route' => '{vertical}.handover.index'],
                ],
                'BÁO CÁO' => [
                    ['label' => 'Báo cáo', 'route' => '{vertical}.reports.index'],
                ],
                'ĐÀO TẠO' => [
                    ['label' => 'Academy',    'route' => '{vertical}.academy.index'],
                    ['label' => 'Chứng nhận', 'route' => '{vertical}.certifications.index'],
                ],
                'CẤU HÌNH' => [
                    ['label' => 'Cấu hình', 'route' => '{vertical}.settings.index'],
                ],
            ],
        ]);

        $this->command?->info('[UpdateTraceabilityTemplate] Cập nhật template traceability: phases, checklist, sidebar, data_collection_template_slug.');
    }
}
