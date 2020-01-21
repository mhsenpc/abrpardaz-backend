<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Models\Plan;
use App\Services\Responder;

class PlanController extends BaseController
{
    /**
     * @OA\Get(
     *      tags={"Plan"},
     *      path="/plans/list",
     *      summary="List all plans",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="List of plans"
     *     ),
     *
     *     )
     *
     */
    function index()
    {
        $plans = Plan::all();
        return Responder::result(['list' => $plans]);
    }
}
