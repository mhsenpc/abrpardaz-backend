<?php

namespace App\Jobs;

use App\Services\MobileService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class sendSmsTicketRepliedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var string
     */
    private $mobile;
    /**
     * @var string
     */
    private $ticket_code;

    /**
     * Create a new job instance.
     *
     * @param string $mobile
     * @param string $ticket_code
     */
    public function __construct(string $mobile, string $ticket_code)
    {
        $this->mobile = $mobile;
        $this->ticket_code = $ticket_code;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!empty($this->mobile))
            MobileService::sendTicketReplied($this->mobile, $this->ticket_code);
    }
}
