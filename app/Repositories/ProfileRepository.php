<?php


namespace App\Repositories;


use App\Models\Profile;
use Prettus\Repository\Eloquent\BaseRepository;

class ProfileRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return "App\\Models\\Profile";
    }
}
