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
}
