<?php


namespace App\Repositories;


use App\Models\Plan;

class PlanRepository extends BaseRepository
{
    function __construct(Plan $model)
    {
        $this->model = $model;
    }
}
