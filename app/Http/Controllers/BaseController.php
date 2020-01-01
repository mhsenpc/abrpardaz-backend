<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @OA\Info(
     *      version="1.0.0",
     *      title="Abrpardaz API",
     *      description="This is the official API which can be used by any user",
     * )
     *
     *  @OA\Server(
     *      url="http://localhost/api/v1",
     *      description="Local server"
     *  )
     *  @OA\Server(
     *      url=L5_SWAGGER_CONST_HOST,
     *      description="The API endpoint"
     *  )
     */
    /**
     * @OA\SecurityScheme(
     *     type="http",
     *     description="Login and copy the token into the below box",
     *     securityScheme="bearerAuth",
     *     type="http",
     *     scheme="bearer",
     *     bearerFormat="JWT"
     * )
     */
}
