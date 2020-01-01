<?php

namespace App\Jobs;

use App\Services\MachineService;
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
    }
}
