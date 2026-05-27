<?php

namespace Modules\ActivityLog\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\ActivityLog\Data\LogEntryData;
use Modules\ActivityLog\Models\ActivityLogAlertRule;
use Modules\ActivityLog\Notifications\ActivityAlertNotification;

class SendAlertAction
{
    use AsAction;

    public string $jobQueue  = 'actlog';
    public int    $jobTries  = 3;
    public array  $jobBackoff = [10, 60, 300];

    public function handle(int $ruleId, LogEntryData $entry): void
    {
        $rule = ActivityLogAlertRule::find($ruleId);
        if (!$rule) return;

        match ($rule->notify_channel) {
            1       => $this->sendEmail($rule, $entry),
            2       => $this->sendDatabase($rule, $entry),
            default => null,
        };
    }

    private function sendEmail(ActivityLogAlertRule $rule, LogEntryData $entry): void
    {
        $emails = array_filter(array_map('trim', explode(',', $rule->notify_target)));
        if (empty($emails)) return;

        \Mail::to($emails)->queue(new \Modules\ActivityLog\app\Mail\ActivityAlertMail($rule, $entry));
    }

    private function sendDatabase(ActivityLogAlertRule $rule, LogEntryData $entry): void
    {
        $ids   = array_filter(array_map('intval', explode(',', $rule->notify_target)));
        $users = \App\Models\User::whereIn('id', $ids)->get();
        \Notification::send($users, new ActivityAlertNotification($rule, $entry));
    }
}
