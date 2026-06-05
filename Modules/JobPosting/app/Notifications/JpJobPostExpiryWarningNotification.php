<?php

namespace Modules\JobPosting\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\JobPosting\Models\JpJobPost;

class JpJobPostExpiryWarningNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly JpJobPost $post,
        private readonly int       $daysLeft,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'           => 'jp_job_post_expiry_warning',
            'job_post_id'    => $this->post->id,
            'job_post_uuid'  => $this->post->uuid,
            'job_post_code'  => $this->post->code,
            'job_post_title' => $this->post->title,
            'expire_at'      => $this->post->expire_at?->toDateString(),
            'days_left'      => $this->daysLeft,
            'message'        => "Tin tuyển dụng [{$this->post->code}] \"{$this->post->title}\" sẽ hết hạn trong {$this->daysLeft} ngày ({$this->post->expire_at?->format('d/m/Y')}).",
            'url'            => route('backend.job-posts.show', $this->post->id),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('backend.job-posts.show', $this->post->id);

        return (new MailMessage)
            ->subject("Tin tuyển dụng sắp hết hạn: [{$this->post->code}] {$this->post->title}")
            ->greeting("Xin chào {$notifiable->name},")
            ->line("Tin tuyển dụng **[{$this->post->code}] {$this->post->title}** sẽ hết hạn vào ngày **{$this->post->expire_at?->format('d/m/Y')}** ({$this->daysLeft} ngày nữa).")
            ->line('Vui lòng xem xét gia hạn hoặc đóng tin nếu không còn cần tuyển.')
            ->action('Xem tin tuyển dụng', $url);
    }
}
