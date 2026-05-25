<?php

namespace Modules\Survey\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Bus\Batch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Survey\Models\Assessment;
use Modules\Survey\Models\AssessmentDomain;
use Modules\Survey\Models\PassFailConfig;
use Modules\Survey\Models\Persona;
use Modules\Survey\Models\PersonaCondition;
use Modules\Survey\Models\PainPointRule;
use Modules\Survey\Models\RecommendationRule;
use Modules\Survey\Models\RoadmapMilestone;
use Modules\Survey\Models\RoadmapPhase;
use Modules\Survey\Models\ScoreBand;
use Modules\Survey\Models\ScoreRule;
use Modules\Survey\Models\ScoreRuleNumericRange;
use Modules\Survey\Models\ScoreRuleOption;
use Modules\Survey\Models\Survey;
use Modules\Survey\Models\SurveyField;
use Modules\Survey\Models\SurveyResponse;
use Modules\Survey\Actions\CreateConfigSnapshotAction;
use Modules\Survey\Actions\RestoreConfigFromSnapshotAction;
use Modules\Survey\Jobs\CalculateSurveyScoreJob;
use Modules\Survey\Models\AssessmentConfigSnapshot;

class ScoringAdminController extends Controller
{

    // ── Trang chính wizard ────────────────────────────────────────────────────

    public function index(Survey $survey): View
    {
        $this->authorize('survey.update');

        return view('survey::scoring.index', [
            'survey'         => $survey,
            'assessmentCode' => $survey->assessment_code ?? $this->deriveCode($survey),
        ]);
    }

    // ── GET config ────────────────────────────────────────────────────────────

    public function getConfig(Survey $survey): JsonResponse
    {
        $this->authorize('survey.update');

        $code       = $survey->assessment_code ?? $this->deriveCode($survey);
        $assessment = Assessment::where('assessment_code', $code)->first();

        $domains = AssessmentDomain::where('assessment_code', $code)
            ->orderBy('sort_order')->get();

        $rules = ScoreRule::where('assessment_code', $code)
            ->with(['options', 'numericRanges'])
            ->get();

        $bands          = ScoreBand::forAssessment($code)->ordered()->get();
        $passFailConfig = PassFailConfig::where('assessment_code', $code)->first();
        $personas       = Persona::forAssessment($code)->with('conditions')->get();
        $painPoints     = PainPointRule::where('assessment_code', $code)->get();
        $recommendations = RecommendationRule::where('assessment_code', $code)->orderBy('priority')->get();
        $roadmapRaw = RoadmapPhase::where('assessment_code', $code)
            ->with('milestones')
            ->orderBy('sort_order')
            ->get();

        // Group by band_code (fallback to maturity_level for old data)
        $roadmap = [];
        foreach ($roadmapRaw as $phase) {
            $key = $phase->band_code ?? $phase->maturity_level ?? '__unknown';
            $roadmap[$key][] = [
                'id'             => $phase->id,
                'phase_code'     => $phase->phase_code,
                'title'          => $phase->title,
                'description'    => $phase->description,
                'duration_weeks' => $phase->duration_weeks,
                'sort_order'     => $phase->sort_order,
                'milestones'     => $phase->milestones->map(fn ($m) => [
                    'id'         => $m->id,
                    'title'      => $m->title,
                    'sort_order' => $m->sort_order,
                ])->values(),
            ];
        }

        return response()->json([
            'assessment'     => $assessment ? [
                'has_scoring'         => $assessment->has_scoring,
                'aggregation_model'   => $assessment->aggregation_model,
                'classification_type' => $assessment->classification_type,
                'version'             => $assessment->version,
            ] : null,
            'domains'         => $domains->values(),
            'rules'           => $rules->map(fn ($r) => $this->serializeRule($r))->values(),
            'bands'           => $bands->values(),
            'pass_fail'       => $passFailConfig,
            'personas'        => $personas->map(fn ($p) => [
                'id'           => $p->id,
                'persona_code' => $p->persona_code,
                'label'        => $p->label,
                'description'  => $p->description,
                'sort_order'   => $p->sort_order,
                'conditions'   => $p->conditions->values(),
            ])->values(),
            'pain_points'     => $painPoints->values(),
            'recommendations' => $recommendations->values(),
            'roadmap'         => $roadmap,
        ]);
    }

