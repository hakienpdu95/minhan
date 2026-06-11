<?php

namespace Modules\Assessment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Assessment\Models\CertificationDefinition;
use Modules\Assessment\Models\WorkforceCertification;
use Modules\Assessment\Models\WorkforceProfile;

class WorkforceCertificationController extends Controller
{
    /** Chứng nhận của tôi */
    public function index(Request $request): View
    {
        $user  = $request->user();
        $orgId = TenantContext::getOrganizationId();

        $profile = WorkforceProfile::withoutTenant()
            ->where('organization_id', $orgId)
            ->where('user_id', $user->id)
            ->first();

        $certifications = $profile
            ? WorkforceCertification::where('workforce_profile_id', $profile->id)
                ->with('definition')
                ->orderByDesc('issued_at')
                ->get()
            : collect();

        $available = CertificationDefinition::whereNull('organization_id')
            ->orWhere('organization_id', $orgId)
            ->orderBy('level_order')
            ->get();

        $earnedCodes = $certifications->where('status', 'active')->pluck('cert_code')->toArray();

        return view('assessment::certifications.index', compact(
            'profile', 'certifications', 'available', 'earnedCodes'
        ));
    }
}
