<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Models\Image;
use App\Services\Responder;
use Illuminate\Http\Request;

class ImageController extends BaseController
{
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
        return Responder::result(['list' => Image::all()]);
    }
}
