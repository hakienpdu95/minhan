<?php

namespace Modules\Deployment\Database\Seeders;

use App\Foundation\Vertical\VerticalTemplate;
use Illuminate\Database\Seeder;

class TraceabilityTemplateSeeder extends Seeder
{
    public function run(): void
    {
        VerticalTemplate::updateOrCreate(['code' => 'traceability'], [
            'label'                   => 'Triển khai Truy xuất nguồn gốc',
            'target_label'            => 'Tổ chức',
            'target_org_category'     => 'cooperative',
            'has_physical_assets'     => true,
            'export_adapter'          => null,
            'readiness_template_slug' => 'readiness_v1',

            'phases' => [
                'draft', 'surveying', 'collecting',
                'standardizing', 'importing',
                'training', 'handover', 'completed',
            ],

            'default_hierarchy' => [
                'site'        => 'Vùng sản xuất',
                'area'        => 'Khu',
                'lot'         => 'Lô',
                'item'        => 'Đơn vị',
                'item_plural' => 'Đơn vị',
                'item_prefix' => 'DV',
            ],

            'default_checklist' => [
                'surveying'     => [
                    ['key' => 'entity_profile_created',   'label' => 'Tạo hồ sơ chủ thể {target}',       'required' => true],
                    ['key' => 'field_survey_done',         'label' => 'Hoàn thành khảo sát thực địa',      'required' => true],
                    ['key' => 'gps_captured',              'label' => 'Thu thập GPS vùng sản xuất',         'required' => true],
                    ['key' => 'area_structure_created',    'label' => 'Tạo cơ cấu {area}/{lot}/{item}',    'required' => true],
                    ['key' => 'photos_uploaded',           'label' => 'Upload hình ảnh vùng sản xuất',      'required' => false],
                ],
                'collecting'    => [
                    ['key' => 'business_license_uploaded', 'label' => 'Đăng ký kinh doanh (ĐKKD)',          'required' => true],
                    ['key' => 'tax_id_verified',           'label' => 'Xác nhận Mã số thuế (MST)',          'required' => true],
                    ['key' => 'quality_cert_uploaded',     'label' => 'Chứng nhận chất lượng',              'required' => false],
                    ['key' => 'logo_uploaded',             'label' => 'Logo tổ chức',                       'required' => false],
                    ['key' => 'product_list_created',      'label' => 'Danh mục sản phẩm',                  'required' => true],
                ],
                'standardizing' => [
                    ['key' => 'entity_data_standardized',  'label' => 'Chuẩn hóa thông tin chủ thể',       'required' => true],
                    ['key' => 'area_codes_standardized',   'label' => 'Mã hóa và chuẩn hóa {area}',        'required' => true],
                    ['key' => 'lot_codes_standardized',    'label' => 'Mã hóa và chuẩn hóa {lot}',         'required' => true],
                    ['key' => 'item_codes_generated',      'label' => 'Sinh mã {item}',                     'required' => true],
                    ['key' => 'activity_history_entered',  'label' => 'Nhập lịch sử hoạt động cũ',          'required' => false],
                    ['key' => 'product_data_standardized', 'label' => 'Chuẩn hóa danh mục sản phẩm',       'required' => true],
                    ['key' => 'ai_validator_passed',       'label' => 'AI Validator đạt ≥ 95%',             'required' => true],
                ],
                'importing'     => [
                    ['key' => 'file_entity_exported',      'label' => 'Xuất file Chủ thể',                  'required' => true],
                    ['key' => 'file_area_exported',        'label' => 'Xuất file {area}',                   'required' => true],
                    ['key' => 'file_lot_exported',         'label' => 'Xuất file {lot}',                    'required' => true],
                    ['key' => 'file_item_exported',        'label' => 'Xuất file {item}',                   'required' => true],
                    ['key' => 'file_product_exported',     'label' => 'Xuất file Sản phẩm',                 'required' => true],
                    ['key' => 'file_history_exported',     'label' => 'Xuất file Lịch sử',                  'required' => false],
                    ['key' => 'partner_import_confirmed',  'label' => 'Xác nhận đã nhập vào hệ thống đối tác', 'required' => true],
                ],
                'training'      => [
                    ['key' => 'login_trained',             'label' => 'Đào tạo đăng nhập hệ thống',         'required' => true],
                    ['key' => 'data_view_trained',         'label' => 'Đào tạo xem và kiểm tra dữ liệu',    'required' => true],
                    ['key' => 'activity_log_trained',      'label' => 'Đào tạo nhập nhật ký',               'required' => true],
                    ['key' => 'photo_upload_trained',      'label' => 'Đào tạo upload ảnh',                 'required' => true],
                    ['key' => 'identifier_mgmt_trained',   'label' => 'Đào tạo quản lý mã định danh/QR',    'required' => false],
                ],
                'handover'      => [
                    ['key' => 'documents_handedover',      'label' => 'Bàn giao hồ sơ pháp lý',            'required' => true],
                    ['key' => 'data_handover_confirmed',   'label' => 'Xác nhận dữ liệu đã bàn giao',      'required' => true],
                    ['key' => 'user_guide_provided',       'label' => 'Cung cấp tài liệu hướng dẫn',       'required' => true],
                    ['key' => 'handover_minutes_signed',   'label' => 'Ký biên bản bàn giao',               'required' => true],
                    ['key' => 'account_transfer_done',     'label' => 'Chuyển giao tài khoản hệ thống',     'required' => true],
                ],
            ],

            'default_activity_types' => [
                'watering'    => 'Tưới nước',
                'fertilizing' => 'Bón phân',
                'spraying'    => 'Phun thuốc / phun nước',
                'harvesting'  => 'Thu hoạch',
                'pruning'     => 'Tỉa cành / tỉa tán',
                'inspection'  => 'Kiểm tra định kỳ',
                'replanting'  => 'Trồng bổ sung',
                'other'       => 'Khác',
            ],

            'default_legal_doc_types' => [
                'business_registration' => 'Đăng ký kinh doanh (ĐKKD)',
                'personal_id'           => 'CCCD / CMND người đại diện',
                'quality_cert'          => 'Chứng nhận chất lượng (OCOP/ATTP/VietGAP)',
                'organic_cert'          => 'Chứng nhận hữu cơ',
                'logo'                  => 'Logo tổ chức',
                'product_photo'         => 'Ảnh sản phẩm',
                'other'                 => 'Khác',
            ],

            'default_roles' => ['pm', 'surveyor', 'data_ops', 'data_entry', 'trainer'],

            'sidebar_config' => [
                'TRIỂN KHAI' => [
                    ['label' => 'Dashboard',         'route' => '{vertical}.dashboard'],
                    ['label' => 'Dự án',             'route' => '{vertical}.projects.index'],
                    ['label' => '{target}',          'route' => '{vertical}.targets.index'],
                    ['label' => 'Khảo sát năng lực', 'route' => '{vertical}.readiness.index'],
                ],
                'CHUẨN BỊ DỮ LIỆU' => [
                    ['label' => '{site}',                  'route' => '{vertical}.sites.index'],
                    ['label' => '{area} – {lot} – {item}', 'route' => '{vertical}.areas.index'],
                    ['label' => 'Lịch sử hoạt động',       'route' => '{vertical}.activity-logs.index'],
                    ['label' => 'Hồ sơ pháp lý',           'route' => '{vertical}.legal-docs.index'],
                    ['label' => 'Export',                   'route' => '{vertical}.export.index'],
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

            'is_active' => true,
        ]);
    }
}
