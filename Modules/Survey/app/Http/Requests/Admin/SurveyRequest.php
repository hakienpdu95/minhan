<?php

namespace Modules\Survey\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Survey\Models\Survey;

class SurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('survey.create') || $this->user()->can('survey.update');
    }

    public function rules(): array
    {
        $surveyId = $this->route('survey')?->id;

        return [
            'title'   => ['required', 'string', 'max:255'],
            'slug'    => [
                'nullable', 'string', 'max:160',
                'regex:/^[a-z0-9\-]+$/',
                Rule::unique('surveys', 'slug')->ignore($surveyId),
            ],
            'version' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'  => 'Tiêu đề survey là bắt buộc.',
            'slug.regex'      => 'Slug chỉ được chứa chữ thường, số và dấu gạch ngang.',
            'slug.unique'     => 'Slug này đã được sử dụng.',
        ];
    }

    public function toData(): array
    {
        return [
            'title'   => $this->input('title'),
            'slug'    => $this->input('slug') ?: null,
            'version' => $this->input('version') ? (int) $this->input('version') : null,
        ];
    }
}
