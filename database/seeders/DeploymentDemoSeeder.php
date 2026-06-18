<?php

namespace Database\Seeders;

use App\Foundation\Vertical\ActivateVerticalAction;
use App\Foundation\Vertical\OrganizationVertical;
use App\Shared\Tenancy\TenantContext;
use Modules\Branch\Models\Branch;
use Modules\Department\Models\Department;
use App\Foundation\Vertical\VerticalTemplate;
use App\Models\User;
use App\Shared\Tenancy\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Deployment\Models\DeploymentChecklistItem;
use Modules\Deployment\Models\DeploymentIssue;
use Modules\Deployment\Models\DeploymentProgressLog;
use Modules\Deployment\Models\DeploymentTarget;
use Modules\Employee\Models\Employee;
use Modules\KpiGoal\Models\KpiGoal;
use Modules\Project\Models\Project;
use Modules\Project\Models\ProjectMember;
use Modules\Survey\Enums\ResponseStatus;
use Modules\Survey\Enums\ValueKind;
use Modules\Survey\Enums\SurveyStatus;
use Modules\Survey\Models\Survey;
use Modules\Survey\Models\SurveyAnswer;
use Modules\Survey\Models\SurveyField;
use Modules\Survey\Models\SurveyFieldOption;
use Modules\Survey\Models\SurveyResponse;
// SurveyResult is a view — use DB::table('assessment_results') directly
use Modules\Survey\Models\SurveySection;
use Spatie\Permission\Models\Role;

class DeploymentDemoSeeder extends Seeder
{
    // ── Cấu hình HTX demo ───────────────────────────────────────────────
    private const HTX_LIST = [
        ['name' => 'HTX Trà hoa vàng Hoa Sơn',   'phase' => 'standardizing', 'score' => 72, 'province' => 'Quảng Ninh'],
        ['name' => 'HTX Nông sản Bình Liêu',       'phase' => 'collecting',    'score' => 58, 'province' => 'Quảng Ninh'],
        ['name' => 'HTX Dược liệu Ba Chẽ',         'phase' => 'surveying',     'score' => 45, 'province' => 'Quảng Ninh'],
        ['name' => 'HTX Chè Tiên Yên',             'phase' => 'exporting',     'score' => 88, 'province' => 'Quảng Ninh'],
        ['name' => 'HTX OCOP Đầm Hà',              'phase' => 'training',      'score' => 62, 'province' => 'Quảng Ninh'],
    ];

    // ── Nhân sự demo ────────────────────────────────────────────────────
    private const TEAM = [
        ['name' => 'Nguyễn Thị Hà',   'email' => 'ha.pm@demo.test',       'role' => 'traceability_pm'],
        ['name' => 'Trần Thị Lan',     'email' => 'lan.survey@demo.test',  'role' => 'traceability_surveyor'],
        ['name' => 'Lê Văn Minh',      'email' => 'minh.survey@demo.test', 'role' => 'traceability_surveyor'],
        ['name' => 'Phạm Thị Ngọc',   'email' => 'ngoc.ops@demo.test',    'role' => 'traceability_data_ops'],
        ['name' => 'Đỗ Thị Thu',      'email' => 'thu.trainer@demo.test', 'role' => 'traceability_trainer'],
    ];

    public function run(): void
    {
        $this->command->info('🚀 DeploymentDemoSeeder bắt đầu...');

        DB::transaction(function () {
            $org   = $this->ensureOrg();
            TenantContext::set($org);

            $admin = User::where('email', 'admin@system.local')->first()
                ?? User::first();

            $this->activateVertical($org, $admin);

            $branch = $this->ensureBranch($org, $admin);
        $dept   = $this->ensureDepartment($org, $branch, $admin);
        $team   = $this->ensureTeam($org, $branch, $dept, $admin);
            $htxOrgs = $this->ensureHtxOrganizations($org);
            $survey  = $this->ensureSurvey($org, $admin);
            $this->patchVerticalTemplate($survey);
            $project = $this->ensureProject($org, $team, $admin);
            $this->seedProjectMembers($project, $team);

            $responses = $this->seedSurveyResponses($survey, $htxOrgs, $team);
            $targets   = $this->seedDeploymentTargets($org, $project, $htxOrgs, $team, $responses, $admin);

            $this->seedChecklistItems($org, $targets);
            $this->seedIssues($org, $targets, $team, $admin);
            $this->seedProgressLogs($org, $targets, $team);
            $this->seedKpiGoals($org, $team);

            TenantContext::flush();
        });

        $this->command->info('✅ Demo seeded thành công!');
        $this->command->line('');
        $this->command->line('  Org      : Demo Organization (id=2)');
        $this->command->line('  Verticals: traceability, nong-san (cả hai active)');
        $this->command->line('  Survey   : "Khảo sát TXNG Readiness" (active)');
        $this->command->line('  HTX      : ' . count(self::HTX_LIST) . ' hợp tác xã');
        $this->command->line('  Team     : ' . count(self::TEAM) . ' nhân sự triển khai');
        $this->command->line('  Login    : ha.pm@demo.test / password');
    }

