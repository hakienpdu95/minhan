<?php

namespace Modules\PerformanceReview\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\PerformanceReview\Models\ReviewCriteria;
use Modules\PerformanceReview\Models\ReviewTemplate;

class ReviewTemplateController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', \Modules\PerformanceReview\Models\PerformanceReview::class);

        $orgId = TenantContext::getOrganizationId();

        $templates = ReviewTemplate::withoutTenant()
            ->where('organization_id', $orgId)
            ->withCount('criteria')
            ->orderByDesc('created_at')
            ->get();

        return view('performancereview::templates.index', compact('templates'));
    }

    public function create()
    {
        $this->authorize('create', \Modules\PerformanceReview\Models\PerformanceReview::class);

        return view('performancereview::templates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', \Modules\PerformanceReview\Models\PerformanceReview::class);

        $orgId = TenantContext::getOrganizationId();

        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'period_type'       => ['required', 'string', Rule::in(['monthly','quarterly','semi_annual','annual','probation','custom'])],
            'apply_to_function' => ['nullable', 'string', 'max:50'],
            'rating_scale'      => ['required', 'integer', Rule::in([5, 10])],
            'criteria'          => ['required', 'array', 'min:1'],
            'criteria.*.criteria_key'  => ['required', 'string', 'max:100'],
            'criteria.*.criteria_name' => ['required', 'string', 'max:255'],
            'criteria.*.weight'        => ['required', 'numeric', 'min:0.01', 'max:100'],
            'criteria.*.max_score'     => ['required', 'integer', 'min:1', 'max:10'],
            'criteria.*.description'   => ['nullable', 'string'],
        ]);

        // Validate total weight = 100
        $totalWeight = collect($validated['criteria'])->sum('weight');
        if (abs($totalWeight - 100) > 0.01) {
            return back()->withInput()->withErrors(['criteria' => 'Tổng trọng số các tiêu chí phải bằng 100. Hiện tại: ' . $totalWeight]);
        }

        $template = ReviewTemplate::create([
            'uuid'              => Str::uuid(),
            'organization_id'   => $orgId,
            'created_by'        => auth()->id(),
            'name'              => $validated['name'],
            'period_type'       => $validated['period_type'],
            'apply_to_function' => $validated['apply_to_function'],
            'rating_scale'      => $validated['rating_scale'],
            'is_system'         => false,
            'is_locked'         => false,
            'is_active'         => true,
        ]);

        foreach ($validated['criteria'] as $i => $c) {
            ReviewCriteria::create([
                'template_id'   => $template->id,
                'criteria_key'  => Str::slug($c['criteria_key'], '_'),
                'criteria_name' => $c['criteria_name'],
                'weight'        => $c['weight'],
                'max_score'     => $c['max_score'],
                'description'   => $c['description'] ?? null,
                'sort_order'    => $i,
            ]);
        }

        return redirect()->route('backend.review-templates.index')
            ->with('success', 'Mẫu đánh giá "' . $template->name . '" đã được tạo.');
    }

    public function show(ReviewTemplate $reviewTemplate)
    {
        $this->authorize('viewAny', \Modules\PerformanceReview\Models\PerformanceReview::class);

        $reviewTemplate->load('criteria');

        return view('performancereview::templates.show', ['template' => $reviewTemplate]);
    }

    public function destroy(Request $request, ReviewTemplate $reviewTemplate): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', \Modules\PerformanceReview\Models\PerformanceReview::class);

        if ($reviewTemplate->is_locked) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Mẫu này đang bị khóa, không thể xóa.'], 422);
            }
            return back()->withErrors(['template' => 'Mẫu này đang bị khóa, không thể xóa.']);
        }

        $name = $reviewTemplate->name;
        $reviewTemplate->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Đã xóa mẫu "' . $name . '".' ]);
        }

        return redirect()->route('backend.review-templates.index')
            ->with('success', 'Đã xóa mẫu "' . $name . '".');
    }
}
