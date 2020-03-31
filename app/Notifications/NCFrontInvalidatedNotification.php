<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NCFrontInvalidatedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    private $user;
    private $profile;
    private $reason;

    /**
     * Create a new notification instance.
     *
     * @param $user
     * @param $profile
     * @param $reason
     */
    public function __construct($user, $profile, $reason)
    {
        $this->user = $user;
        $this->profile = $profile;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
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
            ->subject("ابرپرداز - نیاز به تکمیل اطلاعات حساب کاربری")
            ->line('کاربر گرامی')
            ->line('تصویر جلوی کارت ملی شما توسط کارشناسان ما بررسی شده و متاسفانه تایید نشده است.')
            ->line('دلیل: '. $this->reason)
            ->line('لطفا با ورود به پنل کاربری هر چه سریع تر در جهت تکمیل حساب کاربری خود اقدام نمایید')
            ->action('ورود به ابرپرداز', config('panel.url'));
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
            'message' => 'متاسفانه تصویر جلوی کارت ملی شما مورد تایید قرار نگرفته است. دلیل: ' . $this->reason
        ];
    }
}
