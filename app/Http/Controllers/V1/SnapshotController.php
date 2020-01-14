<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Snapshot\RemoveSnapshotRequest;
use App\Http\Requests\Snapshot\RenameSnapshotRequest;
use App\Http\Requests\Snapshot\OfMachineRequest;
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

    /**
     * @OA\Get(
     *      tags={"Snapshot"},
     *      path="/snapshots/list",
     *      summary="List all snapshots of your account",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="List of all your snapshots"
     *     ),
     *
     *     )
     *
     */
    function index()
    {
        return responder()->success(['list' => $this->repository->all()]);
    }

    /**
     * @OA\Get(
     *      tags={"Snapshot"},
     *      path="/snapshots/ofMachine",
     *      summary="List snapshots of a specific machine",
     *      description="",
     *
     * @OA\Parameter(
     *         name="machine_id",
     *         in="query",
     *         description="id of the machine your want its snapshots",
     *         required=true,
     *         @OA\Schema(
     *             type="int"
     *         )
     *     ),
     *
     * @OA\Response(
     *         response="default",
     *         description="List snapshots of a specific machine"
     *     ),
     *
     *     )
     *
     */
    function ofMachine(OfMachineRequest $request)
    {
        return responder()->success([
            'list' => $this->repository->findWhere([
                'machine_id' => \request('machine_id')
            ])->all()
        ]);
    }


    /**
     * @OA\Post(
     *      tags={"Snapshot"},
     *      path="/snapshots/{id}/rename",
     *      summary="Rename a snapshot",
     *      description="",
     *
     * @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id of the snapshot",
     *         required=true,
     *         @OA\Schema(
     *             type="int"
     *         )
     *     ),
     *
     * @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="new name of the snapshot",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     * @OA\Response(
     *         response="default",
     *         description="result"
     *     ),
     *
     *
     *     )
     *
     */
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

    /**
     * @OA\Delete(
     *      tags={"Snapshot"},
     *      path="/snapshots/{id}/remove",
     *      summary="Removes the snapshot",
     *      description="",
     *
     * @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id of the snapshot",
     *         required=true,
     *         @OA\Schema(
     *             type="int"
     *         )
     *     ),
     *
     * @OA\Response(
     *         response="default",
     *         description="result"
     *     ),
     *
     *
     *     )
     *
     */
    function remove(RemoveSnapshotRequest $request)
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
