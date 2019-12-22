<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Snapshot\RenameSnapshotRequest;
use App\Repositories\SnapshotRepository;
use Illuminate\Http\Request;

class SnapshotController extends BaseController
{
    /**
     * @var SnapshotRepository
     */
    protected $repository;

    public function __construct(SnapshotRepository $repository)
    {
        $this->repository = $repository;
    }

    function index(){
        return responder()->success(['list' => $this->repository->all()]);
    }

    function rename(RenameSnapshotRequest $request){
        //do some stuff
        return responder()->success("نام تصویر آنی با موفقیت تغییر کرد");
    }

    function remove(Request $request){
        //do some stuff
        $this->repository->deleteById($request->input('id'));
        return responder()->success("تصویر آنی با موفقیت حذف شد");
    }
}