    // ── PUT config (transaction) ──────────────────────────────────────────────

    public function saveConfig(Request $request, Survey $survey): JsonResponse
    {
        $this->authorize('survey.update');

        $data = $request->validate([
            'assessment.has_scoring'         => 'required|boolean',
            'assessment.aggregation_model'   => 'required|in:flat_sum,weighted_domain,sectioned',
            'assessment.classification_type' => 'required|in:none,score_band,pass_fail,persona_match',
            'domains'                        => 'array|max:50',
            'domains.*.domain_code'          => 'required|string|max:60|regex:/^[a-z0-9_]+$/',
            'domains.*.label'                => 'nullable|string|max:120',
            'domains.*.weight'               => 'required|numeric|min:0|max:1',
            'domains.*.min_score'            => 'required|numeric',
            'domains.*.max_score'            => 'required|numeric',
            'rules'                          => 'array|max:200',
            'rules.*.field_key'              => 'required|string|max:100',
            'rules.*.question_scoring_type'  => 'required|in:none,boolean,single_choice,multi_choice,numeric_range',
            'bands'                          => 'array|max:20',
            'bands.*.band_code'              => 'required|string|max:60|regex:/^[A-Za-z0-9_]+$/',
            'bands.*.label'                  => 'required|string|max:120',
            'bands.*.min_score'              => 'required|numeric|min:0|max:100',
            'bands.*.max_score'              => 'required|numeric|min:0|max:100',
            'pass_fail'                      => 'nullable|array',
            'pass_fail.passing_score'        => 'nullable|numeric|min:0|max:100',
            'personas'                       => 'array|max:20',
            'pain_points'                    => 'array|max:50',
            'pain_points.*.pain_point_code'  => 'required|string|max:100|regex:/^[a-z0-9_]+$/',
            'pain_points.*.required_flags'   => 'required|string|max:500',
            'recommendations'                => 'array|max:50',
            'roadmap'                        => 'array|max:20',
        ]);

        // Domain uniqueness check
        $domainCodes = collect($data['domains'] ?? [])->pluck('domain_code');
        if ($domainCodes->count() !== $domainCodes->unique()->count()) {
            return response()->json(['message' => 'domain_code bị trùng lặp trong danh sách domains.'], 422);
        }

        // Band uniqueness check
        $bandCodes = collect($data['bands'] ?? [])->pluck('band_code');
        if ($bandCodes->count() !== $bandCodes->unique()->count()) {
            return response()->json(['message' => 'band_code bị trùng lặp trong danh sách bands.'], 422);
        }

        // Domain min < max check
        foreach ($data['domains'] ?? [] as $d) {
            if ((float) $d['min_score'] >= (float) $d['max_score']) {
                return response()->json(['message' => "Domain '{$d['domain_code']}': min_score phải nhỏ hơn max_score."], 422);
            }
        }

        $code = $survey->assessment_code ?? $this->deriveCode($survey);

        DB::transaction(function () use ($data, $code, $survey) {
            // 1 — Assessment hub
            $assessment = Assessment::updateOrCreate(
                ['assessment_code' => $code],
                [
                    'name'                => $survey->title,
                    'has_scoring'         => $data['assessment']['has_scoring'],
                    'aggregation_model'   => $data['assessment']['aggregation_model'],
                    'classification_type' => $data['assessment']['classification_type'],
                    'is_active'           => true,
                ]
            );
            $assessment->increment('version');

            // Link survey → assessment_code
            if ($survey->assessment_code !== $code) {
                $survey->assessment_code = $code;
                $survey->save();
            }

            // 2 — Domains
            AssessmentDomain::where('assessment_code', $code)->delete();
            foreach ($data['domains'] ?? [] as $idx => $d) {
                AssessmentDomain::create([
                    'assessment_code' => $code,
                    'domain_code'     => $d['domain_code'],
                    'label'           => $d['label'] ?? $d['domain_code'],
                    'weight'          => (float) $d['weight'],
                    'min_score'       => (int) $d['min_score'],
                    'max_score'       => (int) $d['max_score'],
                    'sort_order'      => $idx + 1,
                    'is_active'       => true,
                ]);
            }

            // 3 — Score rules
            $incomingKeys = collect($data['rules'] ?? [])->pluck('field_key')->all();
            ScoreRule::where('assessment_code', $code)
                ->whereNotIn('field_key', $incomingKeys)
                ->delete();

            foreach ($data['rules'] ?? [] as $r) {
                $type = $r['question_scoring_type'] ?? 'none';
                $rule = ScoreRule::updateOrCreate(
                    ['assessment_code' => $code, 'field_key' => $r['field_key']],
                    [
                        'feature_code'          => ($r['feature_code'] ?? '') ?: $r['field_key'],
                        'domain_code'           => $r['domain_code'] ?? null,
                        'signal_flag'           => $r['signal_flag'] ?? null,
                        'score_if_true'         => (int) ($r['score_if_true'] ?? 0),
                        'score_if_false'        => (int) ($r['score_if_false'] ?? 0),
                        'question_scoring_type' => $type,
                        'condition_type'        => $type,
                        'min_score_cap'         => isset($r['min_score_cap']) && $r['min_score_cap'] !== '' ? (int) $r['min_score_cap'] : null,
                        'max_score_cap'         => isset($r['max_score_cap']) && $r['max_score_cap'] !== '' ? (int) $r['max_score_cap'] : null,
                        'is_active'             => true,
                    ]
                );

                $rule->options()->delete();
                foreach ($r['options'] ?? [] as $oIdx => $o) {
                    ScoreRuleOption::create([
                        'rule_id'      => $rule->id,
                        'option_value' => $o['option_value'],
                        'option_label' => $o['option_label'] ?? null,
                        'score'        => (int) ($o['score'] ?? 0),
                        'signal_flag'  => $o['signal_flag'] ?? null,
                        'sort_order'   => $oIdx + 1,
                    ]);
                }

                $rule->numericRanges()->delete();
                foreach ($r['ranges'] ?? [] as $rIdx => $nr) {
                    ScoreRuleNumericRange::create([
                        'rule_id'     => $rule->id,
                        'min_value'   => ($nr['min_value'] !== '' && $nr['min_value'] !== null) ? (float) $nr['min_value'] : null,
                        'max_value'   => ($nr['max_value'] !== '' && $nr['max_value'] !== null) ? (float) $nr['max_value'] : null,
                        'score'       => (int) ($nr['score'] ?? 0),
                        'signal_flag' => $nr['signal_flag'] ?? null,
                        'sort_order'  => $rIdx + 1,
                    ]);
                }
            }

            // 4 — Score bands
            ScoreBand::where('assessment_code', $code)->delete();
            foreach ($data['bands'] ?? [] as $idx => $b) {
                $min = (float) $b['min_score'];
                $max = (float) $b['max_score'];
                ScoreBand::create([
                    'assessment_code' => $code,
                    'band_code'       => $b['band_code'],
                    'label'           => $b['label'],
                    'description'     => $b['description'] ?? null,
                    'min_score'       => $min,
                    'max_score'       => $max,
                    'default_min'     => $min,
                    'default_max'     => $max,
                    'sort_order'      => $idx + 1,
                ]);
            }

            // 5 — Pass-fail config
            if (!empty($data['pass_fail'])) {
                PassFailConfig::updateOrCreate(
                    ['assessment_code' => $code],
                    [
                        'passing_score' => (float) ($data['pass_fail']['passing_score'] ?? 70),
                        'label_pass'    => $data['pass_fail']['label_pass'] ?? 'Đạt',
                        'label_fail'    => $data['pass_fail']['label_fail'] ?? 'Không đạt',
                    ]
                );
            }

            // 6 — Personas
            foreach (Persona::where('assessment_code', $code)->get() as $p) {
                $p->conditions()->delete();
                $p->delete();
            }
            foreach ($data['personas'] ?? [] as $idx => $p) {
                $persona = Persona::create([
                    'assessment_code' => $code,
                    'persona_code'    => $p['persona_code'],
                    'label'           => $p['label'],
                    'description'     => $p['description'] ?? null,
                    'sort_order'      => $idx + 1,
                ]);
                foreach ($p['conditions'] ?? [] as $cIdx => $c) {
                    PersonaCondition::create([
                        'persona_id'      => $persona->id,
                        'target_type'     => $c['target_type'],
                        'target_code'     => $c['target_code'] ?? null,
                        'operator'        => $c['operator'],
                        'threshold_value' => ($c['threshold_value'] !== '' && $c['threshold_value'] !== null) ? (float) $c['threshold_value'] : null,
                        'flag_value'      => isset($c['flag_value']) ? (bool) $c['flag_value'] : null,
                        'sort_order'      => $cIdx + 1,
                    ]);
                }
            }

            // 7 — Pain point rules
            PainPointRule::where('assessment_code', $code)->delete();
            foreach ($data['pain_points'] ?? [] as $pp) {
                PainPointRule::create([
                    'assessment_code' => $code,
                    'pain_point_code' => $pp['pain_point_code'],
                    'label'           => $pp['label'] ?? null,
                    'required_flags'  => $pp['required_flags'],
                    'is_active'       => true,
                ]);
            }

            // 8 — Recommendation rules
            RecommendationRule::where('assessment_code', $code)->delete();
            foreach ($data['recommendations'] ?? [] as $idx => $rec) {
                RecommendationRule::create([
                    'assessment_code'     => $code,
                    'recommendation_code' => $rec['recommendation_code'],
                    'label'               => $rec['label'] ?? null,
                    'description'         => $rec['description'] ?? null,
                    'trigger_domain'      => $rec['trigger_domain'],
                    'threshold_score'     => (float) ($rec['threshold_score'] ?? 50),
                    'priority'            => $idx + 1,
                    'is_active'           => true,
                ]);
            }

            // 9 — Roadmap phases + milestones
            foreach (RoadmapPhase::where('assessment_code', $code)->get() as $phase) {
                $phase->milestones()->delete();
                $phase->delete();
            }
            foreach ($data['roadmap'] ?? [] as $bandCode => $phases) {
                foreach ($phases as $idx => $ph) {
                    $phase = RoadmapPhase::create([
                        'assessment_code' => $code,
                        'band_code'       => $bandCode,
                        'maturity_level'  => $bandCode,
                        'phase_code'      => $ph['phase_code'],
                        'title'           => $ph['title'],
                        'description'     => $ph['description'] ?? null,
                        'duration_weeks'  => ($ph['duration_weeks'] ?? '') !== '' ? (int) $ph['duration_weeks'] : null,
                        'sort_order'      => $idx + 1,
                    ]);
                    foreach ($ph['milestones'] ?? [] as $mIdx => $milestone) {
                        RoadmapMilestone::create([
                            'phase_id'   => $phase->id,
                            'title'      => is_array($milestone) ? ($milestone['title'] ?? '') : $milestone,
                            'sort_order' => $mIdx + 1,
                        ]);
                    }
                }
            }

        });

        // Create versioned snapshot after the transaction commits
        $snapshot = app(CreateConfigSnapshotAction::class)->handle(
            $code,
            auth()->user()?->email,
            $request->input('change_note'),
        );

        return response()->json([
            'success'          => true,
            'message'          => 'Scoring đã được lưu và kích hoạt.',
            'snapshot_version' => $snapshot->version,
        ]);
    }

