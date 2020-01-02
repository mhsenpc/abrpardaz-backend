<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Snapshot\RenameSnapshotRequest;
use App\Http\Requests\Snapshots\OfMachineRequest;
use App\Models\Snapshot;
use App\Repositories\SnapshotRepository;
use App\Services\SnapshotService;
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

    function index()
    {
        return responder()->success(['list' => $this->repository->all()]);
    }

    function ofMachine(OfMachineRequest $request)
    {
        return responder()->success([
            'list' => $this->repository->findWhere([
                'machine_id' => \request('machine_id')
            ])->all()
        ]);
    }

    function rename(RenameSnapshotRequest $request)
    {
        $snapshot = $this->repository->find(request('id'));
        $service = new SnapshotService();
        $result = $service->rename(
            $snapshot->remote_id,
            \request('name')
        );

        if($result){
            $this->repository->update(['name'=>\request('name')],\request('id'));
            return responder()->success(['message' => "نام تصویر آنی با موفقیت تغییر کرد"]);
        }
        else{
            return responder()->error(500, "تغییر نام تصویر آنی انجام نشد");
        }

    }

    function remove(Request $request)
    {
        $service = new SnapshotService();
        $result = $service->remove(\request('id'));
        if($result){
            $this->repository->delete(\request('id'));
            return responder()->success(['message' => "تصویر آنی با موفقیت حذف شد"]);
        }
        else{
            return responder()->error(500, "حذف تصویر آنی امکانپذیر نمی باشد");
        }
    }
}
