<?php


namespace App\Services;


use App\Jobs\TakeBackupJob;
use App\Models\Backup;
use App\Models\Machine;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoBackupService
{
    static function run()
    {
        $machines = Machine::where('backup', true)->get();
        foreach ($machines as $machine) {
            if (!in_array($machine->remote_id, ['0', -1])) {
                TakeBackupJob::dispatch($machine->id);
            }
        }
    }

    static function takeBackup(int $machine_id)
    {
        try {
            $machine = Machine::find($machine_id);
            $name = $machine->name . '-' . Carbon::now();
            //$service = new MachineService();
            //$image = $service->takeSnapshot($machine->remote_id, $name);
            $image = new \stdClass();
            $image->id = '645asd54sad54';
            $image->size = rand(1.1, 5.9);


            //every machine has 7 backup slots at most
            $backups = Backup::where('machine_id', $machine_id)->get();
            //so delete latest
            if ($backups->count() > 6) {
                $old_backup = Backup::where('machine_id', $machine_id)->oldest()->first();
                //$service = new SnapshotService();
                //$result = $service->remove($old_backup->remote_id);
                $result = true;
                if ($result) {
                    $old_backup->delete();
                } else {
                    Log::critical("Couldn't delete old backup #" . $old_backup->id);
                }
            }

            //if success save in db
            Backup::create([
                'name' => $name,
                'remote_id' => $image->id,
                'size' => $image->size,
                'machine_id' => $machine->id,
                'user_id' => $machine->user_id,
                'image_id' => $machine->image_id
            ]);
        } catch (\Exception $exception) {
            Log::critical('failed to create backup from machine #' . $machine_id);
            Log::critical($exception);
        }
    }
}
