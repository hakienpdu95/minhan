<?php

namespace Modules\BusinessProject\Database\Seeders;

use App\Models\User;
use App\Shared\Tenancy\Models\Organization;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\BusinessProject\Actions\Closing\AttachKnowledgeAssetAction;
use Modules\BusinessProject\Actions\Closing\SaveFinalReportAction;
use Modules\BusinessProject\Actions\Context\CreateBusinessContextAction;
use Modules\BusinessProject\Actions\Conversion\ConvertLeadToBusinessProjectAction;
use Modules\BusinessProject\Actions\CustomerSuccess\AttachSuccessReviewSurveyAction;
use Modules\BusinessProject\Actions\CustomerSuccess\CreateLeadFromOpportunityAction;
use Modules\BusinessProject\Actions\CustomerSuccess\EnsureCsatNpsSurveyAction;
use Modules\BusinessProject\Actions\CustomerSuccess\MarkFollowUpDoneAction;
use Modules\BusinessProject\Actions\CustomerSuccess\StoreSuccessReviewNoteAction;
use Modules\BusinessProject\Actions\Deliverable\ApproveDeliverableAction;
use Modules\BusinessProject\Actions\Deliverable\ConfirmDeliverableAction;
use Modules\BusinessProject\Actions\Deliverable\SubmitDeliverableForApprovalAction;
use Modules\BusinessProject\Actions\Delivery\ApproveChangeRequestAction;
use Modules\BusinessProject\Actions\Delivery\CreateWeeklyReportAction;
use Modules\BusinessProject\Actions\Delivery\EscalateToChangeRequestAction;
use Modules\BusinessProject\Actions\Delivery\RecordIssueAction;
use Modules\BusinessProject\Actions\Delivery\RecordMeetingAction;
use Modules\BusinessProject\Actions\Delivery\RecordRiskAction;
use Modules\BusinessProject\Actions\Delivery\SaveMeetingMinutesAction;
use Modules\BusinessProject\Actions\Delivery\SubmitChangeRequestForApprovalAction;
use Modules\BusinessProject\Actions\Diagnosis\AddDiagnosisFindingAction;
use Modules\BusinessProject\Actions\Diagnosis\AttachEvidenceToDiagnosisAction;
use Modules\BusinessProject\Actions\Diagnosis\SaveDiagnosisOverviewAction;
use Modules\BusinessProject\Actions\Discovery\AddDiscoveryRecordAction;
use Modules\BusinessProject\Actions\Discovery\SaveBusinessDiscoveryReportAction;
use Modules\BusinessProject\Actions\Discovery\SaveTpsCanvasAction;
use Modules\BusinessProject\Actions\StageGate\AdvanceBusinessProjectStageAction;
use Modules\BusinessProject\Actions\Transformation\AddMilestoneAction;
use Modules\BusinessProject\Actions\Transformation\SaveProposalAction;
use Modules\BusinessProject\Actions\Transformation\SaveSowAction;
use Modules\BusinessProject\Actions\Transformation\SaveTransformationDesignCanvasAction;
use Modules\BusinessProject\Actions\Transformation\SaveTransformationRoadmapAction;
use Modules\BusinessProject\Data\Requests\AttachDiagnosisEvidenceData;
use Modules\BusinessProject\Data\Requests\AttachKnowledgeAssetData;
use Modules\BusinessProject\Data\Requests\AttachSuccessReviewSurveyData;
use Modules\BusinessProject\Data\Requests\ConvertLeadToBusinessProjectData;
use Modules\BusinessProject\Data\Requests\CreateLeadFromOpportunityData;
use Modules\BusinessProject\Data\Requests\StoreBusinessContextData;
use Modules\BusinessProject\Data\Requests\StoreBusinessDiscoveryReportData;
use Modules\BusinessProject\Data\Requests\StoreChangeRequestData;
use Modules\BusinessProject\Data\Requests\StoreDiagnosisFindingData;
use Modules\BusinessProject\Data\Requests\StoreDiagnosisOverviewData;
use Modules\BusinessProject\Data\Requests\StoreDiscoveryRecordData;
use Modules\BusinessProject\Data\Requests\StoreFinalReportData;
use Modules\BusinessProject\Data\Requests\StoreIssueData;
use Modules\BusinessProject\Data\Requests\StoreMeetingData;
use Modules\BusinessProject\Data\Requests\StoreMilestoneData;
use Modules\BusinessProject\Data\Requests\StoreProposalData;
use Modules\BusinessProject\Data\Requests\StoreRiskData;
use Modules\BusinessProject\Data\Requests\StoreSowData;
use Modules\BusinessProject\Data\Requests\StoreSuccessReviewNoteData;
use Modules\BusinessProject\Data\Requests\StoreTpsCanvasData;
use Modules\BusinessProject\Data\Requests\StoreTransformationDesignCanvasData;
use Modules\BusinessProject\Data\Requests\StoreTransformationRoadmapData;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\BusinessProjectMember;
use Modules\BusinessProject\Models\Deliverable;
use Modules\KcCategory\Models\KcCategory;
use Modules\KcItem\Models\KcItem;
use Modules\Lead\Actions\CreateLeadAction;
use Modules\Lead\Data\Requests\StoreLeadData;
use Modules\LeadPipelineStage\Models\LeadPipelineStage;
use Modules\LeadSource\Models\LeadSource;
use Modules\Survey\Models\SurveyAnswer;
use Modules\Survey\Models\SurveyField;
use Modules\Survey\Models\SurveyResponse;

