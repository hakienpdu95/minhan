<?php

namespace Modules\BusinessProject\Actions\Conversion;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Data\Requests\ConvertLeadToBusinessProjectData;
use Modules\BusinessProject\Enums\BusinessProjectStage;
use Modules\BusinessProject\Events\BusinessProjectCreatedFromLead;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\BusinessProjectMember;
use Modules\Customer\Actions\Conversion\ConvertLeadToCustomerAction;
use Modules\Lead\Models\Lead;

class ConvertLeadToBusinessProjectAction
{
    use AsAction;

    public function handle(Lead $lead, ConvertLeadToBusinessProjectData $data): BusinessProject
    {
        $businessProject = DB::transaction(function () use ($lead, $data): BusinessProject {
            // Business Project luôn gắn với 1 Customer (= "Organization" trong ngôn ngữ
            // Handbook — doanh nghiệp đang được tư vấn). Nếu Lead chưa convert Customer,
            // dùng lại đúng Action đã có sẵn thay vì tự viết logic dedup/tạo Customer lần 2.
            if ($lead->customer_id === null) {
                ConvertLeadToCustomerAction::run($lead);
                $lead->refresh();
            }

            $businessProject = BusinessProject::create([
                'organization_id' => $lead->organization_id,
                'uuid' => Str::uuid(),
                'customer_id' => $lead->customer_id,
                'lead_id' => $lead->id,
                'code' => $this->generateCode($lead->organization_id),
                'name' => $data->name,
                'current_stage' => BusinessProjectStage::Context->value,
                'status' => 'active',
                'created_by' => Auth::id(),
            ]);

            BusinessProjectMember::create([
                'business_project_id' => $businessProject->id,
                'user_id' => Auth::id(),
                'project_role' => $data->project_role,
            ]);

            $lead->update(['converted_business_project_id' => $businessProject->id]);

            return $businessProject;
        });

        event(new BusinessProjectCreatedFromLead($businessProject, $lead));

        return $businessProject;
    }

    private function generateCode(int $organizationId): string
    {
        $year = now()->year;
        $seq = BusinessProject::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('BP-%d-%04d', $year, $seq);
    }
}
