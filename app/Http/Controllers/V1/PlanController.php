<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Models\Plan;
use App\Repositories\PlanRepository;
use Illuminate\Http\Request;

class PlanController extends BaseController
{
    /**
     * @var PlanRepository
     */
    protected $repository;

    public function __construct(PlanRepository $repository)
    {
        $this->repository = $repository;
    }

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
        $plans = $this->repository->all();
        return responder()->success(['list'=>$plans]);
    }
}
