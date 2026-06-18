<?php

namespace Modules\Deployment\Database\Seeders;

use App\Foundation\Vertical\VerticalTemplate;
use Illuminate\Database\Seeder;

class NewVerticalsSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedNongSan();
        $this->seedThucPham();
        $this->seedTruongHoc();

        $this->command->info('NewVerticalsSeeder: 3 vertical templates upserted (nong-san, thuc-pham, truong-hoc).');
    }

    // ── 1. Nông sản ───────────────────────────────────────────────────────────

    private function seedNongSan(): void
    {
        VerticalTemplate::updateOrCreate(['code' => 'nong-san'], [
            'label'               => 'Triển khai Nông sản',
            'target_label'        => 'HTX / Hộ sản xuất',
            'target_org_category' => 'cooperative',
            'has_physical_assets' => true,
            'export_adapter'      => null,

            'phases' => [
                'draft', 'surveying', 'collecting',
                'standardizing', 'importing',
                'training', 'handover', 'completed',
            ],

            'default_hierarchy' => [
                'site'        => 'Vùng trồng',
                'area'        => 'Cánh đồng',
                'lot'         => 'Lô đất',
                'item'        => 'Luống / Cây',
                'item_plural' => 'Luống / Cây',
                'item_prefix' => 'NS',
            ],

            'default_checklist' => [
                'surveying' => [
                    ['key' => 'entity_profile_created',  'label' => 'Tạo hồ sơ HTX / Hộ sản xuất',       'required' => true],
                    ['key' => 'field_survey_done',        'label' => 'Hoàn thành khảo sát thực địa',       'required' => true],
                    ['key' => 'gps_captured',             'label' => 'Thu thập GPS vùng trồng',            'required' => true],
                    ['key' => 'area_structure_created',   'label' => 'Tạo cơ cấu cánh đồng / lô đất',     'required' => true],
                    ['key' => 'photos_uploaded',          'label' => 'Upload hình ảnh vùng trồng',         'required' => false],
                ],
                'collecting' => [
                    ['key' => 'business_license_uploaded', 'label' => 'Đăng ký kinh doanh / Hộ kinh doanh', 'required' => true],
                    ['key' => 'tax_id_verified',           'label' => 'Xác nhận Mã số thuế',                'required' => true],
                    ['key' => 'vietgap_cert_uploaded',     'label' => 'Chứng nhận VietGAP / Hữu cơ',       'required' => false],
                    ['key' => 'product_list_created',      'label' => 'Danh mục sản phẩm nông sản',         'required' => true],
                    ['key' => 'logo_uploaded',             'label' => 'Logo / thương hiệu',                 'required' => false],
                ],
                'standardizing' => [
                    ['key' => 'entity_data_standardized',  'label' => 'Chuẩn hóa thông tin chủ thể',       'required' => true],
                    ['key' => 'area_codes_standardized',   'label' => 'Mã hóa cánh đồng',                  'required' => true],
                    ['key' => 'lot_codes_standardized',    'label' => 'Mã hóa lô đất',                     'required' => true],
                    ['key' => 'item_codes_generated',      'label' => 'Sinh mã luống / cây',               'required' => true],
                    ['key' => 'activity_history_entered',  'label' => 'Nhập lịch sử canh tác cũ',          'required' => false],
                    ['key' => 'product_data_standardized', 'label' => 'Chuẩn hóa danh mục sản phẩm',       'required' => true],
                    ['key' => 'ai_validator_passed',       'label' => 'AI Validator đạt ≥ 95%',            'required' => true],
                ],
                'importing' => [
                    ['key' => 'file_entity_exported',   'label' => 'Xuất file Chủ thể',         'required' => true],
                    ['key' => 'file_area_exported',     'label' => 'Xuất file Cánh đồng',       'required' => true],
                    ['key' => 'file_lot_exported',      'label' => 'Xuất file Lô đất',          'required' => true],
                    ['key' => 'file_item_exported',     'label' => 'Xuất file Luống / Cây',     'required' => true],
                    ['key' => 'file_product_exported',  'label' => 'Xuất file Sản phẩm',        'required' => true],
                    ['key' => 'partner_import_confirmed', 'label' => 'Xác nhận nhập vào hệ thống đối tác', 'required' => true],
                ],
                'training' => [
                    ['key' => 'login_trained',          'label' => 'Đào tạo đăng nhập hệ thống',     'required' => true],
                    ['key' => 'data_view_trained',      'label' => 'Đào tạo xem và kiểm tra dữ liệu', 'required' => true],
                    ['key' => 'activity_log_trained',   'label' => 'Đào tạo nhập nhật ký canh tác',   'required' => true],
                    ['key' => 'photo_upload_trained',   'label' => 'Đào tạo upload ảnh thực địa',     'required' => true],
                    ['key' => 'qr_scan_trained',        'label' => 'Đào tạo quét mã QR ngoài đồng',  'required' => false],
                ],
                'handover' => [
                    ['key' => 'documents_handedover',      'label' => 'Bàn giao hồ sơ pháp lý',       'required' => true],
                    ['key' => 'data_handover_confirmed',   'label' => 'Xác nhận dữ liệu bàn giao',    'required' => true],
                    ['key' => 'user_guide_provided',       'label' => 'Cung cấp tài liệu hướng dẫn',  'required' => true],
                    ['key' => 'handover_minutes_signed',   'label' => 'Ký biên bản bàn giao',          'required' => true],
                    ['key' => 'account_transfer_done',     'label' => 'Chuyển giao tài khoản',         'required' => true],
                ],
            ],

            'default_activity_types' => [
                'planting'    => 'Gieo trồng / Cấy',
                'watering'    => 'Tưới nước',
                'fertilizing' => 'Bón phân',
                'spraying'    => 'Phun thuốc / phun nước',
                'harvesting'  => 'Thu hoạch',
                'packaging'   => 'Đóng gói / sơ chế',
                'inspection'  => 'Kiểm tra định kỳ',
                'other'       => 'Khác',
            ],

            'default_legal_doc_types' => [
                'business_registration' => 'Đăng ký kinh doanh / Hộ kinh doanh',
                'personal_id'           => 'CCCD / CMND người đại diện',
                'vietgap_cert'          => 'Chứng nhận VietGAP',
                'organic_cert'          => 'Chứng nhận hữu cơ',
                'land_use_cert'         => 'Giấy chứng nhận quyền sử dụng đất',
                'logo'                  => 'Logo / nhãn hiệu sản phẩm',
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
                    ['label' => 'Nhật ký canh tác',        'route' => '{vertical}.activity-logs.index'],
                    ['label' => 'Hồ sơ pháp lý',          'route' => '{vertical}.legal-docs.index'],
                    ['label' => 'Export',                  'route' => '{vertical}.export.index'],
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
                'CẤU HÌNH' => [
                    ['label' => 'Cấu hình', 'route' => '{vertical}.settings.index'],
                ],
            ],

            'is_active' => true,
        ]);
    }

    // ── 2. Thực phẩm ─────────────────────────────────────────────────────────

    private function seedThucPham(): void
    {
        VerticalTemplate::updateOrCreate(['code' => 'thuc-pham'], [
            'label'               => 'Triển khai Thực phẩm',
            'target_label'        => 'Cơ sở chế biến',
            'target_org_category' => 'food_processor',
            'has_physical_assets' => true,
            'export_adapter'      => null,

            'phases' => [
                'draft', 'surveying', 'collecting',
                'standardizing', 'importing',
                'training', 'handover', 'completed',
            ],

            'default_hierarchy' => [
                'site'        => 'Nhà máy / Xưởng',
                'area'        => 'Dây chuyền',
                'lot'         => 'Lô sản xuất',
                'item'        => 'Sản phẩm',
                'item_plural' => 'Sản phẩm',
                'item_prefix' => 'SP',
            ],

            'default_checklist' => [
                'surveying' => [
                    ['key' => 'facility_profile_created', 'label' => 'Tạo hồ sơ cơ sở chế biến',        'required' => true],
                    ['key' => 'facility_survey_done',     'label' => 'Hoàn thành khảo sát nhà máy',      'required' => true],
                    ['key' => 'production_lines_mapped',  'label' => 'Sơ đồ dây chuyền sản xuất',        'required' => true],
                    ['key' => 'photos_uploaded',          'label' => 'Upload hình ảnh cơ sở',            'required' => false],
                ],
                'collecting' => [
                    ['key' => 'business_license_uploaded', 'label' => 'Giấy phép kinh doanh',            'required' => true],
                    ['key' => 'food_safety_cert_uploaded', 'label' => 'Giấy chứng nhận ATTP',           'required' => true],
                    ['key' => 'haccp_cert_uploaded',       'label' => 'Chứng nhận HACCP / ISO 22000',    'required' => false],
                    ['key' => 'product_list_created',      'label' => 'Danh mục sản phẩm',              'required' => true],
                    ['key' => 'ingredient_sources_mapped', 'label' => 'Bản đồ nguồn nguyên liệu',       'required' => false],
                ],
                'standardizing' => [
                    ['key' => 'facility_data_standardized', 'label' => 'Chuẩn hóa thông tin cơ sở',     'required' => true],
                    ['key' => 'line_codes_standardized',    'label' => 'Mã hóa dây chuyền sản xuất',    'required' => true],
                    ['key' => 'batch_codes_standardized',   'label' => 'Mã hóa lô sản xuất',           'required' => true],
                    ['key' => 'product_codes_generated',    'label' => 'Sinh mã sản phẩm',             'required' => true],
                    ['key' => 'product_data_standardized',  'label' => 'Chuẩn hóa danh mục sản phẩm',  'required' => true],
                    ['key' => 'ai_validator_passed',        'label' => 'AI Validator đạt ≥ 95%',        'required' => true],
                ],
                'importing' => [
                    ['key' => 'file_facility_exported', 'label' => 'Xuất file Cơ sở',          'required' => true],
                    ['key' => 'file_line_exported',     'label' => 'Xuất file Dây chuyền',      'required' => true],
                    ['key' => 'file_batch_exported',    'label' => 'Xuất file Lô sản xuất',    'required' => true],
                    ['key' => 'file_product_exported',  'label' => 'Xuất file Sản phẩm',       'required' => true],
                    ['key' => 'partner_import_confirmed', 'label' => 'Xác nhận nhập hệ thống đối tác', 'required' => true],
                ],
                'training' => [
                    ['key' => 'login_trained',        'label' => 'Đào tạo đăng nhập hệ thống',       'required' => true],
                    ['key' => 'data_view_trained',    'label' => 'Đào tạo xem và kiểm tra dữ liệu',   'required' => true],
                    ['key' => 'batch_log_trained',    'label' => 'Đào tạo nhập nhật ký lô sản xuất', 'required' => true],
                    ['key' => 'qr_label_trained',     'label' => 'Đào tạo in và dán nhãn QR',        'required' => true],
                ],
                'handover' => [
                    ['key' => 'documents_handedover',    'label' => 'Bàn giao hồ sơ pháp lý',      'required' => true],
                    ['key' => 'data_handover_confirmed', 'label' => 'Xác nhận dữ liệu bàn giao',   'required' => true],
                    ['key' => 'user_guide_provided',     'label' => 'Cung cấp tài liệu hướng dẫn', 'required' => true],
                    ['key' => 'handover_minutes_signed', 'label' => 'Ký biên bản bàn giao',         'required' => true],
                    ['key' => 'account_transfer_done',   'label' => 'Chuyển giao tài khoản',        'required' => true],
                ],
            ],

            'default_activity_types' => [
                'receiving'   => 'Tiếp nhận nguyên liệu',
                'processing'  => 'Chế biến / sản xuất',
                'packaging'   => 'Đóng gói',
                'qc_check'    => 'Kiểm tra chất lượng (QC)',
                'storage'     => 'Nhập / Xuất kho',
                'delivery'    => 'Xuất hàng / vận chuyển',
                'recall'      => 'Thu hồi sản phẩm',
                'other'       => 'Khác',
            ],

            'default_legal_doc_types' => [
                'business_registration' => 'Giấy phép kinh doanh',
                'food_safety_cert'      => 'Giấy chứng nhận ATTP',
                'haccp_cert'            => 'Chứng nhận HACCP / ISO 22000',
                'vietgap_cert'          => 'Chứng nhận VietGAP (nếu có)',
                'personal_id'           => 'CCCD / CMND người đại diện',
                'product_label'         => 'Mẫu nhãn sản phẩm',
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
                    ['label' => 'Nhật ký sản xuất',        'route' => '{vertical}.activity-logs.index'],
                    ['label' => 'Hồ sơ pháp lý',          'route' => '{vertical}.legal-docs.index'],
                    ['label' => 'Export',                  'route' => '{vertical}.export.index'],
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
                'CẤU HÌNH' => [
                    ['label' => 'Cấu hình', 'route' => '{vertical}.settings.index'],
                ],
            ],

            'is_active' => true,
        ]);
    }

    // ── 3. Trường học ─────────────────────────────────────────────────────────

    private function seedTruongHoc(): void
    {
        VerticalTemplate::updateOrCreate(['code' => 'truong-hoc'], [
            'label'               => 'Triển khai Trường học số',
            'target_label'        => 'Trường / Đơn vị',
            'target_org_category' => 'school',
            'has_physical_assets' => false,
            'export_adapter'      => null,

            'phases' => [
                'draft', 'surveying', 'setup',
                'piloting', 'rollout',
                'training', 'handover', 'completed',
            ],

            'default_hierarchy' => [
                'site'        => 'Cơ sở / Campus',
                'area'        => 'Khối lớp',
                'lot'         => 'Lớp học',
                'item'        => 'Học sinh',
                'item_plural' => 'Học sinh',
                'item_prefix' => 'HS',
            ],

            'default_checklist' => [
                'surveying' => [
                    ['key' => 'school_profile_created',   'label' => 'Tạo hồ sơ trường học',                  'required' => true],
                    ['key' => 'infra_survey_done',         'label' => 'Khảo sát hạ tầng CNTT (máy tính, mạng)', 'required' => true],
                    ['key' => 'staff_count_confirmed',     'label' => 'Xác nhận số lượng giáo viên / nhân viên', 'required' => true],
                    ['key' => 'student_count_confirmed',   'label' => 'Xác nhận số lượng học sinh',             'required' => true],
                ],
                'setup' => [
                    ['key' => 'accounts_created',         'label' => 'Tạo tài khoản giáo viên',              'required' => true],
                    ['key' => 'student_data_imported',    'label' => 'Import danh sách học sinh',            'required' => true],
                    ['key' => 'class_structure_created',  'label' => 'Tạo cơ cấu khối / lớp',              'required' => true],
                    ['key' => 'system_config_done',       'label' => 'Cấu hình hệ thống theo đặc thù trường', 'required' => true],
                ],
                'piloting' => [
                    ['key' => 'pilot_class_selected',    'label' => 'Chọn lớp thí điểm',                   'required' => true],
                    ['key' => 'pilot_run_completed',     'label' => 'Chạy thử nghiệm và thu thập phản hồi', 'required' => true],
                    ['key' => 'issues_resolved',         'label' => 'Xử lý các vấn đề phát sinh',          'required' => true],
                ],
                'rollout' => [
                    ['key' => 'all_classes_onboarded',   'label' => 'Toàn bộ lớp đã đăng ký sử dụng',    'required' => true],
                    ['key' => 'data_verified',           'label' => 'Xác minh dữ liệu học sinh và điểm',  'required' => true],
                    ['key' => 'parent_portal_activated', 'label' => 'Kích hoạt cổng phụ huynh',           'required' => false],
                ],
                'training' => [
                    ['key' => 'admin_trained',           'label' => 'Đào tạo quản trị viên',              'required' => true],
                    ['key' => 'teachers_trained',        'label' => 'Đào tạo giáo viên sử dụng hệ thống', 'required' => true],
                    ['key' => 'reporting_trained',       'label' => 'Đào tạo xuất báo cáo, điểm danh',   'required' => true],
                ],
                'handover' => [
                    ['key' => 'documents_handedover',    'label' => 'Bàn giao hồ sơ và tài liệu',        'required' => true],
                    ['key' => 'data_handover_confirmed', 'label' => 'Xác nhận dữ liệu bàn giao',         'required' => true],
                    ['key' => 'user_guide_provided',     'label' => 'Cung cấp tài liệu hướng dẫn',       'required' => true],
                    ['key' => 'handover_minutes_signed', 'label' => 'Ký biên bản bàn giao',               'required' => true],
                    ['key' => 'support_contact_set',     'label' => 'Bàn giao đầu mối hỗ trợ sau triển khai', 'required' => true],
                ],
            ],

            'default_activity_types' => [
                'meeting'     => 'Họp / Trao đổi với Ban giám hiệu',
                'training'    => 'Đào tạo giáo viên',
                'setup'       => 'Cài đặt / cấu hình hệ thống',
                'data_entry'  => 'Nhập dữ liệu học sinh / lớp',
                'testing'     => 'Kiểm thử tính năng',
                'support'     => 'Hỗ trợ kỹ thuật',
                'other'       => 'Khác',
            ],

            'default_legal_doc_types' => [
                'establishment_decision' => 'Quyết định thành lập trường',
                'principal_id'           => 'CCCD Hiệu trưởng',
                'school_license'         => 'Giấy phép hoạt động giáo dục',
                'logo'                   => 'Logo trường',
                'contract'               => 'Hợp đồng triển khai',
                'other'                  => 'Khác',
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
                    ['label' => 'Nhật ký hoạt động',       'route' => '{vertical}.activity-logs.index'],
                    ['label' => 'Hồ sơ pháp lý',          'route' => '{vertical}.legal-docs.index'],
                    ['label' => 'Export',                  'route' => '{vertical}.export.index'],
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
                'CẤU HÌNH' => [
                    ['label' => 'Cấu hình', 'route' => '{vertical}.settings.index'],
                ],
            ],

            'is_active' => true,
        ]);
    }
}
