<?php

namespace Modules\AiCopilot\Http\Controllers;

use App\Enums\PermissionEnum as P;
use App\Http\Controllers\Controller;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\AiCopilot\Actions\DestroyAiAgentAction;
use Modules\AiCopilot\Actions\StoreAiAgentAction;
use Modules\AiCopilot\Actions\UpdateAiAgentAction;
use Modules\AiCopilot\Data\Requests\StoreAiAgentData;
use Modules\AiCopilot\Models\AiAgent;

class AiAgentController extends Controller
{
    private const PROVIDERS  = ['claude', 'openai', 'mock'];
    private const TASK_TYPES = ['sop', 'kpi', 'hr', 'lead', 'email', 'general', 'custom'];
    private const MODELS = [
        'claude' => [
            'claude-haiku-4-5-20251001',
            'claude-sonnet-4-6',
            'claude-opus-4-8',
        ],
        'openai' => [
            'gpt-4o-mini',
            'gpt-4o',
        ],
        'mock'   => ['mock-model'],
    ];

    public function index()
    {
        $this->authorize(P::AI_COPILOT_CONFIG->value);

        $orgId = TenantContext::getOrganizationId();

        $agents = AiAgent::withoutTenant()
            ->withoutGlobalScope('active')
            ->where(fn ($q) => $q->where('organization_id', $orgId)->orWhereNull('organization_id'))
            ->orderByRaw('is_system DESC, task_type, name')
            ->get();

        return view('ai_copilot::agents.index', compact('agents'));
    }

    public function create()
    {
        $this->authorize(P::AI_COPILOT_CONFIG->value);

        return view('ai_copilot::agents.create', [
            'providers'  => self::PROVIDERS,
            'taskTypes'  => self::TASK_TYPES,
            'modelsByProvider' => self::MODELS,
        ]);
    }

    public function store(Request $request, StoreAiAgentAction $action): RedirectResponse
    {
        $this->authorize(P::AI_COPILOT_CONFIG->value);

        $data  = StoreAiAgentData::validateAndCreate($request->all());
        $agent = $action->handle($data);

        return redirect()->route('ai.agents.index')
            ->with('success', "Agent \"{$agent->name}\" đã được tạo.");
    }

    public function edit(AiAgent $agent)
    {
        $this->authorize(P::AI_COPILOT_CONFIG->value);

        $agent->load('prompts');

        return view('ai_copilot::agents.edit', [
            'agent'            => $agent,
            'providers'        => self::PROVIDERS,
            'taskTypes'        => self::TASK_TYPES,
            'modelsByProvider' => self::MODELS,
        ]);
    }

    public function update(Request $request, AiAgent $agent, UpdateAiAgentAction $action): RedirectResponse
    {
        $this->authorize(P::AI_COPILOT_CONFIG->value);

        $data = StoreAiAgentData::validateAndCreate($request->all());
        $action->handle($agent, $data);

        return redirect()->route('ai.agents.index')
            ->with('success', "Đã cập nhật agent \"{$agent->name}\".");
    }

    public function destroy(Request $request, AiAgent $agent, DestroyAiAgentAction $action): RedirectResponse|JsonResponse
    {
        $this->authorize(P::AI_COPILOT_CONFIG->value);

        if ($agent->is_system) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Không thể xóa system agent.'], 403);
            }
            return back()->with('error', 'Không thể xóa system agent.');
        }

        $name = $action->handle($agent);

        if ($request->expectsJson()) {
            return response()->json(['message' => "Đã xóa agent \"{$name}\"."]);
        }

        return redirect()->route('ai.agents.index')
            ->with('success', "Đã xóa agent \"{$name}\".");
    }
}
