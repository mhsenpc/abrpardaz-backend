<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendMachineInfoNotification extends Notification
{
    use Queueable;
    private $user;
    private $machine;

    /**
     * Create a new notification instance.
     *
     * @param $user
     * @param $machine
     */
    public function __construct($user, $machine)
    {
        $this->user = $user;
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
            ->subject("اطلاعات سرور شما - ابرپرداز")
            ->line($this->user->first_name . ' ' . $this->user->last_name. ' '. 'عزیز')
            ->line('سرور جدید شما با موفقیت ساخته شد')
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
            //
        ];
    }
}
