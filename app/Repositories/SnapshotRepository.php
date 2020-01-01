<?php


namespace App\Repositories;


use App\Models\Snapshot;
use Prettus\Repository\Eloquent\BaseRepository;

class SnapshotRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return "App\\Models\\Snapshot";
    }

    static function newSnapshot(string $name,int $machine_id,int $user_id){
        return Snapshot::create([
            'name' => $name,
            'machine_id' => $machine_id,
            'user_id' => $user_id
        ]);
    }

    public function updateSizeAndRemoteId(string $remote_id,float $size){
        $this->model->remote_id = $remote_id;
        $this->model->size = $size;
        $this->model->save();
    }
}
