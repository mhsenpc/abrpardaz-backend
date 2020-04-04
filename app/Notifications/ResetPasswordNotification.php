<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;
    /**
     * @var string
     */
    private $token;
    /**
     * @var string
     */
    private $email;

    /**
     * Create a new notification instance.
     *
     * @param string $email
     * @param string $token
     */
    public function __construct(string $email, string $token)
    {
        $this->token = $token;
        $this->email = $email;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $link = config('panel.url') . config('panel.reset_password') . '?token=' . $this->token . '&email=' . $this->email;
        return (new MailMessage)
            ->subject("بازنشانی رمز عبور - ابرپرداز")
            ->line('کاربر گرامی ابرپرداز')
            ->line('ما درخواستی مبنی بر بازنشانی رمز عبور شما را دریافت کردیم.')
            ->line('در صورت تمایل به بازنشانی رمز عبور بر روی لینک زیر کلیک کنید')
            ->action('بازنشانی رمز عبور', $link)
            ->line('در صورتی که این درخواست از طرف شما نبوده، این پیام را نادیده بگیرید');
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
            //
        ];
    }
}
