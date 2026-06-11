<?php

namespace Modules\Assessment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Assessment\Models\CareerPathwayStep;
use Modules\Assessment\Models\WorkforceProfile;

class CareerPathwayController extends Controller
{
    public function index(Request $request): View
    {
        $user  = $request->user();
        $orgId = TenantContext::getOrganizationId();

        $profile = WorkforceProfile::withoutTenant()
            ->where('organization_id', $orgId)
            ->where('user_id', $user->id)
            ->first();

        $steps = CareerPathwayStep::whereNull('organization_id')
            ->orWhere('organization_id', $orgId)
            ->where('is_active', true)
            ->orderBy('step_order')
            ->get();

        $currentLevel = $profile?->tdwcf_maturity_level ?? 'DIGITAL_BEGINNER';

        $levelOrder = [
            'DIGITAL_BEGINNER'     => 0,
            'DIGITAL_AWARE'        => 1,
            'DIGITAL_PRACTITIONER' => 2,
            'DIGITAL_PROFESSIONAL' => 3,
            'DIGITAL_LEADER'       => 4,
        ];
        $currentOrder = $levelOrder[$currentLevel] ?? 0;

        return view('assessment::career-pathway.index', compact(
            'profile', 'steps', 'currentLevel', 'currentOrder', 'levelOrder'
        ));
    }
}
