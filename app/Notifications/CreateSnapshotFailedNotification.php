<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CreateSnapshotFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    private $snapshot;
    private $profile;

    /**
     * Create a new notification instance.
     *
     * @param $snapshot
     * @param $profile
     */
    public function __construct($snapshot, $profile)
    {
        $this->profile = $profile;
        $this->snapshot = $snapshot;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail','database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("[Snapshot #: {$this->snapshot->name}] ساخت تصویر آنی با شکست مواجه شد")
            ->line("تلاش برای ساخت تصویر آنی ".$this->snapshot->name." با شکست مواجه گردید")
            ->line("از این رویداد متاسفیم و مراتب به اطلاع تیم پشتیبانی ابرپرداز ارسال گردید");
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message' => "تلاش برای ساخت تصویر آنی ".$this->snapshot->name." با شکست مواجه گردید"
        ];
    }
}
