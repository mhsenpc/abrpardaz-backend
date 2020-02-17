<?php


namespace App\Http\Controllers\V1;


use App\Http\Controllers\BaseController;
use App\Services\Responder;
use Illuminate\Support\Facades\Auth;

class LimitController extends BaseController
{
    /**
     * @OA\Get(
     *      tags={"Limit"},
     *      path="/limits/list",
     *      summary="List all user limits",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description=""
     *     ),
     *
     *     )
     *
     */
    function index(){
        $user_group = Auth::user()->userGroup;
        return Responder::result([
            'name'=>$user_group->name,
            'current_machines' => Auth::user()->MachineCount,
            'max_machines' => $user_group->max_machines,
            'current_snapshots' => Auth::user()->SnapshotCount,
            'max_snapshots' => $user_group->max_snapshots,
            'current_volumes_usage' => Auth::user()->VolumesUsage,
            'max_volumes_usage' => $user_group->max_volumes_usage,
        ]);
    }
}
