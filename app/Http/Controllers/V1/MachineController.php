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
        $machines = Machine::with(['image', 'plan', 'sshKey'])->where('project_id', request('id'))->get();
        $service = new MachineService();
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
                $service = new MachineService();
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
            return Responder::error('شما به این پروژه دسترسی ندارید');
        }

        $user_limit = User::find($user_id)->userLimit;
        if ($user_limit) {
            if (Auth::user()->MachineCount >= $user_limit->max_machines) {
                return Responder::error('شما اجازه ساخت بیش از ' . $user_limit->max_machines . ' سرور را ندارید');
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
            return Responder::error('منبع سرور جدید مشخص نشده است');
        }

        $plan = Plan::findOrFail($plan_id);
        if ($plan->disk < $image->min_disk) {
            return Responder::error('فضای دیسک پلن انتخابی برای سیستم عامل انتخاب شده ناکافی است');
        }

        if ($plan->ram < $image->min_ram) {
            return Responder::error('فضای رم پلن انتخابی برای سیستم عامل انتخاب شده ناکافی است');
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

            return Responder::success('عملیات ساخت سرور شروع شد');
        } catch (\Exception $exception) {
            $machine->updateRemoteID('-1');
            $admins = User::role('Super Admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new CreateServerFailedAdminNotification($machine, Auth::user()->profile));
            }

            Auth::user()->notify(new CreateServerFailedNotification($machine, Auth::user()->profile));

            Log::critical("Couldn't create server " . $name . " from image #" . $image_id . " for user #" . Auth::id());
            Log::critical($exception);
            return Responder::error('ساخت سرور با شکست مواجه شد');
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

        Log::info('get console machine #' . $machine->id . ',user #' . Auth::id());
        ServerActivity::create([
            'machine_id' => request('id'),
            'user_id' => Auth::id(),
            'message' => 'دسترسی کنسول درخواست شد'
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
        $service = new MachineService();
        $service->powerOn($machine->remote_id);
        Log::info('power on machine #' . $machine->id . ',user #' . Auth::id());
        ServerActivity::create([
            'machine_id' => request('id'),
            'user_id' => Auth::id(),
            'message' => 'سرور روشن شد'
        ]);
        return Responder::success('سرور با موفقیت روشن شد');
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
        $service = new MachineService();
        $service->powerOff($machine->remote_id);
        Log::info('power off machine #' . $machine->id . ',user #' . Auth::id());
        ServerActivity::create([
            'machine_id' => request('id'),
            'user_id' => Auth::id(),
            'message' => 'سرور خاموش شد'
        ]);
        return Responder::success('سرور با موفقیت خاموش شد');
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
            $service = new MachineService();
            $service->softReboot($machine->remote_id);
            Log::info('soft reboot machine #' . $machine->id . ',user #' . Auth::id());
            ServerActivity::create([
                'machine_id' => request('id'),
                'user_id' => Auth::id(),
                'message' => 'سرور راه اندازی مجدد شد'
            ]);
            return Responder::success('سرور با موفقیت راه اندازی شد');
        } catch (\Exception $exception) {
            return Responder::error('امکان راه اندازی مجدد سرور در این لحظه وجود ندارد');
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
        $service = new MachineService();
        $service->hardReboot($machine->remote_id);
        Log::info('hard reboot machine #' . $machine->id . ',user #' . Auth::id());
        ServerActivity::create([
            'machine_id' => request('id'),
            'user_id' => Auth::id(),
            'message' => 'سرور راه اندازی مجدد شد'
        ]);
        return Responder::success('سرور با موفقیت راه اندازی شد');
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
            'message' => 'اطلاعات سرور مجددا به ایمیل شما ارسال گردید'
        ]);
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
        $machine = Machine::find(\request('id'));
        $service = new MachineService();
        $service->rename($machine->remote_id, \request('name') . '-' . \request('id'));

        $machine->name = request('name');
        $machine->save();

        Log::info('rename machine #' . $machine->id . ',user #' . Auth::id());
        ServerActivity::create([
            'machine_id' => request('id'),
            'user_id' => Auth::id(),
            'message' => 'نام سرور تغییر یافت'
        ]);
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
        $image = $machine->image;

        if (request('plan_id') == $machine->plan->id) {
            return Responder::error('پلن انتخاب شده همان پلن فعلی شما می باشد');
        }

        if ($plan->disk < $image->min_disk) {
            return Responder::error('فضای دیسک پلن انتخاب شده برای سیستم عامل شما ناکافی است');
        }

        if ($plan->ram < $image->min_ram) {
            return Responder::error('فضای رم پلن انتخاب شده برای سیستم عامل شما ناکافی است');
        }

        if ($plan->disk <$machine->plan->disk) {
            return Responder::error('اندازه دیسک نباید از دیسک فعلی کمتر باشد');
        }

        RescaleMachineJob::dispatch(request('id'), request('plan_id'), Auth::id());
        return Responder::success('عملیات تغییر پلن سرور شما شروع شد');
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
        $service = new MachineService();
        $rescue_image_id = ""; //TODO: which image should be used to rescue?
        $admin_pass = PasswordGeneratorService::generate();
        try {
            $service->attachImage($machine->remote_id, $rescue_image_id, $admin_pass);
            Log::info('rescue machine #' . $machine->id . ',user #' . Auth::id());
            ServerActivity::create([
                'machine_id' => request('id'),
                'user_id' => Auth::id(),
                'message' => 'سرور به حالت نجات منتقل شد'
            ]);
            return Responder::result([
                'message' => 'سرور به حالت نجات منتقل شد',
                'admin_pass' => $admin_pass
            ]);
        } catch (\Exception $exception) {
            Log::critical('Failed to rescue machine #' . request('id') . ',user #' . Auth::id());
            Log::critical($exception);
            return Responder::error('ورود با حالت نجات با شکست مواجه شد');
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
        $service = new MachineService();
        try {
            $service->detachImage($machine->remote_id);
            Log::info('Unrescue machine #' . $machine->id . ',user #' . Auth::id());
            ServerActivity::create([
                'machine_id' => request('id'),
                'user_id' => Auth::id(),
                'message' => 'سرور از حالت نجات خارج شد'
            ]);
            return Responder::success('سرور از حالت نجات خارج شد');
        } catch (\Exception $exception) {
            Log::critical('Failed to unrescue machine #' . request('id') . ',user #' . Auth::id());
            Log::critical($exception);
            return Responder::error('خروج سرور از حالت نجات با شکست مواجه شد');
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
        $service = new MachineService();
        try {
            $service->attachImage($machine->remote_id, $image, $admin_pass);
            Log::info('Attach image #' . request('image_id') . ', machine #' . $machine->id . ',user #' . Auth::id());
            ServerActivity::create([
                'machine_id' => request('id'),
                'user_id' => Auth::id(),
                'message' => 'تصویر با موفقیت به سرور وصل شد'
            ]);
            return Responder::result([
                'message' => 'تصویر با موفقیت به سرور وصل شد',
                'admin_pass' => $admin_pass
            ]);
        } catch (\Exception $exception) {
            Log::critical('Failed to attach image #' . request('image_id') . ', machine #' . request('id') . ',user #' . Auth::id());
            Log::critical($exception);
            return Responder::error('اتصال تصویر به سرور با شکست مواجه شد');
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
        $service = new MachineService();
        try {
            $service->detachImage($machine->remote_id);
            Log::info('Detach image from machine #' . $machine->id . ',user #' . Auth::id());
            ServerActivity::create([
                'machine_id' => request('id'),
                'user_id' => Auth::id(),
                'message' => 'عملیات قطع اتصال تصویر از سرور موفقیت آمیز بود'
            ]);
            return Responder::success('عملیات قطع اتصال تصویر از سرور موفقیت آمیز بود');
        } catch (\Exception $exception) {
            Log::critical('Failed to detach image from machine #' . request('id') . ',user #' . Auth::id());
            Log::critical($exception);
            return Responder::error('عملیات قطع اتصال تصویر از سرور با شکست مواجه شد');
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
            'message' => 'پشتیبان گیری خودکار سرور فعال گردید'
        ]);
        return Responder::success('نسخه پشتیبان با موفقیت فعال شد');
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
            'message' => 'پشتیبان گیری خودکار سرور غیرفعال گردید'
        ]);
        return Responder::success('نسخه پشتیبان با موفقیت غیرفعال شد');
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
            return Responder::error('فضای دیسک سرور شما برای سیستم عامل انتخاب شده ناکافی است');
        }

        if ($plan->ram < $image->min_ram) {
            return Responder::error('فضای رم سرور شما برای سیستم عامل انتخاب شده ناکافی است');
        }

        RebuildMachineJob::dispatch(request('id'), request('image_id'), Auth::id());
        return Responder::success('عملیات نصب مجدد سیستم عامل بر روی سرور شروع شد. لطفا تا پایان عملیات منتظر بمانید');
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
            $service = new MachineService();
            $service->resetPassword($machine->remote_id, $new_pass);
            $machine->password = $new_pass;
            $machine->save();
            Auth::user()->notify(new MachineResetPasswordNotification(Auth::user()->profile, $machine));
            Log::info('reset password machine #' . $machine->id . ', user #' . Auth::id());
            ServerActivity::create([
                'machine_id' => request('id'),
                'user_id' => Auth::id(),
                'message' => 'کلمه عبور مدیر سیستم سرور تغییر یافت'
            ]);
            return Responder::success('کلمه عبور سرور با موفقیت تغییر یافت و به پست الکترونیک شما ارسال گردید');
        } catch (\Exception $exception) {
            Log::error('failed to reset password machine #' . request('id') . ', user #' . Auth::id());
            return Responder::error('عملیات با شکست مواجه شد');
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
            return Responder::error("تا زمانیکه ساخت سرور به اتمام نرسیده است، امکان حذف آن وجود ندارد");
        }
        try {
            $machine->delete();
            if (!in_array($machine->remote_id, ['0', '-1'])) {
                $service = new MachineService();
                $service->remove($machine->remote_id);
                RemoveMachineBackupsJob::dispatch(\request('id'), Auth::id());
            }
            Log::info('remove machine #' . $machine->id . ',user #' . Auth::id());
        } catch (\Exception $exception) {
            Log::critical('failed to delete machine #' . \request('id'));
            Log::critical($exception);
        }

        return Responder::success('سرور با موفقیت حذف گردید');
    }
}
