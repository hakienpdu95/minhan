<?php

namespace App\Http\Requests;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerticalTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(PermissionEnum::VERTICAL_TEMPLATES_MANAGE->value);
    }

    public function rules(): array
    {
        $ignoreId = $this->route('vertical_template')?->id;

        return [
            'code' => [
                'required', 'string', 'max:50', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                Rule::unique('vertical_templates', 'code')
                    ->whereNull('organization_id')
                    ->ignore($ignoreId),
            ],
            'label'                         => ['required', 'string', 'max:100'],
            'target_label'                  => ['required', 'string', 'max:50'],
            'target_org_category'           => ['required', 'string', 'max:30'],
            'has_physical_assets'           => ['boolean'],
            'readiness_template_slug'       => ['nullable', 'string', 'max:100'],
            'data_collection_template_slug' => ['nullable', 'string', 'max:100'],
            'default_roles'                 => ['nullable', 'array'],
            'default_roles.*'               => ['string', 'max:50', 'regex:/^[a-z0-9_]+$/'],
            'is_active'                     => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'              => 'Vui lòng nhập mã vertical.',
            'code.regex'                 => 'Mã vertical chỉ gồm chữ thường, số, dấu gạch ngang (vd: truy-xuat-nguon-goc).',
            'code.unique'                => 'Mã vertical này đã tồn tại trong thư viện.',
            'label.required'             => 'Vui lòng nhập tên hiển thị.',
            'target_label.required'      => 'Vui lòng nhập nhãn đối tượng triển khai.',
            'target_org_category.required' => 'Vui lòng nhập nhóm đối tượng.',
            'default_roles.*.regex'      => 'Vai trò chỉ gồm chữ thường, số và dấu gạch dưới (vd: data_ops).',
            'default_roles.*.max'        => 'Mỗi vai trò không được vượt quá :max ký tự.',
        ];
    }

    public function toData(): array
    {
        return [
            'code'                          => $this->input('code'),
            'label'                         => $this->input('label'),
            'target_label'                  => $this->input('target_label'),
            'target_org_category'           => $this->input('target_org_category'),
            'has_physical_assets'           => $this->boolean('has_physical_assets'),
            'readiness_template_slug'       => $this->input('readiness_template_slug') ?: null,
            'data_collection_template_slug' => $this->input('data_collection_template_slug') ?: null,
            'default_roles'                 => array_values(array_unique(array_filter(
                array_map('trim', $this->input('default_roles', []))
            ))),
            'is_active'                     => $this->boolean('is_active'),
        ];
    }
}
