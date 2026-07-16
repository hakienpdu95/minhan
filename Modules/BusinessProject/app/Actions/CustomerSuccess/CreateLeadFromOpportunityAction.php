<?php

namespace Modules\BusinessProject\Actions\CustomerSuccess;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Data\Requests\CreateLeadFromOpportunityData;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\SuccessReview;
use Modules\Lead\Actions\CreateLeadAction;
use Modules\Lead\Data\Requests\StoreLeadData;
use Modules\Lead\Models\Lead;
use Modules\LeadPipelineStage\Models\LeadPipelineStage;
use Modules\LeadSource\Models\LeadSource;

/**
 * Giai đoạn 8 — "New Opportunity → Tạo Lead" — chiều NGƯỢC của
 * ConvertLeadToBusinessProjectAction (Lead -> BusinessProject ở Context Workspace). Không tự
 * chế logic tạo Lead, tái dùng nguyên `CreateLeadAction` của module Lead (dedup contact, stage
 * history, LeadCreated event, scoring...). Source = LeadSource code `business_project`
 * (Modules/Lead/database/seeders/LeadSourcesSeeder), source_detail = code dự án cũ — khép vòng
 * lặp toàn hệ thống (spec Phần 3 "Vòng thương mại").
 */
class CreateLeadFromOpportunityAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, CreateLeadFromOpportunityData $data): Lead
    {
        $customer = $businessProject->customer;

        $stage = LeadPipelineStage::where(function ($q) use ($businessProject) {
                $q->whereNull('organization_id')->orWhere('organization_id', $businessProject->organization_id);
            })
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->firstOrFail();

        $source = LeadSource::where('code', 'business_project')->first();

        $lead = CreateLeadAction::run(new StoreLeadData(
            organization_id: $businessProject->organization_id,
            contact_name: $customer?->display_name ?? $businessProject->name,
            contact_phone: $customer?->primary_phone,
            contact_email: $customer?->primary_email,
            contact_company: $customer?->company_name ?? $customer?->display_name,
            stage_id: $stage->id,
            source_id: $source?->id,
            source_detail: "New Opportunity từ dự án {$businessProject->code}",
            expected_value: $data->expected_value,
            title: $data->title ?? "Cơ hội mới — {$businessProject->name}",
            description: $data->description,
        ), $businessProject->organization_id);

        SuccessReview::create([
            'organization_id' => $businessProject->organization_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'business_project_id' => $businessProject->id,
            'new_lead_id' => $lead->id,
            'created_by' => Auth::id(),
        ]);

        return $lead;
    }
}
