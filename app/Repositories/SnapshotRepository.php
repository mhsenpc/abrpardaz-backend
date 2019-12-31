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
}
