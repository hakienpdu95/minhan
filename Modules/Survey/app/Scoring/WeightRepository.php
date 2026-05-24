<?php

namespace Modules\Survey\Scoring;

use Modules\Survey\Models\FeatureWeight;

class WeightRepository
{
    /**
     * Load active domain weights cho một assessment.
     * Trả về array feature_code → weight value.
     * Nếu không có feature_weights records, fallback về assessment_domains.weight.
     *
     * @return array{weights: array<string,float>, version: int}
     */
    public function loadActive(string $assessmentCode, ScoringConfig $config): array
    {
        $featureWeights = FeatureWeight::forAssessment($assessmentCode)
            ->domainLevel()
            ->get();

        if ($featureWeights->isNotEmpty()) {
            $weights = $featureWeights->pluck('weight', 'feature_code')->all();
            $version = $featureWeights->max('version');

            return ['weights' => $weights, 'version' => (int) $version];
        }

        // Fallback: dùng weight từ assessment_domains (schema cũ)
        $weights = [];
        foreach ($config->domains as $domain) {
            $w = $domain->getAttribute('weight');
            if ($w !== null) {
                $weights[$domain->domain_code] = (float) $w;
            }
        }

        return ['weights' => $weights, 'version' => 1];
    }
}