/**
 * Dữ liệu demo cho toàn bộ luồng nghiệp vụ Business Consulting OS — 3 Business Project ở 3 mức
 * độ tiến triển khác nhau (đóng hoàn chỉnh / đang Delivery / mới bắt đầu Discovery) để xem được
 * hết cả 8 workspace + BCOS Dashboard có số liệu thật. Chạy trên đúng tổ chức 'demo' (nơi
 * ceo@demo.test / admin@demo.test đăng nhập) — 2 tài khoản này bypass toàn bộ kiểm tra
 * `isMember()` trong BusinessProjectPolicy (role ceo/system_admin), nên KHÔNG cần thêm họ vào
 * `business_project_members` vẫn xem/thao tác được mọi project demo.
 *
 * Idempotent: kiểm tra theo tên project demo (mã `BP-*` tự sinh theo năm/thứ tự, không dùng
 * được để chống trùng) — chạy lại không tạo thêm bản sao.
 *
 * Mọi bước đều gọi ĐÚNG Action thật của từng workspace (không tự chế insert) — cùng nguyên tắc
 * đã áp dụng xuyên suốt toàn bộ quá trình build BCOS.
 */
class BcosDemoSeeder extends Seeder
{
    private Organization $org;

    private User $ceo;

    private User $leadConsultant;

    private User $consultant;

    private User $pm;

    private User $customerSuccess;

    private KcCategory $kcCategory;

    public function run(): void
    {
        $org = Organization::where('slug', 'demo')->first();

        if (! $org) {
            $this->command?->warn('  ⚠ Không tìm thấy tổ chức "demo" — bỏ qua BcosDemoSeeder.');

            return;
        }

        $this->org = $org;

        TenantContext::set($org);
        setPermissionsTeamId($org->id);

        $ceo = User::where('email', 'ceo@demo.test')->first();

        if (! $ceo) {
            $this->command?->warn('  ⚠ Không tìm thấy ceo@demo.test — bỏ qua BcosDemoSeeder.');

            return;
        }

        $this->ceo = $ceo;

        if (BusinessProject::withoutTenant()->where('organization_id', $org->id)->where('name', 'like', '%(Demo)%')->exists()) {
            $this->command?->info('  BCOS demo data đã tồn tại, bỏ qua.');

            return;
        }

        $this->ensureDemoTeam();
        $this->kcCategory = $this->ensureKcCategory();

        $this->buildClosedProject();
        $this->buildDeliveryProject();
        $this->buildEarlyProject();

        Auth::logout();

        $this->command?->info('  ✓ Đã seed 3 Business Project demo (Closed → Customer Success, Delivery, Discovery).');
    }

    // ── Setup dùng chung ───────────────────────────────────────────────────

    private function ensureDemoTeam(): void
    {
        $definitions = [
            'leadConsultant'  => ['name' => 'Phạm Minh Đức',  'email' => 'lead.consultant@demo.test', 'role' => 'lead_consultant'],
            'consultant'      => ['name' => 'Nguyễn Thị Lan',  'email' => 'consultant@demo.test',      'role' => 'consultant'],
            'pm'              => ['name' => 'Lê Văn Hùng',     'email' => 'pm@demo.test',              'role' => 'pm'],
            'customerSuccess' => ['name' => 'Trần Thị Mai',    'email' => 'cs@demo.test',              'role' => 'customer_success'],
        ];

        foreach ($definitions as $prop => $def) {
            $user = User::firstOrCreate(
                ['email' => $def['email']],
                [
                    'name' => $def['name'],
                    'password' => Hash::make('password'),
                    'organization_id' => $this->org->id,
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$def['role']]);

            $this->$prop = $user;
        }
    }

    private function ensureKcCategory(): KcCategory
    {
        return KcCategory::withoutGlobalScopes()->where('organization_id', $this->org->id)->where('name', 'Case Study & Lessons Learned')->first()
            ?? KcCategory::create([
                'uuid' => (string) Str::uuid(),
                'organization_id' => $this->org->id,
                'name' => 'Case Study & Lessons Learned',
                'slug' => 'case-study-lessons-learned',
                'description' => 'Tri thức đúc kết từ các dự án tư vấn đã triển khai (BCOS Rule R7).',
                'is_active' => true,
                'sort_order' => 1,
                'created_by' => $this->ceo->id,
            ]);
    }

    private function asUser(User $user): void
    {
        Auth::login($user);
    }

