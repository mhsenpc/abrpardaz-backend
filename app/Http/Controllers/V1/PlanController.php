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

    function index(){
        $plans = (new PlanRepository(new Plan()))->all();
        return responder()->success(['list'=>$plans]);
    }
}
