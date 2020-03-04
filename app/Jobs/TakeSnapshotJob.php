<?php

namespace App\Jobs;

use App\Models\Snapshot;
use App\Notifications\CreateServerFailedNotification;
use App\Notifications\CreateSnapshotFailedNotification;
use App\Services\MachineService;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        try {
            $service = new MachineService();
            $image = $service->takeSnapshot($this->remote_id, $this->name, $this->snapshot_id);
            //update size and remote id in snapshots
            Snapshot::find($this->snapshot_id)->updateSizeAndRemoteId($image->id, $image->size);
        } catch (\Exception $exception) {
            Log::critical('failed to remove snapshot #'.$this->snapshot_id);
            Log::critical($exception);
            $snapshot = Snapshot::find($this->snapshot_id);
            $snapshot->user->notify(new CreateSnapshotFailedNotification($snapshot, $snapshot->user->profile));
            $snapshot->delete();
        }
    }
}
