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
use App\Services\VolumeService;
use Illuminate\Support\Facades\Auth;

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
        return responder()->success(['list' => $volumes]);
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
     *             type="float"
     *         )
     *     ),
     *
     *     )
     *
     */
    function createVolume(CreateVolumeRequest $request)
    {
        $service = new VolumeService();
        $volume = $service->create(\request('name'), \request('size'));
        Volume::create([
            'remote_id' => $volume->id,
            'name' => \request('name'),
            'size' => \request('size'),
            'is_root' => false,
            'user_id' => Auth::id()
        ]);
        return responder()->success(['message' => 'فضا با موفقیت ساخته شد']);
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
     *             type="int"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="machine_id",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="int"
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

        return responder()->success(['message' => 'اتصال با موفقیت انجام شد']);
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
     *             type="int"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="machine_id",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="int"
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

        return responder()->success(['message' => 'قطع ارتباط با موفقیت انجام شد']);
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
     *             type="int"
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

        return responder()->success(['message' => 'نام فضا با موفقیت تغییر یافت']);
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
     *             type="int"
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
            return responder()->error(500, 'لطفا قبل از حذف فضا اتصال آن را قطع کنید');
        }

        $service = new VolumeService();
        $service->remove($volume->remote_id);
        $volume->delete();

        return responder()->success(['message' => 'فضا با موفقیت حذف شد']);
    }
}
