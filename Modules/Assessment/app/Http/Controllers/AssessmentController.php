<?php

namespace Modules\Assessment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Assessment\Models\Assessment;

class AssessmentController extends Controller
{
    public function index(): View
    {
        $this->authorize('assessment.view');
        $assessments = Assessment::orderBy('name')->paginate(20);
        return view('assessment::assessments.index', compact('assessments'));
    }

    public function show(Assessment $assessment): View
    {
        $this->authorize('assessment.view');
        return view('assessment::assessments.show', compact('assessment'));
    }

    public function create(): View
    {
        $this->authorize('assessment.config');
        return view('assessment::assessments.create');
    }

    public function store(Request $request)
    {
        $this->authorize('assessment.config');
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'aggregation_model'  => 'required|in:weighted_domain,flat_sum,sectioned',
            'classification_type'=> 'required|in:score_band,pass_fail,persona_match,none',
            'has_scoring'        => 'boolean',
        ]);
        $data['assessment_code'] = $this->generateUniqueCode($data['name']);
        Assessment::create($data);
        return redirect()->route('assessments.index')->with('success', 'Đã tạo assessment.');
    }

    private function generateUniqueCode(string $name): string
    {
        $base = Str::slug($name);
        do {
            $hash = substr(str_replace('-', '', Str::uuid()->toString()), 0, 8);
            $code = "{$base}-{$hash}";
        } while (Assessment::where('assessment_code', $code)->exists());

        return $code;
    }

    public function edit(Assessment $assessment): View
    {
        $this->authorize('assessment.config');
        return view('assessment::assessments.edit', compact('assessment'));
    }

    public function update(Request $request, Assessment $assessment)
    {
        $this->authorize('assessment.config');
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'aggregation_model'  => 'required|in:weighted_domain,flat_sum,sectioned',
            'classification_type'=> 'required|in:score_band,pass_fail,persona_match,none',
            'has_scoring'        => 'boolean',
            'is_active'          => 'boolean',
        ]);
        $assessment->update($data);
        return redirect()->route('assessments.show', $assessment)->with('success', 'Đã cập nhật.');
    }

    public function destroy(Assessment $assessment)
    {
        $this->authorize('assessment.config');
        $assessment->delete();
        return redirect()->route('assessments.index')->with('success', 'Đã xóa.');
    }
}
