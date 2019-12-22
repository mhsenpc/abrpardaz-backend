<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Server\CreateFromImageRequest;
use App\Http\Requests\Server\CreateFromSnapshotRequest;
use App\Http\Requests\Server\TakeSnapshotRequest;
use App\Repositories\MachineRepository;
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

    }

    function createFromImage(CreateFromImageRequest $request){
        return responder()->success("سرور با موفقیت ساخته شد");
    }

    function createFromSnapshot(CreateFromSnapshotRequest $request){
        return responder()->success("سرور با موفقیت ساخته شد");
    }

    function console(){

    }

    function powerOn(){
        return responder()->success("سرور با موفقیت روشن شد");
    }

    function powerOff(){
        return responder()->success("سرور با موفقیت خاموش شد");
    }

    function takeSnapshot(TakeSnapshotRequest $request){
        return responder()->success("تصویر آنی با موفقیت ساخته شد");
    }

    function resendInfo(){
        return responder()->success("اطلاعات سرور مجددا به ایمیل شما ارسال گردید");
    }

    function rename(){
        return responder()->success("نام سرور با موفقیت تغییر یافت");
    }

    function remove(){
        return responder()->success("سرور با موفقیت حذف گردید");
    }
}
