<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\User\ActivateUserRequest;
use App\Http\Requests\User\AddUserRequest;
use App\Http\Requests\User\ChangeUserGroupRequest;
use App\Http\Requests\User\DeactivateUserRequest;
use App\Http\Requests\User\RemoveUserRequest;
use App\Http\Requests\User\ShowUserRequest;
use App\Services\Responder;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends BaseController
{
    function index()
    {
        $users = User::with(['profile', 'userGroup'])->paginate(10);
        return Responder::result(['pagination' => $users]);
    }

    function show(ShowUserRequest $request)
    {
        $item = User::with(['profile'])->find(request('id'));
        if(!empty($item->profile->national_card_front))
            $item->profile->national_card_front = $path = asset('storage/'. $item->profile->national_card_front);

        if(!empty($item->profile->national_card_back))
            $item->profile->national_card_back = $path = asset('storage/'. $item->profile->national_card_back);

        if(!empty($item->profile->birth_certificate))
            $item->profile->birth_certificate = $path = asset('storage/'. $item->profile->birth_certificate);
        return Responder::result(['item' => $item]);
    }

    function add(AddUserRequest $request)
    {
        User::newUser($request->input('email'), $request->input('password'))->activate();
        Log::info('new user created. user #' . Auth::id());
        return Responder::success("کاربر با موفقیت اضافه شد");
    }

    function changeUserGroup(ChangeUserGroupRequest $request){
        User::find(request('id'))->changeUserGroup(request('user_group_id'));
        return Responder::success("گروه کاربری با موفقیت تغییر یافت");
    }

    function remove(RemoveUserRequest $request)
    {
        User::destroy(\request('id'));
        Log::info('user removed. key #' . request('id') . ',user #' . Auth::id());
        return Responder::success("کاربر با موفقیت حذف شد");
    }

    function activate(ActivateUserRequest $request)
    {
        User::find(request('id'))->activate();
        return Responder::success("کاربر با موفقیت فعال شد");
    }

    function deactivate(DeactivateUserRequest $request)
    {
        User::find(request('id'))->deactivate();
        return Responder::success("کاربر با موفقیت غیرفعال شد");
    }
}
