<?php


namespace App\Repositories;


use App\Models\Machine;

class MachineRepository extends BaseRepository
{
    function __construct(Machine $model)
    {
        $this->model = $model;
    }
}
