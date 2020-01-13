<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\SSHKey\AddKeyRequest;
use App\Http\Requests\SSHKey\EditKeyRequest;
use App\Repositories\SSHKeyRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SSHKeyController extends BaseController
{
    /**
     * @var SSHKeyRepository
     */
    protected $repository;

    public function __construct(SSHKeyRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *      tags={"Ssh key"},
     *      path="/sshKeys/list",
     *      summary="List all ssh keys that belong to the current user",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="List of ssh keys"
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
     * @OA\Post(
     *      tags={"Ssh key"},
     *      path="/sshKeys/add",
     *      summary="Add a ssh key to your profile",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
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
     *     @OA\Parameter(
     *         name="content",
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
    function add(AddKeyRequest $request)
    {
        $this->repository->create([
            'name'=> $request->input('name'),
            'content' => $request->input('content'),
            'user_id' => Auth::id()
        ]);
        return responder()->success(['message'=>"کلید با موفقیت اضافه شد"]);
    }

    /**
     * @OA\Post(
     *      tags={"Ssh key"},
     *      path="/sshKeys/edit",
     *      summary="Edit a ssh key using its id",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
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
     *     @OA\Parameter(
     *         name="content",
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
    function edit(EditKeyRequest $request)
    {
        $this->repository->find($request->input('id'))->update([
            'name'=> $request->input('name'),
            'content' => $request->input('content')
        ]);
        return responder()->success(['message'=>"کلید با موفقیت ویرایش شد"]);
    }

    /**
     * @OA\Delete(
     *      tags={"Ssh key"},
     *      path="/sshKeys/remove",
     *      summary="Remove a ssh key using it",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="id",
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
    function remove(Request $request)
    {
        $this->repository->delete(\request('id'));
        return responder()->success(['message'=>"کلید با موفقیت حذف شد"]);
    }
}