    // ── POST rollback to snapshot version ────────────────────────────────────

    public function rollbackConfig(Request $request, Survey $survey, int $version): JsonResponse
    {
        $this->authorize('survey.update');

        $code     = $survey->assessment_code ?? $this->deriveCode($survey);
        $snapshot = AssessmentConfigSnapshot::where('assessment_code', $code)
            ->where('version', $version)
            ->firstOrFail();

        $newSnapshot = app(RestoreConfigFromSnapshotAction::class)->handle(
            $snapshot,
            auth()->user()?->email,
        );

        return response()->json([
            'success'          => true,
            'message'          => "Đã khôi phục config về version {$version}. Version hiện tại: {$newSnapshot->version}.",
            'restored_version' => $version,
            'new_version'      => $newSnapshot->version,
        ]);
    }

    // ── GET snapshot list ─────────────────────────────────────────────────────

    public function getSnapshots(Survey $survey): JsonResponse
    {
        $this->authorize('survey.update');

        $code      = $survey->assessment_code ?? $this->deriveCode($survey);
        $snapshots = AssessmentConfigSnapshot::where('assessment_code', $code)
            ->orderBy('version', 'desc')
            ->get(['id', 'version', 'created_by', 'change_note', 'created_at']);

        return response()->json(['snapshots' => $snapshots]);
    }

