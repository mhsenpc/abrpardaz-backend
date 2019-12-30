<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Server\CreateFromImageRequest;
use App\Http\Requests\Server\CreateFromSnapshotRequest;
use App\Http\Requests\Server\RenameServerRequest;
use App\Http\Requests\Server\TakeSnapshotRequest;
use App\Models\Machine;
use App\Repositories\MachineRepository;
use App\Services\MachineService;
use Illuminate\Http\Request;

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

    function index(){
        $machines = (new MachineRepository(new Machine()))->all();
        return responder()->success(['list'=>$machines]);
    }

    function createFromImage(CreateFromImageRequest $request){
        $service = new MachineService();
        $result = $service->createMachineFromImage();
        if($result){
            return responder()->success(['message'=>"سرور با موفقیت ساخته شد"]);
        }
        else{
            return responder()->error( 500,"ساخت سرور انجام نشد");
        }
    }

    function createFromSnapshot(CreateFromSnapshotRequest $request){
        return responder()->success(['message'=>"سرور با موفقیت ساخته شد"]);
    }

    function console(){
        $machine = Machine::findorFail(\request('id'));
        $service = new MachineService();
        $link = $service->console($machine->remote_id);

        return responder()->success(['link'=>$link]);
    }

    function powerOn(){
        $machine = Machine::findorFail(\request('id'));
        $service = new MachineService();
        $service->powerOn($machine->remote_id);
        return responder()->success(['message'=>"سرور با موفقیت روشن شد"]);
    }

    function powerOff(){
        $machine = Machine::findorFail(\request('id'));
        $service = new MachineService();
        $service->powerOf($machine->remote_id);
        return responder()->success(['message'=>"سرور با موفقیت خاموش شد"]);
    }

    function takeSnapshot(TakeSnapshotRequest $request){
        $machine = Machine::findorFail(\request('id'));
        $service = new MachineService();
        $service->takeSnapshot($machine->remote_id,\request('name'));

        return responder()->success(['message'=>"تصویر آنی با موفقیت ساخته شد"]);
    }

    function resendInfo(){
        return responder()->success(['message'=>"اطلاعات سرور مجددا به ایمیل شما ارسال گردید"]);
    }

    function rename(RenameServerRequest $request){
        $machine = Machine::findorFail(\request('id'));
        $service = new MachineService();
        $service->rename($machine->remote_id,\request('name'));

        return responder()->success(['message'=>"نام سرور با موفقیت تغییر یافت"]);
    }

    function remove(){
        $machine = Machine::findorFail(\request('id'));
        $service = new MachineService();
        $service->remove($machine->remote_id);

        return responder()->success(['message'=>"سرور با موفقیت حذف گردید"]);
    }
}
