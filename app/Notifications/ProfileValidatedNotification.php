<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProfileValidatedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    private $user;
    private $profile;

    /**
     * Create a new notification instance.
     *
     * @param $user
     * @param $profile
     */
    public function __construct($user, $profile)
    {
        $this->user = $user;
        $this->profile = $profile;
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
            ->subject("ابرپرداز - اطلاعات کاربری شما تایید شد")
            ->line('کاربر گرامی')
            ->line('اطلاعات حساب کاربری شما توسط کارشناسان ما بررسی و تایید شد')
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
            'message' => 'اطلاعات حساب کاربری شما توسط کارشناسان ما بررسی و تایید شد'
        ];
    }
}
