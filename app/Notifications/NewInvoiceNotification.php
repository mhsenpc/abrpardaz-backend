<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewInvoiceNotification extends Notification implements ShouldQueue
{
    use Queueable;
    private $profile;
    private $user;
    private $invoice;

    /**
     * Create a new notification instance.
     *
     * @param $user
     * @param $invoice
     */
    public function __construct($user,$profile, $invoice)
    {
        $this->user = $user;
        $this->profile = $profile;
        $this->invoice = $invoice;
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
            ->subject("[invoice ID: {$this->invoice->invoice_id}]  صورتجساب جدید")
            ->line($this->profile->name)
            ->line('صورت حساب جدیدی برای شما ایجاد گردید')
            ->line('لطفا هر چه سریع تر جهت پرداخت آن از طریق پنل اقدام نمایید');
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
            'message' => 'صورت حساب جدید با شماره '.$this->invoice->invoice_id.' برای شما ایجاد گردید'
        ];
    }
}
