<?php


namespace App\Repositories;


use App\Models\Image;

class ImageRepository extends BaseRepository
{
    function __construct()
    {
        $this->model = (new Image());
    }
}
