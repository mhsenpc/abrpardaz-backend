<?php


namespace App\Repositories;


use App\Models\Snapshot;

class SnapshotRepository extends BaseRepository
{
    public function __construct(Snapshot $model)
    {
        $this->model = $model;
    }
}