    /**
     * Giai đoạn 0 (spec) — Lead → Customer → Business Project, qua đúng
     * ConvertLeadToBusinessProjectAction (dùng lại CreateLeadAction của module Lead, không tự
     * chế logic tạo Lead/Customer).
     */
    private function createDemoProject(
        string $projectName,
        string $contactName,
        string $companyName,
        string $industry,
        string $expectedValue,
    ): BusinessProject {
        $this->asUser($this->leadConsultant);

        $stageNew = LeadPipelineStage::where(function ($q) {
            $q->whereNull('organization_id')->orWhere('organization_id', $this->org->id);
        })->where('is_won', false)->where('is_lost', false)->orderBy('sort_order')->firstOrFail();

        $source = LeadSource::where('code', 'referral')->first();

        $lead = CreateLeadAction::run(new StoreLeadData(
            organization_id: $this->org->id,
            contact_name: $contactName,
            contact_phone: '09' . random_int(10000000, 99999999),
            contact_email: Str::slug($contactName) . '@' . Str::slug($companyName) . '.example.vn',
            contact_company: $companyName,
            stage_id: $stageNew->id,
            source_id: $source?->id,
            source_detail: 'Demo seed — BCOS',
            expected_value: (float) $expectedValue,
            title: "Tư vấn: {$projectName}",
            description: "Cơ hội tư vấn cho {$companyName}.",
        ), $this->org->id);

        $businessProject = ConvertLeadToBusinessProjectAction::run($lead, ConvertLeadToBusinessProjectData::from([
            'name' => "{$projectName} (Demo)",
            'project_role' => 'lead_consultant',
        ]));

        $businessProject->customer()->update(['industry' => $industry]);

        foreach ([
            ['user_id' => $this->consultant->id, 'project_role' => 'consultant'],
            ['user_id' => $this->pm->id, 'project_role' => 'pm'],
            ['user_id' => $this->customerSuccess->id, 'project_role' => 'customer_success'],
        ] as $member) {
            BusinessProjectMember::firstOrCreate([
                'business_project_id' => $businessProject->id,
                'user_id' => $member['user_id'],
            ], ['project_role' => $member['project_role']]);
        }

        return $businessProject->fresh();
    }

    // ── Giai đoạn 1 — Context ───────────────────────────────────────────────

    private function completeContext(BusinessProject $businessProject, array $data): void
    {
        $this->asUser($this->leadConsultant);

        CreateBusinessContextAction::run($businessProject, StoreBusinessContextData::from($data));

        $context = $businessProject->context()->first();
        SubmitDeliverableForApprovalAction::run($context->deliverable);

        $this->asUser($this->ceo);
        ApproveDeliverableAction::run($context->deliverable->fresh(), 'Đồng ý — phạm vi rõ ràng, tiếp tục Discovery.');

        $this->advance($businessProject);
    }

    // ── Giai đoạn 2 — Discovery ─────────────────────────────────────────────

    private function completeDiscovery(BusinessProject $businessProject, array $records, array $tps, string $summary): void
    {
        $this->asUser($this->consultant);

        foreach ($records as $record) {
            AddDiscoveryRecordAction::run($businessProject, StoreDiscoveryRecordData::from($record));
        }

        SaveTpsCanvasAction::run($businessProject, StoreTpsCanvasData::from($tps));
        SaveBusinessDiscoveryReportAction::run($businessProject, StoreBusinessDiscoveryReportData::from(['summary' => $summary]));

        $this->advance($businessProject);
    }

    // ── Giai đoạn 3 — Diagnosis ─────────────────────────────────────────────

    private function completeDiagnosis(BusinessProject $businessProject, string $overview, array $findings): void
    {
        $this->asUser($this->consultant);

        SaveDiagnosisOverviewAction::run($businessProject, StoreDiagnosisOverviewData::from(['overview' => $overview]));

        foreach ($findings as $finding) {
            AddDiagnosisFindingAction::run($businessProject, StoreDiagnosisFindingData::from($finding));
        }

        $discoveryReport = $businessProject->deliverables()
            ->where('type', 'business_discovery_report')
            ->whereNull('parent_id')
            ->first();

        if ($discoveryReport) {
            AttachEvidenceToDiagnosisAction::run($businessProject, AttachDiagnosisEvidenceData::from([
                'evidence_deliverable_id' => $discoveryReport->id,
                'evidence_type' => 'document_review',
                'note' => 'Căn cứ tổng hợp từ Business Discovery Report.',
            ]));
        }

        $diagnosisReport = $businessProject->deliverables()->where('type', 'diagnosis_report')->whereNull('parent_id')->first();
        SubmitDeliverableForApprovalAction::run($diagnosisReport);

        $this->asUser($this->ceo);
        ApproveDeliverableAction::run($diagnosisReport->fresh(), 'Đồng ý ưu tiên theo Impact–Effort Matrix.');

        $this->advance($businessProject);
    }

    // ── Giai đoạn 4 — Transformation ────────────────────────────────────────

