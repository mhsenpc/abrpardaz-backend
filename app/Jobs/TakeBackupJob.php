<?php

namespace App\Jobs;

use App\Services\AutoBackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TakeBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var string
     */
    private $machine_id;

    /**
     * Create a new job instance.
     *
     * @param int $machine_id
     */
    public function __construct(int $machine_id)
    {
        $this->machine_id = $machine_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //enable it for
        /*Redis::throttle('key')->allow(1)->every(120)->then(function () { */
        AutoBackupService::takeBackup($this->machine_id);
        /* }, function () {
             // Could not obtain lock...

             return $this->release(10);
         });*/
    }
}
