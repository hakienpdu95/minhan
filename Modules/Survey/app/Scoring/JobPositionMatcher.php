<?php

namespace Modules\Survey\Scoring;

use Modules\Survey\Models\JobPosition;

class JobPositionMatcher
{
    /**
     * Match active job positions against domain scores + overall score.
     *
     * @param  array<string, DomainScoreResult>  $domainScores
     * @return JobPositionResult[]  sorted by match_score descending
     */
    public function match(ScoringConfig $config, array $domainScores, ?float $overallScore): array
    {
        $positions = JobPosition::forAssessment($config->assessmentCode)
            ->active()
            ->ordered()
            ->get();

        $results = [];

        foreach ($positions as $position) {
            // Gate 1: overall score minimum
            if ($overallScore !== null && $position->min_overall_score !== null) {
                if ($overallScore < $position->min_overall_score) {
                    continue;
                }
            }

            $requirements = $position->requirements ?? [];

            if (empty($requirements)) {
                // No domain requirements → full match
                $results[] = new JobPositionResult(
                    positionCode: $position->position_code,
                    title:        $position->title,
                    matchScore:   100.0,
                );
                continue;
            }

            $metCount = 0;
            foreach ($requirements as $domainCode => $minScore) {
                $ds = $domainScores[$domainCode] ?? null;
                if ($ds !== null && $ds->normalizedScore >= (float) $minScore) {
                    $metCount++;
                }
            }

            $matchScore = ($metCount / count($requirements)) * 100.0;

            if ($matchScore >= 50.0) {
                $results[] = new JobPositionResult(
                    positionCode: $position->position_code,
                    title:        $position->title,
                    matchScore:   $matchScore,
                );
            }
        }

        usort($results, fn (JobPositionResult $a, JobPositionResult $b) => $b->matchScore <=> $a->matchScore);

        return $results;
    }
}
