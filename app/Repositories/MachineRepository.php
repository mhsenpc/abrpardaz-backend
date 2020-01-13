<?php


namespace App\Repositories;


use App\Models\Machine;
use Prettus\Repository\Eloquent\BaseRepository;

class MachineRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return "App\\Models\\Machine";
    }


    public static function createMachine(string $name, int $user_id, int $plan_id, int $image_id, $ssh_key_id=null): Machine
    {
        /** @var Machine $machine */
        $machine = new Machine();
        $machine->name = $name;
        $machine->user_id = $user_id;
        $machine->plan_id = $plan_id;
        $machine->image_id = $image_id;
        $machine->ssh_key_id = $ssh_key_id;
        $machine->save();

        return $machine;
    }

    public static function updateRemoteID(int $machine_id, string $remote_id){
        /** @var Machine $machine */
        $machine = Machine::find($machine_id);
        $machine->remote_id = $remote_id;
        $machine->save();
        return $machine;
    }
}
