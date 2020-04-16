<?php

namespace App\Jobs;

use App\Models\Backup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RemoveMachineBackupsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var int
     */
    private $machine_id;
    /**
     * @var int
     */
    private $user_id;

    /**
     * Create a new job instance.
     *
     * @param int $machine_id
     * @param int $user_id
     */
    public function __construct(int $machine_id, int $user_id)
    {
        //
        $this->machine_id = $machine_id;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $backups = Backup::where('machine_id',$this->machine_id)->get();
        foreach ($backups as $backup){
            $backup->delete();
            //$service = new SnapshotService();
            //$result = $service->remove($backup->remote_id);
            $result = true;
            if($result){
                Log::info('Backup removed. id #' . $this->machine_id . ',user #' . $this->user_id);
            }
            else{
                Log::info('Failed to remove backup. id #' . $this->machine_id . ',user #' . $this->user_id);
            }
        }
    }
}
