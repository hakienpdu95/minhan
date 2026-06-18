<?php

namespace Modules\Organization\Http\Controllers;

use App\Foundation\Vertical\ActivateVerticalAction;
use App\Foundation\Vertical\DeactivateVerticalAction;
use App\Foundation\Vertical\OrganizationVertical;
use App\Foundation\Vertical\VerticalConfigItem;
use App\Foundation\Vertical\VerticalTemplate;
use App\Foundation\VerticalRegistry;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Organization\Models\Organization;

class OrganizationVerticalController extends Controller
{
    // ── Index — danh sách tất cả vertical templates, trạng thái per org ───────

    public function index(Organization $organization): View
    {
        $this->authorize('update', $organization);

        $templates = VerticalTemplate::where('is_active', true)
            ->orderBy('label')
            ->get();

        $activated = OrganizationVertical::withoutTenant()
            ->where('organization_id', $organization->id)
            ->get()
            ->keyBy('vertical_code');

        return view('organization::verticals.index', compact('organization', 'templates', 'activated'));
    }

    // ── Activate ──────────────────────────────────────────────────────────────

    public function activate(Request $request, Organization $organization, string $code): RedirectResponse
    {
        $this->authorize('update', $organization);

        (new ActivateVerticalAction)->execute($organization->id, $code);

        return redirect()
            ->route('backend.organizations.verticals.index', $organization)
            ->with('success', 'Đã kích hoạt dịch vụ "' . (VerticalRegistry::resolve($code)?->label() ?? $code) . '".');
    }

    // ── Deactivate ────────────────────────────────────────────────────────────

    public function deactivate(Request $request, Organization $organization, string $code): RedirectResponse
    {
        $this->authorize('update', $organization);

        (new DeactivateVerticalAction)->execute($organization->id, $code);

        return redirect()
            ->route('backend.organizations.verticals.index', $organization)
            ->with('success', 'Đã tắt dịch vụ "' . (VerticalRegistry::resolve($code)?->label() ?? $code) . '".');
    }

    // ── Config — cấu hình chi tiết cho 1 vertical đã active ──────────────────

    public function config(Organization $organization, string $code): View
    {
        $this->authorize('update', $organization);

        $vertical = VerticalRegistry::resolve($code);
        if (! $vertical) {
            abort(404, "Vertical '{$code}' không tồn tại.");
        }

        $orgVertical = OrganizationVertical::withoutTenant()
            ->where('organization_id', $organization->id)
            ->where('vertical_code', $code)
            ->firstOrFail();

        $configItems = VerticalConfigItem::withoutTenant()
            ->where('organization_id', $organization->id)
            ->where('vertical_code', $code)
            ->orderBy('config_group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('config_group');

        return view('organization::verticals.config', compact(
            'organization', 'vertical', 'orgVertical', 'configItems'
        ));
    }

    // ── Update config ─────────────────────────────────────────────────────────

    public function updateConfig(Request $request, Organization $organization, string $code): RedirectResponse
    {
        $this->authorize('update', $organization);

        $items = $request->input('items', []);

        foreach ($items as $id => $data) {
            VerticalConfigItem::withoutTenant()
                ->where('id', (int) $id)
                ->where('organization_id', $organization->id)
                ->where('vertical_code', $code)
                ->update([
                    'label'     => trim($data['label'] ?? ''),
                    'is_active' => ! empty($data['is_active']),
                ]);
        }

        VerticalRegistry::clearCache($code);

        return redirect()
            ->route('backend.organizations.verticals.config', [$organization, $code])
            ->with('success', 'Đã lưu cấu hình.');
    }
}