    // ── POST reprocess all responses ──────────────────────────────────────────

    public function reprocessAll(Request $request, Survey $survey): JsonResponse
    {
        $this->authorize('survey.update');

        if (! $survey->assessment_code) {
            return response()->json(['message' => 'Survey chưa có assessment_code.'], 422);
        }

        $responseIds = SurveyResponse::forSurvey($survey->id)
            ->complete()
            ->pluck('id');

        if ($responseIds->isEmpty()) {
            return response()->json(['message' => 'Không có response nào để tính lại.', 'total' => 0]);
        }

        $jobs = $responseIds->map(fn ($id) => new CalculateSurveyScoreJob($id, force: true))->all();

        $batch = Bus::batch($jobs)
            ->name("reprocess-survey-{$survey->id}")
            ->onQueue('low')
            ->allowFailures()
            ->dispatch();

        return response()->json([
            'batch_id' => $batch->id,
            'total'    => count($jobs),
            'message'  => count($jobs) . ' responses đã được đưa vào hàng đợi tính lại điểm.',
        ]);
    }

    // ── GET batch progress ─────────────────────────────────────────────────────

    public function getBatchStatus(Request $request, Survey $survey, string $batchId): JsonResponse
    {
        $this->authorize('survey.update');

        $batch = Bus::findBatch($batchId);

        if (! $batch) {
            return response()->json(['message' => 'Batch không tìm thấy.'], 404);
        }

        return response()->json([
            'id'             => $batch->id,
            'name'           => $batch->name,
            'total'          => $batch->totalJobs,
            'processed'      => $batch->processedJobs(),
            'failed'         => $batch->failedJobs,
            'progress'       => $batch->progress(),
            'finished'       => $batch->finished(),
            'cancelled'      => $batch->cancelled(),
        ]);
    }

