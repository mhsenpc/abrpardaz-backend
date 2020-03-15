<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\User\ActivateUserRequest;
use App\Http\Requests\User\AddUserRequest;
use App\Http\Requests\User\DeactivateUserRequest;
use App\Http\Requests\User\RemoveUserRequest;
use App\Http\Requests\User\ShowUserRequest;
use App\Services\Responder;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends BaseController
{
    /**
     * @OA\Get(
     *      tags={"User"},
     *      path="/users/list",
     *      summary="List all users",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="List of users"
     *     ),
     *
     *     )
     *
     */
    function index()
    {
        $users = User::with(['profile', 'userGroup'])->paginate(10);
        return Responder::result(['pagination' => $users]);
    }

    /**
     * @OA\Get(
     *      tags={"User"},
     *      path="/users/{id}/show",
     *      summary="Return a specific user",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="Return a specific user"
     *     ),
     *
     *     )
     *
     */
    function show(ShowUserRequest $request)
    {
        $item = User::find(request('id'));
        return Responder::result(['item' => $item]);
    }

    /**
     * @OA\Post(
     *      tags={"User"},
     *      path="/users/add",
     *      summary="Add a user",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *
     *     )
     *
     */
    function add(AddUserRequest $request)
    {
        User::newUser($request->input('email'), $request->input('password'))->activate();
        Log::info('new user created. user #' . Auth::id());
        return Responder::success("کاربر با موفقیت اضافه شد");
    }

    /**
     * @OA\Delete(
     *      tags={"User"},
     *      path="/users/{id}/remove",
     *      summary="Remove a user using its id",
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
    function remove(RemoveUserRequest $request)
    {
        User::destroy(\request('id'));
        Log::info('user removed. key #' . request('id') . ',user #' . Auth::id());
        return Responder::success("کاربر با موفقیت حذف شد");
    }

    /**
     * @OA\Put(
     *      tags={"User"},
     *      path="/users/{id}/activate",
     *      summary="Activates a user",
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
    function activate(ActivateUserRequest $request)
    {
        User::find(request('id'))->activate();
        return Responder::success("کاربر با موفقیت فعال شد");
    }

    /**
     * @OA\Put(
     *      tags={"User"},
     *      path="/users/{id}/deactivate",
     *      summary="Deactivate a user",
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
    function deactivate(DeactivateUserRequest $request)
    {
        User::find(request('id'))->deactivate();
        return Responder::success("کاربر با موفقیت غیرفعال شد");
    }
}
