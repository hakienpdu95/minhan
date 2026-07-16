<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\BusinessProject\Models\SuccessReview;
use Modules\BusinessProject\Notifications\FollowUpDueNotification;

class SendSuccessFollowUpDueNotifications extends Command
{
    protected $signature = 'notifications:success-followup-due';

    protected $description = 'Gửi thông báo cho Customer Success khi follow-up dự án đến hạn hôm nay';

    public function handle(): int
    {
        $today = now()->toDateString();

        $dueReviews = SuccessReview::withoutTenant()
            ->with('createdBy')
            ->whereDate('follow_up_at', $today)
            ->whereNull('followed_up_at')
            ->get();

        $sent = 0;

        foreach ($dueReviews as $review) {
            $creator = $review->createdBy;
            if ($creator) {
                $creator->notify(new FollowUpDueNotification($review));
                $sent++;
            }
        }

        $this->info("Đã gửi {$sent} thông báo follow-up đến hạn hôm nay.");
        Log::info("notifications:success-followup-due sent={$sent} date={$today}");

        return self::SUCCESS;
    }
}