    // ── GET fields ────────────────────────────────────────────────────────────

    public function getFields(Survey $survey): JsonResponse
    {
        $this->authorize('survey.update');

        $code = $survey->assessment_code ?? $this->deriveCode($survey);

        $existingRules = ScoreRule::where('assessment_code', $code)
            ->with(['options', 'numericRanges'])
            ->get()
            ->keyBy('field_key');

        $fields = SurveyField::where('survey_id', $survey->id)
            ->with(['section', 'options'])
            ->ordered()
            ->get()
            ->map(function ($f) use ($existingRules) {
                $rule = $existingRules->get($f->field_key);
                return [
                    'id'              => $f->id,
                    'field_key'       => $f->field_key,
                    'label'           => $f->label,
                    'field_type'      => $f->field_type->value,
                    'field_type_label' => $f->field_type->label(),
                    'is_choice'       => $f->field_type->isChoice(),
                    'is_active'       => $f->is_active,
                    'section'         => $f->section?->title,
                    'field_options'   => $f->field_type->isChoice()
                        ? $f->options->map(fn ($o) => [
                            'value' => $o->option_value,
                            'label' => $o->label,
                        ])->values()
                        : [],
                    'rule'            => $rule ? $this->serializeRule($rule) : null,
                ];
            });

        return response()->json(['fields' => $fields]);
    }

    // ── GET flags ─────────────────────────────────────────────────────────────

    public function getFlags(Survey $survey): JsonResponse
    {
        $this->authorize('survey.update');

        $code = $survey->assessment_code ?? $this->deriveCode($survey);

        $ruleFlags = ScoreRule::where('assessment_code', $code)
            ->whereNotNull('signal_flag')
            ->pluck('signal_flag');

        $optionFlags = DB::table('score_rule_options')
            ->join('score_rules', 'score_rules.id', '=', 'score_rule_options.rule_id')
            ->where('score_rules.assessment_code', $code)
            ->whereNotNull('score_rule_options.signal_flag')
            ->pluck('score_rule_options.signal_flag');

        $rangeFlags = DB::table('score_rule_numeric_ranges')
            ->join('score_rules', 'score_rules.id', '=', 'score_rule_numeric_ranges.rule_id')
            ->where('score_rules.assessment_code', $code)
            ->whereNotNull('score_rule_numeric_ranges.signal_flag')
            ->pluck('score_rule_numeric_ranges.signal_flag');

        $flags = $ruleFlags->merge($optionFlags)->merge($rangeFlags)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return response()->json(['flags' => $flags]);
    }

    // ── POST validate ─────────────────────────────────────────────────────────

