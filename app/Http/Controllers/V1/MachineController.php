<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Server\ActivitiesRequest;
use App\Http\Requests\Server\AttachImageToServerRequest;
use App\Http\Requests\Server\CreateMachineRequest;
use App\Http\Requests\Server\DetachImageFromServerRequest;
use App\Http\Requests\Server\DetailsRequest;
use App\Http\Requests\Server\DisableBackupRequest;
use App\Http\Requests\Server\EnableBackupRequest;
use App\Http\Requests\Server\GetConsoleRequest;
use App\Http\Requests\Server\HardRebootRequest;
use App\Http\Requests\Server\ListRequest;
use App\Http\Requests\Server\MachineResetPasswordRequest;
use App\Http\Requests\Server\PowerOffRequest;
use App\Http\Requests\Server\PowerOnRequest;
use App\Http\Requests\Server\RebuildServerRequest;
use App\Http\Requests\Server\RemoveServerRequest;
use App\Http\Requests\Server\RenameServerRequest;
use App\Http\Requests\Server\RescaleServerRequest;
use App\Http\Requests\Server\RescueServerRequest;
use App\Http\Requests\Server\ResendInfoRequest;
use App\Http\Requests\Server\SoftRebootRequest;
use App\Http\Requests\Server\UnrescueServerRequest;
use App\Jobs\CreateMachineJob;
use App\Jobs\RebuildMachineJob;
use App\Jobs\RemoveMachineBackupsJob;
use App\Jobs\RescaleMachineJob;
use App\Models\Backup;
use App\Models\Image;
use App\Models\Machine;
use App\Models\Plan;
use App\Models\Project;
use App\Models\ServerActivity;
use App\Models\Snapshot;
use App\Notifications\CreateServerFailedAdminNotification;
use App\Notifications\CreateServerFailedNotification;
use App\Notifications\MachineResetPasswordNotification;
use App\Notifications\SendMachineInfoNotification;
use App\Services\MachineService;
use App\Services\PasswordGeneratorService;
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
     *      path="/machines/ofProject/{projectId}",
     *      summary="List Your machines",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="returns a list of machines"
     *     ),
     *
     * @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         description="id of the project you want to work on its machines",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     )
     *
     */
    function ofProject(ListRequest $request)
    {
        $project = Project::find(request('id'));
        $machines = Machine::with(['image', 'plan', 'sshKey'])->where('project_id', request('id'))->get();
        $service = new MachineService(Auth::user()->remote_user_id,Auth::user()->remote_password, $project->remote_id);
        foreach ($machines as &$machine) {
            switch ($machine->remote_id) {
                case '-1':
                    $machine->status = 'ERROR';
                    break;
                case '0':
                    $machine->status = 'creating';
                    break;
                default:
                    try {
                        $server = $service->getServer($machine->remote_id);
                        $machine->status = $server->status;

                    } catch (\Exception $exception) {
                        $machine->status = 'failed';
                    }
                    break;
            }
        }
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
     *         description=""
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
        $machine = Machine::where('id', request('id'))->with(['image', 'plan', 'sshKey'])->first();
        try {
            if ($machine->remote_id === "0") {
                $machine->powerState = 0;
                $machine->status = 'CREATING';
            } else if ($machine->remote_id === "-1") {
                $machine->powerState = 0;
                $machine->status = 'ERROR';
            } else {
                $service = new MachineService(Auth::user()->remote_user_id,Auth::user()->remote_password, $machine->project->remote_id);
                $server = $service->getServer($machine->remote_id);
                $machine->powerState = $server->powerState;
                $machine->status = $server->status;
            }
        } catch (\Exception $exception) {
            $machine->powerState = 0;
            $machine->status = 'ERROR';
        }

        return Responder::result(['machine' => $machine]);
    }

    /**
     * @OA\Get(
     *      tags={"Machine"},
     *      path="/machines/{id}/activities",
     *      summary="Get acitivities of a server",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description=""
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
    function activities(ActivitiesRequest $request)
    {
        $activities = ServerActivity::where('machine_id', request('id'))->get();
        return Responder::result(['list' => $activities]);
    }

    /**
     * @OA\Post(
     *      tags={"Machine"},
     *      path="/machines/create",
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
     * @OA\Parameter(
     *         name="project_id",
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
    function create(CreateMachineRequest $request)
    {
        $user_id = Auth::id();
        $name = \request('name');
        $plan_id = \request('plan_id');
        $image_id = \request('image_id');
        $snapshot_id = \request('snapshot_id');
        $backup_id = \request('backup_id');
        $project_id = request('project_id');
        $ssh_key_id = \request('ssh_key_id');
        $auto_backup = false;
        if (!empty(\request('auto_backup'))) {
            $auto_backup = true;
        }

        if (!User::find($user_id)->projects->contains($project_id)) {
            return Responder::error('?????? ???? ?????? ?????????? ???????????? ????????????');
        }

        $user_limit = User::find($user_id)->userLimit;
        if ($user_limit) {
            if (Auth::user()->MachineCount >= $user_limit->max_machines) {
                return Responder::error('?????? ?????????? ???????? ?????? ???? ' . $user_limit->max_machines . ' ???????? ???? ????????????');
            }
        }

        $image = null;
        $source_remote_id = "source_remote_id";
        if (!empty($snapshot_id)) {
            $snapshot = Snapshot::findOrFail($snapshot_id);
            $image = $snapshot->image;
            $source_remote_id = $snapshot->remote_id;
        } else if (!empty($backup_id)) {
            $backup = Backup::findOrFail($backup_id);
            $image = $backup->image;
            $source_remote_id = $backup->remote_id;
        } else if (!empty($image_id)) {
            $image = Image::findOrFail($image_id);
            $source_remote_id = $image->remote_id;
        } else {
            return Responder::error('???????? ???????? ???????? ???????? ???????? ??????');
        }

        $plan = Plan::findOrFail($plan_id);
        if ($plan->disk < $image->min_disk) {
            return Responder::error('???????? ???????? ?????? ?????????????? ???????? ?????????? ???????? ???????????? ?????? ???????????? ??????');
        }

        if ($plan->ram < $image->min_ram) {
            return Responder::error('???????? ???? ?????? ?????????????? ???????? ?????????? ???????? ???????????? ?????? ???????????? ??????');
        }

        $password = PasswordGeneratorService::generate();

        $machine = Machine::createMachine(
            $name,
            $password,
            $user_id,
            $plan_id,
            $image->id,
            $project_id,
            $auto_backup,
            $ssh_key_id
        );

        try {
            CreateMachineJob::dispatch(
                $user_id,
                $name,
                $plan_id,
                $source_remote_id,
                $machine->id,
                $ssh_key_id
            );

            return Responder::success('???????????? ???????? ???????? ???????? ????');
        } catch (\Exception $exception) {
            $machine->updateRemoteID('-1');
            $admins = User::role('Super Admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new CreateServerFailedAdminNotification($machine, Auth::user()->profile));
            }

            Auth::user()->notify(new CreateServerFailedNotification($machine, Auth::user()->profile));

            Log::critical("Couldn't create server " . $name . " from image #" . $image_id . " for user #" . Auth::id());
            Log::critical($exception);
            return Responder::error('???????? ???????? ???? ???????? ?????????? ????');
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
        $service = new MachineService(Auth::user()->remote_user_id,Auth::user()->remote_password, $machine->project->remote_id);
        $link = $service->console($machine->remote_id);

        Log::info('get console machine #' . $machine->id . ',user #' . Auth::id());
        ServerActivity::create([
            'machine_id' => request('id'),
            'user_id' => Auth::id(),
            'message' => '???????????? ?????????? ?????????????? ????'
        ]);
        return Responder::result(['link' => $link]);
    }

    /**
     * @OA\Put(
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
        $service = new MachineService(Auth::user()->remote_user_id,Auth::user()->remote_password, $machine->project->remote_id);
        $service->powerOn($machine->remote_id);
        Log::info('power on machine #' . $machine->id . ',user #' . Auth::id());
        ServerActivity::create([
            'machine_id' => request('id'),
            'user_id' => Auth::id(),
            'message' => '???????? ???????? ????'
        ]);
        return Responder::success('???????? ???? ???????????? ???????? ????');
    }

    /**
     * @OA\Put(
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
        $service = new MachineService(Auth::user()->remote_user_id,Auth::user()->remote_password, $machine->project->remote_id);
        $service->powerOff($machine->remote_id);
        Log::info('power off machine #' . $machine->id . ',user #' . Auth::id());
        ServerActivity::create([
            'machine_id' => request('id'),
            'user_id' => Auth::id(),
            'message' => '???????? ?????????? ????'
        ]);
        return Responder::success('???????? ???? ???????????? ?????????? ????');
    }

    /**
     * @OA\Put(
     *      tags={"Machine"},
     *      path="/machines/{id}/softReboot",
     *      summary="soft reboot machine ",
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
    function softReboot(SoftRebootRequest $request)
    {
        $machine = Machine::findorFail(\request('id'));
        try {
            $service = new MachineService(Auth::user()->remote_user_id,Auth::user()->remote_password, $machine->project->remote_id);
            $service->softReboot($machine->remote_id);
            Log::info('soft reboot machine #' . $machine->id . ',user #' . Auth::id());
            ServerActivity::create([
                'machine_id' => request('id'),
                'user_id' => Auth::id(),
                'message' => '???????? ?????? ???????????? ???????? ????'
            ]);
            return Responder::success('???????? ???? ???????????? ?????? ???????????? ????');
        } catch (\Exception $exception) {
            return Responder::error('?????????? ?????? ???????????? ???????? ???????? ???? ?????? ???????? ???????? ??????????');
        }
    }

    /**
     * @OA\Put(
     *      tags={"Machine"},
     *      path="/machines/{id}/hardReboot",
     *      summary="hard reboot machine ",
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
    function hardReboot(HardRebootRequest $request)
    {
        $machine = Machine::findorFail(\request('id'));
        $service = new MachineService(Auth::user()->remote_user_id,Auth::user()->remote_password, $machine->project->remote_id);
        $service->hardReboot($machine->remote_id);
        Log::info('hard reboot machine #' . $machine->id . ',user #' . Auth::id());
        ServerActivity::create([
            'machine_id' => request('id'),
            'user_id' => Auth::id(),
            'message' => '???????? ?????? ???????????? ???????? ????'
        ]);
        return Responder::success('???????? ???? ???????????? ?????? ???????????? ????');
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
        Log::info('resend info machine #' . $machine->id . ',user #' . Auth::id());
        ServerActivity::create([
            'machine_id' => request('id'),
            'user_id' => Auth::id(),
            'message' => '?????????????? ???????? ?????????? ???? ?????????? ?????? ?????????? ??????????'
        ]);
        return Responder::success('?????????????? ???????? ?????????? ???? ?????????? ?????? ?????????? ??????????');
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
        $machine = Machine::find(\request('id'));
        $service = new MachineService(Auth::user()->remote_user_id,Auth::user()->remote_password, $machine->project->remote_id);
        $service->rename($machine->remote_id, \request('name') . '-' . \request('id'));

        $machine->name = request('name');
        $machine->save();

        Log::info('rename machine #' . $machine->id . ',user #' . Auth::id());
        ServerActivity::create([
            'machine_id' => request('id'),
            'user_id' => Auth::id(),
            'message' => '?????? ???????? ?????????? ????????'
        ]);
        return Responder::success('?????? ???????? ???? ???????????? ?????????? ????????');
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
            return Responder::error('?????? ???????????? ?????? ???????? ?????? ???????? ?????? ???? ????????');
        }

        if ($plan->disk <$machine->plan->disk) {
            return Responder::error('???????????? ???????? ?????????? ???? ???????? ???????? ???????? ????????');
        }

        RescaleMachineJob::dispatch(request('id'), request('plan_id'), Auth::id());
        return Responder::success('???????????? ?????????? ?????? ???????? ?????? ???????? ????');
    }

    /**
     * @OA\Put(
     *      tags={"Machine"},
     *      path="/machines/{id}/rescue",
     *      summary="Put on a machine on rescue mode",
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
    function rescue(RescueServerRequest $request)
    {
        $machine = Machine::find(request('id'));
        $service = new MachineService(Auth::user()->remote_user_id,Auth::user()->remote_password, $machine->project->remote_id);
        $rescue_image_id = ""; //TODO: which image should be used to rescue?
        $admin_pass = PasswordGeneratorService::generate();
        try {
            $service->attachImage($machine->remote_id, $rescue_image_id, $admin_pass);
            Log::info('rescue machine #' . $machine->id . ',user #' . Auth::id());
            ServerActivity::create([
                'machine_id' => request('id'),
                'user_id' => Auth::id(),
                'message' => '???????? ???? ???????? ???????? ?????????? ????'
            ]);
            return Responder::result([
                'message' => '???????? ???? ???????? ???????? ?????????? ????',
                'admin_pass' => $admin_pass
            ]);
        } catch (\Exception $exception) {
            Log::critical('Failed to rescue machine #' . request('id') . ',user #' . Auth::id());
            Log::critical($exception);
            return Responder::error('???????? ???? ???????? ???????? ???? ???????? ?????????? ????');
        }
    }

    /**
     * @OA\Put(
     *      tags={"Machine"},
     *      path="/machines/{id}/unrescue",
     *      summary="Put off a machine from rescue mode",
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
    function unrescue(UnrescueServerRequest $request)
    {
        $machine = Machine::find(request('id'));
        $service = new MachineService(Auth::user()->remote_user_id,Auth::user()->remote_password, $machine->project->remote_id);
        try {
            $service->detachImage($machine->remote_id);
            Log::info('Unrescue machine #' . $machine->id . ',user #' . Auth::id());
            ServerActivity::create([
                'machine_id' => request('id'),
                'user_id' => Auth::id(),
                'message' => '???????? ???? ???????? ???????? ???????? ????'
            ]);
            return Responder::success('???????? ???? ???????? ???????? ???????? ????');
        } catch (\Exception $exception) {
            Log::critical('Failed to unrescue machine #' . request('id') . ',user #' . Auth::id());
            Log::critical($exception);
            return Responder::error('???????? ???????? ???? ???????? ???????? ???? ???????? ?????????? ????');
        }
    }

    /**
     * @OA\Post(
     *      tags={"Machine"},
     *      path="/machines/{id}/attachImage",
     *      summary="Attach an image to the server",
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
     *         name="image_id",
     *         in="query",
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
    function attachImage(AttachImageToServerRequest $request)
    {
        $machine = Machine::find(request('id'));
        $image = Image::find(request('image_id'));
        $admin_pass = PasswordGeneratorService::generate();
        $service = new MachineService(Auth::user()->remote_user_id,Auth::user()->remote_password, $machine->project->remote_id);
        try {
            $service->attachImage($machine->remote_id, $image, $admin_pass);
            Log::info('Attach image #' . request('image_id') . ', machine #' . $machine->id . ',user #' . Auth::id());
            ServerActivity::create([
                'machine_id' => request('id'),
                'user_id' => Auth::id(),
                'message' => '?????????? ???? ???????????? ???? ???????? ?????? ????'
            ]);
            return Responder::result([
                'message' => '?????????? ???? ???????????? ???? ???????? ?????? ????',
                'admin_pass' => $admin_pass
            ]);
        } catch (\Exception $exception) {
            Log::critical('Failed to attach image #' . request('image_id') . ', machine #' . request('id') . ',user #' . Auth::id());
            Log::critical($exception);
            return Responder::error('?????????? ?????????? ???? ???????? ???? ???????? ?????????? ????');
        }
    }

    /**
     * @OA\Put(
     *      tags={"Machine"},
     *      path="/machines/{id}/detachImage",
     *      summary="Detach the image from the server",
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
    function detachImage(DetachImageFromServerRequest $request)
    {
        $machine = Machine::find(request('id'));
        $service = new MachineService(Auth::user()->remote_user_id,Auth::user()->remote_password, $machine->project->remote_id);
        try {
            $service->detachImage($machine->remote_id);
            Log::info('Detach image from machine #' . $machine->id . ',user #' . Auth::id());
            ServerActivity::create([
                'machine_id' => request('id'),
                'user_id' => Auth::id(),
                'message' => '???????????? ?????? ?????????? ?????????? ???? ???????? ???????????? ???????? ??????'
            ]);
            return Responder::success('???????????? ?????? ?????????? ?????????? ???? ???????? ???????????? ???????? ??????');
        } catch (\Exception $exception) {
            Log::critical('Failed to detach image from machine #' . request('id') . ',user #' . Auth::id());
            Log::critical($exception);
            return Responder::error('???????????? ?????? ?????????? ?????????? ???? ???????? ???? ???????? ?????????? ????');
        }
    }

    /**
     * @OA\Put(
     *      tags={"Machine"},
     *      path="/machines/{id}/enableBackup",
     *      summary="Enables authomatic backups for machine",
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
     *
     * @OA\Response(
     *         response="default",
     *         description=""
     *     ),
     *     )
     *
     */
    function enableBackup(EnableBackupRequest $request)
    {
        $machine = Machine::find(request('id'));
        $machine->enableBackup();
        Log::info('backup enabled for machine #' . $machine->id . ',user #' . Auth::id());
        ServerActivity::create([
            'machine_id' => request('id'),
            'user_id' => Auth::id(),
            'message' => '?????????????? ???????? ???????????? ???????? ???????? ??????????'
        ]);
        return Responder::success('???????? ?????????????? ???? ???????????? ???????? ????');
    }

    /**
     * @OA\Put(
     *      tags={"Machine"},
     *      path="/machines/{id}/disableBackup",
     *      summary="Disables authomatic backups for machine",
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
     *
     * @OA\Response(
     *         response="default",
     *         description=""
     *     ),
     *     )
     *
     */
    function disableBackup(DisableBackupRequest $request)
    {
        $machine = Machine::find(request('id'));
        $machine->disableBackup();
        Log::info('backup disabled for machine #' . $machine->id . ',user #' . Auth::id());
        ServerActivity::create([
            'machine_id' => request('id'),
            'user_id' => Auth::id(),
            'message' => '?????????????? ???????? ???????????? ???????? ?????????????? ??????????'
        ]);
        return Responder::success('???????? ?????????????? ???? ???????????? ?????????????? ????');
    }

    /**
     * @OA\Post(
     *      tags={"Machine"},
     *      path="/machines/{id}/rebuild",
     *      summary="Writes a new image on instance",
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
     *         name="image_id",
     *         in="query",
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
    function rebuild(RebuildServerRequest $request)
    {
        $machine = Machine::find(request('id'));
        $plan = $machine->plan;
        $image = Image::find(request('image_id'));
        if ($plan->disk < $image->min_disk) {
            return Responder::error('???????? ???????? ???????? ?????? ???????? ?????????? ???????? ???????????? ?????? ???????????? ??????');
        }

        if ($plan->ram < $image->min_ram) {
            return Responder::error('???????? ???? ???????? ?????? ???????? ?????????? ???????? ???????????? ?????? ???????????? ??????');
        }

        RebuildMachineJob::dispatch(request('id'), request('image_id'), Auth::id());
        return Responder::success('???????????? ?????? ???????? ?????????? ???????? ???? ?????? ???????? ???????? ????. ???????? ???? ?????????? ???????????? ?????????? ????????????');
    }

    /**
     * @OA\Put(
     *      tags={"Machine"},
     *      path="/machines/{id}/resetPassword",
     *      summary="change password of the machine to a random password",
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
    function resetPassword(MachineResetPasswordRequest $request)
    {
        $machine = Machine::find(request('id'));
        $new_pass = PasswordGeneratorService::generate();
        try {
            $service = new MachineService(Auth::user()->remote_user_id,Auth::user()->remote_password, $machine->project->remote_id);
            $service->resetPassword($machine->remote_id, $new_pass);
            $machine->password = $new_pass;
            $machine->save();
            Auth::user()->notify(new MachineResetPasswordNotification(Auth::user()->profile, $machine));
            Log::info('reset password machine #' . $machine->id . ', user #' . Auth::id());
            ServerActivity::create([
                'machine_id' => request('id'),
                'user_id' => Auth::id(),
                'message' => '???????? ???????? ???????? ?????????? ???????? ?????????? ????????'
            ]);
            return Responder::success('???????? ???????? ???????? ???? ???????????? ?????????? ???????? ?? ???? ?????? ?????????????????? ?????? ?????????? ??????????');
        } catch (\Exception $exception) {
            Log::error('failed to reset password machine #' . request('id') . ', user #' . Auth::id());
            return Responder::error('???????????? ???? ???????? ?????????? ????');
        }
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
        if ($machine->remote_id === "0") {
            return Responder::error("???? ?????????????? ???????? ???????? ???? ?????????? ???????????? ???????? ?????????? ?????? ???? ???????? ??????????");
        }
        try {
            $machine->delete();
            if (!in_array($machine->remote_id, ['0', '-1'])) {
                $service = new MachineService(Auth::user()->remote_user_id,Auth::user()->remote_password, $machine->project->remote_id);
                $service->remove($machine->remote_id);
                RemoveMachineBackupsJob::dispatch(\request('id'), Auth::id());
            }
            Log::info('remove machine #' . $machine->id . ',user #' . Auth::id());
        } catch (\Exception $exception) {
            Log::critical('failed to delete machine #' . \request('id'));
            Log::critical($exception);
        }

        return Responder::success('???????? ???? ???????????? ?????? ??????????');
    }
}
