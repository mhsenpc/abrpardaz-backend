<?php

namespace App\Jobs;

use App\Events\MachineRescale;
use App\Models\Machine;
use App\Models\Plan;
use App\Models\ServerActivity;
use App\Notifications\RebuildMachineNotification;
use App\Notifications\RescaleMachineNotification;
use App\Services\MachineService;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RescaleMachineJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var int
     */
    private $machine_id;
    /**
     * @var int
     */
    private $plan_id;
    /**
     * @var int
     */
    private $user_id;

    /**
     * Create a new job instance.
     *
     * @param int $machine_id
     * @param int $plan_id
     * @param int $user_id
     */
    public function __construct(int $machine_id, int $plan_id, int $user_id)
    {
        $this->machine_id = $machine_id;
        $this->plan_id = $plan_id;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $machine = Machine::find($this->machine_id);
        $plan = Plan::find($this->plan_id);
        $user = User::find($this->user_id);

        $service = new MachineService($user->remote_user_id,$user->remote_password, $machine->project->remote_id);
        try {
            $service->rescale($machine->remote_id, $plan->remote_id);
            $machine->changePlan($plan);
            Log::info('rescale machine #' . $machine->id . ',user #' . $this->user_id);
            ServerActivity::create([
                'machine_id' => $this->machine_id,
                'user_id' => $this->user_id,
                'message' => 'پلن سرور تغییر یافت'
            ]);
            $user = User::find($this->user_id);
            $user->notify(new RescaleMachineNotification($user->profile, $machine,$plan));
            MachineRescale::dispatch($machine);
        } catch (\Exception $exception) {
            Log::critical("Failed to change server #" . $this->machine_id . " to plan #" . $this->plan_id);
            Log::critical($exception);
        }
    }
}
