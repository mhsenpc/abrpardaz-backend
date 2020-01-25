<?php

namespace App\Jobs;

use App\Models\Machine;
use App\Models\MachineBilling;
use App\User;
use App\Models\Volume;
use App\Notifications\SendMachineInfoNotification;
use App\Services\MachineService;
use App\Services\VolumeService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class CreateMachineFromImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var int
     */
    private $user_id;
    /**
     * @var string
     */
    private $name;
    /**
     * @var int
     */
    private $plan_id;
    /**
     * @var int
     */
    private $image_id;
    private $ssh_key_id;
    /**
     * @var int
     */
    private $machine_id;

    /**
     * Create a new job instance.
     *
     * @param int $user_id
     * @param string $name
     * @param int $plan_id
     * @param int $image_id
     * @param $ssh_key_id
     * @param int $machine_id
     */
    public function __construct(int $user_id, string $name, int $plan_id, int $image_id, $ssh_key_id, int $machine_id)
    {
        $this->user_id = $user_id;
        $this->name = $name;
        $this->plan_id = $plan_id;
        $this->image_id = $image_id;
        $this->ssh_key_id = $ssh_key_id;
        $this->machine_id = $machine_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = new MachineService();
        $result = $service->createMachineFromImage(
            $this->machine_id,
            $this->name,
            $this->user_id,
            $this->plan_id,
            $this->image_id,
            $this->ssh_key_id
        );

        $machine = Machine::find($this->machine_id);
        $user = User::find($this->user_id);

        //find its root volume
        $volumeService = new VolumeService();
        $volume_id = $volumeService->findMachineRootVolume($machine->remote_id);
        Volume::create([
            'remote_id' => $volume_id,
            'name' => $volume_id,
            'size' => $machine->plan->disk,
            'is_root' => true,
            'machine_id' => $machine->id,
            'user_id' => $user->id
        ]);

        MachineBilling::create([
            'machine_id' =>$this->machine_id,
            'plan_id' => $this->plan_id,
            'last_billing_date' => Carbon::now()
        ]);

        $user->notify(new SendMachineInfoNotification($user, $machine));
    }
}