    private function completeTransformation(BusinessProject $businessProject, array $canvas, array $milestones, array $proposal, array $sow): void
    {
        $this->asUser($this->leadConsultant);

        SaveTransformationDesignCanvasAction::run($businessProject, StoreTransformationDesignCanvasData::from($canvas));
        SaveTransformationRoadmapAction::run($businessProject, StoreTransformationRoadmapData::from([
            'overview' => 'Lộ trình chuyển đổi 3 tầng: Quick Wins trong tháng đầu, xây năng lực trong 90 ngày, chuẩn hoá toàn diện trong năm.',
        ]));

        foreach ($milestones as $milestone) {
            AddMilestoneAction::run($businessProject, StoreMilestoneData::from($milestone));
        }

        $proposalDeliverable = SaveProposalAction::run($businessProject, StoreProposalData::from($proposal));
        $sowDeliverable = SaveSowAction::run($businessProject, StoreSowData::from($sow));

        SubmitDeliverableForApprovalAction::run($proposalDeliverable);
        SubmitDeliverableForApprovalAction::run($sowDeliverable);

        $this->asUser($this->ceo);
        ApproveDeliverableAction::run($proposalDeliverable->fresh(), 'Duyệt nội bộ — sẵn sàng gửi khách.');
        ApproveDeliverableAction::run($sowDeliverable->fresh(), 'Duyệt nội bộ — sẵn sàng gửi khách.');

        $this->asUser($this->pm);
        ConfirmDeliverableAction::run($proposalDeliverable->fresh());
        ConfirmDeliverableAction::run($sowDeliverable->fresh());

        $this->advance($businessProject);
    }

    // ── Giai đoạn 5 — Delivery ──────────────────────────────────────────────

    /**
     * Task integration (AttachTaskToProjectAction) CỐ Ý bỏ qua ở demo này — module Task bắt
     * buộc `project_id` trỏ tới generic Project, mà generic Project lại bắt buộc `owner_id` trỏ
     * tới 1 Employee thật (employees.branch_id/department_id cũng NOT NULL). Tổ chức 'demo'
     * hiện chưa có Employee nào — bootstrap cả chuỗi Branch→Department→Employee chỉ để đính 2
     * Task demo là việc của seeder HR riêng, ngoài phạm vi "demo luồng nghiệp vụ BCOS". Delivery
     * Workspace vẫn được minh hoạ đầy đủ qua Meeting/Weekly Report/Issue/Risk/Change Request.
     */
    private function completeDelivery(BusinessProject $businessProject, bool $withChangeRequest): void
    {
        $this->asUser($this->pm);

        $meeting = RecordMeetingAction::run($businessProject, StoreMeetingData::from([
            'type' => 'weekly_review',
            'title' => 'Weekly Review tuần 1',
            'held_at' => now()->subWeek()->toDateTimeString(),
        ]));

        SaveMeetingMinutesAction::run($meeting, \Modules\BusinessProject\Data\Requests\SaveMeetingMinutesData::from([
            'minutes' => 'Đã hoàn thành Quick Win đầu tiên, thống nhất kế hoạch đào tạo tuần tới.',
            'action_items' => "Hoàn tất tài liệu đào tạo\nLên lịch buổi đào tạo với team vận hành",
        ]));

        CreateWeeklyReportAction::run($businessProject, \Modules\BusinessProject\Data\Requests\StoreWeeklyReportData::from([
            'narrative' => 'Tiến độ đúng kế hoạch, đội ngũ khách hàng phối hợp tốt.',
        ]));

        $issue = RecordIssueAction::run($businessProject, StoreIssueData::from([
            'title' => 'Dữ liệu tồn kho chưa đồng bộ giữa các chi nhánh',
            'description' => 'Phát hiện lúc đối chiếu số liệu tuần đầu triển khai.',
            'severity' => 'medium',
        ]));

        RecordRiskAction::run($businessProject, StoreRiskData::from([
            'title' => 'Nhân sự vận hành có thể nghỉ việc giữa dự án',
            'description' => 'Đã có dấu hiệu luân chuyển nhân sự ở phòng vận hành.',
            'likelihood' => 'medium',
            'impact' => 'medium',
        ]));

        if ($withChangeRequest) {
            $changeRequest = EscalateToChangeRequestAction::run($issue, StoreChangeRequestData::from([
                'title' => 'Bổ sung hạng mục đồng bộ dữ liệu liên chi nhánh',
                'description' => 'Issue tồn kho không đồng bộ ảnh hưởng tới phạm vi ban đầu, cần bổ sung hạng mục.',
                'impacts_scope' => false,
            ]));

            SubmitChangeRequestForApprovalAction::run($changeRequest);

            $this->asUser($this->ceo);
            ApproveChangeRequestAction::run($changeRequest->fresh(), 'Đồng ý bổ sung hạng mục, không ảnh hưởng SOW.');
        }
    }

    // ── Giai đoạn 6 — Closing ───────────────────────────────────────────────

