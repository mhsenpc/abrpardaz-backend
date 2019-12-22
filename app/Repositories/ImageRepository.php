<?php


namespace App\Repositories;


use App\Models\Image;

class ImageRepository extends BaseRepository
{
    function __construct(Image $model)
    {
        $this->model = $model;
    }
}
