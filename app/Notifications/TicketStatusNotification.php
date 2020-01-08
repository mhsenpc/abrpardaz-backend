<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusNotification extends Notification
{
    use Queueable;
    private $ticketOwner;
    private $ticket;

    /**
     * Create a new notification instance.
     *
     * @param $ticketOwner
     * @param $ticket
     */
    public function __construct($ticketOwner, $ticket)
    {
        $this->ticketOwner = $ticketOwner;
        $this->ticket = $ticket;
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
            ->subject("RE: {$this->ticket->title} (شماره تیکت: {$this->ticket->ticket_id})")
            ->line($this->ticketOwner->first_name . ' ' . $this->ticketOwner->last_name. ' '. 'عزیز')
            ->line('تیکت شما به شماره '.$this->ticket->ticket_id .' بسته شد');
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
