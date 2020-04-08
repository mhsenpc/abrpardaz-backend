<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Backup\RemoveBackupRequest;
use App\Http\Requests\Backup\RenameBackupRequest;
use App\Http\Requests\Backup\TriggerBackupRequest;
use App\Jobs\TakeBackupJob;
use App\Models\Backup;
use App\Models\Machine;
use App\Services\Responder;
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
        $backups = Backup::paginate(10);
        return Responder::result(['pagination' => $backups]);
    }

    /**
     * @OA\Post(
     *      tags={"Backup"},
     *      path="/backups/{id}/trigger",
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
        Machine::findOrFail(request('machine_id'));
        TakeBackupJob::dispatch(request('machine_id'));
        return Responder::success('تهیه نسخه پشتیبان بصورت دستی اجرا شد');
    }

    /**
     * @OA\Post(
     *      tags={"Backup"},
     *      path="/backups/{id}/rename",
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
     * @OA\Response(
     *         response="default",
     *         description="result"
     *     ),
     *
     *
     *     )
     *
     */
    function rename(RenameBackupRequest $request)
    {
        $backup = Backup::findOrFail(request('id'));
        //$service = new SnapshotService();
        //$result = $service->rename($backup->remote_id,request('name'));
        $result = true;
        if ($result) {
            $backup->name = request('name');
            $backup->save();
            Log::info('Backup removed. id #' . request('id') . ',user #' . Auth::id());
            return Responder::success('نام نسخه پشتیبان با موفقیت تغییر یافت');
        } else {
            return Responder::error('متاسفانه تغییر نام این نسخه پشتیبان انجام نشد');
        }
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
        //$service = new SnapshotService();
        //$result = $service->remove($backup->remote_id);
        $result = true;
        if ($result) {
            $backup->delete();
            Log::info('Backup removed. id #' . request('id') . ',user #' . Auth::id());
            return Responder::success("نسخه پشتیبان با موفقیت حذف شد");
        } else {
            return Responder::error('متاسفانه نسخه پشتیبان حذف نگردید');
        }
    }
}