    private function completeClosing(BusinessProject $businessProject, string $summary, string $kcTitle, string $kcType, string $kcContent): KcItem
    {
        $this->asUser($this->pm);

        $finalReport = SaveFinalReportAction::run($businessProject, StoreFinalReportData::from(['summary' => $summary]));
        SubmitDeliverableForApprovalAction::run($finalReport);

        $this->asUser($this->ceo);
        ApproveDeliverableAction::run($finalReport->fresh(), 'Duyệt Final Report — dự án đạt mục tiêu đề ra.');

        $kcItem = KcItem::create([
            'uuid' => (string) Str::uuid(),
            'category_id' => $this->kcCategory->id,
            'organization_id' => $businessProject->organization_id,
            'title' => $kcTitle,
            'slug' => Str::slug($kcTitle) . '-' . Str::random(6),
            'type' => $kcType,
            'industry' => $businessProject->customer?->industry,
            'summary' => Str::limit($kcContent, 150),
            'content' => $kcContent,
            'status' => 'approved',
            'visibility' => 'internal',
            'owner_id' => $this->leadConsultant->id,
            'created_by' => $this->leadConsultant->id,
        ]);

        $this->asUser($this->pm);
        AttachKnowledgeAssetAction::run($businessProject, AttachKnowledgeAssetData::from(['kc_item_id' => $kcItem->id]));

        $this->advance($businessProject); // Closing -> Knowledge (đóng dự án)
        $this->advance($businessProject); // Knowledge -> Customer Success (gate trivial)

        return $kcItem;
    }

    // ── Giai đoạn 8 — Customer Success ──────────────────────────────────────

    private function completeCustomerSuccess(BusinessProject $businessProject, int $csatScore, int $npsScore, string $note): void
    {
        $this->asUser($this->customerSuccess);

        $survey = EnsureCsatNpsSurveyAction::run($businessProject->organization_id);

        $response = SurveyResponse::create([
            'survey_id' => $survey->id,
            'respondent_ref' => $businessProject->customer?->display_name ?? $businessProject->name,
            'status' => \Modules\Survey\Enums\ResponseStatus::Complete->value,
            'submitted_at' => now(),
        ]);

        $npsField = SurveyField::where('survey_id', $survey->id)->where('field_type', \Modules\Survey\Enums\FieldType::Nps->value)->first();
        $csatField = SurveyField::where('survey_id', $survey->id)->where('field_type', \Modules\Survey\Enums\FieldType::Rating->value)->first();

        if ($npsField) {
            SurveyAnswer::create(['response_id' => $response->id, 'field_id' => $npsField->id, 'value_number' => $npsScore]);
        }

        if ($csatField) {
            SurveyAnswer::create(['response_id' => $response->id, 'field_id' => $csatField->id, 'value_number' => $csatScore]);
        }

        AttachSuccessReviewSurveyAction::run($businessProject, AttachSuccessReviewSurveyData::from([
            'survey_response_id' => $response->id,
        ]));

        $review = StoreSuccessReviewNoteAction::run($businessProject, StoreSuccessReviewNoteData::from([
            'follow_up_at' => now()->addMonth()->toDateString(),
            'follow_up_note' => $note,
            'renewal_status' => 'considering',
            'renewal_note' => 'Khách hàng hài lòng, đang cân nhắc gói đồng hành giai đoạn tiếp theo.',
        ]));

        MarkFollowUpDoneAction::run($review);

        CreateLeadFromOpportunityAction::run($businessProject, CreateLeadFromOpportunityData::from([
            'title' => "Giai đoạn 2 — Mở rộng cùng {$businessProject->customer?->display_name}",
            'description' => 'New Opportunity từ Customer Success — khách muốn mở rộng phạm vi đồng hành.',
            'expected_value' => 150_000_000,
        ]));
    }

    private function advance(BusinessProject $businessProject): void
    {
        $this->asUser($this->ceo);
        AdvanceBusinessProjectStageAction::run($businessProject->fresh());
    }

    // ═════════════════════════════════════════════════════════════════════
    // Project 1 — Đóng hoàn chỉnh, đi hết vòng đời tới Customer Success
    // ═════════════════════════════════════════════════════════════════════

