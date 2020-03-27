<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\User\ChangeRoleRequest;
use App\Http\Requests\User\ManualVerifyEmailRequest;
use App\Http\Requests\User\UnsuspendUserRequest;
use App\Http\Requests\User\AddUserRequest;
use App\Http\Requests\User\ChangeUserLimitRequest;
use App\Http\Requests\User\SuspendUserRequest;
use App\Http\Requests\User\RemoveUserRequest;
use App\Http\Requests\User\ShowUserRequest;
use App\Services\Responder;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends BaseController
{
    function __construct()
    {
        $this->middleware('permission:List Users|Add Users|Change User Limit|Change User Role|Remove Users|Verify Users|Suspend Users|Unsuspend Users', ['only' => ['index','show']]);
        $this->middleware('permission:Add Users', ['only' => ['add']]);
        $this->middleware('permission:Change User Limit', ['only' => ['changeUserLimit']]);
        $this->middleware('permission:Change User Role', ['only' => ['changeRole']]);
        $this->middleware('permission:Remove Users', ['only' => ['remove']]);
        $this->middleware('permission:Verify Users', ['only' => ['verifyEmail']]);
        $this->middleware('permission:Suspend Users', ['only' => ['suspend']]);
        $this->middleware('permission:Unsuspend Users', ['only' => ['unsuspend']]);
    }

    function index()
    {
        $users = User::with(['profile', 'userLimit','roles'])->paginate(10);
        return Responder::result(['pagination' => $users]);
    }

    function show(ShowUserRequest $request)
    {
        $item = User::with(['profile','roles'])->find(request('id'));
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
        User::newUser($request->input('email'), $request->input('password'))->verifyEmail();
        Log::info('new user created. user #' . Auth::id());
        return Responder::success("کاربر با موفقیت اضافه شد");
    }

    function changeUserLimit(ChangeUserLimitRequest $request){
        User::find(request('id'))->changeUserLimit(request('user_group_id'));
        return Responder::success("گروه کاربری با موفقیت تغییر یافت");
    }

    function remove(RemoveUserRequest $request)
    {
        User::destroy(\request('id'));
        Log::info('user removed. key #' . request('id') . ',user #' . Auth::id());
        return Responder::success("کاربر با موفقیت حذف شد");
    }

    function unsuspend(UnsuspendUserRequest $request)
    {
        User::find(request('id'))->unsuspend();
        return Responder::success("کاربر با موفقیت از مسدودیت خارج شد");
    }

    function suspend(SuspendUserRequest $request)
    {
        User::find(request('id'))->suspend();
        return Responder::success("کاربر با موفقیت مسدود شد");
    }

    function verifyEmail(ManualVerifyEmailRequest $request)
    {
        User::find(request('id'))->verifyEmail();
        return Responder::success("ایمیل کاربر با موفقیت تایید شد");
    }

    function changeRole(ChangeRoleRequest $request){
        $user = User::find(request('id'));
        $user->syncRoles([request('role_id')]);
        foreach($user->tokens as $token) {
            $token->revoke();
        }
        return Responder::success("نقش کاربر با موفقیت تغییر یافت");
    }
}
