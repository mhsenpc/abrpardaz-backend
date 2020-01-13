<?php


namespace App\Repositories;


use Prettus\Repository\Eloquent\BaseRepository;

class VolumeRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return "App\\Models\\Volume";
    }

    function createRootVolume(int $machine_id, int $user_id, string $remote_id, string $name, float $size)
    {
        $this->create([
            'remote_id' => $remote_id,
            'name' => $name,
            'size' => $size,
            'is_root' => true,
            'machine_id' => $machine_id,
            'user_id' => $user_id
        ]);
    }
}
