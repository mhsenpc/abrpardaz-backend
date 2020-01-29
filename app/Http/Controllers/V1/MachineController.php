<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Server\CreateFromImageRequest;
use App\Http\Requests\Server\CreateFromSnapshotRequest;
use App\Http\Requests\Server\DetailsRequest;
use App\Http\Requests\Server\GetConsoleRequest;
use App\Http\Requests\Server\PowerOffRequest;
use App\Http\Requests\Server\PowerOnRequest;
use App\Http\Requests\Server\RemoveServerRequest;
use App\Http\Requests\Server\RenameServerRequest;
use App\Http\Requests\Server\RescaleServerRequest;
use App\Http\Requests\Server\ResendInfoRequest;
use App\Http\Requests\Server\TakeSnapshotRequest;
use App\Jobs\CreateMachineFromImageJob;
use App\Jobs\CreateMachineFromSnapshotJob;
use App\Jobs\TakeSnapshotJob;
use App\Models\Machine;
use App\Models\Plan;
use App\Models\Snapshot;
use App\Notifications\SendMachineInfoNotification;
use App\Services\MachineService;
use App\Services\Responder;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MachineController extends BaseController
{
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
        $machines = Machine::with(['image', 'plan', 'sshKey'])->get();
        return Responder::result(['list' => $machines]);
    }

    /**
     * @OA\Get(
     *      tags={"Machine"},
     *      path="/machines/{id}/details",
     *      summary="Detailed information of a machine",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="returns a list of machines"
     *     ),
     *
     *
     * @OA\Parameter(
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
     *
     *
     */
    function details(DetailsRequest $request)
    {
        $machines = Machine::where('id', request('id'))->with(['image', 'plan', 'sshKey'])->first();
        return Responder::result(['machine' => $machines]);
    }

    /**
     * @OA\Post(
     *      tags={"Machine"},
     *      path="/machines/createFromImage",
     *      summary="Create a new machine from an image",
     *      description="",
     *
     *
     * @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     * @OA\Parameter(
     *         name="plan_id",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     * @OA\Parameter(
     *         name="image_id",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     * @OA\Parameter(
     *         name="ssh_key_id",
     *         in="query",
     *         description="",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
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
        $project_id = request('project_id');
        $ssh_key_id = \request('ssh_key_id');

        if (!User::find($user_id)->projects->contains($project_id)) {
            return Responder::error('شما به این پروژه دسترسی ندارید');
        }

        try {
            $machine = Machine::createMachine(
                $name,
                $user_id,
                $plan_id,
                $image_id,
                $project_id,
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

            return Responder::success('عملیات ساخت سرور شروع شد');
        } catch (\Exception $exception) {
            Log::critical("Couldn't create server for user #" . Auth::id());
            Log::critical($exception);
            return Responder::error('ساخت سرور انجام نشد');
        }
    }

    function createFromSnapshot(CreateFromSnapshotRequest $request)
    {
        $user_id = Auth::id();
        $name = \request('name');
        $plan_id = \request('plan_id');
        $snapshot_id = \request('snapshot_id');
        $project_id = request('project_id');
        $ssh_key_id = \request('ssh_key_id');

        if (!User::find($user_id)->projects->contains($project_id)) {
            return Responder::error('شما به این پروژه دسترسی ندارید');
        }

        $snapshot = Snapshot::find($snapshot_id);
        $image_id = $snapshot->image_id;

        try {
            $machine = Machine::createMachine(
                $name,
                $user_id,
                $plan_id,
                $image_id,
                $project_id,
                $ssh_key_id
            );

            CreateMachineFromSnapshotJob::dispatch(
                $user_id,
                $name,
                $plan_id,
                $image_id,
                $ssh_key_id,
                $machine->id
            );

            return Responder::success('عملیات ساخت سرور شروع شد');
        } catch (\Exception $exception) {
            Log::critical("Couldn't create server for user #" . Auth::id());
            Log::critical($exception);
            return Responder::error('ساخت سرور انجام نشد');
        }
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
     *             type="integer"
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
    function console(GetConsoleRequest $request)
    {
        $machine = Machine::findorFail(\request('id'));
        $service = new MachineService();
        $link = $service->console($machine->remote_id);

        return Responder::result(['link' => $link]);
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
     *             type="integer"
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
    function powerOn(PowerOnRequest $request)
    {
        $machine = Machine::findorFail(\request('id'));
        $service = new MachineService();
        $service->powerOn($machine->remote_id);
        return Responder::success('سرور با موفقیت روشن شد');
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
     *             type="integer"
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
    function powerOff(PowerOffRequest $request)
    {
        $machine = Machine::findorFail(\request('id'));
        $service = new MachineService();
        $service->powerOff($machine->remote_id);
        return Responder::success('سرور با موفقیت خاموش شد');
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
        $machine = Machine::findorFail(\request('id'));

        $snapshot = Snapshot::newSnapshot(
            \request('name'),
            \request('id'),
            Auth::id(),
            $machine->image_id
        );

        TakeSnapshotJob::dispatch($machine->remote_id, \request('name'), $snapshot->id);

        return Responder::success('عملیات ساخت تصویر آنی شروع شد');
    }

    /**
     * @OA\Put(
     *      tags={"Machine"},
     *      path="/machines/{id}/resendInfo",
     *      summary="Resends information of this machine",
     *      description="",
     *
     * @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
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
    function resendInfo(ResendInfoRequest $request)
    {
        $machine = Machine::find(\request('id'));
        Auth::user()->notify(new SendMachineInfoNotification(Auth::user(), $machine));
        return Responder::success('اطلاعات سرور مجددا به ایمیل شما ارسال گردید');
    }

    /**
     * @OA\Post(
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
     *             type="integer"
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

        $machine->name = request('name');
        $machine->save();

        return Responder::success('نام سرور با موفقیت تغییر یافت');
    }

    /**
     * @OA\Post(
     *      tags={"Machine"},
     *      path="/machines/{id}/rescale",
     *      summary="Change the plan of a machine",
     *      description="",
     *
     * @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     * @OA\Parameter(
     *         name="plan_id",
     *         in="query",
     *         description="The new plan you want for the machine",
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
    function rescale(RescaleServerRequest $request)
    {
        $machine = Machine::find(request('id'));
        $plan = Plan::find(request('plan_id'));
        if (request('plan_id') == $machine->plan->id) {
            return Responder::error('پلن انتخاب شده همان پلن فعلی شما می باشد');
        }
        $machine->changePlan($plan);
        return Responder::success('پلن با موفقیت تغییر یافت');
    }

    /**
     * @OA\Delete(
     *      tags={"Machine"},
     *      path="/machines/{id}/remove",
     *      summary="Removes the machine ",
     *      description="",
     *
     * @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
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
    function remove(RemoveServerRequest $request)
    {
        $machine = Machine::findorFail(\request('id'));
        $service = new MachineService();
        $service->remove($machine->remote_id);

        $machine->billing->stopBilling();
        $machine->delete();

        return Responder::success('سرور با موفقیت حذف گردید');
    }
}
