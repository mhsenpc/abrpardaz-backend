<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\SSHKey\AddKeyRequest;
use App\Http\Requests\SSHKey\EditKeyRequest;
use App\Http\Requests\SSHKey\RemoveKeyRequest;
use App\Models\SshKey;
use Illuminate\Support\Facades\Auth;

class SSHKeyController extends BaseController
{
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
        return responder()->success(['list' => SshKey::all()]);
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
        SshKey::create([
            'name' => $request->input('name'),
            'content' => $request->input('content'),
            'user_id' => Auth::id()
        ]);
        return responder()->success(['message' => "کلید با موفقیت اضافه شد"]);
    }

    /**
     * @OA\Post(
     *      tags={"Ssh key"},
     *      path="/sshKeys/{id}/edit",
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
     *         in="path",
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
        SshKey::find(\request('id'))->update([
            'name' => \request('name'),
            'content' => \request('content')
        ]);
        return responder()->success(['message' => "کلید با موفقیت ویرایش شد"]);
    }

    /**
     * @OA\Delete(
     *      tags={"Ssh key"},
     *      path="/sshKeys/{id}/remove",
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
     *         in="path",
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
    function remove(RemoveKeyRequest $request)
    {
        SshKey::destroy(\request('id'));
        return responder()->success(['message' => "کلید با موفقیت حذف شد"]);
    }
}
