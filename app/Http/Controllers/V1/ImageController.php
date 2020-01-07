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

    /**
     * @OA\Get(
     *      tags={"Image"},
     *      path="/images/os",
     *      summary="List all operating systems",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="List of operating system"
     *     ),
     *
     *     )
     *
     */
    function os(){
        return responder()->success(['items' => $this->repository->all()]);
    }
}
