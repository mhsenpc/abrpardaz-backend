<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Models\Plan;

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
    function index(){
        $plans = Plan::all();
        return responder()->success(['list'=>$plans]);
    }
}
