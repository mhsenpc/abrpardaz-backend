<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Server\CreateFromImageRequest;
use App\Http\Requests\Server\CreateFromSnapshotRequest;
use App\Http\Requests\Server\RenameServerRequest;
use App\Http\Requests\Server\TakeSnapshotRequest;
use App\Jobs\CreateMachineFromImageJob;
use App\Jobs\TakeSnapshotJob;
use App\Models\Image;
use App\Models\Machine;
use App\Models\Plan;
use App\Repositories\MachineRepository;
use App\Repositories\SnapshotRepository;
use App\Services\MachineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MachineController extends BaseController
{
    /**
     * @var MachineRepository
     */
    protected $repository;

    public function __construct(MachineRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *      tags={"Machine"},
     *      path="/machines/list",
     *      summary="List Your machines",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="returns a list of machines"
     *     ),
     *     )
     *
     */
    function index()
    {
        $machines = $this->repository->with(['image', 'plan', 'sshKey'])->all();
        return responder()->success(['list' => $machines]);
    }

    /**
     * @OA\Post(
     *      tags={"Machine"},
     *      path="/machines/createFromImage",
     *      summary="Create a new machine from an image",
     *      description="",
     *
     * @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     * @OA\Response(
     *         response="default",
     *         description=""
     *     ),
     *    )
     *
     */
    function createFromImage(CreateFromImageRequest $request)
    {
        $user_id = Auth::id();
        $name = \request('name');
        $plan_id = \request('plan_id');
        $image_id = \request('image_id');
        $ssh_key_id = \request('ssh_key_id');

        try {
            $machine = MachineRepository::createMachine(
                $name,
                $user_id,
                $plan_id,
                $image_id,
                $ssh_key_id
            );

            CreateMachineFromImageJob::dispatch(
                $user_id,
                $name,
                $plan_id,
                $image_id,
                $ssh_key_id,
                $machine->id
            );

            return responder()->success(['message' => "عملیات ساخت سرور شروع شد"]);
        } catch (\Exception $exception) {
            return responder()->error(500, "ساخت سرور انجام نشد");
        }
    }

    function createFromSnapshot(CreateFromSnapshotRequest $request)
    {
        return responder()->success(['message' => "سرور با موفقیت ساخته شد"]);
    }

    /**
     * @OA\Get(
     *      tags={"Machine"},
     *      path="/machines/{id}/console",
     *      summary="Get console url of the machine ",
     *      description="",
     *
     * @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     * @OA\Response(
     *         response="default",
     *         description=""
     *     ),
     *
     *
     *     )
     *
     */
    function console()
    {
        $machine = Machine::findorFail(\request('id'));
        $service = new MachineService();
        $link = $service->console($machine->remote_id);

        return responder()->success(['link' => $link]);
    }

    /**
     * @OA\Post(
     *      tags={"Machine"},
     *      path="/machines/{id}/powerOn",
     *      summary="powers on the machine ",
     *      description="",
     *
     * @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     * @OA\Response(
     *         response="default",
     *         description=""
     *     ),
     *     )
     *
     */
    function powerOn()
    {
        $machine = Machine::findorFail(\request('id'));
        $service = new MachineService();
        $service->powerOn($machine->remote_id);
        return responder()->success(['message' => "سرور با موفقیت روشن شد"]);
    }

    /**
     * @OA\Post(
     *      tags={"Machine"},
     *      path="/machines/{id}/powerOff",
     *      summary="powers off the machine ",
     *      description="",
     *
     * @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     * @OA\Response(
     *         response="default",
     *         description=""
     *     ),
     *     )
     *
     */
    function powerOff()
    {
        $machine = Machine::findorFail(\request('id'));
        $service = new MachineService();
        $service->powerOf($machine->remote_id);
        return responder()->success(['message' => "سرور با موفقیت خاموش شد"]);
    }

    /**
     * @OA\Post(
     *      tags={"Machine"},
     *      path="/machines/{id}/takeSnapshot",
     *      summary="Take snapshot from a machine",
     *      description="",
     *
     * @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
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
        $machine = Machine::findorFail(\request('id'));

        $snapshot = SnapshotRepository::newSnapshot(
            \request('name'),
            \request('id'),
            Auth::id()
        );

        TakeSnapshotJob::dispatch($machine->remote_id, \request('name'), $snapshot->id);

        return responder()->success(['message' => "عملیات ساخت شروع تصویر آنی شروع شد"]);
    }

    function resendInfo()
    {
        return responder()->success(['message' => "اطلاعات سرور مجددا به ایمیل شما ارسال گردید"]);
    }

    /**
     * @OA\Put(
     *      tags={"Machine"},
     *      path="/machines/{id}/rename",
     *      summary="Change the name of a machine",
     *      description="",
     *
     * @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     * @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="The new name you want to put on the machine",
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
    function rename(RenameServerRequest $request)
    {
        $machine = Machine::findorFail(\request('id'));
        $service = new MachineService();
        $service->rename($machine->remote_id, \request('name'));

        return responder()->success(['message' => "نام سرور با موفقیت تغییر یافت"]);
    }

    /**
     * @OA\Delete(
     *      tags={"Machine"},
     *      path="/machines/remove",
     *      summary="Removes the machine ",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description=""
     *     ),
     *     )
     *
     */
    function remove()
    {
        $machine = Machine::findorFail(\request('id'));
        $service = new MachineService();
        $service->remove($machine->remote_id);

        return responder()->success(['message' => "سرور با موفقیت حذف گردید"]);
    }
}
