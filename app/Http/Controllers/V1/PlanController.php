<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Plan\AddPlanRequest;
use App\Http\Requests\Plan\EditPlanRequest;
use App\Http\Requests\Plan\RemovePlanRequest;
use App\Http\Requests\Plan\ShowPlanRequest;
use App\Models\Plan;
use App\Services\FlavorSyncerService;
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

    function show(ShowPlanRequest $request)
    {
        $item = Plan::find(request('id'));
        return Responder::result(['item' => $item]);
    }

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

    function remove(RemovePlanRequest $request)
    {
        Plan::destroy(\request('id'));
        Log::info('Plan removed. key #' . request('id') . ',user #' . Auth::id());
        return Responder::success("پلن با موفقیت حذف شد");
    }

    function sync(){
        $service = new FlavorSyncerService();
        $service->setRenderHtml(true);
        $service->sync();
    }
}
