<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Plan\AddPlanRequest;
use App\Http\Requests\Plan\EditPlanRequest;
use App\Http\Requests\Plan\RemovePlanRequest;
use App\Http\Requests\Plan\ShowPlanRequest;
use App\Models\Plan;
use App\Services\Responder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        $plans = Plan::paginate();
        return Responder::result(['pagination' => $plans]);
    }

    /**
     * @OA\Get(
     *      tags={"Plan"},
     *      path="/plans/{id}/show",
     *      summary="Return a specific plan",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="Return a specific plan"
     *     ),
     *
     *     )
     *
     */
    function show(ShowPlanRequest $request)
    {
        $item = Plan::find(request('id'));
        return Responder::result(['item' => $item]);
    }

    /**
     * @OA\Post(
     *      tags={"Plan"},
     *      path="/plans/add",
     *      summary="Add a plan",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="remote_id",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="disk",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="ram",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="vcpu",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="hourly_price",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *
     *     )
     *
     */
    function add(AddPlanRequest $request)
    {
        Plan::create([
            'remote_id' => $request->input('remote_id'),
            'name' => $request->input('name'),
            'disk' => $request->input('disk'),
            'ram' => $request->input('ram'),
            'vcpu' => $request->input('vcpu'),
            'hourly_price' => $request->input('hourly_price'),
        ]);
        Log::info('new plan created. user #' . Auth::id());
        return Responder::success("پلن با موفقیت اضافه شد");
    }

    /**
     * @OA\Post(
     *      tags={"Plan"},
     *      path="/plans/{id}/edit",
     *      summary="Edit a plan using its id",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="remote_id",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="disk",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="ram",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="vcpu",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="hourly_price",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *
     *     )
     *
     */
    function edit(EditPlanRequest $request)
    {
        Plan::find(\request('id'))->update([
            'remote_id' => $request->input('remote_id'),
            'name' => $request->input('name'),
            'disk' => $request->input('disk'),
            'ram' => $request->input('ram'),
            'vcpu' => $request->input('vcpu'),
            'hourly_price' => $request->input('hourly_price'),
        ]);
        Log::info('Plan edited. key #' . request('id') . ',user #' . Auth::id());
        return Responder::success("پلن با موفقیت ویرایش شد");
    }

    /**
     * @OA\Delete(
     *      tags={"Plan"},
     *      path="/plans/{id}/remove",
     *      summary="Remove a plan using its id",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     )
     *
     */
    function remove(RemovePlanRequest $request)
    {
        Plan::destroy(\request('id'));
        Log::info('Plan removed. key #' . request('id') . ',user #' . Auth::id());
        return Responder::success("پلن با موفقیت حذف شد");
    }
}