    public function validateConfig(Request $request, Survey $survey): JsonResponse
    {
        $this->authorize('survey.update');

        $data   = $request->json()->all();
        $errors = [];

        $aggModel = $data['assessment']['aggregation_model'] ?? '';

        if ($aggModel === 'weighted_domain') {
            $weightSum = collect($data['domains'] ?? [])->sum('weight');
            if (abs($weightSum - 1.0) > 0.01) {
                $errors[] = 'Tổng weight domains phải = 1.00 (hiện tại: ' . round($weightSum, 3) . ')';
            }
            foreach ($data['domains'] ?? [] as $d) {
                if ((int) ($d['min_score'] ?? 0) >= (int) ($d['max_score'] ?? 0)) {
                    $errors[] = "Domain '{$d['domain_code']}': min_score >= max_score";
                }
            }
        }

        if (($data['assessment']['classification_type'] ?? '') === 'score_band') {
            $bands = collect($data['bands'] ?? [])->sortBy(fn ($b) => (float) $b['min_score'])->values();
            for ($i = 1; $i < $bands->count(); $i++) {
                $prev    = $bands[$i - 1];
                $curr    = $bands[$i];
                $prevMax = (float) $prev['max_score'];
                $currMin = (float) $curr['min_score'];
                if (abs($currMin - $prevMax) > 1.01) {
                    $errors[] = "Bands có gap: {$prev['band_code']} kết thúc tại {$prevMax}, {$curr['band_code']} bắt đầu từ {$currMin}";
                }
                if ($curr['max_score'] <= $curr['min_score']) {
                    $errors[] = "Band '{$curr['band_code']}': max_score phải lớn hơn min_score";
                }
            }
        }

        // C.5 — Score rule constraints
        foreach ($data['rules'] ?? [] as $r) {
            $type   = $r['question_scoring_type'] ?? 'none';
            $fKey   = $r['field_key'] ?? '?';
            $optCnt = count($r['options'] ?? []);

            if (in_array($type, ['multi_choice', 'single_choice'], true) && $optCnt < 2) {
                $errors[] = "Câu '{$fKey}': {$type} cần tối thiểu 2 options (hiện có {$optCnt}).";
            }

            if ($type === 'multi_choice') {
                $maxCap = $r['max_score_cap'] ?? null;
                if ($maxCap === null || $maxCap === '') {
                    $errors[] = "Câu '{$fKey}': multi_choice bắt buộc khai báo max_score_cap.";
                }
                $minCap = $r['min_score_cap'] ?? null;
                if ($maxCap !== null && $maxCap !== '' && $minCap !== null && $minCap !== '' && (int) $minCap >= (int) $maxCap) {
                    $errors[] = "Câu '{$fKey}': min_score_cap ({$minCap}) phải nhỏ hơn max_score_cap ({$maxCap}).";
                }
            }

            if ($type === 'numeric_range') {
                $ranges = collect($r['ranges'] ?? [])
                    ->filter(fn ($nr) => ($nr['min_value'] ?? '') !== '' && ($nr['max_value'] ?? '') !== '')
                    ->sortBy(fn ($nr) => (float) $nr['min_value'])
                    ->values();

                for ($i = 1; $i < $ranges->count(); $i++) {
                    $prev = $ranges[$i - 1];
                    $curr = $ranges[$i];
                    if ((float) $curr['min_value'] < (float) $prev['max_value']) {
                        $errors[] = "Câu '{$fKey}': numeric_range overlap [{$prev['min_value']}–{$prev['max_value']}] và [{$curr['min_value']}–{$curr['max_value']}].";
                    }
                }
            }
        }

        return response()->json(['valid' => empty($errors), 'errors' => $errors]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function deriveCode(Survey $survey): string
    {
        return str_replace('-', '_', $survey->slug);
    }

    private function serializeRule(ScoreRule $rule): array
    {
        return [
            'id'                    => $rule->id,
            'field_key'             => $rule->field_key,
            'feature_code'          => $rule->feature_code,
            'domain_code'           => $rule->domain_code,
            'signal_flag'           => $rule->signal_flag,
            'score_if_true'         => $rule->score_if_true,
            'score_if_false'        => $rule->score_if_false,
            'question_scoring_type' => $rule->question_scoring_type ?? $rule->condition_type,
            'min_score_cap'         => $rule->min_score_cap,
            'max_score_cap'         => $rule->max_score_cap,
            'options'               => $rule->options->values(),
            'ranges'                => $rule->numericRanges->values(),
        ];
    }
}