    private function buildClosedProject(): void
    {
        $bp = $this->createDemoProject(
            projectName: 'Chuỗi Nhà hàng Vàng — Chuyển đổi số vận hành',
            contactName: 'Ông Đặng Văn Hòa',
            companyName: 'Nhà hàng Vàng',
            industry: 'Nhà hàng - Dịch vụ ăn uống',
            expectedValue: '180000000',
        );

        $this->completeContext($bp, [
            'company_profile' => ['notes' => 'Chuỗi 5 nhà hàng tại TP.HCM, 120 nhân sự, doanh thu ~40 tỷ/năm. Đang gặp khó khăn trong quản lý tồn kho nguyên liệu và quy trình order giữa các chi nhánh.'],
            'stakeholders' => ['notes' => 'Ông Đặng Văn Hòa (CEO, người ra quyết định chính), Bà Nguyễn Thu (Quản lý vận hành 5 chi nhánh), Đội bếp trưởng từng chi nhánh.'],
            'strategic_goals' => ['notes' => 'Chuẩn hoá quy trình order-tồn kho-thanh toán trong 6 tháng, giảm thất thoát nguyên liệu 15%, mở rộng thêm 2 chi nhánh trong năm tới.'],
        ]);

        $this->completeDiscovery(
            $bp,
            records: [
                ['type' => 'interview', 'title' => 'Phỏng vấn CEO về mục tiêu chuyển đổi', 'notes' => 'CEO nhấn mạnh vấn đề thất thoát nguyên liệu và thiếu số liệu real-time giữa các chi nhánh.', 'occurred_at' => now()->subDays(20)->toDateString(), 'participants' => 'Ông Đặng Văn Hòa'],
                ['type' => 'observation', 'title' => 'Quan sát quy trình order tại chi nhánh Q1', 'notes' => 'Order vẫn ghi tay, đối chiếu cuối ngày thủ công, dễ sai lệch.', 'occurred_at' => now()->subDays(18)->toDateString(), 'participants' => 'Bếp trưởng chi nhánh Q1'],
                ['type' => 'document_review', 'title' => 'Rà soát sổ sách tồn kho 3 tháng gần nhất', 'notes' => 'Phát hiện chênh lệch tồn kho trung bình 8-12%/tháng giữa sổ sách và kiểm kê thực tế.', 'occurred_at' => now()->subDays(15)->toDateString(), 'participants' => null],
            ],
            tps: [
                'problem' => 'Thất thoát nguyên liệu do quy trình tồn kho thủ công, thiếu đồng bộ dữ liệu giữa 5 chi nhánh.',
                'goal' => 'Chuẩn hoá quy trình order-tồn kho, giảm thất thoát nguyên liệu về dưới 5%.',
                'scope' => 'Trong phạm vi: quy trình order, quản lý tồn kho, báo cáo vận hành. Ngoài phạm vi: hệ thống POS, marketing.',
            ],
            summary: 'Doanh nghiệp đang vận hành 5 chi nhánh với quy trình order-tồn kho thủ công, thiếu chuẩn hoá, dẫn tới thất thoát nguyên liệu 8-12%/tháng. Cần số hoá quy trình cốt lõi trước khi mở rộng.',
        );

        $this->completeDiagnosis(
            $bp,
            overview: 'Nguyên nhân gốc: thiếu quy trình chuẩn + công cụ hỗ trợ đối chiếu tồn kho real-time. Ưu tiên xử lý Quick Win về quy trình trước khi đầu tư công cụ.',
            findings: [
                ['problem' => 'Order ghi tay, dễ sai lệch giữa bếp và thu ngân', 'category' => 'process', 'root_cause' => 'Chưa có quy trình chuẩn hoá order xuyên chi nhánh', 'impact' => 'high', 'effort' => 'low'],
                ['problem' => 'Không có báo cáo tồn kho real-time', 'category' => 'digital', 'root_cause' => 'Chưa đầu tư công cụ quản lý tồn kho phù hợp quy mô', 'impact' => 'high', 'effort' => 'high'],
                ['problem' => 'Nhân sự bếp chưa được đào tạo quy trình chuẩn', 'category' => 'people', 'root_cause' => 'Thiếu chương trình đào tạo onboarding', 'impact' => 'medium', 'effort' => 'low'],
            ],
        );

        $this->completeTransformation(
            $bp,
            canvas: [
                'business_goal' => 'Chuẩn hoá vận hành 5 chi nhánh, sẵn sàng mở rộng thêm 2 chi nhánh trong năm tới.',
                'priority_problems' => 'Thất thoát nguyên liệu, order thủ công thiếu chuẩn hoá.',
                'transformation_objectives' => 'Giảm thất thoát nguyên liệu về dưới 5% trong 6 tháng.',
                'key_initiatives' => 'Chuẩn hoá SOP order-tồn kho; đào tạo đội ngũ; triển khai công cụ đối chiếu tồn kho.',
                'quick_wins' => 'Ban hành SOP order chuẩn cho 5 chi nhánh trong tháng đầu.',
                'resources' => '1 Lead Consultant, 1 Consultant, đội vận hành phía khách hàng.',
                'risks' => 'Nhân sự vận hành có thể luân chuyển giữa dự án.',
                'success_metrics' => 'Tỷ lệ thất thoát nguyên liệu, thời gian đối chiếu tồn kho cuối ngày.',
            ],
            milestones: [
                ['category' => 'quick_win', 'title' => 'Ban hành SOP order chuẩn', 'description' => 'Áp dụng cho cả 5 chi nhánh.', 'target_date' => now()->addDays(14)->toDateString()],
                ['category' => 'day_90', 'title' => 'Triển khai công cụ đối chiếu tồn kho', 'description' => 'Đồng bộ dữ liệu real-time giữa các chi nhánh.', 'target_date' => now()->addDays(90)->toDateString()],
                ['category' => 'day_365', 'title' => 'Mở rộng thêm 2 chi nhánh mới', 'description' => 'Áp dụng quy trình đã chuẩn hoá cho chi nhánh mới.', 'target_date' => now()->addDays(365)->toDateString()],
            ],
            proposal: [
                'solution' => 'Chuẩn hoá SOP order-tồn kho, đào tạo đội ngũ vận hành, triển khai công cụ đối chiếu tồn kho real-time cho 5 chi nhánh.',
                'collaboration_plan' => 'Làm việc trực tiếp với đội vận hành 2 buổi/tuần trong 3 tháng đầu, Weekly Review với CEO.',
            ],
            sow: [
                'scope' => 'Chuẩn hoá quy trình order-tồn kho cho 5 chi nhánh hiện có. Không bao gồm hệ thống POS/marketing.',
                'deliverables' => 'SOP order-tồn kho chuẩn, chương trình đào tạo, báo cáo đối chiếu tồn kho hàng tuần.',
                'responsibilities' => 'THUCHOCVN: tư vấn + đào tạo. Khách hàng: bố trí nhân sự tham gia, cung cấp số liệu vận hành.',
            ],
        );

        $this->completeDelivery($bp, withChangeRequest: true);

        $kcItem = $this->completeClosing(
            $bp,
            summary: 'Dự án đạt mục tiêu đề ra: SOP order-tồn kho chuẩn được áp dụng tại cả 5 chi nhánh, tỷ lệ thất thoát nguyên liệu giảm từ 10% xuống còn 4.5%. Khách hàng hài lòng và đang cân nhắc giai đoạn 2 mở rộng.',
            kcTitle: 'Lessons Learned — Chuẩn hoá vận hành chuỗi nhà hàng đa chi nhánh',
            kcType: 'lessons_learned',
            kcContent: 'Bài học rút ra: (1) Chuẩn hoá SOP trước khi đầu tư công cụ luôn mang lại Quick Win nhanh và rẻ hơn. (2) Đào tạo trực tiếp tại chi nhánh hiệu quả hơn đào tạo tập trung với mô hình chuỗi F&B. (3) Cần cam kết rõ với CEO về thời gian nhân sự vận hành tham gia dự án ngay từ đầu để tránh rủi ro luân chuyển giữa dự án.',
        );

        $this->completeCustomerSuccess(
            $bp,
            csatScore: 5,
            npsScore: 9,
            note: 'Theo dõi tiến độ áp dụng SOP tại 2 chi nhánh mới nếu khách hàng mở rộng.',
        );
    }

