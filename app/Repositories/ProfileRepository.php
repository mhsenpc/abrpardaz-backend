<?php


namespace App\Repositories;


use App\Models\Profile;

class ProfileRepository extends BaseRepository
{
    function __construct(Profile $model)
    {
        $this->model = $model;
    }
}
