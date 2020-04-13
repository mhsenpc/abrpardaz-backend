<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Volume\AttachToMachineRequest;
use App\Http\Requests\Volume\CreateVolumeRequest;
use App\Http\Requests\Volume\DetachFromMachineRequest;
use App\Http\Requests\Volume\RemoveVolumeRequest;
use App\Http\Requests\Volume\RenameVolumeRequest;
use App\Models\Machine;
use App\Models\Volume;
use App\Services\Responder;
use App\Services\VolumeService;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VolumeController extends BaseController
{
    /**
     * @OA\Get(
     *      tags={"Volume"},
     *      path="/volumes/list",
     *      summary="Returns the list of your volumes",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     )
     *
     */
    public function index()
    {
        $volumes = Volume::all();
        return Responder::result(['list' => $volumes]);
    }

    /**
     * @OA\Post(
     *      tags={"Volume"},
     *      path="/volumes/createVolume",
     *      summary="Creates a volume",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
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
     *         name="size",
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
    function createVolume(CreateVolumeRequest $request)
    {
        $service = new VolumeService();

        $user_limit = User::find(Auth::id())->userLimit;
        if ($user_limit) {
            if (Auth::user()->VolumesUsage + \request('size') > $user_limit->max_volumes_usage) {
                return Responder::error('شما اجازه ایجاد فضا بیش از ' . $user_limit->max_volumes_usage . ' گیگابایت را ندارید');
            }
        }

        $volume = $service->create(\request('name'), \request('size'));
        Volume::create([
            'remote_id' => $volume->id,
            'name' => \request('name'),
            'size' => \request('size'),
            'user_id' => Auth::id(),
            'last_billing_date' => Carbon::now()
        ]);

        Log::info('new volume created size' . request('size') . ',user #' . Auth::id());
        return Responder::success('فضا با موفقیت ساخته شد');
    }

    /**
     * @OA\Post(
     *      tags={"Volume"},
     *      path="/volumes/{id}/attachToMachine",
     *      summary="Attaches a volume to a machine",
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
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="machine_id",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     )
     *
     */
    public function attachToMachine(AttachToMachineRequest $request)
    {
        $volume = Volume::find(\request('id'));
        $machine = Machine::find(\request('machine_id'));

        $service = new VolumeService();
        $service->attachVolumeToMachine($machine->remote_id, $volume->remote_id);

        Log::info('volume attach to machine #' . request('machine_id') . ', volume #' . request('id') . ',user #' . Auth::id());
        return Responder::success('اتصال با موفقیت انجام شد');
    }

    /**
     * @OA\Post(
     *      tags={"Volume"},
     *      path="/volumes/{id}/detachFromMachine",
     *      summary="Detaches a volume from a machine",
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
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="machine_id",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     )
     *
     */
    function detachFromMachine(DetachFromMachineRequest $request)
    {
        $volume = Volume::find(\request('id'));
        $machine = Machine::find(\request('machine_id'));

        $service = new VolumeService();
        $service->detachVolumeFromMachine($machine->remote_id, $volume->remote_id);

        Log::info('volume detach from machine #' . request('machine_id') . ', volume #' . request('id') . ',user #' . Auth::id());
        return Responder::success('قطع ارتباط با موفقیت انجام شد');
    }

    /**
     * @OA\Post(
     *      tags={"Volume"},
     *      path="/volumes/{id}/rename",
     *      summary="Change the name of the volume",
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
     *             type="integer"
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
     *     )
     *
     */
    function rename(RenameVolumeRequest $request)
    {
        $volume = Volume::find(\request('id'));
        $volume->name = \request('name');
        $volume->save();

        Log::info('volume rename #' . request('id') . ',user #' . Auth::id());

        return Responder::success('نام فضا با موفقیت تغییر یافت');
    }

    /**
     * @OA\Delete(
     *      tags={"Volume"},
     *      path="/volumes/{id}/remove",
     *      summary="Removes the volume",
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
     *             type="integer"
     *         )
     *     ),
     *
     *     )
     *
     */
    function remove(RemoveVolumeRequest $request)
    {
        $volume = Volume::find(\request('id'));

        if (!empty($volume->machine_id)) {
            return Responder::error('لطفا قبل از حذف فضا اتصال آن را قطع کنید');
        }

        $service = new VolumeService();
        $service->remove($volume->remote_id);
        $volume->stopBilling();
        $volume->delete();

        Log::info('volume remove #' . request('id') . ',user #' . Auth::id());
        return Responder::success('فضا با موفقیت حذف شد');
    }
}