    // ────────────────────────────────────────────────────────────────────
    // 1. Tổ chức
    // ────────────────────────────────────────────────────────────────────
    private function ensureOrg(): Organization
    {
        $org = Organization::find(2)
            ?? Organization::where('slug', 'demo-organization')->first();

        if (! $org) {
            $org = Organization::create([
                'name'        => 'Demo Organization',
                'slug'        => 'demo-organization',
                'status'      => 'active',
                'is_system'   => false,
                'industry'    => 'Nông nghiệp / Truy xuất nguồn gốc',
                'province_code' => 'QN',
                'city'        => 'Quảng Ninh',
            ]);
        }

        $this->command->line("  Org: {$org->name} (id={$org->id})");
        return $org;
    }

    // ────────────────────────────────────────────────────────────────────
    // 2. Kích hoạt verticals cho org demo
    // ────────────────────────────────────────────────────────────────────
    private function activateVertical(Organization $org, User $admin): void
    {
        // traceability — giữ nguyên logic cũ (idempotent, không dùng Action vì
        // config items đã được seed riêng bởi các bước khác trong seeder này)
        $exists = OrganizationVertical::withoutTenant()
            ->where('organization_id', $org->id)
            ->where('vertical_code', 'traceability')
            ->exists();

        if (! $exists) {
            OrganizationVertical::create([
                'organization_id' => $org->id,
                'vertical_code'   => 'traceability',
                'status'          => 'active',
                'activated_at'    => now(),
                'activated_by'    => $admin->id,
            ]);
            $this->command->line('  Vertical "traceability" đã kích hoạt.');
        } else {
            $this->command->line('  Vertical "traceability" đã tồn tại, bỏ qua.');
        }

        // nong-san — dùng ActivateVerticalAction để seed đủ config items + roles
        $nongSanExists = OrganizationVertical::withoutTenant()
            ->where('organization_id', $org->id)
            ->where('vertical_code', 'nong-san')
            ->where('status', 'active')
            ->exists();

        if (! $nongSanExists) {
            auth()->setUser($admin); // ActivateVerticalAction dùng auth()->id() cho activated_by
            (new ActivateVerticalAction)->execute($org->id, 'nong-san');
            $this->command->line('  Vertical "nong-san" đã kích hoạt (21 config items, 5 roles).');
        } else {
            $this->command->line('  Vertical "nong-san" đã tồn tại, bỏ qua.');
        }
    }

    // ────────────────────────────────────────────────────────────────────
    // Branch mặc định
    // ────────────────────────────────────────────────────────────────────
    private function ensureBranch(Organization $org, User $admin): Branch
    {
        return Branch::firstOrCreate(
            ['organization_id' => $org->id, 'code' => 'HQ'],
            [
                'organization_id' => $org->id,
                'name'            => 'Trụ sở chính',
                'code'            => 'HQ',
                'type'            => 'headquarters',
                'status'          => 'active',
                'path'            => '/',
                'depth'           => 0,
                'created_by'      => $admin->id,
            ]
        );
    }

    // ────────────────────────────────────────────────────────────────────
    // Department mặc định
    // ────────────────────────────────────────────────────────────────────
    private function ensureDepartment(Organization $org, Branch $branch, User $admin): Department
    {
        return Department::firstOrCreate(
            ['organization_id' => $org->id, 'code' => 'TXNG-DEPT'],
            [
                'organization_id' => $org->id,
                'branch_id'       => $branch->id,
                'name'            => 'Bộ phận Triển khai',
                'code'            => 'TXNG-DEPT',
                'status'          => 'active',
                'path'            => '/',
                'depth'           => 0,
                'created_by'      => $admin->id,
            ]
        );
    }

