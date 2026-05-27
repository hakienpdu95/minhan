<?php

namespace Modules\ActivityLog\Services;

use Modules\ActivityLog\Data\LogEntryData;
use Modules\ActivityLog\Models\ActivityLogAlertRule;
use Modules\ActivityLog\Actions\SendAlertAction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

final class AlertEvaluatorService
{
    public function evaluate(LogEntryData $entry): void
    {
        $rules = Cache::remember('actlog:alert_rules', 300,
            fn() => ActivityLogAlertRule::where('is_active', 1)->get()
        );

        foreach ($rules as $rule) {
            if (!$this->matches($rule, $entry))      continue;
            if ($this->inCooldown($rule))            continue;
            if (!$this->conditionMet($rule, $entry)) continue;
            $this->trigger($rule, $entry);
        }
    }

    private function matches(ActivityLogAlertRule $rule, LogEntryData $entry): bool
    {
        if ($rule->module    && $rule->module    !== $entry->module)     return false;
        if ($rule->action    && $rule->action    !== $entry->action)     return false;
        if ($rule->level_min && $entry->level->value < $rule->level_min) return false;
        return true;
    }

    private function conditionMet(ActivityLogAlertRule $rule, LogEntryData $entry): bool
    {
        return match ($rule->condition_type) {
            1       => true,
            2       => $this->checkCount($rule, $entry),
            default => false,
        };
    }

    private function checkCount(ActivityLogAlertRule $rule, LogEntryData $entry): bool
    {
        $key   = "actlog:alert:{$rule->id}:{$entry->module}:{$entry->action}";
        $count = (int) Cache::increment($key);
        if ($count === 1) Cache::expire($key, $rule->window_minutes * 60);
        return $count >= $rule->threshold_count;
    }

    private function inCooldown(ActivityLogAlertRule $rule): bool
    {
        return $rule->last_triggered_at
            && Carbon::parse($rule->last_triggered_at)
                ->addMinutes($rule->cooldown_minutes)->isFuture();
    }

    private function trigger(ActivityLogAlertRule $rule, LogEntryData $entry): void
    {
        $rule->update(['last_triggered_at' => now()]);
        Cache::forget('actlog:alert_rules');
        SendAlertAction::dispatch($rule->id, $entry)->onQueue('actlog');
    }
}
