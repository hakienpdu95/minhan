<?php

namespace Modules\BusinessProject\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Models\DeliverableTemplate;

/**
 * Template Library (Phase 2, mảng 5/5 — spec "Template Service"). CRUD đơn giản, KHÔNG gắn với
 * 1 Business Project — cấu hình cấp tổ chức (Founder/Lead Consultant/System Admin), tương tự
 * cấp độ Knowledge Center hay RolePermission, không phải 1 workspace trong 8 workspace BCOS.
 */
class TemplateLibraryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', DeliverableTemplate::class);

        $orgId = TenantContext::getOrganizationId();

        $templates = DeliverableTemplate::availableTo($orgId)
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->groupBy('type');

        return view('businessproject::template-library.index', [
            'templatesByType' => $templates,
            'types' => DeliverableType::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', DeliverableTemplate::class);

        return view('businessproject::template-library.create', [
            'types' => DeliverableType::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', DeliverableTemplate::class);

        $validated = $this->validateRequest($request);

        DeliverableTemplate::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'organization_id' => TenantContext::getOrganizationId(),
            'type' => $validated['type'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'content' => $validated['content'],
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('backend.template-library.index')
            ->with('success', 'Đã tạo Template mới.');
    }

    public function edit(DeliverableTemplate $templateLibrary): View
    {
        $this->authorize('update', $templateLibrary);

        return view('businessproject::template-library.edit', [
            'template' => $templateLibrary,
            'types' => DeliverableType::cases(),
        ]);
    }

    public function update(DeliverableTemplate $templateLibrary, Request $request): RedirectResponse
    {
        $this->authorize('update', $templateLibrary);

        $validated = $this->validateRequest($request);

        $templateLibrary->update([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'content' => $validated['content'],
            'is_active' => $request->boolean('is_active'),
            'updated_by' => auth()->id(),
        ]);

        return redirect()
            ->route('backend.template-library.index')
            ->with('success', 'Đã cập nhật Template.');
    }

    public function destroy(DeliverableTemplate $templateLibrary): RedirectResponse
    {
        $this->authorize('delete', $templateLibrary);

        $templateLibrary->delete();

        return redirect()
            ->route('backend.template-library.index')
            ->with('success', 'Đã xóa Template.');
    }

    private function validateRequest(Request $request): array
    {
        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(array_column(DeliverableType::cases(), 'value'))],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'content_json' => ['required', 'json'],
        ]);

        $validated['content'] = json_decode($validated['content_json'], true);
        unset($validated['content_json']);

        return $validated;
    }
}
