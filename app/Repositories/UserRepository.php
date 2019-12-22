<?php


namespace App\Repositories;


use App\Models\User;

class UserRepository extends BaseRepository
{
    function __construct(User $model)
    {
        $this->model = $model;
    }
}
