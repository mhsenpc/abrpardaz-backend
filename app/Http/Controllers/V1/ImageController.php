<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Repositories\ImageRepository;
use Illuminate\Http\Request;

class ImageController extends BaseController
{
    /**
     * @var ImageRepository
     */
    protected $repository;

    public function __construct(ImageRepository $repository)
    {
        $this->repository = $repository;
    }

    function os(){
        return responder()->success(['items' => $this->repository->all()]);
    }
}
