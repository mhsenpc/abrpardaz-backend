<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Backup\OfMachineRequest;
use App\Http\Requests\Backup\RemoveBackupRequest;
use App\Http\Requests\Backup\TriggerBackupRequest;
use App\Http\Requests\Backup\UpdateBackupInfoRequest;
use App\Models\Backup;
use App\Models\Machine;
use App\Services\AutoBackupService;
use App\Services\Responder;
use App\Services\SnapshotService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BackupController extends BaseController
{

    /**
     * @OA\Get(
     *      tags={"Backup"},
     *      path="/backups/list",
     *      summary="List all backups",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="List backups"
     *     ),
     *
     *     )
     *
     */
    function index()
    {
        $backups = Machine::with('backups.image')->whereHas('backups')->get();
        return Responder::result(['list' => $backups]);
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
            'list' => Backup::where('machine_id', \request('machine_id'))->oldest()->get()
        ]);
    }

    /**
     * @OA\Put(
     *      tags={"Backup"},
     *      path="/backups/trigger",
     *      summary="triggers a manual backup",
     *      description="",
     *
     * @OA\Parameter(
     *         name="machine_id",
     *         in="query",
     *         description="id of the machine",
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
    function trigger(TriggerBackupRequest $request)
    {
        AutoBackupService::takeBackup(request('machine_id'));
        return Responder::success('تهیه نسخه پشتیبان بصورت دستی اجرا شد');
    }

    /**
     * @OA\Post(
     *      tags={"Backup"},
     *      path="/backups/{id}/updateInfo",
     *      summary="Rename a backup",
     *      description="",
     *
     * @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id of the backup",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     * @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="new name of the backup",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     * @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="The extra information you put on the backup",
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
    function updateInfo(UpdateBackupInfoRequest $request)
    {
        $backup = Backup::findOrFail(request('id'));
        $backup->name = request('name');
        $backup->description = request('description');
        $backup->save();
        try {
            if (!in_array($backup->remote_id, ['0', '-1'])) {
                $remote_name = \request('name') . "-" . request('id');
                $service = new SnapshotService();
                $service->rename($backup->remote_id, $remote_name);
                Log::info('Backup rename. id #' . request('id') . ',user #' . Auth::id());
            }
        } catch (\Exception $exception) {
            Log::error('failed to rename backup #' . request('id') . ',user #' . Auth::id());
            Log::error($exception);;
        }
        return Responder::success('نام نسخه پشتیبان با موفقیت تغییر یافت');
    }

    /**
     * @OA\Delete(
     *      tags={"Backup"},
     *      path="/backups/{id}/remove",
     *      summary="Removes the backup",
     *      description="",
     *
     * @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id of the backup",
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
    function remove(RemoveBackupRequest $request)
    {
        $backup = Backup::findOrFail(request('id'));
        try {
            if (!in_array($backup->remote_id, ['0', '-1'])) {
                $service = new SnapshotService();
                $service->remove($backup->remote_id);
            }
            $backup->delete();
            Log::info('Backup removed. id #' . request('id') . ',user #' . Auth::id());
            return Responder::success("نسخه پشتیبان با موفقیت حذف شد");
        } catch (\Exception $exception) {
            Log::error('Failed to remove backup. id #' . request('id') . ',user #' . Auth::id());
            Log::error($exception);
            return Responder::error("عملیات حذف نسخه پشتیبان با شکست مواجه گردید");
        }
    }
}
