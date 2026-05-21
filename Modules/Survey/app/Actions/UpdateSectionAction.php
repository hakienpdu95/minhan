<?php

namespace Modules\Survey\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Survey\Data\SectionFormData;
use Modules\Survey\Models\SurveySection;

class UpdateSectionAction
{
    use AsAction;

    public function handle(SurveySection $section, SectionFormData $data): SurveySection
    {
        $section->update([
            'title' => $data->title,
            'icon'  => $data->icon,
        ]);

        activity()->performedOn($section)
            ->withProperties(['title' => $data->title])
            ->log('section.updated');

        return $section->fresh();
    }
}
