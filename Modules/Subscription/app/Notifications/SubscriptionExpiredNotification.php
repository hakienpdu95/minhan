<?php

namespace Modules\Subscription\Notifications;

use App\Shared\Tenancy\Models\Organization;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Laravelcm\Subscriptions\Models\Subscription;

class SubscriptionExpiredNotification extends Notification
{
    public function __construct(
        public readonly Organization $organization,
        public readonly Subscription $subscription,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $planName = $this->subscription->plan->name ?? 'subscription';
        $orgName  = $this->organization->name;

        return (new MailMessage)
            ->subject("[{$orgName}] Subscription đã hết hạn")
            ->greeting("Xin chào {$notifiable->name},")
            ->line("Subscription **{$planName}** của **{$orgName}** đã hết hạn.")
            ->line('Một số tính năng của bạn có thể bị hạn chế. Vui lòng gia hạn để khôi phục truy cập đầy đủ.')
            ->action('Gia hạn ngay', route('subscription.portal.billing'))
            ->line('Cảm ơn bạn đã tin dùng dịch vụ của chúng tôi.');
    }
}