    // ═════════════════════════════════════════════════════════════════════
    // Project 2 — Đang ở Delivery (dự án "sống", chưa đóng)
    // ═════════════════════════════════════════════════════════════════════

    private function buildDeliveryProject(): void
    {
        $bp = $this->createDemoProject(
            projectName: 'Xưởng May Thành Công — Tối ưu quy trình sản xuất',
            contactName: 'Bà Vũ Thị Hạnh',
            companyName: 'Xưởng May Thành Công',
            industry: 'Sản xuất - May mặc',
            expectedValue: '220000000',
        );

        $this->completeContext($bp, [
            'company_profile' => ['notes' => 'Xưởng may gia công 200 công nhân, chuyên đơn hàng xuất khẩu. Năng suất chưa ổn định, tỷ lệ lỗi sản phẩm còn cao.'],
            'stakeholders' => ['notes' => 'Bà Vũ Thị Hạnh (Giám đốc xưởng), Quản đốc các chuyền may.'],
            'strategic_goals' => ['notes' => 'Tăng năng suất chuyền may 20%, giảm tỷ lệ lỗi sản phẩm xuống dưới 2% trong 4 tháng.'],
        ]);

        $this->completeDiscovery(
            $bp,
            records: [
                ['type' => 'interview', 'title' => 'Phỏng vấn Giám đốc về mục tiêu tối ưu sản xuất', 'notes' => 'Giám đốc muốn chuẩn hoá quy trình kiểm soát chất lượng giữa các chuyền.', 'occurred_at' => now()->subDays(10)->toDateString(), 'participants' => 'Bà Vũ Thị Hạnh'],
                ['type' => 'observation', 'title' => 'Quan sát chuyền may số 3', 'notes' => 'Công đoạn kiểm tra chất lượng cuối chuyền còn thủ công, thiếu checklist chuẩn.', 'occurred_at' => now()->subDays(8)->toDateString(), 'participants' => 'Quản đốc chuyền 3'],
            ],
            tps: [
                'problem' => 'Tỷ lệ lỗi sản phẩm cao do thiếu checklist kiểm soát chất lượng chuẩn hoá giữa các chuyền.',
                'goal' => 'Giảm tỷ lệ lỗi sản phẩm xuống dưới 2%.',
                'scope' => 'Trong phạm vi: quy trình kiểm soát chất lượng, đào tạo quản đốc. Ngoài phạm vi: đầu tư máy móc mới.',
            ],
            summary: 'Xưởng may có năng suất chưa ổn định, tỷ lệ lỗi sản phẩm ~6%, chủ yếu do thiếu checklist kiểm soát chất lượng chuẩn hoá giữa các chuyền may.',
        );

        $this->completeDiagnosis(
            $bp,
            overview: 'Nguyên nhân gốc: thiếu checklist kiểm soát chất lượng chuẩn, quản đốc từng chuyền tự áp dụng cách kiểm tra khác nhau.',
            findings: [
                ['problem' => 'Không có checklist kiểm soát chất lượng thống nhất', 'category' => 'process', 'root_cause' => 'Mỗi chuyền tự phát triển cách kiểm tra riêng', 'impact' => 'high', 'effort' => 'low'],
                ['problem' => 'Quản đốc chưa được đào tạo phương pháp kiểm soát chất lượng bài bản', 'category' => 'people', 'root_cause' => 'Thiếu chương trình đào tạo quản đốc', 'impact' => 'medium', 'effort' => 'high'],
            ],
        );

        $this->completeTransformation(
            $bp,
            canvas: [
                'business_goal' => 'Tăng năng suất chuyền may 20%, giảm tỷ lệ lỗi dưới 2%.',
                'priority_problems' => 'Thiếu checklist kiểm soát chất lượng chuẩn hoá.',
                'transformation_objectives' => 'Chuẩn hoá quy trình kiểm soát chất lượng toàn bộ chuyền may trong 90 ngày.',
                'key_initiatives' => 'Xây checklist QC chuẩn; đào tạo quản đốc; thí điểm 1 chuyền trước khi nhân rộng.',
                'quick_wins' => 'Checklist QC chuẩn cho chuyền thí điểm trong 2 tuần đầu.',
                'resources' => '1 Lead Consultant, 1 Consultant, quản đốc các chuyền.',
                'risks' => 'Công nhân có thể phản ứng với quy trình kiểm soát mới.',
                'success_metrics' => 'Tỷ lệ lỗi sản phẩm, năng suất/chuyền/ngày.',
            ],
            milestones: [
                ['category' => 'quick_win', 'title' => 'Checklist QC thí điểm chuyền 3', 'description' => null, 'target_date' => now()->addDays(14)->toDateString()],
                ['category' => 'day_90', 'title' => 'Nhân rộng checklist QC toàn bộ chuyền', 'description' => null, 'target_date' => now()->addDays(90)->toDateString()],
            ],
            proposal: [
                'solution' => 'Xây dựng checklist kiểm soát chất lượng chuẩn, đào tạo quản đốc, thí điểm rồi nhân rộng toàn xưởng.',
                'collaboration_plan' => 'Làm việc trực tiếp tại xưởng 2 buổi/tuần trong giai đoạn thí điểm.',
            ],
            sow: [
                'scope' => 'Chuẩn hoá quy trình kiểm soát chất lượng cho toàn bộ chuyền may hiện có.',
                'deliverables' => 'Checklist QC chuẩn, chương trình đào tạo quản đốc, báo cáo tỷ lệ lỗi hàng tuần.',
                'responsibilities' => 'THUCHOCVN: tư vấn + đào tạo. Khách hàng: bố trí quản đốc tham gia đào tạo.',
            ],
        );

        // Dự án đang "sống" ở Delivery — chưa escalate Change Request, chưa đóng dự án.
        $this->completeDelivery($bp, withChangeRequest: false);
    }

