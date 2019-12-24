<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyRequest;
use App\Models\Profile;
use App\Notifications\RegisterUserNotification;
use App\Repositories\ProfileRepository;
use App\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends BaseController
{
    /**
     * @var UserRepository
     */
    protected $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    function register(RegisterRequest $request)
    {
        $user = $this->repository->newUser(
            $request->input('email'),
            Hash::make($request->input('password'))
        );

        $user->notify(new RegisterUserNotification());

        return responder()->success(['message'=>'لینک فعال سازی به ایمیل شما ارسال گردید']);
    }

    function login(LoginRequest $request)
    {

    }

    function forgetPassword(ForgetPasswordRequest $request)
    {

    }

    function verify(VerifyRequest $request)
    {

    }

    function logout(LogoutRequest $request)
    {
        Auth::logout();
        return responder()->success(['message'=>'شما با موفقیت خارج شدید']);
    }
}
