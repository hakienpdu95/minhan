<?php

namespace Modules\Survey\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('survey.create') || $this->user()->can('survey.update');
    }

    public function rules(): array
    {
        return [
            'title'   => ['required', 'string', 'max:255'],
            'version' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề survey là bắt buộc.',
        ];
    }

    public function toData(): array
    {
        return [
            'title'   => $this->input('title'),
            'version' => $this->input('version') ? (int) $this->input('version') : null,
        ];
    }
}
