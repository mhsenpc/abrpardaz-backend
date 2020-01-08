<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketNotification extends Notification
{
    use Queueable;
    private $ticket;
    private $user;

    /**
     * Create a new notification instance.
     *
     * @param $ticket
     * @param $user
     */
    public function __construct($ticket, $user)
    {
        $this->ticket = $ticket;
        $this->user = $user;
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
        return (new MailMessage)
            ->subject("[Ticket ID: {$this->ticket->ticket_id}] {$this->ticket->title}")
            ->line($this->user->first_name.' '.$this->user->last_name. ' عزیز')
            ->line('از اینکه با پشتیبانی ابرپرداز تماس گرفتید متشکریم.')
            ->line('تیکتی برای شما باز شده و به محض پاسخ دهی به شما اطلاع داده خواهد شد')
            ->line('اطلاعات تیکت شما:')
            ->line('عنوان:' . $this->ticket->title)
            ->line('اولویت:' . $this->ticket->priority)
            ->line('وضعیت:' . $this->ticket->status)
            ->line('هر زمان که تمایل داشته باشید می توانید از طریق لینک زیر تیکت خود را مشاهده کنید')
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
            //
        ];
    }
}
