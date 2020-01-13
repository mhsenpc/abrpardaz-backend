<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\AddIDParameterTrait;
use App\Http\Requests\Ticket\CloseTicketRequest;
use App\Http\Requests\Ticket\NewReplyRequest;
use App\Http\Requests\Ticket\NewTicketRequest;
use App\Http\Requests\Ticket\ShowTicketRequest;
use App\Http\Requests\Volume\RemoveVolumeRequest;
use App\Http\Requests\Volume\RenameVolumeRequest;
use App\Models\Category;
use App\Models\Reply;
use App\Models\Ticket;
use App\Notifications\NewTicketNotification;
use App\Notifications\TicketReplyNotification;
use App\Notifications\TicketStatusNotification;
use App\Repositories\SSHKeyRepository;
use App\Repositories\TicketRepository;
use App\Repositories\VolumeRepository;
use App\Services\VolumeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VolumeController extends BaseController
{
    /**
     * @var VolumeRepository
     */
    protected $repository;

    public function __construct(VolumeRepository $repository)
    {
        $this->repository = $repository;
    }

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
        $volumes = $this->repository->all();
        return responder()->success(['list' => $volumes]);
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
        $volume = $this->repository->find(\request('id'));
        $this->repository->update([
            'name' => \request('name')
        ], \request('id'));
    }

    /**
     * @OA\Post(
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
        $volume = $this->repository->find(\request('id'));
        $service = new VolumeService();
        $service->remove($volume->remote_id);
        $this->repository->delete(\request('id'));
    }
}
