<?php


namespace App\Repositories;


use App\Models\Snapshot;

class SnapshotRepository extends BaseRepository
{
    function __construct()
    {
        $this->model = (new Snapshot());
    }
}
