<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReplyNotification extends Notification
{
    use Queueable;
    private $ticket;
    private $reply;
    private $profile;

    /**
     * Create a new notification instance.
     *
     * @param $ticket
     * @param $reply
     * @param $profile
     */
    public function __construct($ticket, $reply, $profile)
    {
        $this->ticket = $ticket;
        $this->reply = $reply;
        $this->profile = $profile;
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
            ->subject("RE: {$this->ticket->title} (Ticket ID: {$this->ticket->ticket_id})")
            ->line($this->reply->comment)
            ->line("پاسخ توسط:".$this->profile->first_name . ' ' . $this->profile->last_name)
            ->line('عناون:' . $this->ticket->title)
            ->line('شماره تیکت:' . $this->ticket->ticket_id)
            ->line('وضعیت:' . $this->ticket->status)
            ->line('هر زمان که تمایل داشته باشید می توانید از طریق لینک زیر تیکت خود را مشاهده کنید')
            ->action('نمایش تیکت', url('tickets/' . $this->ticket->ticket_id));
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
