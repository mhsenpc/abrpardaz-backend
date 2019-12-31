<?php


namespace App\Repositories;


use App\Models\Plan;
use Prettus\Repository\Eloquent\BaseRepository;

class PlanRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return "App\\Models\\Plan";
    }
}
