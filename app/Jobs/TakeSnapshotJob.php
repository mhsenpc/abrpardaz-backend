<?php

namespace App\Jobs;

use App\Services\MachineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TakeSnapshotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var string
     */
    private $remote_id;
    /**
     * @var string
     */
    private $name;
    /**
     * @var int
     */
    private $snapshot_id;

    /**
     * Create a new job instance.
     *
     * @param string $remote_id
     * @param string $name
     * @param int $snapshot_id
     */
    public function __construct(string $remote_id, string $name, int $snapshot_id)
    {
        $this->remote_id = $remote_id;
        $this->name = $name;
        $this->snapshot_id = $snapshot_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = new MachineService();
        $service->takeSnapshot($this->remote_id, $this->name,$this->snapshot_id);
    }
}
