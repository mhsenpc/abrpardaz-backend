<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;
    private $ticket;
    private $profile;
    private $user;

    /**
     * Create a new notification instance.
     *
     * @param $ticket
     * @param $user
     * @param $profile
     */
    public function __construct($ticket, $user, $profile)
    {
        $this->ticket = $ticket;
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
            ->subject("[Ticket ID: {$this->ticket->ticket_id}] {$this->ticket->title}")
            ->line($this->profile->name)
            ->line('تیکت جدیدی ایجاد کرده است')
            ->line('اطلاعات تیکت :')
            ->line('عنوان:' . $this->ticket->title)
            ->line('اولویت:' . $this->ticket->priority)
            ->line('وضعیت:' . $this->ticket->status)
            ->action('نمایش تیکت', url('tickets/' . $this->ticket->ticket_id));
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
            'message' => 'تیکت جدیدی به شماره ' . $this->ticket->ticket_id . ' از طرف ' . $this->profile->name . ' ارسال شده است'
        ];
    }
}
