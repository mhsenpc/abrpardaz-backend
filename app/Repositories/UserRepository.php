<?php


namespace App\Repositories;


use App\Models\Profile;
use App\Models\User;

class UserRepository extends BaseRepository
{
    function __construct(User $model)
    {
        $this->model = $model;
    }

    function newUser(string $email, string $password)
    {
        $profile = (new Profile());
        $profile->save();

        $this->model->password = $password;
        $this->model->is_root = true;
        $this->model->is_active = false;
        $this->model->email = $email;
        $this->model->profile_id = $profile->id;
        $this->model->save();

        return $this->model;
    }
}
