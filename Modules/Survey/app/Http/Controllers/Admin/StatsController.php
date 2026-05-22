<?php

namespace Modules\Survey\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\Survey\Models\Survey;
use Modules\Survey\Services\SurveyStatsService;

class StatsController extends Controller
{
    public function index(Survey $survey, SurveyStatsService $service)
    {
        $this->authorize('survey.view_responses');

        $stats = $service->forSurvey($survey);
        $byDay = $service->totalByDay($survey, 30);

        return view('survey::stats.index', compact('survey', 'stats', 'byDay'));
    }
}
