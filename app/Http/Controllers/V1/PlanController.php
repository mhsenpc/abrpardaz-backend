<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
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

    }
}
