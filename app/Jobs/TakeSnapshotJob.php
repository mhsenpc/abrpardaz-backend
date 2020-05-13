<?php

namespace App\Jobs;

use App\Events\SnapshotCreated;
use App\Models\Machine;
use App\Models\ServerActivity;
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
    private $machine_id;
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
     * @param int $machine_id
     * @param string $name
     * @param int $snapshot_id
     */
    public function __construct(int $machine_id, string $name, int $snapshot_id)
    {
        $this->machine_id = $machine_id;
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
            $machine = Machine::find($this->machine_id);
            $service = new MachineService($machine->user->remote_user_id,$machine->user->remote_password, $machine->project->remote_id);
            $image = $service->takeSnapshot($machine->remote_id, $this->name,$this->snapshot_id);
            //update size and remote id in snapshots
            Snapshot::find($this->snapshot_id)->updateSizeAndRemoteId($image->id, $image->size);

            SnapshotCreated::dispatch($machine->user_id, $this->snapshot_id,$this->name);
            Log::info('take snapshot machine #' . $machine->id . ',user #' . $machine->user_id);
            ServerActivity::create([
                'machine_id' => $this->machine_id,
                'user_id' => $machine->user_id,
                'message' => 'تصویر آنی با نام '.$this->name. " برای سرور ".$machine->name. " ساخته شد"
            ]);
        } catch (\Exception $exception) {
            Log::critical('failed to take snapshot #'.$this->snapshot_id);
            Log::critical($exception);
            $snapshot = Snapshot::find($this->snapshot_id);
            $snapshot->user->notify(new CreateSnapshotFailedNotification($snapshot, $snapshot->user->profile));
            $snapshot->remote_id = -1;
            $snapshot->save();
            $snapshot->delete();
        }
    }
}
