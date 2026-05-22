<?php

namespace Modules\Survey\Data;

use Spatie\LaravelData\Data;

class SurveyFormData extends Data
{
    public function __construct(
        public readonly string $title,
        public readonly ?int   $version = null,
    ) {}
}
