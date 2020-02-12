<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Snapshot\RemoveSnapshotRequest;
use App\Http\Requests\Snapshot\RenameSnapshotRequest;
use App\Http\Requests\Snapshot\OfMachineRequest;
use App\Models\Snapshot;
use App\Models\SnapshotBilling;
use App\Services\Responder;
use App\Services\SnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SnapshotController extends BaseController
{
    /**
     * @OA\Get(
     *      tags={"Snapshot"},
     *      path="/snapshots/list",
     *      summary="List all snapshots of your account",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="List of all your snapshots"
     *     ),
     *
     *     )
     *
     */
    function index()
    {
        return Responder::result(['list' => Snapshot::all()]);
    }

    /**
     * @OA\Get(
     *      tags={"Snapshot"},
     *      path="/snapshots/ofMachine",
     *      summary="List snapshots of a specific machine",
     *      description="",
     *
     * @OA\Parameter(
     *         name="machine_id",
     *         in="query",
     *         description="id of the machine your want its snapshots",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     * @OA\Response(
     *         response="default",
     *         description="List snapshots of a specific machine"
     *     ),
     *
     *     )
     *
     */
    function ofMachine(OfMachineRequest $request)
    {
        return Responder::result([
            'list' => Snapshot::where('machine_id',\request('machine_id'))->get()
        ]);
    }


    /**
     * @OA\Post(
     *      tags={"Snapshot"},
     *      path="/snapshots/{id}/rename",
     *      summary="Rename a snapshot",
     *      description="",
     *
     * @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id of the snapshot",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     * @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="new name of the snapshot",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     * @OA\Response(
     *         response="default",
     *         description="result"
     *     ),
     *
     *
     *     )
     *
     */
    function rename(RenameSnapshotRequest $request)
    {
        $snapshot = Snapshot::find(request('id'));
        $service = new SnapshotService();
        $result = $service->rename(
            $snapshot->remote_id,
            \request('name')
        );

        $result = true;
        if($result){
            $snapshot->name = \request('name');
            $snapshot->save();
            Log::info('snapshot renamed snapshot #'.request('id').',user #'.Auth::id());
            return Responder::success("نام تصویر آنی با موفقیت تغییر کرد");
        }
        else{
            Log::warning('failed to rename snapshot. snapshot #'.request('id').',user #'.Auth::id());
            return Responder::error("تغییر نام تصویر آنی انجام نشد");
        }

    }

    /**
     * @OA\Delete(
     *      tags={"Snapshot"},
     *      path="/snapshots/{id}/remove",
     *      summary="Removes the snapshot",
     *      description="",
     *
     * @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id of the snapshot",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     * @OA\Response(
     *         response="default",
     *         description="result"
     *     ),
     *
     *
     *     )
     *
     */
    function remove(RemoveSnapshotRequest $request)
    {
        $service = new SnapshotService();
        $result = $service->remove(\request('id'));
        if($result){
            $snapshot = Snapshot::find(\request('id'));
            $snapshot->stopBilling();
            $snapshot->delete();
            Log::info('snapshot removed snapshot #'.request('id').',user #'.Auth::id());
            return Responder::success("تصویر آنی با موفقیت حذف شد");
        }
        else{
            Log::warning('failed to remove snapshot. snapshot #'.request('id').',user #'.Auth::id());
            return Responder::error("حذف تصویر آنی امکانپذیر نمی باشد");
        }
    }
}
