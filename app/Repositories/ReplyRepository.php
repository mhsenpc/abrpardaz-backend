<?php


namespace App\Repositories;


use App\Models\SshKey;
use Prettus\Repository\Eloquent\BaseRepository;

class ReplyRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return "App\\Models\\Reply";
    }
}
