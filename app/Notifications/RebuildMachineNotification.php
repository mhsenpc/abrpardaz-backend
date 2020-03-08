<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RebuildMachineNotification extends Notification implements ShouldQueue
{
    use Queueable;
    private $profile;
    private $machine;

    /**
     * Create a new notification instance.
     *
     * @param $profile
     * @param $machine
     */
    public function __construct($profile, $machine)
    {
        $this->profile = $profile;
        $this->machine = $machine;
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
        return (new MailMessage)
            ->subject("بازسازی سرور - ابرپرداز")
            ->line($this->profile->name)
            ->line('سرور شما با موفقیت با تصویر جدید بازسازی شد')
            ->line('نام: ' . $this->machine->name)
            ->line('IP: ' . $this->machine->public_ipv4)
            ->line('Username: root' )
            ->line('Password: ' . $this->machine->password);
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
        ];
    }
}
