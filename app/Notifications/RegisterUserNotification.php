<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegisterUserNotification extends Notification implements ShouldQueue
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
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $link = config('panel.url').config('panel.verify_page'). '?token=' . $this->token . '&email=' . $this->email;
        return (new MailMessage)
                    ->subject("فعال سازی حساب کاربری - ابرپرداز")
                    ->line('به ابرپرداز خوش آمدید.')
                    ->line('برای فعال سازی حساب کاربری خود بر روی دکمه فعال سازی کلیک کنید.')
                    ->action('فعال سازی', $link );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
