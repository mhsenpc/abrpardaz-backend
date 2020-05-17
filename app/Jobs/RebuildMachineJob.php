<?php

namespace App\Jobs;

use App\Models\Image;
use App\Models\Machine;
use App\Models\ServerActivity;
use App\Notifications\RebuildMachineNotification;
use App\Services\MachineService;
use App\Services\PasswordGeneratorService;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RebuildMachineJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var int
     */
    private $machine_id;
    /**
     * @var int
     */
    private $image_id;
    /**
     * @var int
     */
    private $user_id;

    /**
     * Create a new job instance.
     *
     * @param int $machine_id
     * @param int $image_id
     * @param int $user_id
     */
    public function __construct(int $machine_id, int $image_id, int $user_id)
    {
        $this->machine_id = $machine_id;
        $this->image_id = $image_id;
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
        $image = Image::find($this->image_id);
        $new_pass = PasswordGeneratorService::generate();
        try {
            $service = new MachineService();
            $service->rebuild($machine->remote_id, $image->remote_id, $new_pass);
            $machine->password = $new_pass;
            $machine->image_id =$this->image_id;
            $machine->save();
            $user = User::find($this->user_id);
            $user->notify(new RebuildMachineNotification($user->profile, $machine));
            Log::info('rebuild machine #' . $machine->id . ', image #' . request('image_id') . ', user #' . $this->user_id);
            ServerActivity::create([
                'machine_id' => $this->machine_id,
                'user_id' => $this->user_id,
                'message' => 'تصویر جدید بر روی ماشین بارگذاری شد'
            ]);
        } catch (\Exception $exception) {
            Log::error('failed to rebuild machine #' . $this->machine_id . ', image #' . $this->image_id . ', user #' . $this->user_id);
            Log::error($exception);
        }
    }
}