    // ═════════════════════════════════════════════════════════════════════
    // Project 3 — Mới bắt đầu, còn ở Discovery (gate chưa đủ điều kiện)
    // ═════════════════════════════════════════════════════════════════════

    private function buildEarlyProject(): void
    {
        $bp = $this->createDemoProject(
            projectName: 'Nông sản Sạch Ba Miền — Mở rộng kênh bán hàng online',
            contactName: 'Anh Nguyễn Văn Tài',
            companyName: 'Nông sản Sạch Ba Miền',
            industry: 'Nông nghiệp - Bán lẻ',
            expectedValue: '90000000',
        );

        $this->completeContext($bp, [
            'company_profile' => ['notes' => 'Cửa hàng nông sản sạch, 3 điểm bán tại Hà Nội, đang muốn mở rộng kênh bán hàng online.'],
            'stakeholders' => ['notes' => 'Anh Nguyễn Văn Tài (Chủ cửa hàng, người ra quyết định).'],
            'strategic_goals' => ['notes' => 'Xây kênh bán hàng online, tăng doanh thu 30% trong 6 tháng.'],
        ]);

        // Cố ý dừng ở Discovery, CHƯA đủ điều kiện gate (chỉ 1 Interview + TPS Canvas thiếu
        // trường, CHƯA có Business Discovery Report) — demo đúng tính năng gate checklist chặn
        // đúng khi thiếu điều kiện, không phải lỗi.
        $this->asUser($this->consultant);

        AddDiscoveryRecordAction::run($bp, StoreDiscoveryRecordData::from([
            'type' => 'interview',
            'title' => 'Phỏng vấn chủ cửa hàng về kênh bán hàng hiện tại',
            'notes' => 'Hiện chỉ bán tại 3 điểm offline, chưa có kênh online chính thức, chủ yếu qua giới thiệu truyền miệng.',
            'occurred_at' => now()->subDays(3)->toDateString(),
            'participants' => 'Anh Nguyễn Văn Tài',
        ]));

        SaveTpsCanvasAction::run($bp, StoreTpsCanvasData::from([
            'problem' => 'Chưa có kênh bán hàng online, phụ thuộc hoàn toàn vào 3 điểm bán offline.',
            'goal' => null,
            'scope' => null,
        ]));
    }
}
