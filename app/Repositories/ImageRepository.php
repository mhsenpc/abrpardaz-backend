<?php


namespace App\Repositories;


use App\Models\Image;
use Prettus\Repository\Eloquent\BaseRepository;

class ImageRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return "App\\Models\\Image";
    }
}
