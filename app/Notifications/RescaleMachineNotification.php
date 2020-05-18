<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RescaleMachineNotification extends Notification implements ShouldQueue
{
    use Queueable;
    private $profile;
    private $machine;
    private $plan;

    /**
     * Create a new notification instance.
     *
     * @param $profile
     * @param $machine
     * @param $plan
     */
    public function __construct($profile, $machine, $plan)
    {
        $this->profile = $profile;
        $this->machine = $machine;
        $this->plan = $plan;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail','database'];
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
            ->subject("تغییر پلن سرور - ابرپرداز")
            ->line($this->profile->name.' عزیز')
            ->line('پلن سرور شما با موفقیت تغییر یافت')
            ->line('اطلاعات پلن جدید به شرح زیر می باشد.')
            ->line('نام: ' . $this->machine->name)
            ->line('IP: ' . $this->machine->public_ipv4)
            ->line('Plan: '.$this->plan->name )
            ->line('Ram: '.$this->plan->ram )
            ->line('Disk: '.$this->plan->dsik)
            ->line('VCPU: '.$this->plan->vcpu );
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
            'message' => 'سرور شما با نام ' . $this->machine->name. ' به پلن '. $this->plan->name . ' ارتقا یافت'
        ];
    }
}
