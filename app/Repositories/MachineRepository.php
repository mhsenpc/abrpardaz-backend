<?php


namespace App\Repositories;


use App\Models\Machine;

class MachineRepository extends BaseRepository
{
    function __construct()
    {
        $this->model = (new Machine());
    }
}
