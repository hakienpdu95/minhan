<?php

namespace App\Http\Controllers\Backend;

use App\Foundation\Vertical\VerticalTemplate;
use App\Http\Controllers\Controller;
use App\Http\Requests\VerticalTemplateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * System Admin — quản lý thư viện mẫu vertical (organization_id = null).
 * Bản instance riêng của từng tổ chức quản lý qua
 * Modules\Organization\Http\Controllers\OrganizationVerticalController, không qua đây.
 */
class VerticalTemplateController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(VerticalTemplate::class, 'vertical_template');
    }

    public function index(): View
    {
        $templates = VerticalTemplate::whereNull('organization_id')
            ->withCount(['phases', 'clones'])
            ->orderBy('label')
            ->get();

        return view('backend.vertical-templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('backend.vertical-templates.create');
    }

    public function store(VerticalTemplateRequest $request): RedirectResponse
    {
        $template = VerticalTemplate::create(array_merge($request->toData(), [
            'organization_id' => null,
            'status'          => 'active',
            'sidebar_config'  => [],
        ]));

        return redirect()
            ->route('backend.vertical-templates.edit', $template)
            ->with('success', "Đã tạo bản mẫu \"{$template->label}\". Thêm phase/checklist để hoàn thiện.");
    }

    public function edit(VerticalTemplate $verticalTemplate): View
    {
        return view('backend.vertical-templates.edit', [
            'template'   => $verticalTemplate,
            'phasesData' => $verticalTemplate->toBuilderPhasesData(),
        ]);
    }

    public function update(VerticalTemplateRequest $request, VerticalTemplate $verticalTemplate): RedirectResponse
    {
        $verticalTemplate->update($request->toData());

        return redirect()
            ->route('backend.vertical-templates.edit', $verticalTemplate)
            ->with('success', 'Đã cập nhật thông tin bản mẫu.');
    }

    public function destroy(VerticalTemplate $verticalTemplate): RedirectResponse
    {
        $label = $verticalTemplate->label;
        $verticalTemplate->delete();

        return redirect()
            ->route('backend.vertical-templates.index')
            ->with('success', "Đã xóa bản mẫu \"{$label}\".");
    }
}
