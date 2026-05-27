<?php

namespace Modules\ActivityLog\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ActivityLog\Models\ActivityLogAlertRule;

class AlertRuleController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ActivityLogAlertRule::orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'module'          => 'nullable|string|max:64',
            'action'          => 'nullable|string|max:128',
            'level_min'       => 'required|integer|min:1|max:5',
            'condition_type'  => 'required|integer|in:1,2',
            'threshold_count' => 'nullable|integer|min:1',
            'window_minutes'  => 'nullable|integer|min:1',
            'notify_channel'  => 'required|integer|in:1,2',
            'notify_target'   => 'required|string|max:255',
            'cooldown_minutes'=> 'nullable|integer|min:1',
            'is_active'       => 'boolean',
        ]);

        $rule = ActivityLogAlertRule::create($data);

        return response()->json($rule, 201);
    }

    public function update(Request $request, ActivityLogAlertRule $rule): JsonResponse
    {
        $data = $request->validate([
            'name'            => 'sometimes|string|max:255',
            'module'          => 'nullable|string|max:64',
            'action'          => 'nullable|string|max:128',
            'level_min'       => 'sometimes|integer|min:1|max:5',
            'condition_type'  => 'sometimes|integer|in:1,2',
            'threshold_count' => 'nullable|integer|min:1',
            'window_minutes'  => 'nullable|integer|min:1',
            'notify_channel'  => 'sometimes|integer|in:1,2',
            'notify_target'   => 'sometimes|string|max:255',
            'cooldown_minutes'=> 'nullable|integer|min:1',
            'is_active'       => 'boolean',
        ]);

        $rule->update($data);

        return response()->json($rule->fresh());
    }

    public function destroy(ActivityLogAlertRule $rule): JsonResponse
    {
        $rule->delete();

        return response()->json(null, 204);
    }
}
