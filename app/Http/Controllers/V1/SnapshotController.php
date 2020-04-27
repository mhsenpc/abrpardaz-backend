<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Snapshot\OfMachineRequest;
use App\Http\Requests\Snapshot\RemoveSnapshotRequest;
use App\Http\Requests\Snapshot\RenameSnapshotRequest;
use App\Http\Requests\Snapshot\TakeSnapshotRequest;
use App\Jobs\TakeSnapshotJob;
use App\Models\Backup;
use App\Models\Machine;
use App\Models\Snapshot;
use App\Services\Responder;
use App\Services\SnapshotService;
use App\User;
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
     *      tags={"Backup"},
     *      path="/backups/ofMachine",
     *      summary="List backups of a specific machine",
     *      description="",
     *
     * @OA\Parameter(
     *         name="machine_id",
     *         in="query",
     *         description="id of the machine your want its backups",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     * @OA\Response(
     *         response="default",
     *         description="List backups of a specific machine"
     *     ),
     *
     *     )
     *
     */
    function ofMachine(OfMachineRequest $request)
    {
        return Responder::result([
            'list' => Snapshot::where('machine_id', \request('machine_id'))->get()
        ]);
    }

    /**
     * @OA\Post(
     *      tags={"Snapshot"},
     *      path="/snapshots/takeSnapshot",
     *      summary="Take snapshot from a machine",
     *      description="",
     *
     * @OA\Parameter(
     *         name="machine_id",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     * @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="The name you want to put on the snapshot",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *
     * @OA\Response(
     *         response="default",
     *         description=""
     *     ),
     *     )
     *
     */
    function takeSnapshot(TakeSnapshotRequest $request)
    {
        $machine = Machine::findorFail(\request('machine_id'));

        $user_limit = User::find(Auth::id())->userLimit;
        if ($user_limit) {
            if (Auth::user()->SnapshotCount >= $user_limit->max_snapshots) {
                return Responder::error('شما اجازه ساخت بیش از ' . $user_limit->max_snapshots . ' تصویر آنی را ندارید');
            }
        }

        $snapshot = Snapshot::newSnapshot(
            \request('name'),
            \request('machine_id'),
            Auth::id(),
            $machine->image_id
        );

        TakeSnapshotJob::dispatch($machine->id, \request('name'), $snapshot->id);

        return Responder::success('عملیات ساخت تصویر آنی شروع شد');
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
        $snapshot->name = \request('name');
        $snapshot->save();

        $remote_name = \request('name') . "-" . request('id');

        try {
            $service = new SnapshotService();
            $service->rename(
                $snapshot->remote_id,
                $remote_name
            );
            Log::info('snapshot renamed snapshot #' . request('id') . ',user #' . Auth::id());
        } catch (\Exception $exception) {
            Log::error('failed to rename snapshot. snapshot #' . request('id') . ',user #' . Auth::id());
            Log::error($exception);
        }

        return Responder::success("نام تصویر آنی با موفقیت تغییر کرد");
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
        $snapshot = Snapshot::find(\request('id'));
        $snapshot->stopBilling();
        $snapshot->delete();
        try {
            $service = new SnapshotService();
            $service->remove($snapshot->remote_id);
            Log::info('snapshot removed snapshot #' . request('id') . ',user #' . Auth::id());
        } catch (\Exception $exception) {
            Log::error('failed to remove snapshot #' . request('id') . ',user #' . Auth::id());
            Log::error($exception);
        }
        return Responder::success("تصویر آنی با موفقیت حذف شد");
    }
}