    // ────────────────────────────────────────────────────────────────────
    // 3. Nhân sự triển khai
    // ────────────────────────────────────────────────────────────────────
    private function ensureTeam(Organization $org, Branch $branch, Department $dept, User $admin): array
    {
        $team = [];

        foreach (self::TEAM as $member) {
            $user = User::firstOrCreate(
                ['email' => $member['email']],
                [
                    'name'              => $member['name'],
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                    'organization_id'   => $org->id,
                    'is_active'         => true,
                    'account_type'      => 'org_member',
                ]
            );

            $user->organization_id = $org->id;
            $user->save();

            $role = Role::where('name', $member['role'])->first();
            if ($role && ! $user->hasRole($member['role'])) {
                $user->assignRole($role);
            }

            $employee = Employee::withoutGlobalScopes()
                ->where('organization_id', $org->id)
                ->where('email', $member['email'])
                ->first();

            if (! $employee) {
                $now = Carbon::now();
                DB::table('employees')->insert([
                    'organization_id'  => $org->id,
                    'user_id'          => $user->id,
                    'branch_id'        => $branch->id,
                    'department_id'    => $dept->id,
                    'full_name'        => $member['name'],
                    'email'            => $member['email'],
                    'employee_code'    => strtoupper(Str::random(6)),
                    'status'           => 'active',
                    'employment_type'  => 'full_time',
                    'hired_at'         => Carbon::now()->subMonths(6),
                    'snap_branch_name' => 'Trụ sở chính',
                    'snap_dept_name'   => 'Bộ phận Triển khai',
                    'created_by'       => $admin->id,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
                $employee = Employee::withoutGlobalScopes()
                    ->where('organization_id', $org->id)
                    ->where('email', $member['email'])
                    ->first();
            }

            $team[$member['role']] ??= $user;
            $team['employees'][]    = $employee;
            $team['users'][]        = $user;
        }

        $this->command->line('  Team: ' . count(self::TEAM) . ' nhân sự đã tạo/cập nhật.');
        return $team;
    }

    // ────────────────────────────────────────────────────────────────────
    // 4. Tổ chức HTX
    // ────────────────────────────────────────────────────────────────────
    private function ensureHtxOrganizations(Organization $org): array
    {
        $htxOrgs = [];

        foreach (self::HTX_LIST as $htx) {
            $slug    = Str::slug($htx['name']);
            $htxOrg  = Organization::firstOrCreate(
                ['slug' => $slug],
                [
                    'name'      => $htx['name'],
                    'slug'      => $slug,
                    'status'    => 'active',
                    'is_system' => false,
                    'industry'  => 'Nông nghiệp',
                    'city'      => $htx['province'],
                ]
            );
            $htxOrgs[] = $htxOrg;
        }

        $this->command->line('  HTX: ' . count($htxOrgs) . ' tổ chức HTX đã tạo/cập nhật.');
        return $htxOrgs;
    }

    // ────────────────────────────────────────────────────────────────────
    // 5. Khảo sát TXNG Readiness
    // ────────────────────────────────────────────────────────────────────
    private function ensureSurvey(Organization $org, User $admin): Survey
    {
        $existing = Survey::where('organization_id', $org->id)
            ->where('slug', 'like', 'txng-readiness%')
            ->first();

        if ($existing) {
            $this->command->line("  Survey đã tồn tại: {$existing->title}");
            return $existing;
        }

        $survey = Survey::create([
            'organization_id'        => $org->id,
            'title'                  => 'Khảo sát TXNG Readiness',
            'description'            => 'Đánh giá mức độ sẵn sàng triển khai Truy xuất nguồn gốc của hợp tác xã. Kết quả dùng để lập kế hoạch và phân bổ nguồn lực triển khai phù hợp.',
            'slug'                   => 'txng-readiness-' . Str::random(6),
            'status'                 => SurveyStatus::Active,
            'version'                => 1,
            'allow_multiple_responses' => false,
        ]);

        $sections = [
            [
                'title' => 'Hạ tầng kỹ thuật',
                'code'  => 'infrastructure',
                'fields' => [
                    ['key' => 'has_smartphone',  'label' => 'HTX có smartphone để chụp ảnh và nhập liệu?', 'type' => 5, 'opts' => ['co' => 'Có', 'khong' => 'Không']],
                    ['key' => 'has_internet',    'label' => 'HTX có kết nối Internet ổn định?',            'type' => 5, 'opts' => ['co' => 'Có', 'yeu' => 'Yếu/không ổn định', 'khong' => 'Không']],
                    ['key' => 'has_computer',    'label' => 'HTX có máy tính/laptop?',                     'type' => 5, 'opts' => ['co' => 'Có', 'khong' => 'Không']],
                    ['key' => 'infra_score',     'label' => 'Tự đánh giá hạ tầng (1–5 sao)',               'type' => 7, 'max' => 5],
                ],
            ],
            [
                'title' => 'Nhân sự & năng lực',
                'code'  => 'personnel',
                'fields' => [
                    ['key' => 'has_it_person',   'label' => 'Có người phụ trách TXNG/công nghệ thông tin?',  'type' => 5, 'opts' => ['co' => 'Có', 'khong' => 'Không']],
                    ['key' => 'can_excel',        'label' => 'Nhân sự biết sử dụng Excel cơ bản?',           'type' => 5, 'opts' => ['co' => 'Có', 'mot_phan' => 'Một phần', 'khong' => 'Không']],
                    ['key' => 'can_photo',        'label' => 'Nhân sự biết chụp ảnh thực địa đạt chuẩn?',   'type' => 5, 'opts' => ['co' => 'Có', 'can_dao_tao' => 'Cần đào tạo', 'khong' => 'Không']],
                    ['key' => 'staff_count',      'label' => 'Số nhân sự có thể tham gia nhập liệu',         'type' => 3, 'min' => 0, 'max' => 50],
                ],
            ],
            [
                'title' => 'Dữ liệu hiện có',
                'code'  => 'data_readiness',
                'fields' => [
                    ['key' => 'has_diary',        'label' => 'HTX có nhật ký canh tác/sản xuất?',            'type' => 5, 'opts' => ['day_du' => 'Đầy đủ', 'mot_phan' => 'Một phần', 'khong' => 'Chưa có']],
                    ['key' => 'has_photos',       'label' => 'Có ảnh thực địa (khu, lô, cây)?',              'type' => 5, 'opts' => ['nhieu' => 'Nhiều ảnh', 'it' => 'Ít ảnh', 'khong' => 'Chưa có']],
                    ['key' => 'has_gps',          'label' => 'Đã đo GPS vùng trồng?',                        'type' => 5, 'opts' => ['day_du' => 'Đầy đủ', 'mot_phan' => 'Một phần', 'khong' => 'Chưa đo']],
                    ['key' => 'legal_docs',       'label' => 'Hồ sơ pháp lý sẵn có (chọn tất cả)',           'type' => 6, 'opts' => ['dkkd' => 'ĐKKD', 'mst' => 'MST', 'ocop' => 'OCOP', 'attp' => 'ATTP', 'logo' => 'Logo']],
                ],
            ],
            [
                'title' => 'Quy trình & sẵn sàng',
                'code'  => 'process',
                'fields' => [
                    ['key' => 'familiar_txng',   'label' => 'HTX đã biết về Truy xuất nguồn gốc?',           'type' => 5, 'opts' => ['ro' => 'Hiểu rõ', 'it' => 'Biết sơ', 'khong' => 'Chưa biết']],
                    ['key' => 'ready_timeline',  'label' => 'HTX sẵn sàng triển khai trong bao lâu?',        'type' => 4, 'opts' => ['ngay' => 'Ngay bây giờ', '1thang' => '1 tháng', '3thang' => '3 tháng', 'chua_biet' => 'Chưa biết']],
                    ['key' => 'overall_readiness','label' => 'Đánh giá tổng thể mức độ sẵn sàng (1–10)',     'type' => 12],
                ],
            ],
        ];

        foreach ($sections as $sIdx => $sData) {
            $section = SurveySection::create([
                'survey_id'       => $survey->id,
                'title'           => $sData['title'],
                'section_code'    => $sData['code'],
                'sort_order'      => $sIdx + 1,
            ]);

            foreach ($sData['fields'] as $fIdx => $fData) {
                $field = SurveyField::create([
                    'survey_id'  => $survey->id,
                    'section_id' => $section->id,
                    'field_key'  => $fData['key'],
                    'label'      => $fData['label'],
                    'field_type' => $fData['type'],
                    'value_kind' => ValueKind::String->value,
                    'is_required' => true,
                    'is_active'  => true,
                    'sort_order' => $fIdx + 1,
                    'rule_max'   => $fData['max'] ?? null,
                    'rule_min'   => $fData['min'] ?? null,
                ]);

                if (! empty($fData['opts'])) {
                    $oIdx = 0;
                    foreach ($fData['opts'] as $val => $label) {
                        SurveyFieldOption::create([
                            'field_id'     => $field->id,
                            'option_value' => $val,
                            'label'        => $label,
                            'sort_order'   => ++$oIdx,
                        ]);
                    }
                }
            }
        }

        $this->command->line("  Survey: \"{$survey->title}\" đã tạo (active).");
        return $survey;
    }

    // ────────────────────────────────────────────────────────────────────
    // 5b. Gán readiness_template_slug cho VerticalTemplate
    // ────────────────────────────────────────────────────────────────────
    private function patchVerticalTemplate(Survey $survey): void
    {
        $template = VerticalTemplate::where('code', 'traceability')->first();
        if (! $template) {
            return;
        }

        if ($template->readiness_template_slug !== $survey->slug) {
            $template->update(['readiness_template_slug' => $survey->slug]);
            $this->command->line("  VerticalTemplate: readiness_template_slug = \"{$survey->slug}\"");
        }
    }

    // ────────────────────────────────────────────────────────────────────
    // 6. Dự án triển khai
    // ────────────────────────────────────────────────────────────────────
    private function ensureProject(Organization $org, array $team, User $admin): Project
    {
        $existing = Project::where('organization_id', $org->id)
            ->where('code', 'TXNG-QN-2026')
            ->first();

        if ($existing) {
            return $existing;
        }

        $pmUser     = $team['traceability_pm'] ?? $admin;
        $pmEmployee = Employee::withoutGlobalScopes()->where('user_id', $pmUser->id)->first();

        $project = Project::create([
            'organization_id' => $org->id,
            'code'            => 'TXNG-QN-2026',
            'name'            => 'Dự án Triển khai TXNG Trà hoa vàng Quảng Ninh 2026',
            'description'     => 'Triển khai hệ thống Truy xuất nguồn gốc trên nền tảng CheckVN cho 5 HTX Trà hoa vàng và nông sản đặc sản tỉnh Quảng Ninh.',
            'status'          => 'active',
            'priority'        => 'high',
            'vertical_code'   => 'traceability',
            'owner_id'        => $pmEmployee?->id ?? ($team['employees'][0] ?? null)?->id,
            'start_date'      => Carbon::now()->subDays(45),
            'end_date'        => Carbon::now()->addDays(90),
            'progress_pct'    => 42,
            'created_by'      => $admin->id,
        ]);

        $this->command->line("  Project: \"{$project->name}\" đã tạo.");
        return $project;
    }

    // ────────────────────────────────────────────────────────────────────
    // 6b. Thành viên dự án
    // ────────────────────────────────────────────────────────────────────
    private function seedProjectMembers(Project $project, array $team): void
    {
        $employees = $team['employees'] ?? [];
        $count = 0;

        foreach ($employees as $i => $employee) {
            $exists = ProjectMember::where('project_id', $project->id)
                ->where('employee_id', $employee->id)
                ->exists();

            if ($exists) {
                continue;
            }

            ProjectMember::create([
                'project_id'       => $project->id,
                'employee_id'      => $employee->id,
                'role'             => $i === 0 ? 'lead' : 'member',
                'is_lead'          => $i === 0,
                'contribution_pct' => $i === 0 ? 30 : 20,
                'joined_at'        => Carbon::now()->subDays(45),
            ]);
            $count++;
        }

        if ($count > 0) {
            $this->command->line("  Project members: {$count} thành viên.");
        }
    }

    // ────────────────────────────────────────────────────────────────────
    // 7. Phản hồi khảo sát (1 response / HTX)
    // ────────────────────────────────────────────────────────────────────
    private function seedSurveyResponses(Survey $survey, array $htxOrgs, array $team): array
    {
        $responses  = [];
        $surveyors  = array_values(array_filter($team['users'] ?? [], fn ($u) =>
            $u->hasRole('traceability_surveyor') || $u->hasRole('traceability_pm')
        ));

        // Pre-load fields keyed by field_key
        $fields = SurveyField::where('survey_id', $survey->id)
            ->with('options')
            ->get()
            ->keyBy('field_key');

        // Demo answer profiles per HTX — scores từ spec
        $profiles = [
            // HTX Hoa Sơn: 72/100 – khá sẵn sàng
            ['has_smartphone'=>'co','has_internet'=>'co','has_computer'=>'co','infra_score'=>4,
             'has_it_person'=>'co','can_excel'=>'mot_phan','can_photo'=>'co','staff_count'=>3,
             'has_diary'=>'mot_phan','has_photos'=>'nhieu','has_gps'=>'day_du','legal_docs'=>['dkkd','mst','ocop'],
             'familiar_txng'=>'ro','ready_timeline'=>'ngay','overall_readiness'=>7],
            // HTX Bình Liêu: 58/100 – trung bình
            ['has_smartphone'=>'co','has_internet'=>'yeu','has_computer'=>'khong','infra_score'=>3,
             'has_it_person'=>'khong','can_excel'=>'mot_phan','can_photo'=>'can_dao_tao','staff_count'=>2,
             'has_diary'=>'mot_phan','has_photos'=>'it','has_gps'=>'mot_phan','legal_docs'=>['dkkd','mst'],
             'familiar_txng'=>'it','ready_timeline'=>'1thang','overall_readiness'=>5],
            // HTX Ba Chẽ: 45/100 – cần nhiều hỗ trợ
            ['has_smartphone'=>'co','has_internet'=>'khong','has_computer'=>'khong','infra_score'=>2,
             'has_it_person'=>'khong','can_excel'=>'khong','can_photo'=>'can_dao_tao','staff_count'=>1,
             'has_diary'=>'khong','has_photos'=>'khong','has_gps'=>'khong','legal_docs'=>['dkkd'],
             'familiar_txng'=>'khong','ready_timeline'=>'3thang','overall_readiness'=>3],
            // HTX Tiên Yên: 88/100 – sẵn sàng cao
            ['has_smartphone'=>'co','has_internet'=>'co','has_computer'=>'co','infra_score'=>5,
             'has_it_person'=>'co','can_excel'=>'co','can_photo'=>'co','staff_count'=>5,
             'has_diary'=>'day_du','has_photos'=>'nhieu','has_gps'=>'day_du','legal_docs'=>['dkkd','mst','ocop','attp','logo'],
             'familiar_txng'=>'ro','ready_timeline'=>'ngay','overall_readiness'=>9],
            // HTX Đầm Hà: 62/100 – trung bình khá
            ['has_smartphone'=>'co','has_internet'=>'co','has_computer'=>'khong','infra_score'=>3,
             'has_it_person'=>'khong','can_excel'=>'co','can_photo'=>'co','staff_count'=>2,
             'has_diary'=>'mot_phan','has_photos'=>'it','has_gps'=>'mot_phan','legal_docs'=>['dkkd','mst','ocop'],
             'familiar_txng'=>'it','ready_timeline'=>'1thang','overall_readiness'=>6],
        ];

        foreach ($htxOrgs as $i => $htxOrg) {
            $existing = SurveyResponse::where('survey_id', $survey->id)
                ->where('respondent_ref', $htxOrg->email ?: "htx-{$htxOrg->id}@demo.test")
                ->first();

            if ($existing) {
                $responses[] = $existing;
                continue;
            }

            $ref      = $htxOrg->email ?: "htx-{$htxOrg->id}@demo.test";
            $profile  = $profiles[$i] ?? $profiles[0];
            $submittedAt = Carbon::now()->subDays(30 - ($i * 5));

            $response = SurveyResponse::create([
                'survey_id'      => $survey->id,
                'respondent_ref' => $ref,
                'status'         => ResponseStatus::Complete->value,
                'submitted_at'   => $submittedAt,
            ]);

            // Seed answers
            foreach ($profile as $fieldKey => $value) {
                $field = $fields->get($fieldKey);
                if (! $field) {
                    continue;
                }

                if (is_array($value)) {
                    // Checkbox — multiple options
                    foreach ($value as $optVal) {
                        $opt = $field->options->firstWhere('option_value', $optVal);
                        SurveyAnswer::create([
                            'response_id'  => $response->id,
                            'field_id'     => $field->id,
                            'option_id'    => $opt?->id,
                            'value_string' => $optVal,
                        ]);
                    }
                } elseif ($field->field_type->value === 3) {
                    // Number
                    SurveyAnswer::create([
                        'response_id'  => $response->id,
                        'field_id'     => $field->id,
                        'value_number' => (float) $value,
                    ]);
                } elseif ($field->field_type->value === 7 || $field->field_type->value === 12) {
                    // Rating / NPS
                    SurveyAnswer::create([
                        'response_id'  => $response->id,
                        'field_id'     => $field->id,
                        'value_number' => (float) $value,
                    ]);
                } else {
                    // Radio / Select
                    $opt = $field->options->firstWhere('option_value', $value);
                    SurveyAnswer::create([
                        'response_id'  => $response->id,
                        'field_id'     => $field->id,
                        'option_id'    => $opt?->id,
                        'value_string' => $value,
                    ]);
                }
            }

            // Seed result (readiness score)
            $score = self::HTX_LIST[$i]['score'];
            $resultExists = DB::table('assessment_results')
                ->where('subject_type', SurveyResponse::class)
                ->where('subject_id', $response->id)
                ->exists();

            if (! $resultExists) {
                DB::table('assessment_results')->insert([
                    'uuid'          => Str::uuid()->toString(),
                    'subject_type'  => SurveyResponse::class,
                    'subject_id'    => $response->id,
                    'overall_score' => $score,
                    'maturity_level' => $this->scoreToMaturity($score),
                    'assessment_code' => 'txng-readiness',
                    'weight_version' => 1,
                    'calculated_at' => $submittedAt,
                    'created_at'    => $submittedAt,
                    'updated_at'    => $submittedAt,
                ]);
            }

            $responses[] = $response;
        }

        $this->command->line('  Survey responses: ' . count($responses) . ' HTX đã có kết quả.');
        return $responses;
    }

    // ────────────────────────────────────────────────────────────────────
    // 8. Deployment targets
    // ────────────────────────────────────────────────────────────────────
    private function seedDeploymentTargets(
        Organization $org,
        Project $project,
        array $htxOrgs,
        array $team,
        array $responses,
        User $admin
    ): array {
        $targets = [];
        $employees = $team['employees'] ?? [];

        foreach ($htxOrgs as $i => $htxOrg) {
            $existing = DeploymentTarget::where('organization_id', $org->id)
                ->where('target_organization_id', $htxOrg->id)
                ->where('vertical_code', 'traceability')
                ->first();

            $response = $responses[$i] ?? null;

            if ($existing) {
                // Đồng bộ readiness_response_id về TXNG survey response
                if ($response && $existing->readiness_response_id !== $response->id) {
                    $existing->update(['readiness_response_id' => $response->id]);
                }
                $targets[] = $existing;
                continue;
            }

            $htxConfig  = self::HTX_LIST[$i];
            $assignedEmp = $employees[$i % count($employees)] ?? null;

            $target = DeploymentTarget::create([
                'organization_id'           => $org->id,
                'project_id'                => $project->id,
                'vertical_code'             => 'traceability',
                'target_organization_id'    => $htxOrg->id,
                'current_phase'             => $htxConfig['phase'],
                'assigned_employee_id'      => $assignedEmp?->id,
                'readiness_response_id'     => $response?->id,
                'readiness_score'           => $htxConfig['score'],
                'notes'                     => "Khảo sát Readiness: {$htxConfig['score']}/100. Tỉnh {$htxConfig['province']}.",
                'created_by'                => $admin->id,
            ]);

            $targets[] = $target;
        }

        $this->command->line('  Deployment targets: ' . count($targets) . ' HTX.');
        return $targets;
    }

    // ────────────────────────────────────────────────────────────────────
    // 9. Checklist items theo phase
    // ────────────────────────────────────────────────────────────────────
    private function seedChecklistItems(Organization $org, array $targets): void
    {
        $template = VerticalTemplate::where('code', 'traceability')->first();
        if (! $template) {
            return;
        }

        $defaultChecklist = $template->default_checklist ?? [];
        $count = 0;

        foreach ($targets as $i => $target) {
            $phase = $target->current_phase;
            $items = $defaultChecklist[$phase] ?? [];

            foreach ($items as $idx => $item) {
                $exists = DeploymentChecklistItem::where('deployment_target_id', $target->id)
                    ->where('phase', $phase)
                    ->where('item_key', $item['key'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                // HTX đang ở phase này: mark 60–80% done để có dữ liệu thực
                $isDone = $idx < (int) (count($items) * 0.7);

                DeploymentChecklistItem::create([
                    'organization_id'     => $org->id,
                    'deployment_target_id' => $target->id,
                    'phase'               => $phase,
                    'item_key'            => $item['key'],
                    'item_label'          => $item['label'],
                    'is_required'         => $item['required'] ?? false,
                    'is_done'             => $isDone,
                    'done_at'             => $isDone ? Carbon::now()->subDays(rand(2, 15)) : null,
                ]);
                $count++;
            }

            // Cũng thêm checklist của các phase đã qua (hoàn thành 100%)
            $phaseOrder = ['surveying', 'collecting', 'standardizing', 'exporting', 'training', 'handover'];
            $currentIdx = array_search($phase, $phaseOrder);

            foreach ($phaseOrder as $pIdx => $prevPhase) {
                if ($pIdx >= $currentIdx) {
                    break;
                }
                $prevItems = $defaultChecklist[$prevPhase] ?? [];
                foreach ($prevItems as $item) {
                    $exists = DeploymentChecklistItem::where('deployment_target_id', $target->id)
                        ->where('phase', $prevPhase)
                        ->where('item_key', $item['key'])
                        ->exists();
                    if ($exists) {
                        continue;
                    }
                    DeploymentChecklistItem::create([
                        'organization_id'      => $org->id,
                        'deployment_target_id' => $target->id,
                        'phase'                => $prevPhase,
                        'item_key'             => $item['key'],
                        'item_label'           => $item['label'],
                        'is_required'          => $item['required'] ?? false,
                        'is_done'              => true,
                        'done_at'              => Carbon::now()->subDays(rand(10, 30)),
                    ]);
                    $count++;
                }
            }
        }

        $this->command->line("  Checklist items: {$count} items.");
    }

    // ────────────────────────────────────────────────────────────────────
    // 10. Issues
    // ────────────────────────────────────────────────────────────────────
    private function seedIssues(Organization $org, array $targets, array $team, User $admin): void
    {
        $issueTemplates = [
            ['title' => 'Thiếu dữ liệu GPS cho 2 lô',        'severity' => 'high',   'status' => 'open'],
            ['title' => 'Ảnh thực địa chưa đủ tiêu chuẩn',   'severity' => 'medium', 'status' => 'open'],
            ['title' => 'Nhật ký canh tác chưa có',           'severity' => 'high',   'status' => 'in_progress'],
            ['title' => 'Hồ sơ OCOP chưa được cung cấp',     'severity' => 'medium', 'status' => 'resolved'],
            ['title' => 'Sai mã lô trong file chuẩn hóa',    'severity' => 'low',    'status' => 'resolved'],
        ];

        $owners = $team['users'] ?? [$admin];
        $count = 0;

        foreach ($targets as $i => $target) {
            // Mỗi HTX 1–2 issues
            $issueCount = ($i % 3 === 0) ? 2 : 1;

            for ($j = 0; $j < $issueCount; $j++) {
                $template = $issueTemplates[($i + $j) % count($issueTemplates)];

                $exists = DeploymentIssue::where('deployment_target_id', $target->id)
                    ->where('title', $template['title'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                DeploymentIssue::create([
                    'organization_id'      => $org->id,
                    'deployment_target_id' => $target->id,
                    'project_id'           => $target->project_id,
                    'title'                => $template['title'],
                    'description'          => "Phát hiện trong quá trình kiểm tra dữ liệu phase {$target->current_phase}.",
                    'severity'             => $template['severity'],
                    'status'               => $template['status'],
                    'owner_id'             => $owners[$i % count($owners)]->id,
                    'resolved_at'          => $template['status'] === 'resolved' ? Carbon::now()->subDays(rand(1, 5)) : null,
                    'created_by'           => $owners[0]->id,
                ]);
                $count++;
            }
        }

        $this->command->line("  Issues: {$count} issues.");
    }

    // ────────────────────────────────────────────────────────────────────
    // 11. Progress logs
    // ────────────────────────────────────────────────────────────────────
    private function seedProgressLogs(Organization $org, array $targets, array $team): void
    {
        $phaseOrder  = ['surveying', 'collecting', 'standardizing', 'exporting', 'training', 'handover'];
        $phasePercent = [25, 45, 65, 80, 90, 100];
        $loggers    = $team['users'] ?? [];
        $count = 0;

        foreach ($targets as $i => $target) {
            $currentIdx = array_search($target->current_phase, $phaseOrder);
            if ($currentIdx === false) {
                $currentIdx = 0;
            }

            // Log cho mỗi phase đã hoàn thành + phase hiện tại
            for ($p = 0; $p <= $currentIdx; $p++) {
                $exists = DeploymentProgressLog::where('deployment_target_id', $target->id)
                    ->where('phase', $phaseOrder[$p])
                    ->exists();

                if ($exists) {
                    continue;
                }

                $pct    = $p < $currentIdx ? 100 : $phasePercent[$p];
                $logger = $loggers[$i % max(1, count($loggers))];

                DeploymentProgressLog::create([
                    'organization_id'      => $org->id,
                    'deployment_target_id' => $target->id,
                    'phase'                => $phaseOrder[$p],
                    'percent'              => $pct,
                    'remark'               => $p < $currentIdx
                        ? "Phase {$phaseOrder[$p]} hoàn thành."
                        : "Đang thực hiện — {$pct}% hoàn thành.",
                    'logged_by'            => $logger->id,
                    'logged_at'            => Carbon::now()->subDays(($currentIdx - $p) * 8 + rand(0, 3)),
                ]);
                $count++;
            }
        }

        $this->command->line("  Progress logs: {$count} bản ghi.");
    }

    // ────────────────────────────────────────────────────────────────────
    // 12. KPI Goals cho team
    // ────────────────────────────────────────────────────────────────────
    private function seedKpiGoals(Organization $org, array $team): void
    {
        $employees = $team['employees'] ?? [];
        $adminUser = User::where('email', 'admin@system.local')->first() ?? User::first();
        $adminEmployee = Employee::withoutGlobalScopes()->where('user_id', $adminUser->id)->first();

        $kpiTemplates = [
            ['traceability_pm'       => ['title' => 'Hoàn thành triển khai 5 HTX đúng tiến độ', 'target' => 5, 'current' => 2, 'unit' => 'HTX']],
            ['traceability_surveyor' => ['title' => 'Khảo sát thực địa và chuẩn hóa dữ liệu', 'target' => 5, 'current' => 3, 'unit' => 'HTX']],
            ['traceability_data_ops' => ['title' => 'Chuẩn hóa và xuất file CheckVN', 'target' => 3, 'current' => 1, 'unit' => 'file']],
            ['traceability_trainer'  => ['title' => 'Đào tạo nhân sự HTX sử dụng CheckVN', 'target' => 3, 'current' => 1, 'unit' => 'HTX']],
        ];

        $count = 0;
        foreach ($employees as $i => $employee) {
            $user = $employee->user ?? null;
            if (! $user) {
                continue;
            }

            foreach ($kpiTemplates as $roleKpi) {
                $role     = array_key_first($roleKpi);
                $kpiData  = $roleKpi[$role];
                if (! $user->hasRole($role)) {
                    continue;
                }

                $exists = KpiGoal::where('organization_id', $org->id)
                    ->where('employee_id', $employee->id)
                    ->where('title', $kpiData['title'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('kpi_goals')->insert([
                    'organization_id' => $org->id,
                    'employee_id'     => $employee->id,
                    'cycle_label'     => 'Q2-2026',
                    'cycle_start'     => Carbon::create(2026, 4, 1)->toDateString(),
                    'cycle_end'       => Carbon::create(2026, 6, 30)->toDateString(),
                    'title'           => $kpiData['title'],
                    'goal_type'       => 'manual',
                    'target_value'    => $kpiData['target'],
                    'current_value'   => $kpiData['current'],
                    'unit'            => $kpiData['unit'],
                    'direction'       => 'higher_better',
                    'weight_percent'  => 100,
                    'status'          => 'active',
                    'achievement_pct' => round(($kpiData['current'] / $kpiData['target']) * 100),
                    'approved_by'     => $adminEmployee?->id,
                    'approved_at'     => Carbon::now()->subDays(15),
                    'created_by'      => $adminUser->id,
                    'created_at'      => Carbon::now(),
                    'updated_at'      => Carbon::now(),
                ]);
                $count++;
            }
        }

        $this->command->line("  KPI Goals: {$count} mục tiêu.");
    }

    // ────────────────────────────────────────────────────────────────────
    // Helpers
    // ────────────────────────────────────────────────────────────────────
    private function scoreToMaturity(int $score): string
    {
        return match (true) {
            $score >= 80 => 'advanced',
            $score >= 60 => 'intermediate',
            $score >= 40 => 'basic',
            default      => 'initial',
        };
    }
}
