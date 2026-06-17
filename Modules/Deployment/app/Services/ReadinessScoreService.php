<?php

namespace Modules\Deployment\Services;

use Modules\Survey\Models\SurveyAnswer;
use Modules\Survey\Models\SurveyField;
use Modules\Survey\Models\SurveyResponse;

class ReadinessScoreService
{
    private const BANDS = [
        80 => ['label' => 'Sẵn sàng',            'color' => 'success'],
        60 => ['label' => 'Gần sẵn sàng',         'color' => 'info'],
        40 => ['label' => 'Sẵn sàng có hỗ trợ',  'color' => 'warning'],
        0  => ['label' => 'Chưa sẵn sàng',        'color' => 'error'],
    ];

    /** @return array{score: int, band: string, color: string, domains: array, answered: int} */
    public function compute(SurveyResponse $response): array
    {
        // Load answers with their fields and sections
        $answers = SurveyAnswer::where('response_id', $response->id)
            ->whereNotNull('value_number')
            ->with('field.section')
            ->get();

        if ($answers->isEmpty()) {
            return $this->emptyResult();
        }

        // Group answers by section_code (domain)
        $byDomain = $answers->groupBy(fn($a) => $a->field?->section?->section_code ?? 'unknown');

        $domainScores = [];
        foreach ($byDomain as $domainCode => $domainAnswers) {
            if ($domainCode === 'unknown') continue;

            $rawTotal = $domainAnswers->sum('value_number');
            $maxRaw   = $domainAnswers->count() * 5; // max rating = 5
            $normalized = $maxRaw > 0 ? (int) round(($rawTotal / $maxRaw) * 100) : 0;

            $domainScores[$domainCode] = [
                'raw'        => (int) $rawTotal,
                'max_raw'    => $maxRaw,
                'score'      => $normalized,
                'count'      => $domainAnswers->count(),
            ];
        }

        $totalScore = count($domainScores) > 0
            ? (int) round(array_sum(array_column($domainScores, 'score')) / count($domainScores))
            : 0;

        return [
            'score'    => $totalScore,
            'band'     => $this->band($totalScore),
            'color'    => $this->color($totalScore),
            'domains'  => $domainScores,
            'answered' => $answers->count(),
        ];
    }

    public function band(int $score): string
    {
        foreach (self::BANDS as $threshold => $info) {
            if ($score >= $threshold) return $info['label'];
        }
        return 'Chưa sẵn sàng';
    }

    public function color(int $score): string
    {
        foreach (self::BANDS as $threshold => $info) {
            if ($score >= $threshold) return $info['color'];
        }
        return 'error';
    }

    private function emptyResult(): array
    {
        return ['score' => 0, 'band' => 'Chưa đánh giá', 'color' => 'ghost', 'domains' => [], 'answered' => 0];
    }
}
