<?php

namespace Modules\Subscription\Notifications;

use App\Shared\Tenancy\Models\Organization;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Laravelcm\Subscriptions\Models\Subscription;

class RenewalReminderNotification extends Notification
{
    public function __construct(
        public readonly Organization $organization,
        public readonly Subscription $subscription,
        public readonly int          $daysLeft,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $planName  = $this->subscription->plan->name ?? 'subscription';
        $endsAt    = $this->subscription->ends_at?->format('d/m/Y');
        $orgName   = $this->organization->name;

        return (new MailMessage)
            ->subject("[{$orgName}] Subscription sắp hết hạn trong {$this->daysLeft} ngày")
            ->greeting("Xin chào {$notifiable->name},")
            ->line("Subscription **{$planName}** của **{$orgName}** sẽ hết hạn vào **{$endsAt}** ({$this->daysLeft} ngày nữa).")
            ->line('Vui lòng gia hạn để tiếp tục sử dụng đầy đủ tính năng.')
            ->action('Gia hạn ngay', route('subscription.portal.billing'))
            ->line('Cảm ơn bạn đã tin dùng dịch vụ của chúng tôi.');
    }
}
