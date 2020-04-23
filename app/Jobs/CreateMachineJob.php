<?php

namespace App\Jobs;

use App\Events\MachineCreated;
use App\Models\Machine;
use App\Models\MachineBilling;
use App\Notifications\SendMachineInfoNotification;
use App\Services\MachineService;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateMachineJob implements ShouldQueue
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
     * @param int $machine_id
     * @param $ssh_key_id
     */
    public function __construct(int $user_id, string $name, int $plan_id, int $image_id, int $machine_id, $ssh_key_id)
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
        $machine = Machine::find($this->machine_id);
        $user = User::with('profile')->find($this->user_id);
        $meta_data =[
            'user' => json_encode($user)
        ];

        $service = new MachineService();
        $result = $service->createMachineFromImage(
            $this->machine_id, $this->name, $machine->password , $this->user_id, $this->plan_id, $this->image_id,$meta_data, $this->ssh_key_id
        );

        //update machine record
        $machine->updateRemoteID($result->id);

        foreach (reset($result->addresses) as $address) {
            if ($address['version'] == 4) {
                $machine->updateIpv4($address['addr']);
            }
        }

        $user = User::find($this->user_id);

        MachineBilling::create([
            'machine_id' => $this->machine_id,
            'plan_id' => $this->plan_id,
            'last_billing_date' => Carbon::now()
        ]);

        MachineCreated::dispatch($machine);

        $user->notify(new SendMachineInfoNotification($user, $machine));
    }
}
